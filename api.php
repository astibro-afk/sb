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
    if ($date !== '' && $object !== '') {
        return 'r:' . hash('sha256', $date . "\0" . $object);
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
        ensure_archive_columns($pdo);
    } catch (Throwable $e) {
        error_log('SB storage database error: ' . $e->getMessage());
        reply(500, ['ok' => false, 'error' => 'Не удалось подключиться к MySQL. Проверьте config.php и наличие базы данных.']);
    }
    return $pdo;
}

function current_user(PDO $pdo): ?array {
    if (empty($_SESSION['sb_user_id'])) return null;
    $stmt = $pdo->prepare('SELECT * FROM sb_users WHERE id = :id AND is_active = 1');
    $stmt->execute([':id' => (int)$_SESSION['sb_user_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function user_ctx(PDO $pdo, array $u): array {
    $objectName = '';
    if (!empty($u['object_id'])) {
        $stmt = $pdo->prepare('SELECT name FROM sb_objects WHERE id = :id');
        $stmt->execute([':id' => (int)$u['object_id']]);
        $objectName = (string)($stmt->fetchColumn() ?: '');
    }
    return [
        'id' => (int)$u['id'],
        'username' => (string)$u['username'],
        'full_name' => (string)$u['full_name'],
        'role' => (string)$u['role'],
        'object_id' => $u['object_id'] !== null ? (int)$u['object_id'] : null,
        'object_name' => $objectName,
    ];
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
    $ownObject = (string)($ctx['object_name'] ?? '');

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

            // Разграничение по объекту: не-администратор работает только со своим объектом.
            if (!$isAdmin) {
                if ($object === '') $object = $ownObject;
                if ($object !== $ownObject) continue; // чужой объект — запись игнорируется
                $data['b0_to'] = $object;
            }

            $key = archive_record_key(['data' => ['b0_date' => $date, 'b0_to' => $object], 't' => (int)($record['t'] ?? 0), 'fio' => $fio]);
            $select->execute([':k' => $key]);
            $row = $select->fetch();
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
        }

        // Верхняя граница архива (400 записей) чистится только администратором,
        // чтобы ограниченный в правах пользователь не «вытеснял» чужие записи.
        if ($isAdmin) {
            $pdo->exec(
                'DELETE FROM sb_archive_records
                 WHERE record_key NOT IN (
                     SELECT record_key FROM (
                         SELECT record_key FROM sb_archive_records
                         ORDER BY report_date DESC, updated_at DESC LIMIT 400
                     ) AS keep_records
                 )'
            );
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

$cfg = require __DIR__ . '/config.php';
$action = (string)($_GET['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

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
    $body = input_json();
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
    reply(200, ['ok' => true, 'user' => user_ctx($pdo, $user), 'csrf' => $_SESSION['sb_csrf']]);
}

if ($action === 'logout' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
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

    // Архив фильтруется на сервере по роли.
    $ownObject = (string)$ctx['object_name'];
    $archiveStmt = $pdo->query('SELECT payload FROM sb_archive_records ORDER BY report_date DESC, updated_at DESC');
    $archive = [];
    foreach ($archiveStmt->fetchAll() as $row) {
        $item = json_decode((string)$row['payload'], true);
        if (!is_array($item)) continue;
        if ($ctx['role'] !== 'admin') {
            $obj = (string)($item['data']['b0_to'] ?? '');
            if ($obj !== $ownObject) continue;
        }
        $archive[] = $item;
    }
    $data[KEY_ARCH] = $archive;

    reply(200, ['ok' => true, 'data' => $data, 'user' => $ctx, 'objects' => $objects, 'csrf' => csrf_token()]);
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
        if ($ctx['role'] !== 'admin' && $ctx['object_name'] === '') {
            reply(403, ['ok' => false, 'error' => 'Администратор ещё не назначил вам торговый объект. Сохранение отчета невозможно.']);
        }
        try {
            save_archive($pdo, $body['value'], $ctx);
        } catch (Throwable $e) {
            error_log('SB archive save error: ' . $e->getMessage());
            reply(500, ['ok' => false, 'error' => 'Не удалось сохранить архив на сервере.']);
        }
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

if ($action === 'archive_delete' && $method === 'POST') {
    $body = input_json();
    check_csrf($body);
    $date = (string)($body['date'] ?? '');
    $object = (string)($body['object'] ?? '');
    $record = [
        'data' => ['b0_date' => $date, 'b0_to' => $object],
        't' => (int)($body['t'] ?? 0),
        'fio' => (string)($body['fio'] ?? ''),
    ];
    $key = archive_record_key($record);

    if ($ctx['role'] === 'user') {
        reply(403, ['ok' => false, 'error' => 'Удаление записей архива запрещено для вашей роли.']);
    }
    if ($ctx['role'] === 'moderator' && $object !== $ctx['object_name']) {
        reply(403, ['ok' => false, 'error' => 'Можно удалять записи только своего торгового объекта.']);
    }

    $stmt = $pdo->prepare('DELETE FROM sb_archive_records WHERE record_key = :k');
    $stmt->execute([':k' => $key]);
    reply(200, ['ok' => true]);
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
    $objectId = ($body['object_id'] ?? null) === null || $body['object_id'] === '' ? null : (int)$body['object_id'];
    $isActive = !empty($body['is_active']) ? 1 : 0;
    $password = (string)($body['password'] ?? '');

    if (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) {
        reply(400, ['ok' => false, 'error' => 'Логин: 3–64 символа — латиница, цифры, точка, подчёркивание, дефис.']);
    }
    if ($fullName === '' || mb_strlen($fullName) > 255) {
        reply(400, ['ok' => false, 'error' => 'Укажите ФИО (до 255 символов).']);
    }
    if (!in_array($role, ['admin', 'moderator', 'user'], true)) {
        reply(400, ['ok' => false, 'error' => 'Недопустимая роль.']);
    }
    if ($objectId !== null) {
        $chk = $pdo->prepare('SELECT COUNT(*) FROM sb_objects WHERE id = :id');
        $chk->execute([':id' => $objectId]);
        if (!(int)$chk->fetchColumn()) {
            reply(400, ['ok' => false, 'error' => 'Указанный торговый объект не найден.']);
        }
    }

    $dup = $pdo->prepare('SELECT COUNT(*) FROM sb_users WHERE username = :u AND id <> :id');
    $dup->execute([':u' => $username, ':id' => $id]);
    if ((int)$dup->fetchColumn() > 0) {
        reply(400, ['ok' => false, 'error' => 'Такой логин уже занят.']);
    }

    if ($id > 0) {
        if ($id === (int)$ctx['id'] && (!$isActive || $role !== 'admin')) {
            reply(400, ['ok' => false, 'error' => 'Нельзя отключить себя или снять с себя роль администратора.']);
        }
        if ($password !== '' && strlen($password) < 6) {
            reply(400, ['ok' => false, 'error' => 'Новый пароль — не менее 6 символов.']);
        }
        $stmt = $pdo->prepare('UPDATE sb_users SET username = :u, full_name = :f, role = :r, object_id = :o, is_active = :a WHERE id = :id');
        $stmt->execute([':u' => $username, ':f' => $fullName, ':r' => $role, ':o' => $objectId, ':a' => $isActive, ':id' => $id]);
        if ($password !== '') {
            $stmt = $pdo->prepare('UPDATE sb_users SET password_hash = :h WHERE id = :id');
            $stmt->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $id]);
        }
        reply(200, ['ok' => true]);
    }

    if (strlen($password) < 6) {
        reply(400, ['ok' => false, 'error' => 'Пароль нового пользователя — не менее 6 символов.']);
    }
    $stmt = $pdo->prepare('INSERT INTO sb_users (username, password_hash, full_name, role, object_id, is_active) VALUES (:u, :h, :f, :r, :o, :a)');
    $stmt->execute([':u' => $username, ':h' => password_hash($password, PASSWORD_DEFAULT), ':f' => $fullName, ':r' => $role, ':o' => $objectId, ':a' => $isActive]);
    reply(200, ['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
}

if ($action === 'objects_list' && $method === 'GET') {
    $rows = $pdo->query('SELECT id, name, is_active FROM sb_objects ORDER BY name')->fetchAll();
    if ($ctx['role'] !== 'admin') {
        $rows = array_values(array_filter($rows, function($r) { return (int)$r['is_active'] === 1; }));
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
        reply(200, ['ok' => true]);
    }
    $stmt = $pdo->prepare('INSERT INTO sb_objects (name, is_active) VALUES (:n, :a)');
    $stmt->execute([':n' => $name, ':a' => $isActive]);
    reply(200, ['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
}

reply(404, ['ok' => false, 'error' => 'Неизвестная операция.']);
