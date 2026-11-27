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
    $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    if ($json === false) {
        error_log('SB API JSON encode error: ' . json_last_error_msg());
        $json = '{"ok":false,"error":"Не удалось сформировать ответ сервера."}';
    }
    echo $json;
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
    $indexes = $pdo->query("SELECT DISTINCT INDEX_NAME FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sb_archive_records'")
        ->fetchAll(PDO::FETCH_COLUMN);
    foreach (['idx_owner_user' => "CREATE INDEX idx_owner_user ON sb_archive_records (owner_user_id)",
              'idx_object_id'  => "CREATE INDEX idx_object_id ON sb_archive_records (object_id)"] as $idx => $sql) {
        if (!in_array($idx, $indexes, true)) {
            try { $pdo->exec($sql); } catch (Throwable $e) { error_log('SB archive index: ' . $e->getMessage()); }
        }
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
        // Инициализация схемы один раз на PHP-сессию, а не при каждом AJAX-запросе.
        // Версию можно повысить при добавлении новых миграций.
        if ((string)($_SESSION['sb_schema_version'] ?? '') !== '2026-11-27-2') {
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
            $pdo->exec("CREATE TABLE IF NOT EXISTS sb_tasks (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                object_id INT UNSIGNED NOT NULL,
                title VARCHAR(180) NOT NULL,
                description TEXT NULL,
                priority VARCHAR(16) NOT NULL DEFAULT 'normal',
                status VARCHAR(24) NOT NULL DEFAULT 'new',
                assignee_id INT UNSIGNED NOT NULL,
                created_by INT UNSIGNED NOT NULL,
                due_date DATE NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tasks_object (object_id, status, due_date),
                INDEX idx_tasks_assignee (assignee_id, status, due_date),
                INDEX idx_tasks_creator (created_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $pdo->exec("CREATE TABLE IF NOT EXISTS sb_task_comments (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                task_id BIGINT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                user_name VARCHAR(255) NOT NULL DEFAULT '',
                comment TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_task_comments_task (task_id, created_at),
                CONSTRAINT fk_task_comments_task FOREIGN KEY (task_id) REFERENCES sb_tasks(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $pdo->exec("CREATE TABLE IF NOT EXISTS sb_user_presence (
                user_id INT UNSIGNED NOT NULL PRIMARY KEY,
                last_seen DATETIME NOT NULL,
                INDEX idx_presence_last_seen (last_seen),
                CONSTRAINT fk_presence_user FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            ensure_user_objects($pdo);
            ensure_task_targets($pdo);
            $_SESSION['sb_schema_version'] = '2026-11-27-2';
        }
    } catch (Throwable $e) {
        error_log('SB storage database error: ' . $e->getMessage());
        reply(500, ['ok' => false, 'error' => 'Не удалось подключиться к MySQL. Проверьте config.php и наличие базы данных.']);
    }
    return $pdo;
}

function ensure_task_targets(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS sb_task_assignees (
        task_id BIGINT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (task_id, user_id), INDEX idx_task_assignees_user (user_id, task_id),
        CONSTRAINT fk_task_assignees_task FOREIGN KEY (task_id) REFERENCES sb_tasks(id) ON DELETE CASCADE,
        CONSTRAINT fk_task_assignees_user FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS sb_task_objects (
        task_id BIGINT UNSIGNED NOT NULL, object_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (task_id, object_id), INDEX idx_task_objects_object (object_id, task_id),
        CONSTRAINT fk_task_objects_task FOREIGN KEY (task_id) REFERENCES sb_tasks(id) ON DELETE CASCADE,
        CONSTRAINT fk_task_objects_object FOREIGN KEY (object_id) REFERENCES sb_objects(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("INSERT IGNORE INTO sb_task_assignees (task_id,user_id) SELECT id,assignee_id FROM sb_tasks");
    $pdo->exec("INSERT IGNORE INTO sb_task_objects (task_id,object_id) SELECT id,object_id FROM sb_tasks");
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

if ($action === 'presence_ping' && $method === 'GET') {
    $st = $pdo->prepare('INSERT INTO sb_user_presence (user_id,last_seen) VALUES (:uid,NOW())
        ON DUPLICATE KEY UPDATE last_seen=NOW()');
    $st->execute([':uid'=>(int)$ctx['id']]);
    reply(200, ['ok'=>true]);
}

if ($action === 'dashboard_data' && $method === 'GET') {
    $today = date('Y-m-d');
    $monthStart = date('Y-m-01');
    $role = (string)$ctx['role'];
    $uid = (int)$ctx['id'];

    // Объекты, доступные текущей роли. Для legacy-записей архива также сохраняем
    // сопоставление по имени объекта, если object_id ещё не был записан.
    $allowedObjects = [];
    if ($role === 'admin') {
        $allowedObjects = $pdo->query('SELECT id,name FROM sb_objects WHERE is_active=1 ORDER BY name')->fetchAll();
    } else {
        foreach (get_user_object_rows($pdo, $uid) as $object) {
            if ((int)$object['is_active'] === 1) $allowedObjects[] = ['id'=>(int)$object['id'], 'name'=>(string)$object['name']];
        }
    }
    $allowedIds = array_map(function($o) { return (int)$o['id']; }, $allowedObjects);
    $allowedNames = array_map(function($o) { return (string)$o['name']; }, $allowedObjects);

    // Сводка и последние задачи: агрегаты считают весь доступный набор,
    // а подробные карточки ограничены шестью записями.
    $taskWhere = ['o.is_active=1'];
    $taskParams = [];
    if ($role === 'user') {
        $taskWhere[] = 'EXISTS(SELECT 1 FROM sb_task_assignees ta WHERE ta.task_id=t.id AND ta.user_id=:task_uid)';
        $taskWhere[] = 'EXISTS(SELECT 1 FROM sb_user_objects mine WHERE mine.user_id=:task_uid_obj AND mine.object_id=t.object_id)';
        $taskParams[':task_uid'] = $uid;
        $taskParams[':task_uid_obj'] = $uid;
    } elseif ($role === 'moderator') {
        if (!$allowedIds) {
            $taskWhere[] = '1=0';
        } else {
            $marks = [];
            foreach ($allowedIds as $i => $id) {
                $key = ':task_object_' . $i;
                $marks[] = $key;
                $taskParams[$key] = $id;
            }
            $taskWhere[] = 'EXISTS(SELECT 1 FROM sb_task_objects txo WHERE txo.task_id=t.id AND txo.object_id IN (' . implode(',', $marks) . '))';
        }
    }
    $taskSqlWhere = implode(' AND ', $taskWhere);
    $taskSummaryStmt = $pdo->prepare("SELECT COUNT(*) AS total,
        SUM(CASE WHEN t.status<>'done' THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN t.status='review' THEN 1 ELSE 0 END) AS review_count,
        SUM(CASE WHEN t.status<>'done' AND t.due_date IS NOT NULL AND t.due_date<CURDATE() THEN 1 ELSE 0 END) AS overdue_count
        FROM sb_tasks t JOIN sb_objects o ON o.id=t.object_id WHERE " . $taskSqlWhere);
    $taskSummaryStmt->execute($taskParams);
    $taskSummary = $taskSummaryStmt->fetch() ?: [];
    $taskListStmt = $pdo->prepare("SELECT t.id,t.object_id,o.name AS object_name,t.title,t.priority,t.status,
        t.assignee_id,au.full_name AS assignee_name,t.created_by,cu.full_name AS creator_name,
        t.due_date,t.updated_at
        FROM sb_tasks t JOIN sb_objects o ON o.id=t.object_id
        JOIN sb_users au ON au.id=t.assignee_id JOIN sb_users cu ON cu.id=t.created_by
        WHERE " . $taskSqlWhere . "
        ORDER BY CASE WHEN t.status='done' THEN 1 ELSE 0 END, t.due_date IS NULL, t.due_date ASC, t.updated_at DESC
        LIMIT 6");
    $taskListStmt->execute($taskParams);
    $tasks = $taskListStmt->fetchAll();
    foreach ($tasks as &$task) {
        $task['id'] = (int)$task['id'];
        $task['object_id'] = (int)$task['object_id'];
        $task['assignee_id'] = (int)$task['assignee_id'];
        $task['created_by'] = (int)$task['created_by'];
        $task['is_overdue'] = !empty($task['due_date']) && $task['due_date'] < $today && $task['status'] !== 'done';
    }
    unset($task);

    // Количество сотрудников в пределах той же зоны доступа, что и employees_data.
    $employeeSql = 'SELECT COUNT(*) FROM sb_users u WHERE 1=1';
    $employeeParams = [];
    if ($role === 'user') {
        $employeeSql .= ' AND u.id=:employee_self';
        $employeeParams[':employee_self'] = $uid;
    } elseif ($role === 'moderator') {
        $employeeSql .= " AND (u.id=:employee_self OR (u.id<>:employee_self2 AND EXISTS (
            SELECT 1 FROM sb_user_objects tuo
            JOIN sb_user_objects muo ON muo.object_id=tuo.object_id
            JOIN sb_objects ob ON ob.id=tuo.object_id AND ob.is_active=1
            WHERE tuo.user_id=u.id AND muo.user_id=:employee_moderator
        )))";
        $employeeParams[':employee_self'] = $uid;
        $employeeParams[':employee_self2'] = $uid;
        $employeeParams[':employee_moderator'] = $uid;
    }
    $employeeStmt = $pdo->prepare($employeeSql);
    $employeeStmt->execute($employeeParams);
    $employeeCount = (int)$employeeStmt->fetchColumn();

    $reportWhere = ['a.report_date >= :report_from', 'a.report_date <= :report_to'];
    $reportParams = [':report_from'=>$monthStart, ':report_to'=>$today];
    if ($role === 'user') {
        $reportWhere[] = 'a.owner_user_id=:report_owner';
        $reportParams[':report_owner'] = $uid;
    } elseif ($role === 'moderator') {
        if (!$allowedIds) {
            $reportWhere[] = '1=0';
        } else {
            $idMarks = [];
            $nameMarks = [];
            foreach ($allowedIds as $i => $id) {
                $idKey = ':report_object_id_' . $i;
                $nameKey = ':report_object_name_' . $i;
                $idMarks[] = $idKey;
                $nameMarks[] = $nameKey;
                $reportParams[$idKey] = $id;
                $reportParams[$nameKey] = $allowedNames[$i];
            }
            $reportWhere[] = '(a.object_id IN (' . implode(',', $idMarks) . ') OR (a.object_id IS NULL AND a.object_name IN (' . implode(',', $nameMarks) . ')))';
        }
    }
    $reportSqlWhere = implode(' AND ', $reportWhere);
    $reportCountStmt = $pdo->prepare('SELECT COUNT(*) FROM sb_archive_records a WHERE ' . $reportSqlWhere);
    $reportCountStmt->execute($reportParams);
    $reportCount = (int)$reportCountStmt->fetchColumn();
    $reportListStmt = $pdo->prepare("SELECT a.record_key,a.report_date,a.object_name,
        COALESCE(NULLIF(a.owner_name,''), CASE WHEN JSON_VALID(a.payload) THEN
            COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(a.payload,'$.owner_name')),''),
                     NULLIF(JSON_UNQUOTE(JSON_EXTRACT(a.payload,'$.data.b0_fio')),''))
            ELSE '' END) AS employee_name
        FROM sb_archive_records a WHERE " . $reportSqlWhere . "
        ORDER BY a.report_date DESC,a.updated_at DESC LIMIT 6");
    $reportListStmt->execute($reportParams);
    $reports = [];
    foreach ($reportListStmt->fetchAll() as $row) {
        $reports[] = ['record_key'=>(string)$row['record_key'],'date'=>(string)$row['report_date'],
            'object'=>(string)$row['object_name'],'employee'=>(string)($row['employee_name'] ?? '')];
    }
    $lastReport = null;
    if ($role === 'user') {
        $lastStmt = $pdo->prepare('SELECT MAX(report_date) FROM sb_archive_records WHERE owner_user_id=:owner');
        $lastStmt->execute([':owner'=>$uid]);
        $lastReport = $lastStmt->fetchColumn() ?: null;
    }

    $notificationStmt = $pdo->prepare('SELECT id,type,title,message,link,is_read,created_at
        FROM sb_notifications WHERE user_id=:notification_uid AND is_read=0
        ORDER BY id DESC LIMIT 4');
    $notificationStmt->execute([':notification_uid'=>$uid]);
    $notifications = $notificationStmt->fetchAll();
    foreach ($notifications as &$notification) $notification['is_read'] = (int)$notification['is_read'];
    unset($notification);
    $unreadStmt = $pdo->prepare('SELECT COUNT(*) FROM sb_notifications WHERE user_id=:unread_uid AND is_read=0');
    $unreadStmt->execute([':unread_uid'=>$uid]);

    reply(200, [
        'ok'=>true, 'user'=>$ctx, 'csrf'=>csrf_token(),
        'tasks'=>$tasks,
        'task_summary'=>[
            'total'=>(int)($taskSummary['total'] ?? 0),
            'active'=>(int)($taskSummary['active_count'] ?? 0),
            'review'=>(int)($taskSummary['review_count'] ?? 0),
            'overdue'=>(int)($taskSummary['overdue_count'] ?? 0)
        ],
        'reports'=>$reports, 'reports_count'=>$reportCount,
        'employees_count'=>$employeeCount, 'last_report'=>$lastReport,
        'objects'=>$allowedObjects,
        'notifications'=>$notifications, 'unread'=>(int)$unreadStmt->fetchColumn()
    ]);
}

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

if ($action === 'tasks_data' && $method === 'GET') {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 50;
    $search = trim((string)($_GET['search'] ?? ''));
    if (mb_strlen($search) > 120) $search = mb_substr($search, 0, 120);
    $statusFilter = (string)($_GET['status'] ?? '');
    if (!in_array($statusFilter, ['', 'new', 'in_progress', 'review', 'done'], true)) {
        reply(400, ['ok'=>false, 'error'=>'Некорректный фильтр статуса.']);
    }
    $objectFilter = max(0, (int)($_GET['object_id'] ?? 0));

    $baseWhere = ['o.is_active=1'];
    $baseParams = [];
    if ($ctx['role'] === 'user') {
        $baseWhere[] = "EXISTS(SELECT 1 FROM sb_task_assignees ta WHERE ta.task_id=t.id AND ta.user_id=:scope_uid)";
        $baseParams[':scope_uid'] = (int)$ctx['id'];
    } elseif ($ctx['role'] === 'moderator') {
        $baseWhere[] = "EXISTS(SELECT 1 FROM sb_task_objects txo JOIN sb_user_objects uo ON uo.object_id=txo.object_id WHERE txo.task_id=t.id AND uo.user_id=:scope_mid)";
        $baseParams[':scope_mid'] = (int)$ctx['id'];
    }

    // KPI рассчитываются по всему доступному пользователю набору задач,
    // независимо от текущего поиска, фильтра и номера страницы.
    $scopeSql = " FROM sb_tasks t JOIN sb_objects o ON o.id=t.object_id
        JOIN sb_users au ON au.id=t.assignee_id JOIN sb_users cu ON cu.id=t.created_by
        WHERE " . implode(' AND ', $baseWhere);
    $statsSt = $pdo->prepare("SELECT COUNT(*) AS total,
        COALESCE(SUM(t.status='in_progress'),0) AS active,
        COALESCE(SUM(t.status='review'),0) AS review_count,
        COALESCE(SUM(t.due_date IS NOT NULL AND t.due_date<:stats_today AND t.status<>'done'),0) AS overdue" . $scopeSql);
    $statsParams = $baseParams;
    $statsParams[':stats_today'] = date('Y-m-d');
    $statsSt->execute($statsParams);
    $stats = $statsSt->fetch() ?: ['total'=>0,'active'=>0,'review_count'=>0,'overdue'=>0];

    $where = $baseWhere;
    $params = $baseParams;
    if ($statusFilter !== '') {
        $where[] = 't.status=:filter_status';
        $params[':filter_status'] = $statusFilter;
    }
    if ($objectFilter > 0) {
        $where[] = 'EXISTS(SELECT 1 FROM sb_task_objects fo WHERE fo.task_id=t.id AND fo.object_id=:filter_object)';
        $params[':filter_object'] = $objectFilter;
    }
    if ($search !== '') {
        $where[] = "(t.title LIKE :search_title OR t.description LIKE :search_description
            OR o.name LIKE :search_object OR au.full_name LIKE :search_assignee OR cu.full_name LIKE :search_creator)";
        $needle = '%' . $search . '%';
        $params[':search_title'] = $needle;
        $params[':search_description'] = $needle;
        $params[':search_object'] = $needle;
        $params[':search_assignee'] = $needle;
        $params[':search_creator'] = $needle;
    }
    $filteredSql = " FROM sb_tasks t JOIN sb_objects o ON o.id=t.object_id
        JOIN sb_users au ON au.id=t.assignee_id JOIN sb_users cu ON cu.id=t.created_by
        WHERE " . implode(' AND ', $where);
    $countSt = $pdo->prepare('SELECT COUNT(*)' . $filteredSql);
    $countSt->execute($params);
    $filteredTotal = (int)$countSt->fetchColumn();
    $totalPages = max(1, (int)ceil($filteredTotal / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $tasksSql = "SELECT t.id,t.object_id,o.name AS object_name,t.title,t.description,t.priority,t.status,
        t.assignee_id,au.full_name AS assignee_name,t.created_by,cu.full_name AS creator_name,
        (SELECT GROUP_CONCAT(DISTINCT u.full_name ORDER BY u.full_name SEPARATOR ', ') FROM sb_task_assignees ta JOIN sb_users u ON u.id=ta.user_id WHERE ta.task_id=t.id) AS assignee_names,
        (SELECT GROUP_CONCAT(DISTINCT ob.name ORDER BY ob.name SEPARATOR ', ') FROM sb_task_objects txo JOIN sb_objects ob ON ob.id=txo.object_id WHERE txo.task_id=t.id) AS object_names,
        t.due_date,t.created_at,t.updated_at" . $filteredSql . "
        ORDER BY CASE WHEN t.status='done' THEN 1 ELSE 0 END, t.due_date IS NULL,
        t.due_date ASC, t.updated_at DESC, t.id DESC LIMIT " . $perPage . " OFFSET " . $offset;
    $st = $pdo->prepare($tasksSql);
    $st->execute($params);
    $tasks = $st->fetchAll();

    $commentCounts = [];
    $ids = array_map(function($task) { return (int)$task['id']; }, $tasks);
    if ($ids) {
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $cs = $pdo->prepare("SELECT task_id,COUNT(*) AS comment_count FROM sb_task_comments WHERE task_id IN ($marks) GROUP BY task_id");
        $cs->execute($ids);
        foreach ($cs->fetchAll() as $c) $commentCounts[(int)$c['task_id']] = (int)$c['comment_count'];
    }
    foreach ($tasks as &$task) {
        $task['id'] = (int)$task['id'];
        $task['object_id'] = (int)$task['object_id'];
        $task['assignee_id'] = (int)$task['assignee_id'];
        $task['created_by'] = (int)$task['created_by'];
        $task['comment_count'] = $commentCounts[$task['id']] ?? 0;
        $task['is_overdue'] = !empty($task['due_date']) && $task['due_date'] < date('Y-m-d') && $task['status'] !== 'done';
        $task['can_manage'] = $ctx['role'] !== 'user' && ($ctx['role'] === 'admin' || (int)$task['created_by'] === (int)$ctx['id']);
        $recipientCheck=$pdo->prepare('SELECT 1 FROM sb_task_assignees WHERE task_id=:task AND user_id=:user');
        $recipientCheck->execute([':task'=>$task['id'],':user'=>(int)$ctx['id']]);
        $task['is_recipient'] = (bool)$recipientCheck->fetchColumn();
        $task['can_update'] = $task['is_recipient'] || $ctx['role']==='admin' || ($ctx['role']==='moderator' && (int)$task['created_by']===(int)$ctx['id']);
    }

    $objectsSql = "SELECT o.id,o.name FROM sb_objects o WHERE o.is_active=1";
    $objectParams = [];
    if ($ctx['role'] !== 'admin') {
        $objectsSql .= " AND EXISTS(SELECT 1 FROM sb_user_objects uo WHERE uo.object_id=o.id AND uo.user_id=:viewer)";
        $objectParams[':viewer'] = (int)$ctx['id'];
    }
    $objectsSql .= " ORDER BY o.name";
    $os = $pdo->prepare($objectsSql);
    $os->execute($objectParams);
    $objects = $os->fetchAll();

    $assignees = [];
    if ($ctx['role'] !== 'user') {
        $usersSql = "SELECT u.id,u.full_name,u.role,MIN(uo.object_id) AS object_id FROM sb_users u
            JOIN sb_user_objects uo ON uo.user_id=u.id JOIN sb_objects o ON o.id=uo.object_id
            WHERE u.is_active=1 AND o.is_active=1";
        $userParams = [];
        if ($ctx['role'] === 'moderator') {
            $usersSql .= " AND EXISTS(SELECT 1 FROM sb_user_objects mine WHERE mine.user_id=:mid AND mine.object_id=uo.object_id)";
            $userParams[':mid'] = (int)$ctx['id'];
        }
        $usersSql .= " GROUP BY u.id,u.full_name,u.role ORDER BY u.full_name";
        $us = $pdo->prepare($usersSql);
        $us->execute($userParams);
        $assignees = $us->fetchAll();
    }

    reply(200, [
        'ok'=>true,
        'tasks'=>$tasks,
        'objects'=>$objects,
        'assignees'=>$assignees,
        'user'=>$ctx,
        'csrf'=>csrf_token(),
        'stats'=>[
            'total'=>(int)$stats['total'],
            'active'=>(int)$stats['active'],
            'review'=>(int)$stats['review_count'],
            'overdue'=>(int)$stats['overdue']
        ],
        'pagination'=>['page'=>$page,'per_page'=>$perPage,'total'=>$filteredTotal,'total_pages'=>$totalPages]
    ]);
}
if ($action === 'task_comments' && $method === 'GET') {
    $taskId=(int)($_GET['id']??0); if($taskId<1) reply(400,['ok'=>false,'error'=>'Не выбрана задача.']);
    $st=$pdo->prepare("SELECT t.id, EXISTS(SELECT 1 FROM sb_task_assignees ta WHERE ta.task_id=t.id AND ta.user_id=:uid) AS recipient, EXISTS(SELECT 1 FROM sb_task_objects txo JOIN sb_user_objects uo ON uo.object_id=txo.object_id WHERE txo.task_id=t.id AND uo.user_id=:mid) AS object_access FROM sb_tasks t WHERE t.id=:id LIMIT 1");
    $st->execute([':uid'=>(int)$ctx['id'],':mid'=>(int)$ctx['id'],':id'=>$taskId]); $task=$st->fetch();
    if(!$task) reply(404,['ok'=>false,'error'=>'Задача не найдена.']);
    $allowed=$ctx['role']==='admin'||($ctx['role']==='user'&&(int)$task['recipient']===1)||($ctx['role']==='moderator'&&(int)$task['object_access']===1);
    if(!$allowed) reply(403,['ok'=>false,'error'=>'Нет доступа к комментариям этой задачи.']);
    $cs=$pdo->prepare('SELECT user_name,comment,created_at FROM sb_task_comments WHERE task_id=:id ORDER BY id ASC'); $cs->execute([':id'=>$taskId]);
    reply(200,['ok'=>true,'comments'=>$cs->fetchAll()]);
}
if ($action === 'task_create' && $method === 'POST') {
    if(!in_array($ctx['role'],['admin','moderator'],true)) reply(403,['ok'=>false,'error'=>'Создавать задачи могут только руководитель и модератор.']);
    $body=input_json(); check_csrf($body);
    $objectIds=array_values(array_unique(array_filter(array_map('intval',is_array($body['object_ids']??null)?$body['object_ids']:[]),function($v){return $v>0;})));
    $assigneeIds=array_values(array_unique(array_filter(array_map('intval',is_array($body['assignee_ids']??null)?$body['assignee_ids']:[]),function($v){return $v>0;})));
    $title=trim((string)($body['title']??'')); $description=trim((string)($body['description']??'')); $priority=(string)($body['priority']??'normal'); $due=(string)($body['due_date']??'');
    if($title===''||mb_strlen($title)>180||mb_strlen($description)>5000) reply(400,['ok'=>false,'error'=>'Укажите название задачи (до 180 символов); описание — до 5000 символов.']);
    if(!in_array($priority,['low','normal','high'],true)) reply(400,['ok'=>false,'error'=>'Некорректный приоритет.']);
    if($due!==''&&!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/',$due)) reply(400,['ok'=>false,'error'=>'Некорректный срок выполнения.']);
    if(!$objectIds&&!$assigneeIds) reply(400,['ok'=>false,'error'=>'Выберите сотрудников или объекты.']);
    $allObjects=$pdo->query('SELECT id,name FROM sb_objects WHERE is_active=1')->fetchAll(); $names=[]; foreach($allObjects as $o)$names[(int)$o['id']]=$o['name'];
    foreach($objectIds as $oid) if(!isset($names[$oid])) reply(400,['ok'=>false,'error'=>'Выбран недействующий объект.']);
    if($ctx['role']==='moderator'&&$objectIds){$q=$pdo->prepare('SELECT COUNT(*) FROM sb_user_objects WHERE user_id=? AND object_id IN ('.implode(',',array_fill(0,count($objectIds),'?')).')');$q->execute(array_merge([(int)$ctx['id']],$objectIds));if((int)$q->fetchColumn()!==count($objectIds))reply(403,['ok'=>false,'error'=>'Нельзя назначать задачи по чужим объектам.']);}
    $recipients=[]; $targets=[];
    foreach($objectIds as $oid){$targets[$oid]=true;$q=$pdo->prepare('SELECT u.id FROM sb_users u JOIN sb_user_objects uo ON uo.user_id=u.id WHERE u.is_active=1 AND uo.object_id=?');$q->execute([$oid]);foreach($q->fetchAll(PDO::FETCH_COLUMN) as $uid)$recipients[(int)$uid]=true;}
    if($assigneeIds){$q=$pdo->prepare('SELECT u.id,uo.object_id FROM sb_users u JOIN sb_user_objects uo ON uo.user_id=u.id JOIN sb_objects o ON o.id=uo.object_id AND o.is_active=1 WHERE u.is_active=1 AND u.id IN ('.implode(',',array_fill(0,count($assigneeIds),'?')).')');$q->execute($assigneeIds);$found=[];foreach($q->fetchAll() as $row){$uid=(int)$row['id'];$oid=(int)$row['object_id'];$found[$uid]=true;$targets[$oid]=true;if($ctx['role']==='moderator'){$chk=$pdo->prepare('SELECT 1 FROM sb_user_objects WHERE user_id=? AND object_id=?');$chk->execute([(int)$ctx['id'],$oid]);if(!$chk->fetchColumn())reply(403,['ok'=>false,'error'=>'Нельзя назначить сотрудника вне ваших объектов.']);}}
        if(count($found)!==count($assigneeIds))reply(400,['ok'=>false,'error'=>'Выбранный сотрудник неактивен или не закреплён за объектом.']); foreach($assigneeIds as $uid)$recipients[$uid]=true;}
    $recipientIds=array_keys($recipients); $targetIds=array_keys($targets); sort($recipientIds); sort($targetIds);
    if(!$recipientIds||!$targetIds) reply(400,['ok'=>false,'error'=>'На выбранных объектах нет активных сотрудников.']);
    $pdo->beginTransaction();
    try {
        $ins=$pdo->prepare("INSERT INTO sb_tasks(object_id,title,description,priority,status,assignee_id,created_by,due_date) VALUES(?,?,?,?,'new',?,?,?)");
        $ins->execute([$targetIds[0],$title,$description,$priority,$recipientIds[0],(int)$ctx['id'],$due!==''?$due:null]); $id=(int)$pdo->lastInsertId();
        $la=$pdo->prepare('INSERT INTO sb_task_assignees(task_id,user_id) VALUES(?,?)'); foreach($recipientIds as $uid)$la->execute([$id,$uid]);
        $lo=$pdo->prepare('INSERT INTO sb_task_objects(task_id,object_id) VALUES(?,?)'); foreach($targetIds as $oid)$lo->execute([$id,$oid]);
        $objectNames=array_map(function($oid)use($names){return $names[$oid]??'';},$targetIds);
        foreach($recipientIds as $uid) notification_add($pdo,$uid,'task','Новая задача','Вам назначена задача «'.$title.'» по объектам: '.implode(', ',$objectNames).'.','tasks.php');
        $pdo->commit();
    } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); error_log('SB task create error: '.$e->getMessage()); reply(500,['ok'=>false,'error'=>'Не удалось создать задачу.']); }
    audit_log($pdo,$ctx,'task_created','task',$id,$title,['object_ids'=>$targetIds,'assignee_ids'=>$recipientIds]); reply(200,['ok'=>true,'id'=>$id]);
}
if ($action === 'task_update' && $method === 'POST') {
    $body=input_json(); check_csrf($body); $id=(int)($body['id']??0);
    $st=$pdo->prepare("SELECT t.*, EXISTS(SELECT 1 FROM sb_task_assignees ta WHERE ta.task_id=t.id AND ta.user_id=:uid) AS is_recipient, EXISTS(SELECT 1 FROM sb_task_objects txo JOIN sb_user_objects uo ON uo.object_id=txo.object_id WHERE txo.task_id=t.id AND uo.user_id=:mid) AS has_object FROM sb_tasks t WHERE t.id=:id LIMIT 1");
    $st->execute([':uid'=>(int)$ctx['id'],':mid'=>(int)$ctx['id'],':id'=>$id]); $task=$st->fetch(); if(!$task)reply(404,['ok'=>false,'error'=>'Задача не найдена.']);
    $isRecipient=(int)$task['is_recipient']===1; $isManager=$ctx['role']==='admin'||($ctx['role']==='moderator'&&(int)$task['created_by']===(int)$ctx['id']);
    $canSee=$ctx['role']==='admin'||($ctx['role']==='user'&&$isRecipient)||($ctx['role']==='moderator'&&(int)$task['has_object']===1); if(!$canSee)reply(403,['ok'=>false,'error'=>'Нет доступа к этой задаче.']);
    $status=(string)($body['status']??'');
    if($status!==''){
        if($isRecipient&&!$isManager&&!in_array($status,['in_progress','review'],true))reply(403,['ok'=>false,'error'=>'Исполнитель может перевести задачу в работу или на проверку.']);
        if(!$isRecipient&&!$isManager)reply(403,['ok'=>false,'error'=>'Недостаточно прав для изменения статуса.']);
        if($isManager&&!in_array($status,['in_progress','done'],true))reply(400,['ok'=>false,'error'=>'Руководитель может вернуть задачу в работу или подтвердить выполнение.']);
        if(!in_array($status,['in_progress','review','done'],true))reply(400,['ok'=>false,'error'=>'Недопустимый статус.']);
        if($status==='done'&&$task['status']!=='review')reply(409,['ok'=>false,'error'=>'Подтвердить можно только задачу на проверке.']);
        $up=$pdo->prepare('UPDATE sb_tasks SET status=:status WHERE id=:id');$up->execute([':status'=>$status,':id'=>$id]);
        audit_log($pdo,$ctx,'task_status_changed','task',$id,(string)$task['title'],['from'=>$task['status'],'to'=>$status]);
        if($status==='review'&&(int)$task['created_by']!==(int)$ctx['id'])notification_add($pdo,(int)$task['created_by'],'task','Задача ожидает проверки','Сотрудник отправил задачу «'.$task['title'].'» на проверку.','tasks.php');
        if($status==='done'){$q=$pdo->prepare('SELECT user_id FROM sb_task_assignees WHERE task_id=?');$q->execute([$id]);foreach($q->fetchAll(PDO::FETCH_COLUMN) as $uid)if((int)$uid!==(int)$ctx['id'])notification_add($pdo,(int)$uid,'task','Задача принята','Руководитель подтвердил выполнение задачи «'.$task['title'].'».','tasks.php');}
    }
    $comment=trim((string)($body['comment']??'')); if($comment!==''){if(mb_strlen($comment)>3000)reply(400,['ok'=>false,'error'=>'Комментарий не должен превышать 3000 символов.']);
        $ins=$pdo->prepare('INSERT INTO sb_task_comments(task_id,user_id,user_name,comment) VALUES(?,?,?,?)');$ins->execute([$id,(int)$ctx['id'],(string)$ctx['full_name'],$comment]);
        $q=$pdo->prepare('SELECT user_id FROM sb_task_assignees WHERE task_id=? UNION SELECT created_by FROM sb_tasks WHERE id=?');$q->execute([$id,$id]);foreach($q->fetchAll(PDO::FETCH_COLUMN) as $uid)if((int)$uid!==(int)$ctx['id'])notification_add($pdo,(int)$uid,'task','Комментарий к задаче','Новый комментарий к задаче «'.$task['title'].'».','tasks.php');
        audit_log($pdo,$ctx,'task_comment_added','task',$id,(string)$task['title']);
    }
    reply(200,['ok'=>true]);
}
if ($action === 'employees_data' && $method === 'GET') {
    $targetId = (int)($_GET['user_id'] ?? 0);
    $sql = "SELECT u.id,u.username,u.full_name,u.role,u.is_active,u.created_at,
        (SELECT MAX(a.created_at) FROM sb_audit_log a WHERE a.actor_user_id=u.id AND a.action='login') AS last_login,
        (SELECT COUNT(*) FROM sb_archive_records ar WHERE ar.owner_user_id=u.id) AS reports_count,
        (SELECT MAX(ar.report_date) FROM sb_archive_records ar WHERE ar.owner_user_id=u.id) AS last_report,
        (SELECT CASE WHEN p.last_seen >= DATE_SUB(NOW(), INTERVAL 90 SECOND) THEN 1 ELSE 0 END
            FROM sb_user_presence p WHERE p.user_id=u.id) AS is_online
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
    // Загружаем назначения одним запросом вместо отдельного запроса на каждого сотрудника.
    $objectsByUser = [];
    if ($users) {
        $userIds = array_map(function($employee) { return (int)$employee['id']; }, $users);
        $marks = implode(',', array_fill(0, count($userIds), '?'));
        $objectSt = $pdo->prepare("SELECT uo.user_id,o.name FROM sb_user_objects uo
            JOIN sb_objects o ON o.id=uo.object_id
            WHERE uo.user_id IN ($marks) ORDER BY o.name");
        $objectSt->execute($userIds);
        foreach ($objectSt->fetchAll() as $objectRow) {
            $objectsByUser[(int)$objectRow['user_id']][] = (string)$objectRow['name'];
        }
    }
    foreach ($users as &$employee) {
        $employee['objects'] = $objectsByUser[(int)$employee['id']] ?? [];
        $employee['reports_count']=(int)$employee['reports_count'];
        $employee['is_active']=(int)$employee['is_active'];
        $employee['is_online']=(int)($employee['is_online'] ?? 0);
    }
    unset($employee);
    if ($targetId > 0 && !$users) reply(404,['ok'=>false,'error'=>'Сотрудник не найден или у вас нет доступа.']);
    reply(200,['ok'=>true,'employees'=>$users,'user'=>$ctx,'csrf'=>csrf_token()]);
}
if ($action === 'employees_presence' && $method === 'GET') {
    // Лёгкий endpoint для обновления статусов без повторной загрузки карточек и статистики.
    $sql = "SELECT u.id,u.is_active,
        CASE WHEN p.last_seen >= DATE_SUB(NOW(), INTERVAL 90 SECOND) THEN 1 ELSE 0 END AS is_online
        FROM sb_users u LEFT JOIN sb_user_presence p ON p.user_id=u.id WHERE 1=1";
    $params = [];
    if ($ctx['role'] === 'user') {
        $sql .= ' AND u.id=:self_id';
        $params[':self_id'] = (int)$ctx['id'];
    } elseif ($ctx['role'] === 'moderator') {
        $sql .= " AND (u.id=:self_id OR (u.id<>:self_id2 AND EXISTS (
            SELECT 1 FROM sb_user_objects tuo
            JOIN sb_user_objects muo ON muo.object_id=tuo.object_id
            JOIN sb_objects ob ON ob.id=tuo.object_id AND ob.is_active=1
            WHERE tuo.user_id=u.id AND muo.user_id=:moderator_id
        )))";
        $params[':self_id'] = (int)$ctx['id'];
        $params[':self_id2'] = (int)$ctx['id'];
        $params[':moderator_id'] = (int)$ctx['id'];
    }
    $sql .= ' ORDER BY u.id';
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $employees = $st->fetchAll();
    foreach ($employees as &$employee) {
        $employee['id'] = (int)$employee['id'];
        $employee['is_active'] = (int)$employee['is_active'];
        $employee['is_online'] = (int)$employee['is_online'];
    }
    unset($employee);
    reply(200, ['ok' => true, 'employees' => $employees]);
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
    notification_add($pdo, $targetId, 'security', 'Смена пароля', 'Администратор создал одноразовую ссылку для смены пароля. Если вы не ожидали этого, обратитесь к руководителю.', 'index.html');
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

    $where = ['a.report_date >= :date_from', 'a.report_date <= :date_to'];
    $baseParams = [':date_from' => $from, ':date_to' => $to];

    if ($ctx['role'] !== 'admin') {
        if (!$allowedIds) reply(200, ['ok' => true, 'objects' => [], 'employees' => [], 'records' => [], 'summary' => ['total' => 0, 'objects' => 0, 'employees' => 0], 'page' => 1, 'per_page' => 25, 'pages' => 0, 'user' => $ctx, 'csrf' => csrf_token()]);
        $idMarks = [];
        foreach ($allowedIds as $i => $id) {
            $key = ':allowed_id_' . $i;
            $idMarks[] = $key;
            $baseParams[$key] = $id;
        }
        $nameMarks = [];
        foreach ($allowedNames as $i => $name) {
            $key = ':allowed_name_' . $i;
            $nameMarks[] = $key;
            $baseParams[$key] = $name;
        }
        $where[] = '(a.object_id IN (' . implode(',', $idMarks) . ') OR (a.object_id IS NULL AND a.object_name IN (' . implode(',', $nameMarks) . ')))';
    }

    if ($objectIds) {
        $selectedNameMarks = [];
        $idMarks = [];
        foreach ($objectIds as $i => $id) {
            $idKey = ':selected_id_' . $i;
            $nameKey = ':selected_name_' . $i;
            $idMarks[] = $idKey;
            $selectedNameMarks[] = $nameKey;
            $baseParams[$idKey] = $id;
            $baseParams[$nameKey] = (string)$availableById[$id]['name'];
        }
        $where[] = '(a.object_id IN (' . implode(',', $idMarks) . ') OR (a.object_id IS NULL AND a.object_name IN (' . implode(',', $selectedNameMarks) . ')))';
    }

    // Пользователь видит только собственные отчёты; модератор — отчёты по своим объектам.
    if ($ctx['role'] === 'user') {
        $where[] = 'a.owner_user_id = :viewer_owner_id';
        $baseParams[':viewer_owner_id'] = (int)$ctx['id'];
    }

    $baseWhere = implode(' AND ', $where);
    $employeeSql = "SELECT DISTINCT COALESCE(a.owner_user_id, CASE WHEN JSON_VALID(a.payload) THEN
            CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.owner_user_id')), '') AS UNSIGNED)
            ELSE NULL END) AS owner_user_id,
        COALESCE(NULLIF(a.owner_name, ''), CASE WHEN JSON_VALID(a.payload) THEN
            COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.owner_name')), ''),
                     NULLIF(JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.data.b0_fio')), ''))
            ELSE '' END) AS employee_name
        FROM sb_archive_records a WHERE " . $baseWhere . "
        ORDER BY employee_name";
    $employeeStmt = $pdo->prepare($employeeSql);
    $employeeStmt->execute($baseParams);
    $employees = [];
    foreach ($employeeStmt->fetchAll() as $employee) {
        $employeeIdOption = (int)($employee['owner_user_id'] ?? 0);
        if ($employeeIdOption > 0) $employees[] = ['id' => $employeeIdOption, 'name' => (string)($employee['employee_name'] ?? '')];
    }

    $filteredWhere = $where;
    $params = $baseParams;
    $employeeId = (int)($_GET['employee_id'] ?? 0);
    if ($employeeId > 0) {
        $filteredWhere[] = "COALESCE(a.owner_user_id, CASE WHEN JSON_VALID(a.payload) THEN
            CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.owner_user_id')), '') AS UNSIGNED)
            ELSE NULL END) = :selected_employee_id";
        $params[':selected_employee_id'] = $employeeId;
    }
    $search = trim((string)($_GET['search'] ?? ''));
    if ($search !== '') {
        $search = mb_substr($search, 0, 120);
        $search = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);
        $filteredWhere[] = "CONCAT_WS(' ', a.report_date, a.object_name, COALESCE(NULLIF(a.owner_name, ''), CASE WHEN JSON_VALID(a.payload) THEN JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.owner_name')) ELSE '' END),
            CASE WHEN JSON_VALID(a.payload) THEN
                COALESCE(JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.data.b0_fio')),
                         JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.fio')), '')
            ELSE '' END) LIKE :search ESCAPE '!'";
        $params[':search'] = '%' . $search . '%';
    }
    $filteredSql = implode(' AND ', $filteredWhere);

    $summaryStmt = $pdo->prepare("SELECT COUNT(*) AS total,
        COUNT(DISTINCT a.object_name) AS object_count,
        COUNT(DISTINCT COALESCE(CAST(a.owner_user_id AS CHAR),
            CASE WHEN JSON_VALID(a.payload) THEN NULLIF(JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.owner_user_id')), '') ELSE NULL END,
            NULLIF(a.owner_name, ''))) AS employee_count
        FROM sb_archive_records a WHERE " . $filteredSql);
    $summaryStmt->execute($params);
    $summary = $summaryStmt->fetch() ?: ['total' => 0, 'object_count' => 0, 'employee_count' => 0];
    $total = (int)$summary['total'];

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = (int)($_GET['per_page'] ?? 25);
    if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 25;
    $pages = $total > 0 ? (int)ceil($total / $perPage) : 0;
    $page = $pages > 0 ? min($page, $pages) : 1;
    $offset = ($page - 1) * $perPage;

    // В список передаются только поля таблицы. Большой payload документа загружается
    // отдельным запросом при открытии предпросмотра или скачивании.
    $listSql = "SELECT a.record_key, a.report_date, a.object_name, a.owner_user_id, a.owner_name,
        a.object_id, a.updated_at,
        COALESCE(a.owner_user_id, CASE WHEN JSON_VALID(a.payload) THEN
            CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.owner_user_id')), '') AS UNSIGNED)
            ELSE NULL END) AS resolved_owner_user_id,
        COALESCE(NULLIF(a.owner_name, ''), CASE WHEN JSON_VALID(a.payload) THEN JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.owner_name')) ELSE '' END) AS resolved_owner_name,
        CASE WHEN JSON_VALID(a.payload) THEN
            COALESCE(JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.data.b0_fio')),
                     JSON_UNQUOTE(JSON_EXTRACT(a.payload, '$.fio')), '')
            ELSE '' END AS employee_name
        FROM sb_archive_records a WHERE " . $filteredSql . "
        ORDER BY a.report_date DESC, a.object_name ASC, a.updated_at DESC, a.record_key ASC
        LIMIT :limit OFFSET :offset";
    $listStmt = $pdo->prepare($listSql);
    foreach ($params as $key => $value) $listStmt->bindValue($key, $value);
    $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStmt->execute();

    $records = [];
    foreach ($listStmt->fetchAll() as $row) {
        $records[] = [
            'record_key' => (string)$row['record_key'],
            'date' => (string)$row['report_date'],
            'object' => (string)$row['object_name'],
            'employee' => (string)($row['employee_name'] ?? ''),
            'author' => (string)($row['resolved_owner_name'] ?? ''),
            'owner_user_id' => $row['resolved_owner_user_id'] !== null ? (int)$row['resolved_owner_user_id'] : null,
            'object_id' => $row['object_id'] !== null ? (int)$row['object_id'] : null,
            'updated_at' => (string)$row['updated_at'],
        ];
    }

    reply(200, [
        'ok' => true,
        'objects' => $assignedRows,
        'employees' => $employees,
        'records' => $records,
        'summary' => ['total' => $total, 'objects' => (int)$summary['object_count'], 'employees' => (int)$summary['employee_count']],
        'page' => $page,
        'per_page' => $perPage,
        'pages' => $pages,
        'user' => $ctx,
        'from' => $from,
        'to' => $to,
        'csrf' => csrf_token()
    ]);
}

if ($action === 'report_detail' && $method === 'GET') {
    $recordKey = trim((string)($_GET['record_key'] ?? ''));
    if (!preg_match('/^[rd]:[a-f0-9]{64}$/i', $recordKey)) {
        reply(400, ['ok' => false, 'error' => 'Некорректный идентификатор отчёта.']);
    }
    $stmt = $pdo->prepare('SELECT record_key, object_id, object_name, owner_user_id, payload
        FROM sb_archive_records WHERE record_key = :k LIMIT 1');
    $stmt->execute([':k' => $recordKey]);
    $row = $stmt->fetch();
    if (!$row) reply(404, ['ok' => false, 'error' => 'Отчёт не найден.']);

    if ($ctx['role'] === 'user') {
        if ($row['owner_user_id'] === null || (int)$row['owner_user_id'] !== (int)$ctx['id']) {
            reply(403, ['ok' => false, 'error' => 'Нет доступа к этому отчёту.']);
        }
    } elseif ($ctx['role'] === 'moderator') {
        $assignedIds = [];
        $assignedNames = [];
        foreach (get_user_object_rows($pdo, (int)$ctx['id']) as $assigned) {
            if ((int)$assigned['is_active'] !== 1) continue;
            $assignedIds[(int)$assigned['id']] = true;
            $assignedNames[(string)$assigned['name']] = true;
        }
        $allowed = $row['object_id'] !== null
            ? isset($assignedIds[(int)$row['object_id']])
            : isset($assignedNames[(string)$row['object_name']]);
        if (!$allowed) reply(403, ['ok' => false, 'error' => 'Нет доступа к этому отчёту.']);
    } elseif ($ctx['role'] !== 'admin') {
        reply(403, ['ok' => false, 'error' => 'Недостаточно прав.']);
    }

    $item = json_decode((string)$row['payload'], true);
    if (!is_array($item)) reply(500, ['ok' => false, 'error' => 'Не удалось прочитать данные отчёта.']);
    reply(200, [
        'ok' => true,
        'data' => isset($item['data']) && is_array($item['data']) ? $item['data'] : [],
        'dyn' => isset($item['dyn']) && is_array($item['dyn']) ? $item['dyn'] : []
    ]);
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
        notification_add($pdo, $newId, 'account', 'Учётная запись создана', 'Для вас создана учётная запись на сайте СБ. Используйте одноразовую ссылку для установки пароля.', 'activate.php');
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
