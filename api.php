<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', '28800');
$isHttps = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_name('sbapp_session');
session_start();

define('KEY_REPORT', 'spravka_control_v154');
define('KEY_DYN',    'spravka_control_v154__dyn');
define('KEY_LISTS',  'spravka_control_v154__lists');
define('KEY_ARCH',   'spravka_control_v154__archive');

function reply(int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function input_json(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 12 * 1024 * 1024) {
        reply(413, ['ok' => false, 'error' => 'Слишком большой запрос.']);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        reply(400, ['ok' => false, 'error' => 'Некорректный JSON запроса.']);
    }
    return $data;
}

function csrf_token(): string {
    if (empty($_SESSION['sb_csrf'])) {
        $_SESSION['sb_csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['sb_csrf'];
}

function check_csrf(array $body): void {
    if (!hash_equals(csrf_token(), (string)($body['csrf'] ?? ''))) {
        reply(403, ['ok' => false, 'error' => 'Недействительный CSRF-токен. Обновите страницу и попробуйте снова.']);
    }
}

function physical_key(string $logical): string {
    // Черновик и динамические строки изолированы по PHP-сессии браузера.
    if ($logical === KEY_REPORT || $logical === KEY_DYN) {
        return $logical . '__session_' . substr(hash('sha256', session_id()), 0, 32);
    }
    return $logical;
}

function archive_record_key(array $record): string {
    $data = isset($record['data']) && is_array($record['data']) ? $record['data'] : [];
    $date = (string)($data['b0_date'] ?? '');
    $object = (string)($data['b0_to'] ?? '');
    $ownerId = (int)($record['owner_user_id'] ?? 0);
    if ($date !== '' && $object !== '') {
        $identity = $date . "\0" . $object;
        if ($ownerId > 0) $identity .= "\0" . $ownerId;
        return 'r:' . hash('sha256', $identity);
    }
    return 'd:' . hash('sha256', (string)($record['t'] ?? 0) . "\0" . (string)($record['fio'] ?? ''));
}

function merge_history(array $incoming, ?array $existing): array {
    if (!$existing) return $incoming;
    $newTime = (int)($incoming['t'] ?? 0);
    $oldTime = (int)($existing['t'] ?? 0);
    if ($oldTime > $newTime) return $existing;

    $history = [];
    if ($newTime !== $oldTime && $oldTime > 0) {
        $history[] = [
            't' => $existing['t'] ?? 0,
            'label' => $existing['label'] ?? '',
            'fio' => $existing['fio'] ?? '',
            'done' => $existing['done'] ?? 0,
            'total' => $existing['total'] ?? 0,
            'data' => $existing['data'] ?? [],
            'dyn' => $existing['dyn'] ?? [],
        ];
    }
    foreach ([$incoming['hist'] ?? [], $existing['hist'] ?? []] as $list) {
        if (!is_array($list)) continue;
        foreach ($list as $item) {
            if (is_array($item)) $history[] = $item;
        }
    }
    $seen = [];
    $clean = [];
    foreach ($history as $item) {
        $stamp = (string)($item['t'] ?? '');
        if ($stamp === '' || isset($seen[$stamp])) continue;
        $seen[$stamp] = true;
        $clean[] = $item;
    }
    usort($clean, function($a, $b) {
        return (int)($b['t'] ?? 0) <=> (int)($a['t'] ?? 0);
    });
    $incoming['hist'] = array_slice($clean, 0, 5);
    return $incoming;
}

function ensure_archive_columns(PDO $pdo): void {
    $have = $pdo->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sb_archive_records'")
        ->fetchAll(PDO::FETCH_COLUMN);
    $have = $have ?: [];
    $cols = [
        'owner_user_id' => "ALTER TABLE sb_archive_records ADD COLUMN owner_user_id INT UNSIGNED NULL DEFAULT NULL",
        'owner_name'    => "ALTER TABLE sb_archive_records ADD COLUMN owner_name VARCHAR(255) NULL DEFAULT NULL",
        'object_id'     => "ALTER TABLE sb_archive_records ADD COLUMN object_id INT UNSIGNED NULL DEFAULT NULL",
    ];
    foreach ($cols as $col => $sql) {
        if (!in_array($col, $have, true)) {
            try { $pdo->exec($sql); } catch (Throwable $e) { error_log('SB auth alter: ' . $e->getMessage()); }
        }
    }
    foreach (['idx_owner_user' => "CREATE INDEX idx_owner_user ON sb_archive_records (owner_user_id)",
              'idx_object_id'  => "CREATE INDEX idx_object_id ON sb_archive_records (object_id)"] as $idx => $sql) {
        if (!in_array($idx, ['idx_owner_user', 'idx_object_id'], true)) continue;
        try { $pdo->exec($sql); } catch (Throwable $e) { /* уже существует */ }
    }
}

function db(array $cfg): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    foreach (['db_host', 'db_name', 'db_user', 'db_pass'] as $name) {
        if (!isset($cfg[$name]) || $cfg[$name] === '' || strpos((string)$cfg[$name], 'УКАЖИТЕ_') === 0) {
            reply(500, ['ok' => false, 'error' => 'Не заполнены настройки MySQL в config.php.']);
        }
    }

    $dsn = 'mysql:host=' . $cfg['db_host'] . ';dbname=' . $cfg['db_name'] . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, (string)$cfg['db_user'], (string)$cfg['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS sb_app_data (
                data_key VARCHAR(100) NOT NULL PRIMARY KEY,
                payload LONGTEXT NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS sb_archive_records (
                record_key CHAR(66) NOT NULL PRIMARY KEY,
                report_date VARCHAR(10) NOT NULL DEFAULT '',
                object_name VARCHAR(255) NOT NULL DEFAULT '',
                owner_user_id INT UNSIGNED NULL DEFAULT NULL,
                owner_name VARCHAR(255) NULL DEFAULT NULL,
                object_id INT UNSIGNED NULL DEFAULT NULL,
                payload LONGTEXT NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_report_date (report_date),
                INDEX idx_object_name (object_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS sb_users (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(64) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(255) NOT NULL DEFAULT '',
                role VARCHAR(16) NOT NULL DEFAULT 'user',
                object_id INT UNSIGNED NULL DEFAULT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS sb_objects (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec("CREATE TABLE IF NOT EXISTS sb_user_activation_tokens (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                token_hash CHAR(64) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_activation_user (user_id),
                CONSTRAINT fk_activation_user FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        ensure_archive_columns($pdo);
        $pdo->exec("CREATE TABLE IF NOT EXISTS sb_report_comments (
            record_key CHAR(66) NOT NULL PRIMARY KEY,
            comment TEXT NOT NULL,
            author_user_id INT UNSIGNED NOT NULL,
            author_name VARCHAR(255) NOT NULL DEFAULT '',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_report_comment_author (author_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS sb_audit_log (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            actor_user_id INT UNSIGNED NULL DEFAULT NULL,
            actor_name VARCHAR(255) NOT NULL DEFAULT '',
            action VARCHAR(64) NOT NULL,
            target_type VARCHAR(64) NOT NULL DEFAULT '',
            target_id INT UNSIGNED NULL DEFAULT NULL,
            target_label VARCHAR(255) NOT NULL DEFAULT '',
            details TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_audit_created (created_at),
            INDEX idx_audit_actor (actor_user_id),
            INDEX idx_audit_action (action)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS sb_notifications (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            type VARCHAR(48) NOT NULL DEFAULT 'info',
            title VARCHAR(180) NOT NULL,
            message TEXT NOT NULL,
            link VARCHAR(255) NOT NULL DEFAULT '',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notifications_user_read (user_id, is_read, created_at),
            CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        ensure_user_objects($pdo);
    } catch (Throwable $e) {
        error_log('SB storage database error: ' . $e->getMessage());
        reply(500, ['ok' => false, 'error' => 'Не удалось подключиться к MySQL. Проверьте config.php и наличие базы данных.']);
    }
    return $pdo;
}

function ensure_user_objects(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS sb_user_objects (
        user_id INT UNSIGNED NOT NULL,
        object_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, object_id),
        INDEX idx_user_objects_object (object_id),
        CONSTRAINT fk_user_objects_user FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE,
        CONSTRAINT fk_user_objects_object FOREIGN KEY (object_id) REFERENCES sb_objects(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    // Идемпотентный перенос прежней одиночной привязки.
    $pdo->exec("INSERT IGNORE INTO sb_user_objects (user_id, object_id)
        SELECT id, object_id FROM sb_users WHERE object_id IS NOT NULL");
}

function audit_log(PDO $pdo, ?array $actor, string $action, string $targetType = '', ?int $targetId = null, string $targetLabel = '', array $details = []): void {
    try {
        $actorId = isset($actor['id']) ? (int)$actor['id'] : (isset($actor['user_id']) ? (int)$actor['user_id'] : 0);
        $actorName = (string)($actor['full_name'] ?? $actor['actor_name'] ?? $actor['username'] ?? '');
        $stmt = $pdo->prepare('INSERT INTO sb_audit_log (actor_user_id, actor_name, action, target_type, target_id, target_label, details) VALUES (:uid, :name, :action, :type, :target_id, :label, :details)');
        $stmt->execute([':uid' => $actorId > 0 ? $actorId : null, ':name' => mb_substr($actorName, 0, 255), ':action' => mb_substr($action, 0, 64), ':type' => mb_substr($targetType, 0, 64), ':target_id' => $targetId, ':label' => mb_substr($targetLabel, 0, 255), ':details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null]);
    } catch (Throwable $e) { error_log('SB audit log error: ' . $e->getMessage()); }
}

function notification_add(PDO $pdo, int $userId, string $type, string $title, string $message, string $link = ''): void {
    if ($userId < 1) return;
    try {
        $st = $pdo->prepare('INSERT INTO sb_notifications (user_id, type, title, message, link) VALUES (:uid, :type, :title, :message, :link)');
        $st->execute([':uid'=>$userId, ':type'=>mb_substr($type,0,48), ':title'=>mb_substr($title,0,180), ':message'=>$message, ':link'=>mb_substr($link,0,255)]);
    } catch (Throwable $e) { error_log('SB notification error: '.$e->getMessage()); }
}

function get_user_object_rows(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT o.id, o.name, o.is_active
        FROM sb_user_objects uo JOIN sb_objects o ON o.id = uo.object_id
        WHERE uo.user_id = :id ORDER BY o.name");
    $stmt->execute([':id' => $userId]);
    $rows = $stmt->fetchAll();
    if (!$rows) {
        $legacy = $pdo->prepare("SELECT o.id, o.name, o.is_active FROM sb_users u
            JOIN sb_objects o ON o.id = u.object_id WHERE u.id = :id AND u.object_id IS NOT NULL");
        $legacy->execute([':id' => $userId]);
        $row = $legacy->fetch();
        if ($row) $rows[] = $row;
    }
    return $rows;
}

function user_object_ids(PDO $pdo, int $userId): array {
    return array_map(function($row) { return (int)$row['id']; }, get_user_object_rows($pdo, $userId));
}

function user_ctx(PDO $pdo, array $u): array {
    $objectRows = get_user_object_rows($pdo, (int)$u['id']);
    $objectIds = array_map(function($row) { return (int)$row['id']; }, $objectRows);
    $objectNames = array_map(function($row) { return (string)$row['name']; }, $objectRows);
    return [
        'id' => (int)$u['id'],
        'username' => (string)$u['username'],
        'full_name' => (string)$u['full_name'],
        'role' => (string)$u['role'],
        'object_id' => $objectIds ? $objectIds[0] : ($u['object_id'] !== null ? (int)$u['object_id'] : null),
        'object_name' => $objectNames ? $objectNames[0] : '',
        'object_ids' => $objectIds,
        'object_names' => $objectNames,
    ];
}

function current_user(PDO $pdo): ?array {
    if (empty($_SESSION['sb_user_id'])) return null;
    $stmt = $pdo->prepare('SELECT * FROM sb_users WHERE id = :id AND is_active = 1');
    $stmt->execute([':id' => (int)$_SESSION['sb_user_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function require_role(array $ctx, string $role): void {
    if ($ctx['role'] !== $role) {
        reply(403, ['ok' => false, 'error' => 'Недостаточно прав для этого действия.']);
    }
}

function ensure_object(PDO $pdo, string $name, bool $isAdmin): ?int {
    $name = trim($name);
    if ($name === '' || mb_strlen($name) > 255) return null;
    $stmt = $pdo->prepare('SELECT id FROM sb_objects WHERE name = :n LIMIT 1');
    $stmt->execute([':n' => $name]);
    $id = $stmt->fetchColumn();
    if ($id !== false) return (int)$id;
    if (!$isAdmin) return null; // не-администратор не может создавать объекты
    try {
        $ins = $pdo->prepare('INSERT INTO sb_objects (name) VALUES (:n)');
        $ins->execute([':n' => $name]);
        return (int)$pdo->lastInsertId();
    } catch (Throwable $e) {
        return null;
    }
}

function save_archive(PDO $pdo, array $records, array $ctx): void {
    if (count($records) > 1000) {
        reply(413, ['ok' => false, 'error' => 'Слишком много записей архива в одном запросе.']);
    }
    $isAdmin = $ctx['role'] === 'admin';
    $allowedObjectNames = [];
    if (!$isAdmin) {
        foreach (get_user_object_rows($pdo, (int)$ctx['id']) as $assigned) {
            if ((int)$assigned['is_active'] === 1) $allowedObjectNames[(string)$assigned['name']] = true;
        }
    }
    $defaultObject = (string)($ctx['object_name'] ?? '');

    $pdo->beginTransaction();
    try {
        $select = $pdo->prepare('SELECT payload FROM sb_archive_records WHERE record_key = :k FOR UPDATE');
        $upsert = $pdo->prepare(
            'INSERT INTO sb_archive_records (record_key, report_date, object_name, owner_user_id, owner_name, object_id, payload)
             VALUES (:k, :d, :o, :ou, :on, :oi, :p)
             ON DUPLICATE KEY UPDATE report_date = VALUES(report_date),
                 object_name = VALUES(object_name),
                 owner_user_id = VALUES(owner_user_id),
                 owner_name = VALUES(owner_name),
                 object_id = VALUES(object_id),
                 payload = VALUES(payload)'
        );

        foreach ($records as $record) {
            if (!is_array($record)) continue;
            $data = isset($record['data']) && is_array($record['data']) ? $record['data'] : [];
            $date = (string)($data['b0_date'] ?? '');
            $object = (string)($data['b0_to'] ?? '');
            $fio = (string)($record['fio'] ?? '');

            // Ограниченный пользователь может работать только с назначенными активными объектами.
            if (!$isAdmin) {
                if ($object === '') $object = $defaultObject;
                if ($object === '' || !isset($allowedObjectNames[$object])) continue;
                $data['b0_to'] = $object;
            }

            $requestedOwnerId = (int)($record['owner_user_id'] ?? 0);
            $keyOwnerId = $ctx['role'] === 'user'
                ? (int)$ctx['id']
                : ($requestedOwnerId > 0 ? $requestedOwnerId : (int)$ctx['id']);
            $key = archive_record_key([
                'data' => ['b0_date' => $date, 'b0_to' => $object],
                'owner_user_id' => $keyOwnerId,
                't' => (int)($record['t'] ?? 0),
                'fio' => $fio
            ]);
            $select->execute([':k' => $key]);
            $row = $select->fetch();
            $legacyKey = null;
            if (!$row) {
                $legacyKey = archive_record_key([
                    'data' => ['b0_date' => $date, 'b0_to' => $object],
                    't' => (int)($record['t'] ?? 0),
                    'fio' => $fio
                ]);
                if ($legacyKey !== $key) {
                    $select->execute([':k' => $legacyKey]);
                    $legacyRow = $select->fetch();
                    if ($legacyRow) {
                        $legacyPayload = json_decode((string)$legacyRow['payload'], true);
                        $legacyOwner = is_array($legacyPayload) ? (int)($legacyPayload['owner_user_id'] ?? 0) : 0;
                        if ($legacyOwner > 0 && $legacyOwner === $keyOwnerId) {
                            $row = $legacyRow;
                        } else {
                            $legacyKey = null;
                        }
                    } else {
                        $legacyKey = null;
                    }
                } else {
                    $legacyKey = null;
                }
            }
            // If the client supplied another author's ID but no matching
            // stored report exists, this is a new report by the current user.
            if (!$row && $keyOwnerId !== (int)$ctx['id']) {
                $keyOwnerId = (int)$ctx['id'];
                $key = archive_record_key([
                    'data' => ['b0_date' => $date, 'b0_to' => $object],
                    'owner_user_id' => $keyOwnerId,
                    't' => (int)($record['t'] ?? 0),
                    'fio' => $fio
                ]);
                $select->execute([':k' => $key]);
                $row = $select->fetch();
                $legacyKey = null;
            }

            $existing = $row ? json_decode((string)$row['payload'], true) : null;
            if (!is_array($existing)) $existing = null;

            $ownerId = $existing['owner_user_id'] ?? null;
            $ownerName = (string)($existing['owner_name'] ?? '');

            // Права на изменение существующей записи.
            if ($ctx['role'] === 'user') {
                if (is_array($existing) && $ownerId === null) continue;          // старая запись без владельца — только просмотр
                if ($ownerId !== null && (int)$ownerId !== (int)$ctx['id']) continue; // чужая запись
                // ФИО составителя для пользователя всегда его собственное.
                $fio = (string)$ctx['full_name'];
                $record['fio'] = $fio;
                $data['b0_fio'] = $fio;
            }

            // Владелец: если у записи уже есть владелец — сохраняем его (модератор,
            // редактируя чужой отчёт, не «переписывает» авторство). Новым записям
            // владелец присваивается из текущей сессии.
            if ($ownerId === null) {
                $ownerId = (int)$ctx['id'];
                $ownerName = (string)$ctx['full_name'];
            }
            $record['owner_user_id'] = $ownerId;
            $record['owner_name'] = $ownerName;
            $record['data'] = $data;

            $record = merge_history($record, $existing);

            $objId = ensure_object($pdo, $object, $isAdmin);
            if ($objId === null) continue;

            $payload = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            if (strlen($payload) > 2 * 1024 * 1024) continue;

            $upsert->execute([
                ':k' => $key,
                ':d' => substr($date, 0, 10),
                ':o' => substr($object, 0, 255),
                ':ou' => $ownerId,
                ':on' => substr($ownerName, 0, 255),
                ':oi' => $objId,
                ':p' => $payload,
            ]);
            if ($legacyKey !== null) {
                $deleteLegacy = $pdo->prepare('DELETE FROM sb_archive_records WHERE record_key = :k');
                $deleteLegacy->execute([':k' => $legacyKey]);
            }
        }

        // Архив хранится без автоматического ограничения по количеству записей.
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

$cfg = require __DIR__ . '/config.php';
$action = (string)($_GET['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Активация учётной записи по одноразовой ссылке (без авторизованной сессии).
if ($action === 'activate_info' && $method === 'GET') {
    $token = (string)($_GET['token'] ?? '');
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) reply(400, ['ok' => false, 'error' => 'Некорректная одноразовая ссылка.']);
    $pdo = db($cfg);
    $st = $pdo->prepare('SELECT u.username, u.full_name, t.expires_at FROM sb_user_activation_tokens t JOIN sb_users u ON u.id=t.user_id WHERE t.token_hash=:h AND t.expires_at > NOW() LIMIT 1');
    $st->execute([':h' => hash('sha256', $token)]);
    $row = $st->fetch();
    if (!$row) reply(404, ['ok' => false, 'error' => 'Одноразовая ссылка недействительна или срок её действия истёк. Обратитесь к администратору за новой ссылкой.']);
    reply(200, ['ok' => true, 'username' => $row['username'], 'full_name' => $row['full_name']]);
}
if ($action === 'activate_complete' && $method === 'POST') {
    $body = input_json();
    $token = (string)($body['token'] ?? '');
    $password = (string)($body['password'] ?? '');
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) reply(400, ['ok' => false, 'error' => 'Некорректная ссылка активации.']);
    if (strlen($password) < 8 || strlen($password) > 4096) reply(400, ['ok' => false, 'error' => 'Пароль должен содержать не менее 8 символов.']);
    $pdo = db($cfg);
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT user_id FROM sb_user_activation_tokens WHERE token_hash=:h AND expires_at > NOW() LIMIT 1 FOR UPDATE');
        $st->execute([':h' => hash('sha256', $token)]);
        $userId = $st->fetchColumn();
        if (!$userId) { $pdo->rollBack(); reply(404, ['ok' => false, 'error' => 'Одноразовая ссылка недействительна или срок её действия истёк.']); }
        $st = $pdo->prepare('UPDATE sb_users SET password_hash=:p, is_active=1 WHERE id=:id');
        $st->execute([':p' => password_hash($password, PASSWORD_DEFAULT), ':id' => (int)$userId]);
        if ($st->rowCount() !== 1) { $pdo->rollBack(); reply(409, ['ok' => false, 'error' => 'Учётная запись уже активирована или отключена.']); }
        $st = $pdo->prepare('DELETE FROM sb_user_activation_tokens WHERE user_id=:id');
        $st->execute([':id' => (int)$userId]);
        $pdo->commit();
        audit_log($pdo, ['id' => (int)$userId, 'full_name' => 'Пользователь'], 'password_changed', 'user', (int)$userId, 'Пароль обновлён по одноразовой ссылке');
        reply(200, ['ok' => true]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('SB activation error: ' . $e->getMessage());
        reply(500, ['ok' => false, 'error' => 'Не удалось активировать учётную запись.']);
    }
}

if ($action === 'status' && $method === 'GET') {
    $u = null;
    if (!empty($_SESSION['sb_user_id'])) {
        $pdo = db($cfg);
        $u = current_user($pdo);
    }
    reply(200, [
        'ok' => true,
        'authenticated' => $u !== null,
        'user' => $u ? user_ctx($pdo, $u) : null,
        'csrf' => csrf_token(),
    ]);
}

if ($action === 'login' && $method === 'POST') {
    // Для входа принимаем обычную HTML-форму: некоторые бесплатные хостинги
    // блокируют или подменяют AJAX-запросы с application/json.
    $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
    $body = strpos($contentType, 'application/x-www-form-urlencoded') !== false
        ? $_POST
        : input_json();
    check_csrf($body);
    $username = trim((string)($body['username'] ?? ''));
    $password = (string)($body['password'] ?? '');
    if ($username === '' || $password === '') {
        usleep(350000);
        reply(401, ['ok' => false, 'error' => 'Введите логин и пароль.']);
    }
    $pdo = db($cfg);
    $stmt = $pdo->prepare('SELECT * FROM sb_users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();
    if (!$user || !(int)$user['is_active'] || !password_verify($password, (string)$user['password_hash'])) {
        usleep(350000);
        reply(401, ['ok' => false, 'error' => 'Неверный логин или пароль, либо учётная запись отключена.']);
    }
    session_regenerate_id(true);
    $_SESSION['sb_user_id'] = (int)$user['id'];
    $_SESSION['sb_login_time'] = time();
    $_SESSION['sb_csrf'] = bin2hex(random_bytes(32));
    audit_log($pdo, $user, 'login', 'session', (int)$user['id'], 'Вход в систему');
    reply(200, ['ok' => true, 'user' => user_ctx($pdo, $user), 'csrf' => $_SESSION['sb_csrf']]);
}

if ($action === 'logout' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
    $logoutPdo = db($cfg);
    $logoutUser = current_user($logoutPdo);
    if ($logoutUser) audit_log($logoutPdo, $logoutUser, 'logout', 'session', (int)$logoutUser['id'], 'Выход из системы');
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    reply(200, ['ok' => true]);
}

$pdo = db($cfg);
$user = current_user($pdo);
if (!$user) {
    reply(401, ['ok' => false, 'need_login' => true,
        'error' => 'Требуется вход. Обновите страницу и авторизуйтесь.']);
}
$ctx = user_ctx($pdo, $user);

if ($action === 'notifications_data' && $method === 'GET') {
    $st = $pdo->prepare('SELECT id, type, title, message, link, is_read, created_at FROM sb_notifications WHERE user_id=:uid ORDER BY id DESC LIMIT 100');
    $st->execute([':uid'=>(int)$ctx['id']]);
    $items = $st->fetchAll();
    $unread = 0;
    foreach ($items as &$item) { $item['is_read'] = (int)$item['is_read']; if (!$item['is_read']) $unread++; }
    unset($item);
    reply(200, ['ok'=>true, 'notifications'=>$items, 'unread'=>$unread, 'csrf'=>csrf_token()]);
}
if ($action === 'notifications_read' && $method === 'POST') {
    $body = input_json(); check_csrf($body);
    $id = (int)($body['id'] ?? 0);
    if ($id > 0) {
        $st = $pdo->prepare('UPDATE sb_notifications SET is_read=1 WHERE id=:id AND user_id=:uid');
        $st->execute([':id'=>$id, ':uid'=>(int)$ctx['id']]);
    } elseif (!empty($body['all'])) {
        $st = $pdo->prepare('UPDATE sb_notifications SET is_read=1 WHERE user_id=:uid');
        $st->execute([':uid'=>(int)$ctx['id']]);
    } else reply(400, ['ok'=>false, 'error'=>'Не выбрано уведомление.']);
    reply(200, ['ok'=>true]);
}

if ($action === 'employees_data' && $method === 'GET') {
    $targetId = (int)($_GET['user_id'] ?? 0);
    $sql = "SELECT u.id,u.username,u.full_name,u.role,u.is_active,u.created_at,
        (SELECT MAX(a.created_at) FROM sb_audit_log a WHERE a.actor_user_id=u.id AND a.action='login') AS last_login,
        (SELECT COUNT(*) FROM sb_archive_records ar WHERE ar.owner_user_id=u.id) AS reports_count,
        (SELECT MAX(ar.report_date) FROM sb_archive_records ar WHERE ar.owner_user_id=u.id) AS last_report
        FROM sb_users u WHERE 1=1";
    $params = [];
    if ($ctx['role'] === 'user') {
        $sql .= ' AND u.id=:self_id'; $params[':self_id']=(int)$ctx['id'];
    } elseif ($ctx['role'] === 'moderator') {
        $sql .= " AND (u.id=:self_id OR (u.id<>:self_id2 AND EXISTS (
            SELECT 1 FROM sb_user_objects tuo
            JOIN sb_user_objects muo ON muo.object_id=tuo.object_id
            JOIN sb_objects ob ON ob.id=tuo.object_id AND ob.is_active=1
            WHERE tuo.user_id=u.id AND muo.user_id=:moderator_id
        )))";
        $params[':self_id']=(int)$ctx['id']; $params[':self_id2']=(int)$ctx['id']; $params[':moderator_id']=(int)$ctx['id'];
    }
    if ($targetId > 0) { $sql .= ' AND u.id=:target_id'; $params[':target_id']=$targetId; }
    $sql .= ' ORDER BY u.full_name,u.id';
    $st=$pdo->prepare($sql); $st->execute($params); $users=$st->fetchAll();
    $objectSt=$pdo->prepare('SELECT o.name FROM sb_user_objects uo JOIN sb_objects o ON o.id=uo.object_id WHERE uo.user_id=:uid ORDER BY o.name');
    foreach ($users as &$employee) {
        $objectSt->execute([':uid'=>(int)$employee['id']]);
        $employee['objects']=array_column($objectSt->fetchAll(),'name');
        $employee['reports_count']=(int)$employee['reports_count'];
        $employee['is_active']=(int)$employee['is_active'];
    }
    unset($employee);
    if ($targetId > 0 && !$users) reply(404,['ok'=>false,'error'=>'Сотрудник не найден или у вас нет доступа.']);
    reply(200,['ok'=>true,'employees'=>$users,'user'=>$ctx,'csrf'=>csrf_token()]);
}
if ($action === 'employee_history' && $method === 'GET') {
    $targetId=(int)($_GET['user_id'] ?? 0);
    if ($targetId < 1) reply(400,['ok'=>false,'error'=>'Не выбран сотрудник.']);
    if ($ctx['role']==='user' && $targetId!==(int)$ctx['id']) reply(403,['ok'=>false,'error'=>'Недостаточно прав.']);
    if ($ctx['role']==='moderator') {
        $check=$pdo->prepare("SELECT COUNT(*) FROM sb_user_objects a JOIN sb_user_objects b ON b.object_id=a.object_id JOIN sb_objects o ON o.id=a.object_id AND o.is_active=1 WHERE a.user_id=:target AND b.user_id=:viewer");
        $check->execute([':target'=>$targetId,':viewer'=>(int)$ctx['id']]);
        if ((int)$check->fetchColumn()===0 && $targetId!==(int)$ctx['id']) reply(403,['ok'=>false,'error'=>'Нет доступа к истории этого сотрудника.']);
    }
    $st=$pdo->prepare("SELECT actor_name,action,target_label,created_at FROM sb_audit_log WHERE actor_user_id=:uid OR (target_type='user' AND target_id=:uid2) ORDER BY id DESC LIMIT 30");
    $st->execute([':uid'=>$targetId,':uid2'=>$targetId]);
    reply(200,['ok'=>true,'history'=>$st->fetchAll()]);
}

if ($action === 'admin_password_reset' && $method === 'POST') {
    require_role($ctx, 'admin');
    $body = input_json();
    check_csrf($body);
    $targetId = (int)($body['user_id'] ?? 0);
    if ($targetId < 1) reply(400, ['ok' => false, 'error' => 'Не выбран пользователь.']);
    $find = $pdo->prepare('SELECT id, username, full_name, is_active FROM sb_users WHERE id = :id LIMIT 1');
    $find->execute([':id' => $targetId]);
    $target = $find->fetch();
    if (!$target) reply(404, ['ok' => false, 'error' => 'Пользователь не найден.']);
    if (!(int)$target['is_active']) reply(409, ['ok' => false, 'error' => 'Сначала включите учётную запись, затем создайте ссылку для смены пароля.']);
    $token = bin2hex(random_bytes(32));
    $pdo->prepare('DELETE FROM sb_user_activation_tokens WHERE user_id = :id')->execute([':id' => $targetId]);
    $save = $pdo->prepare('INSERT INTO sb_user_activation_tokens (user_id, token_hash, expires_at) VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL 48 HOUR))');
    $save->execute([':id' => $targetId, ':hash' => hash('sha256', $token)]);
    $base = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')), '/\\');
    $url = ((!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http') . '://' . (string)($_SERVER['HTTP_HOST'] ?? '') . ($base ? $base : '') . '/activate.php?token=' . $token;
    audit_log($pdo, $ctx, 'password_reset_link_created', 'user', $targetId, (string)$target['username']);
    notification_add($pdo, $targetId, 'security', 'Смена пароля', 'Администратор создал одноразовую ссылку для смены пароля. Если вы не ожидали этого, обратитесь к руководителю.', 'activate.php');
    reply(200, ['ok' => true, 'activation_url' => $url, 'expires_in_hours' => 48]);
}

if ($action === 'report_comment' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
    if ($ctx['role'] !== 'admin') {
        reply(403, ['ok' => false, 'error' => 'Комментарии руководителя может изменять только администратор.']);
    }
    $recordKey = trim((string)($body['record_key'] ?? ''));
    $comment = trim((string)($body['comment'] ?? ''));
    if (!preg_match('/^[rd]:[a-f0-9]{64}$/i', $recordKey) || mb_strlen($comment) > 5000) {
        reply(400, ['ok' => false, 'error' => 'Проверьте отчёт и длину комментария (до 5000 символов).']);
    }
    $exists = $pdo->prepare('SELECT record_key, owner_user_id, object_name FROM sb_archive_records WHERE record_key = :k LIMIT 1');
    $exists->execute([':k' => $recordKey]);
    $reportOwner = $exists->fetch();
    if (!$reportOwner) reply(404, ['ok' => false, 'error' => 'Отчёт не найден.']);
    if ($comment === '') {
        $del = $pdo->prepare('DELETE FROM sb_report_comments WHERE record_key = :k');
        $del->execute([':k' => $recordKey]);
    } else {
        $save = $pdo->prepare('INSERT INTO sb_report_comments (record_key, comment, author_user_id, author_name)
            VALUES (:k, :c, :uid, :name)
            ON DUPLICATE KEY UPDATE comment = VALUES(comment), author_user_id = VALUES(author_user_id),
                author_name = VALUES(author_name), updated_at = CURRENT_TIMESTAMP');
        $save->execute([':k' => $recordKey, ':c' => $comment, ':uid' => (int)$ctx['id'], ':name' => (string)$ctx['full_name']]);
    }
    audit_log($pdo, $ctx, 'report_comment_updated', 'report', null, $recordKey);
    if ($comment !== '' && (int)($reportOwner['owner_user_id'] ?? 0) > 0 && (int)$reportOwner['owner_user_id'] !== (int)$ctx['id']) {
        notification_add($pdo, (int)$reportOwner['owner_user_id'], 'report_comment', 'Комментарий к отчёту', 'Руководитель добавил или обновил комментарий к вашему отчёту по объекту «'.(string)$reportOwner['object_name'].'».', 'reports.php');
    }
    reply(200, ['ok' => true, 'comment' => $comment, 'author' => (string)$ctx['full_name']]);
}

if ($action === 'reports_data' && $method === 'GET') {
    $from = (string)($_GET['from'] ?? date('Y-m-01'));
    $to = (string)($_GET['to'] ?? date('Y-m-d'));
    $objectIds = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)($_GET['object_ids'] ?? ''))), function($id) { return $id > 0; })));
    if (!$objectIds) {
        $legacyObjectId = (int)($_GET['object_id'] ?? 0);
        if ($legacyObjectId > 0) $objectIds = [$legacyObjectId];
    }
    $validDate = function($value) {
        $d = DateTime::createFromFormat('!Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    };
    if (!$validDate($from) || !$validDate($to) || $from > $to) {
        reply(400, ['ok' => false, 'error' => 'Укажите корректный период: дата начала не позже даты окончания.']);
    }

    $assignedRows = [];
    $allowedIds = [];
    $allowedNames = [];
    if ($ctx['role'] !== 'admin') {
        foreach (get_user_object_rows($pdo, (int)$ctx['id']) as $obj) {
            if ((int)$obj['is_active'] !== 1) continue;
            $assignedRows[] = $obj;
            $allowedIds[] = (int)$obj['id'];
            $allowedNames[] = (string)$obj['name'];
        }
    } else {
        $assignedRows = $pdo->query('SELECT id, name, is_active FROM sb_objects WHERE is_active = 1 ORDER BY name')->fetchAll();
    }

    $availableById = [];
    foreach ($assignedRows as $obj) $availableById[(int)$obj['id']] = $obj;
    foreach ($objectIds as $id) {
        if (!isset($availableById[$id])) {
            reply(403, ['ok' => false, 'error' => 'Нет доступа к одному из выбранных объектов.']);
        }
    }

    $sql = 'SELECT a.record_key, a.report_date, a.object_name, a.owner_user_id, a.owner_name, a.object_id, a.payload, a.updated_at,
                   c.comment AS manager_comment, c.author_name AS comment_author, c.updated_at AS comment_updated_at
            FROM sb_archive_records a LEFT JOIN sb_report_comments c ON c.record_key = a.record_key
            WHERE a.report_date >= :date_from AND a.report_date <= :date_to';
    $params = [':date_from' => $from, ':date_to' => $to];

    if ($ctx['role'] !== 'admin') {
        if (!$allowedIds) reply(200, ['ok' => true, 'objects' => [], 'records' => [], 'user' => $ctx, 'csrf' => csrf_token()]);
        $idMarks = [];
        foreach ($allowedIds as $i => $id) {
            $key = ':allowed_id_' . $i;
            $idMarks[] = $key;
            $params[$key] = $id;
        }
        $nameMarks = [];
        foreach ($allowedNames as $i => $name) {
            $key = ':allowed_name_' . $i;
            $nameMarks[] = $key;
            $params[$key] = $name;
        }
        $sql .= ' AND (a.object_id IN (' . implode(',', $idMarks) . ') OR (a.object_id IS NULL AND a.object_name IN (' . implode(',', $nameMarks) . ')))';
    }

    if ($objectIds) {
        $selectedNameMarks = [];
        $idMarks = [];
        foreach ($objectIds as $i => $id) {
            $idKey = ':selected_id_' . $i;
            $nameKey = ':selected_name_' . $i;
            $idMarks[] = $idKey;
            $selectedNameMarks[] = $nameKey;
            $params[$idKey] = $id;
            $params[$nameKey] = (string)$availableById[$id]['name'];
        }
        $sql .= ' AND (a.object_id IN (' . implode(',', $idMarks) . ') OR (a.object_id IS NULL AND a.object_name IN (' . implode(',', $selectedNameMarks) . ')))';
    }
    // Пользователь видит только собственные отчёты; модератор — все отчёты
    // по закреплённым объектам, администратор — все отчёты.
    if ($ctx['role'] === 'user') {
        $sql .= ' AND a.owner_user_id = :viewer_owner_id';
        $params[':viewer_owner_id'] = (int)$ctx['id'];
    }

    $employeeId = (int)($_GET['employee_id'] ?? 0);
    if ($employeeId > 0) {
        $sql .= ' AND a.owner_user_id = :selected_employee_id';
        $params[':selected_employee_id'] = $employeeId;
    }

    $sql .= ' ORDER BY a.report_date DESC, a.object_name ASC, a.updated_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = [];
    foreach ($stmt->fetchAll() as $row) {
        $item = json_decode((string)$row['payload'], true);
        if (!is_array($item)) continue;
        $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : [];
        $records[] = [
            'record_key' => (string)$row['record_key'],
            'manager_comment' => (string)($row['manager_comment'] ?? ''),
            'comment_author' => (string)($row['comment_author'] ?? ''),
            'comment_updated_at' => (string)($row['comment_updated_at'] ?? ''),
            'date' => (string)($data['b0_date'] ?? $row['report_date']),
            'object' => (string)($data['b0_to'] ?? $row['object_name']),
            'employee' => (string)($data['b0_fio'] ?? $item['fio'] ?? ''),
            'author' => (string)($item['owner_name'] ?? $row['owner_name'] ?? ''),
            'owner_user_id' => isset($item['owner_user_id']) ? (int)$item['owner_user_id'] : ($row['owner_user_id'] !== null ? (int)$row['owner_user_id'] : null),
            'done' => (int)($item['done'] ?? 0),
            'total' => (int)($item['total'] ?? 0),
            'saved_at' => (int)($item['t'] ?? 0),
            'updated_at' => (string)$row['updated_at'],
            'data' => $data,
            'dyn' => isset($item['dyn']) && is_array($item['dyn']) ? $item['dyn'] : [],
        ];
    }

    reply(200, ['ok' => true, 'objects' => $assignedRows, 'records' => $records, 'user' => $ctx, 'from' => $from, 'to' => $to, 'csrf' => csrf_token()]);
}

if ($action === 'load' && $method === 'GET') {
    $logicalKeys = [KEY_REPORT, KEY_DYN, KEY_LISTS];
    $physicalKeys = array_map('physical_key', $logicalKeys);
    $placeholders = implode(',', array_fill(0, count($physicalKeys), '?'));
    $stmt = $pdo->prepare('SELECT data_key, payload FROM sb_app_data WHERE data_key IN (' . $placeholders . ')');
    $stmt->execute($physicalKeys);

    $data = [];
    $logicalByPhysical = array_combine($physicalKeys, $logicalKeys);
    foreach ($stmt->fetchAll() as $row) {
        $logical = $logicalByPhysical[$row['data_key']] ?? null;
        if ($logical !== null) $data[$logical] = json_decode((string)$row['payload'], true);
    }

    $objects = $pdo->query('SELECT id, name, is_active FROM sb_objects ORDER BY name')->fetchAll();
    $allowedIds = $ctx['role'] === 'admin' ? [] : user_object_ids($pdo, (int)$ctx['id']);
    if ($ctx['role'] !== 'admin') {
        $allowedLookup = array_fill_keys($allowedIds, true);
        $objects = array_values(array_filter($objects, function($obj) use ($allowedLookup) {
            return (int)$obj['is_active'] === 1 && isset($allowedLookup[(int)$obj['id']]);
        }));
    }

    // Архив фильтруется на сервере по всем назначенным объектам.
    $allowedNames = [];
    if ($ctx['role'] !== 'admin') {
        foreach ($objects as $obj) $allowedNames[(string)$obj['name']] = true;
    }
    $archiveStmt = $pdo->query('SELECT payload FROM sb_archive_records ORDER BY report_date DESC, updated_at DESC');
    $archive = [];
    foreach ($archiveStmt->fetchAll() as $row) {
        $item = json_decode((string)$row['payload'], true);
        if (!is_array($item)) continue;
        if ($ctx['role'] !== 'admin') {
            $obj = (string)($item['data']['b0_to'] ?? '');
            if (!isset($allowedNames[$obj])) continue;
        }
        $archive[] = $item;
    }
    $data[KEY_ARCH] = $archive;

    // Все роли, включая администратора, отображаются сотрудниками
    // только на тех активных объектах, которые закреплены за ними.
    $employeeSql = "SELECT DISTINCT u.id, u.full_name, uo.object_id
                    FROM sb_users u
                    JOIN sb_user_objects uo ON uo.user_id = u.id
                    JOIN sb_objects o ON o.id = uo.object_id AND o.is_active = 1
                    WHERE u.role IN ('user', 'moderator', 'admin')
                      AND (u.is_active = 1 OR EXISTS (
                          SELECT 1 FROM sb_user_activation_tokens t
                          WHERE t.user_id = u.id AND t.expires_at > NOW()
                      ))";
    $employeeParams = [];
    if ($ctx['role'] === 'moderator') {
        if (!$allowedIds) {
            $employeeSql .= " AND 1 = 0";
        } else {
            $employeeSql .= " AND uo.object_id IN (" . implode(',', array_fill(0, count($allowedIds), '?')) . ")";
            $employeeParams = array_merge($employeeParams, $allowedIds);
        }
    } elseif ($ctx['role'] === 'user') {
        $employeeSql .= " AND u.id = ?";
        $employeeParams[] = (int)$ctx['id'];
    }
    $employeeSql .= " ORDER BY u.full_name, u.id, uo.object_id";
    $employeeStmt = $pdo->prepare($employeeSql);
    $employeeStmt->execute($employeeParams);
    $employees = $employeeStmt->fetchAll();

    reply(200, ['ok' => true, 'data' => $data, 'user' => $ctx, 'objects' => $objects, 'employees' => $employees, 'csrf' => csrf_token()]);
}

if ($action === 'save' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
    $key = (string)($body['key'] ?? '');
    if (!in_array($key, [KEY_REPORT, KEY_DYN, KEY_LISTS, KEY_ARCH], true) || !array_key_exists('value', $body)) {
        reply(400, ['ok' => false, 'error' => 'Недопустимый ключ или отсутствуют данные.']);
    }

    if ($key === KEY_ARCH) {
        if (!is_array($body['value'])) reply(400, ['ok' => false, 'error' => 'Архив должен быть массивом записей.']);
        if ($ctx['role'] !== 'admin' && !$ctx['object_ids']) {
            reply(403, ['ok' => false, 'error' => 'Администратор ещё не назначил вам торговый объект. Сохранение отчета невозможно.']);
        }
        try {
            save_archive($pdo, $body['value'], $ctx);
        } catch (Throwable $e) {
            error_log('SB archive save error: ' . $e->getMessage());
            reply(500, ['ok' => false, 'error' => 'Не удалось сохранить архив на сервере.']);
        }
        audit_log($pdo, $ctx, 'reports_saved', 'reports', null, 'Сохранение дневных отчётов', ['count' => count($body['value'])]);
        reply(200, ['ok' => true]);
    }

    try {
        $payload = json_encode($body['value'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    } catch (Throwable $e) {
        reply(400, ['ok' => false, 'error' => 'Не удалось сериализовать данные.']);
    }
    if (strlen($payload) > 10 * 1024 * 1024) {
        reply(413, ['ok' => false, 'error' => 'Размер данных превышает допустимый лимит.']);
    }

    $physical = physical_key($key);
    $stmt = $pdo->prepare(
        'INSERT INTO sb_app_data (data_key, payload) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE payload = VALUES(payload)'
    );
    $stmt->execute([':k' => $physical, ':v' => $payload]);
    reply(200, ['ok' => true]);
}

if ($action === 'delete' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
    $key = (string)($body['key'] ?? '');
    if (!in_array($key, [KEY_REPORT, KEY_DYN, KEY_LISTS], true)) {
        reply(400, ['ok' => false, 'error' => 'Недопустимый ключ.']);
    }
    $stmt = $pdo->prepare('DELETE FROM sb_app_data WHERE data_key = :k');
    $stmt->execute([':k' => physical_key($key)]);
    reply(200, ['ok' => true]);
}

if ($action === 'reports_delete' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
    $recordKey = trim((string)($body['record_key'] ?? ''));
    if (!preg_match('/^[rd]:[a-f0-9]{64}$/i', $recordKey)) {
        reply(400, ['ok' => false, 'error' => 'Некорректный идентификатор отчёта.']);
    }

    try {
        $pdo->beginTransaction();
        $find = $pdo->prepare('SELECT record_key, object_id, object_name, owner_user_id
            FROM sb_archive_records WHERE record_key = :k LIMIT 1 FOR UPDATE');
        $find->execute([':k' => $recordKey]);
        $record = $find->fetch();
        if (!$record) {
            $pdo->rollBack();
            reply(404, ['ok' => false, 'error' => 'Отчёт уже удалён или не найден.']);
        }

        if ($ctx['role'] === 'user') {
            if ($record['owner_user_id'] === null || (int)$record['owner_user_id'] !== (int)$ctx['id']) {
                $pdo->rollBack();
                reply(403, ['ok' => false, 'error' => 'Можно удалять только собственные отчёты.']);
            }
        } elseif ($ctx['role'] === 'moderator') {
            $assignedIds = [];
            $assignedNames = [];
            foreach (get_user_object_rows($pdo, (int)$ctx['id']) as $assigned) {
                if ((int)$assigned['is_active'] !== 1) continue;
                $assignedIds[(int)$assigned['id']] = true;
                $assignedNames[(string)$assigned['name']] = true;
            }
            $objectAllowed = $record['object_id'] !== null
                ? isset($assignedIds[(int)$record['object_id']])
                : isset($assignedNames[(string)$record['object_name']]);
            if (!$objectAllowed) {
                $pdo->rollBack();
                reply(403, ['ok' => false, 'error' => 'Можно удалять отчёты только по закреплённым за вами объектам.']);
            }
        } elseif ($ctx['role'] !== 'admin') {
            $pdo->rollBack();
            reply(403, ['ok' => false, 'error' => 'Недостаточно прав для удаления отчётов.']);
        }

        $delComment = $pdo->prepare('DELETE FROM sb_report_comments WHERE record_key = :k');
        $delComment->execute([':k' => $recordKey]);
        $del = $pdo->prepare('DELETE FROM sb_archive_records WHERE record_key = :k');
        $del->execute([':k' => $recordKey]);
        $pdo->commit();
        audit_log($pdo, $ctx, 'report_deleted', 'report', null, (string)$record['record_key'], ['object' => $record['object_name']]);
        reply(200, ['ok' => true]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('SB report delete error: ' . $e->getMessage());
        reply(500, ['ok' => false, 'error' => 'Не удалось удалить отчёт.']);
    }
}

if ($action === 'archive_delete' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
    $date = (string)($body['date'] ?? '');
    $object = (string)($body['object'] ?? '');
    $record = [
        'data' => ['b0_date' => $date, 'b0_to' => $object],
        'owner_user_id' => (int)($body['owner_user_id'] ?? 0),
        't' => (int)($body['t'] ?? 0),
        'fio' => (string)($body['fio'] ?? ''),
    ];
    $key = archive_record_key($record);

    if ($ctx['role'] === 'user') {
        reply(403, ['ok' => false, 'error' => 'Удаление записей архива запрещено для вашей роли.']);
    }
    if ($ctx['role'] === 'moderator') {
        $allowed = [];
        foreach (get_user_object_rows($pdo, (int)$ctx['id']) as $assigned) {
            if ((int)$assigned['is_active'] === 1) $allowed[(string)$assigned['name']] = true;
        }
        if (!isset($allowed[$object])) reply(403, ['ok' => false, 'error' => 'Можно удалять записи только назначенных вам торговых объектов.']);
    }

    $stmt = $pdo->prepare('DELETE FROM sb_archive_records WHERE record_key = :k');
    $stmt->execute([':k' => $key]);
    reply(200, ['ok' => true]);
}

if ($action === 'moderator_user_create' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
    if (!in_array($ctx['role'], ['admin', 'moderator'], true)) reply(403, ['ok' => false, 'error' => 'Недостаточно прав.']);
    $username = trim((string)($body['username'] ?? ''));
    $fullName = trim((string)($body['full_name'] ?? ''));
    if (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) reply(400, ['ok' => false, 'error' => 'Логин: 3–64 символа — латиница, цифры, точка, подчёркивание, дефис.']);
    if ($fullName === '' || mb_strlen($fullName) > 255) reply(400, ['ok' => false, 'error' => 'Укажите ФИО (до 255 символов).']);

    if ($ctx['role'] === 'moderator') {
        $objectRows = array_filter(get_user_object_rows($pdo, (int)$ctx['id']), function($row) { return (int)$row['is_active'] === 1; });
        $assignedIds = array_map(function($row) { return (int)$row['id']; }, array_values($objectRows));
        $rawIds = $body['object_ids'] ?? $assignedIds;
        if (!is_array($rawIds)) reply(400, ['ok' => false, 'error' => 'Некорректный список объектов.']);
        $objectIds = array_values(array_unique(array_filter(array_map('intval', $rawIds), function($v) { return $v > 0; })));
        if (array_diff($objectIds, $assignedIds)) reply(403, ['ok' => false, 'error' => 'Нельзя назначить сотруднику объект, который не закреплён за вами.']);
    } else {
        $rawIds = $body['object_ids'] ?? (($body['object_id'] ?? '') === '' ? [] : [$body['object_id']]);
        if (!is_array($rawIds)) reply(400, ['ok' => false, 'error' => 'Некорректный список объектов.']);
        $objectIds = array_values(array_unique(array_filter(array_map('intval', $rawIds), function($v) { return $v > 0; })));
    }
    if ($ctx['role'] === 'moderator' && !$assignedIds) reply(403, ['ok' => false, 'error' => 'Ваш профиль не привязан к торговым объектам. Обратитесь к администратору.']);
    if ($ctx['role'] === 'moderator' && !$objectIds) reply(400, ['ok' => false, 'error' => 'Выберите хотя бы один объект для нового сотрудника.']);
    if ($objectIds) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM sb_objects WHERE id IN (' . implode(',', array_fill(0, count($objectIds), '?')) . ') AND is_active=1');
        $check->execute($objectIds);
        if ((int)$check->fetchColumn() !== count($objectIds)) reply(400, ['ok' => false, 'error' => 'Один или несколько объектов не найдены либо отключены.']);
    }
    $dup = $pdo->prepare('SELECT COUNT(*) FROM sb_users WHERE username=:u');
    $dup->execute([':u' => $username]);
    if ((int)$dup->fetchColumn()) reply(400, ['ok' => false, 'error' => 'Такой логин уже занят.']);
    $token = bin2hex(random_bytes(32));
    $placeholderHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
    try {
        $pdo->beginTransaction();
        $primaryObject = $objectIds ? $objectIds[0] : null;
        $st = $pdo->prepare("INSERT INTO sb_users (username,password_hash,full_name,role,object_id,is_active) VALUES (:u,:p,:f,'user',:o,0)");
        $st->execute([':u'=>$username, ':p'=>$placeholderHash, ':f'=>$fullName, ':o'=>$primaryObject]);
        $newId = (int)$pdo->lastInsertId();
        if ($objectIds) {
            $link = $pdo->prepare('INSERT IGNORE INTO sb_user_objects (user_id, object_id) VALUES (:u, :o)');
            foreach ($objectIds as $oid) $link->execute([':u'=>$newId, ':o'=>$oid]);
        }
        $st = $pdo->prepare('INSERT INTO sb_user_activation_tokens (user_id,token_hash,expires_at) VALUES (:u,:h,DATE_ADD(NOW(), INTERVAL 48 HOUR))');
        $st->execute([':u'=>$newId, ':h'=>hash('sha256',$token)]);
        $pdo->commit();
        audit_log($pdo, $ctx, 'user_created', 'user', $newId, $username, ['role' => 'user', 'object_ids' => $objectIds]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('SB user create error: '.$e->getMessage());
        reply(500, ['ok'=>false, 'error'=>'Не удалось создать пользователя.']);
    }
    $base = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')), '/\\');
    $url = ((!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http')
        . '://' . (string)($_SERVER['HTTP_HOST'] ?? '') . ($base ? $base : '') . '/activate.php?token=' . $token;
    reply(200, ['ok'=>true, 'id'=>$newId, 'activation_url'=>$url, 'expires_in_hours'=>48]);
}

if ($action === 'users_list' && $method === 'GET') {
    require_role($ctx, 'admin');
    $rows = $pdo->query('SELECT id, username, full_name, role, object_id, is_active, created_at FROM sb_users ORDER BY id')->fetchAll();
    reply(200, ['ok' => true, 'users' => $rows, 'csrf' => csrf_token()]);
}

if ($action === 'user_save' && $method === 'POST') {
    require_role($ctx, 'admin');
    $body = input_json();
    check_csrf($body);
    $id = (int)($body['id'] ?? 0);
    $username = trim((string)($body['username'] ?? ''));
    $fullName = trim((string)($body['full_name'] ?? ''));
    $role = (string)($body['role'] ?? 'user');
    $rawIds = $body['object_ids'] ?? (($body['object_id'] ?? null) === null || $body['object_id'] === '' ? [] : [$body['object_id']]);
    if (!is_array($rawIds)) reply(400, ['ok' => false, 'error' => 'Некорректный список объектов.']);
    $objectIds = array_values(array_unique(array_filter(array_map('intval', $rawIds), function($v) { return $v > 0; })));
    $objectId = $objectIds ? $objectIds[0] : null;
    $isActive = !empty($body['is_active']) ? 1 : 0;
    $password = (string)($body['password'] ?? '');

    if (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) reply(400, ['ok' => false, 'error' => 'Логин: 3–64 символа — латиница, цифры, точка, подчёркивание, дефис.']);
    if ($fullName === '' || mb_strlen($fullName) > 255) reply(400, ['ok' => false, 'error' => 'Укажите ФИО (до 255 символов).']);
    if (!in_array($role, ['admin', 'moderator', 'user'], true)) reply(400, ['ok' => false, 'error' => 'Недопустимая роль.']);
    if ($objectIds) {
        $chk = $pdo->prepare('SELECT COUNT(*) FROM sb_objects WHERE id IN (' . implode(',', array_fill(0, count($objectIds), '?')) . ')');
        $chk->execute($objectIds);
        if ((int)$chk->fetchColumn() !== count($objectIds)) reply(400, ['ok' => false, 'error' => 'Один или несколько торговых объектов не найдены.']);
    }
    $dup = $pdo->prepare('SELECT COUNT(*) FROM sb_users WHERE username = :u AND id <> :id');
    $dup->execute([':u' => $username, ':id' => $id]);
    if ((int)$dup->fetchColumn() > 0) reply(400, ['ok' => false, 'error' => 'Такой логин уже занят.']);

    if ($id > 0) {
        $oldObjectIds = user_object_ids($pdo, $id);
        sort($oldObjectIds); $newObjectIdsSorted = $objectIds; sort($newObjectIdsSorted);
        if ($id === (int)$ctx['id'] && (!$isActive || $role !== 'admin')) reply(400, ['ok' => false, 'error' => 'Нельзя отключить себя или снять с себя роль администратора.']);
        if ($password !== '' && strlen($password) < 8) reply(400, ['ok' => false, 'error' => 'Новый пароль — не менее 8 символов.']);
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE sb_users SET username=:u, full_name=:f, role=:r, object_id=:o, is_active=:a WHERE id=:id');
            $stmt->execute([':u'=>$username, ':f'=>$fullName, ':r'=>$role, ':o'=>$objectId, ':a'=>$isActive, ':id'=>$id]);
            $pdo->prepare('DELETE FROM sb_user_objects WHERE user_id=:id')->execute([':id'=>$id]);
            if ($objectIds) {
                $link = $pdo->prepare('INSERT INTO sb_user_objects (user_id, object_id) VALUES (:u, :o)');
                foreach ($objectIds as $oid) $link->execute([':u'=>$id, ':o'=>$oid]);
            }
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE sb_users SET password_hash=:h WHERE id=:id');
                $stmt->execute([':h'=>password_hash($password, PASSWORD_DEFAULT), ':id'=>$id]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
        audit_log($pdo, $ctx, 'user_updated', 'user', $id, $username, ['role' => $role, 'object_ids' => $objectIds, 'active' => $isActive]);
        if ($oldObjectIds !== $newObjectIdsSorted) notification_add($pdo, $id, 'access', 'Изменены назначенные объекты', 'Руководитель обновил список торговых объектов, доступных в вашей учётной записи.', 'index.html');
        reply(200, ['ok' => true]);
    }
    if (strlen($password) < 8) reply(400, ['ok' => false, 'error' => 'Пароль нового пользователя — не менее 8 символов.']);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO sb_users (username,password_hash,full_name,role,object_id,is_active) VALUES (:u,:h,:f,:r,:o,:a)');
        $stmt->execute([':u'=>$username, ':h'=>password_hash($password, PASSWORD_DEFAULT), ':f'=>$fullName, ':r'=>$role, ':o'=>$objectId, ':a'=>$isActive]);
        $newId = (int)$pdo->lastInsertId();
        if ($objectIds) {
            $link = $pdo->prepare('INSERT INTO sb_user_objects (user_id, object_id) VALUES (:u, :o)');
            foreach ($objectIds as $oid) $link->execute([':u'=>$newId, ':o'=>$oid]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    audit_log($pdo, $ctx, 'user_created', 'user', $newId, $username, ['role' => $role, 'object_ids' => $objectIds, 'active' => $isActive]);
    notification_add($pdo, $newId, 'account', 'Учётная запись создана', 'Для вас создана учётная запись на сайте СБ. Если пароль ещё не установлен, используйте ссылку активации от администратора.', 'index.html');
    reply(200, ['ok' => true, 'id' => $newId]);
}

if ($action === 'objects_list' && $method === 'GET') {
    $rows = $pdo->query('SELECT id, name, is_active FROM sb_objects ORDER BY name')->fetchAll();
    if ($ctx['role'] !== 'admin') {
        $allowed = array_fill_keys(user_object_ids($pdo, (int)$ctx['id']), true);
        $rows = array_values(array_filter($rows, function($r) use ($allowed) {
            return (int)$r['is_active'] === 1 && isset($allowed[(int)$r['id']]);
        }));
    }
    reply(200, ['ok' => true, 'objects' => $rows, 'csrf' => csrf_token()]);
}

if ($action === 'object_save' && $method === 'POST') {
    require_role($ctx, 'admin');
    $body = input_json();
    check_csrf($body);
    $id = (int)($body['id'] ?? 0);
    $name = trim((string)($body['name'] ?? ''));
    $isActive = !empty($body['is_active']) ? 1 : 0;

    if ($name === '' || mb_strlen($name) > 255) {
        reply(400, ['ok' => false, 'error' => 'Название объекта не заполнено (до 255 символов).']);
    }
    $dup = $pdo->prepare('SELECT COUNT(*) FROM sb_objects WHERE name = :n AND id <> :id');
    $dup->execute([':n' => $name, ':id' => $id]);
    if ((int)$dup->fetchColumn() > 0) {
        reply(400, ['ok' => false, 'error' => 'Объект с таким названием уже существует.']);
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE sb_objects SET name = :n, is_active = :a WHERE id = :id');
        $stmt->execute([':n' => $name, ':a' => $isActive, ':id' => $id]);
        audit_log($pdo, $ctx, 'object_updated', 'object', $id, $name, ['active' => $isActive]);
        reply(200, ['ok' => true]);
    }
    $stmt = $pdo->prepare('INSERT INTO sb_objects (name, is_active) VALUES (:n, :a)');
    $stmt->execute([':n' => $name, ':a' => $isActive]);
    $newObjectId = (int)$pdo->lastInsertId();
    audit_log($pdo, $ctx, 'object_created', 'object', $newObjectId, $name, ['active' => $isActive]);
    reply(200, ['ok' => true, 'id' => $newObjectId]);
}

reply(404, ['ok' => false, 'error' => 'Неизвестная операция.']);
