<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

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

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token(): string {
    if (empty($_SESSION['sb_csrf'])) $_SESSION['sb_csrf'] = bin2hex(random_bytes(32));
    return (string)$_SESSION['sb_csrf'];
}

$err = '';
$cfg = require __DIR__ . '/config.php';
try {
    $dsn = 'mysql:host=' . $cfg['db_host'] . ';dbname=' . $cfg['db_name'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, (string)$cfg['db_user'], (string)$cfg['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS sb_users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(64) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL DEFAULT '',
        role VARCHAR(16) NOT NULL DEFAULT 'user',
        object_id INT UNSIGNED NULL DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS sb_objects (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    error_log('SB users page database error: ' . $e->getMessage());
    http_response_code(500);
    exit('Не удалось подключиться к базе данных. Проверьте настройки сайта.');
}

if (empty($_SESSION['sb_user_id'])) {
    header('Location: index.html');
    exit;
}
$st = $pdo->prepare('SELECT * FROM sb_users WHERE id = :id AND is_active = 1 LIMIT 1');
$st->execute([':id' => (int)$_SESSION['sb_user_id']]);
$me = $st->fetch();
if (!$me) {
    header('Location: index.html');
    exit;
}
if ($me['role'] !== 'admin') {
    http_response_code(403);
    exit('Доступ к управлению пользователями разрешён только администратору.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        $err = 'Сессия формы устарела. Обновите страницу и повторите действие.';
    } else {
        $act = (string)($_POST['act'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        if ($act === 'object_save') {
            $name = trim((string)($_POST['name'] ?? ''));
            $active = !empty($_POST['is_active']) ? 1 : 0;
            if ($name === '' || mb_strlen($name) > 255) {
                $err = 'Укажите название объекта (до 255 символов).';
            } else {
                $dup = $pdo->prepare('SELECT COUNT(*) FROM sb_objects WHERE name = :n AND id <> :id');
                $dup->execute([':n' => $name, ':id' => $id]);
                if ((int)$dup->fetchColumn() > 0) {
                    $err = 'Объект с таким названием уже существует.';
                } elseif ($id > 0) {
                    $st = $pdo->prepare('UPDATE sb_objects SET name = :n, is_active = :a WHERE id = :id');
                    $st->execute([':n' => $name, ':a' => $active, ':id' => $id]);
                } else {
                    $st = $pdo->prepare('INSERT INTO sb_objects (name, is_active) VALUES (:n, :a)');
                    $st->execute([':n' => $name, ':a' => $active]);
                }
            }
        } elseif ($act === 'user_save') {
            $username = trim((string)($_POST['username'] ?? ''));
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $role = (string)($_POST['role'] ?? 'user');
            $objectId = ($_POST['object_id'] ?? '') === '' ? null : (int)$_POST['object_id'];
            $isActive = !empty($_POST['is_active']) ? 1 : 0;
            $password = (string)($_POST['password'] ?? '');
            if (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) {
                $err = 'Логин: 3–64 символа (латиница, цифры, . _ -).';
            } elseif ($fullName === '') {
                $err = 'Укажите ФИО.';
            } elseif (!in_array($role, ['admin', 'moderator', 'user'], true)) {
                $err = 'Недопустимая роль.';
            } elseif ($id === (int)$me['id'] && (!$isActive || $role !== 'admin')) {
                $err = 'Нельзя отключить себя или снять с себя роль администратора.';
            } elseif ($password !== '' && strlen($password) < 6) {
                $err = 'Пароль — не менее 6 символов.';
            } else {
                $dup = $pdo->prepare('SELECT COUNT(*) FROM sb_users WHERE username = :u AND id <> :i');
                $dup->execute([':u' => $username, ':i' => $id]);
                if ((int)$dup->fetchColumn() > 0) {
                    $err = 'Такой логин уже занят.';
                } elseif ($id > 0) {
                    $st = $pdo->prepare('UPDATE sb_users SET username=:u, full_name=:f, role=:r, object_id=:o, is_active=:a WHERE id=:id');
                    $st->execute([':u' => $username, ':f' => $fullName, ':r' => $role, ':o' => $objectId, ':a' => $isActive, ':id' => $id]);
                    if ($password !== '') {
                        $st = $pdo->prepare('UPDATE sb_users SET password_hash = :h WHERE id = :id');
                        $st->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $id]);
                    }
                } elseif (strlen($password) < 6) {
                    $err = 'Пароль нового пользователя — не менее 6 символов.';
                } else {
                    $st = $pdo->prepare('INSERT INTO sb_users (username, password_hash, full_name, role, object_id, is_active) VALUES (:u, :h, :f, :r, :o, 1)');
                    $st->execute([':u' => $username, ':h' => password_hash($password, PASSWORD_DEFAULT), ':f' => $fullName, ':r' => $role, ':o' => $objectId]);
                }
            }
        } elseif ($act === 'user_toggle') {
            if ($id === (int)$me['id']) {
                $err = 'Нельзя отключить собственную учётную запись.';
            } else {
                $st = $pdo->prepare('UPDATE sb_users SET is_active = 1 - is_active WHERE id = :id');
                $st->execute([':id' => $id]);
            }
        }
    }
}

$users = $pdo->query('SELECT id, username, full_name, role, object_id, is_active, created_at FROM sb_users ORDER BY id')->fetchAll();
$objects = $pdo->query('SELECT id, name, is_active FROM sb_objects ORDER BY name')->fetchAll();
$objName = [];
foreach ($objects as $o) { $objName[(int)$o['id']] = $o['name']; }
$roleNames = ['admin' => 'Администратор', 'moderator' => 'Модератор', 'user' => 'Пользователь'];
?>
<!DOCTYPE html>
<html lang="ru"><head><meta charset="UTF-8"><title>Управление пользователями — отчет СБ</title>
<style>
body{font-family:Arial,sans-serif;background:#F0F7F7;color:#1A2E35;margin:0;padding:24px}
.wrap{max-width:1000px;margin:0 auto}
.card{background:#fff;border-radius:16px;padding:24px;box-shadow:0 12px 48px rgba(13,115,119,.12);margin-bottom:24px}
h2{color:#0A5C5F;margin:0 0 16px}h3{color:#0A5C5F;margin:24px 0 8px}
table{width:100%;border-collapse:collapse;font-size:14px}
th,td{padding:8px;border-bottom:1px solid rgba(13,115,119,.12);text-align:left}
th{color:#5A7A80;font-size:12px;text-transform:uppercase}
label{display:block;font-size:13px;color:#5A7A80;margin:10px 0 4px}
input,select{width:100%;box-sizing:border-box;padding:9px;border:1px solid rgba(13,115,119,.25);border-radius:8px;font-size:14px}
.btn{display:inline-block;padding:10px 18px;border:none;border-radius:8px;background:#0D7377;color:#fff;font-weight:700;cursor:pointer;font-size:14px;text-decoration:none}
.btn.ghost{background:#E0F2F1;color:#0D7377}
.btn.danger{background:#FFE8E0;color:#FF6B47}
.row{display:flex;gap:12px;flex-wrap:wrap}.row>div{flex:1;min-width:160px}
.badge{padding:2px 8px;border-radius:8px;font-size:12px;font-weight:700}
.ok{color:#047857}.err{color:#FF6B47}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
</style></head><body><div class="wrap">
<div class="top">
<h2 style="margin:0">Управление пользователями</h2>
<a class="btn ghost" href="index.html">К отчетам</a>
</div>
<?php if ($err) echo '<p class="err">' . h($err) . '</p>'; ?>

<div class="card">
<h3>Торговые объекты</h3>
<form method="post">
<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="object_save">
<div class="row"><div><label>Название объекта</label><input name="name" required></div>
<div><label>Активен</label><select name="is_active"><option value="1" selected>Да</option><option value="0">Нет</option></select></div>
<div><label>&nbsp;</label><button class="btn">Добавить объект</button></div></div>
</form>
<table><tr><th>ID</th><th>Название</th><th>Статус</th></tr>
<?php foreach ($objects as $o): ?>
<tr>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="object_save"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
<td><?= (int)$o['id'] ?></td>
<td><input name="name" value="<?= h($o['name']) ?>"></td>
<td><select name="is_active"><option value="1" <?= $o['is_active'] ? 'selected' : '' ?>>Активен</option><option value="0" <?= !$o['is_active'] ? 'selected' : '' ?>>Отключен</option></select></td>
<td><button class="btn ghost">Сохранить</button></td>
</form>
</tr>
<?php endforeach; ?>
</table>
</div>

<div class="card">
<h3>Пользователи</h3>
<form method="post">
<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_save">
<div class="row">
<div><label>Логин</label><input name="username" required></div>
<div><label>ФИО</label><input name="full_name" required></div>
<div><label>Пароль (не менее 6)</label><input name="password" type="password" required></div>
<div><label>Роль</label><select name="role"><option value="user">Пользователь</option><option value="moderator">Модератор</option><option value="admin">Администратор</option></select></div>
<div><label>Объект</label><select name="object_id"><option value="">— не назначен —</option>
<?php foreach ($objects as $o) echo '<option value="' . (int)$o['id'] . '">' . h($o['name']) . '</option>'; ?>
</select></div>
<div><label>&nbsp;</label><button class="btn">Создать</button></div>
</div>
</form>
<table><tr><th>Логин</th><th>ФИО</th><th>Роль</th><th>Объект</th><th>Статус</th><th>Создан</th><th></th></tr>
<?php foreach ($users as $u): ?>
<tr>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_save"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
<td><input name="username" value="<?= h($u['username']) ?>" style="width:120px"></td>
<td><input name="full_name" value="<?= h($u['full_name']) ?>"></td>
<td><select name="role"><?php foreach ($roleNames as $rk => $rn) echo '<option value="' . $rk . '"' . ($u['role'] === $rk ? ' selected' : '') . '>' . h($rn) . '</option>'; ?></select></td>
<td><select name="object_id"><option value="">— не назначен —</option>
<?php foreach ($objects as $o) echo '<option value="' . (int)$o['id'] . '"' . ((int)$o['id'] === (int)$u['object_id'] ? ' selected' : '') . '>' . h($o['name']) . '</option>'; ?>
</select></td>
<td><select name="is_active"><option value="1" <?= $u['is_active'] ? 'selected' : '' ?>>Активен</option><option value="0" <?= !$u['is_active'] ? 'selected' : '' ?>>Отключен</option></select></td>
<td><?= h((string)$u['created_at']) ?></td>
<td><button class="btn ghost">Сохранить</button></td>
</form>
</tr>
<tr>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_save"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
<td colspan="7"><div class="row" style="max-width:420px"><div><label>Новый пароль (пусто — не менять)</label><input name="password" type="password"></div><div><label>&nbsp;</label><button class="btn ghost">Сменить пароль</button></div></div></td>
</form>
</tr>
<tr>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
<td colspan="7">
<span class="badge <?= $u['is_active'] ? 'ok' : 'err' ?>"><?= $u['is_active'] ? 'активен' : 'отключен' ?></span>
<button class="btn danger" <?= (int)$u['id'] === (int)$me['id'] ? 'disabled' : '' ?>><?= $u['is_active'] ? 'Отключить' : 'Включить' ?></button>
</td>
</form>
</tr>
<?php endforeach; ?>
</table>
</div>
</div></body></html>
