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

        if ($act === 'object_delete') {
            $check = $pdo->prepare('SELECT COUNT(*) FROM sb_users WHERE object_id = :id');
            $check->execute([':id' => $id]);
            if ($id < 1) {
                $err = 'Не выбран объект для удаления.';
            } elseif ((int)$check->fetchColumn() > 0) {
                $err = 'Нельзя удалить объект: к нему привязаны пользователи. Сначала переназначьте их на другой объект.';
            } else {
                $del = $pdo->prepare('DELETE FROM sb_objects WHERE id = :id');
                $del->execute([':id' => $id]);
                if ($del->rowCount() === 0) $err = 'Объект не найден.';
            }
        } elseif ($act === 'user_delete') {
            if ($id < 1 || $id === (int)$me['id']) {
                $err = 'Нельзя удалить собственную учётную запись.';
            } else {
                $pdo->beginTransaction();
                try {
                    $lock = $pdo->prepare('SELECT id, role, is_active FROM sb_users WHERE id = :id FOR UPDATE');
                    $lock->execute([':id' => $id]);
                    $target = $lock->fetch();
                    if (!$target) {
                        $err = 'Пользователь не найден.';
                    } elseif ($target['role'] === 'admin' && (int)$target['is_active'] === 1) {
                        $admins = $pdo->query("SELECT COUNT(*) FROM sb_users WHERE role = 'admin' AND is_active = 1")->fetchColumn();
                        if ((int)$admins <= 1) {
                            $err = 'Нельзя удалить последнего активного администратора.';
                        }
                    }
                    if ($err === '') {
                        $del = $pdo->prepare('DELETE FROM sb_users WHERE id = :id');
                        $del->execute([':id' => $id]);
                    }
                    $pdo->commit();
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    throw $e;
                }
            }
        } elseif ($act === 'object_save') {
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
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#f3f7f8">
<title>Управление — отчет СБ</title>
<style>
:root{--ink:#18343a;--muted:#71858a;--line:#e5edef;--teal:#08777b;--teal-dark:#075e62;--pale:#eaf5f5;--bg:#f3f7f8;--white:#fff;--red:#b94b35;--red-bg:#fff0ec;--green:#16734f;--green-bg:#eaf7f0}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:13px/1.4 Arial,sans-serif}button,input,select{font:inherit}
.page{width:100%;max-width:none;margin:0;padding:16px 18px 30px}.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}.heading{display:flex;align-items:center;gap:10px}.logo{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:var(--teal);color:#fff;font-weight:800}h1{font-size:19px;margin:0}.subtitle{font-size:11px;color:var(--muted)}
.panel{background:#fff;border:1px solid #eaf0f1;border-radius:12px;padding:13px;margin-bottom:12px}.panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}.panel-title{font-size:15px;margin:0}.counter{font-size:11px;color:var(--muted);background:#f1f5f6;border-radius:20px;padding:3px 8px}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:31px;padding:5px 10px;border:0;border-radius:7px;background:var(--teal);color:#fff;text-decoration:none;font-weight:700;font-size:12px;cursor:pointer;white-space:nowrap}.btn:hover{background:var(--teal-dark)}.btn.secondary{background:var(--pale);color:var(--teal-dark)}.btn.danger{background:var(--red-bg);color:var(--red)}.btn:disabled{opacity:.4;cursor:not-allowed}
.fields{display:grid;grid-template-columns:1.2fr 1.4fr 1fr 1fr auto;gap:8px;align-items:end}.field{min-width:0}label{display:block;margin:0 0 3px;color:var(--muted);font-size:10px;font-weight:700}input,select{width:100%;height:31px;padding:5px 8px;border:1px solid #dce6e8;border-radius:7px;background:#fff;color:var(--ink);outline:none;min-width:0;font-size:12px}input:focus,select:focus{border-color:#65aeb0;box-shadow:0 0 0 2px #08777b16}
.objects{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px;margin-top:10px}.object-item{display:grid;grid-template-columns:minmax(0,1fr) 100px auto auto;gap:6px;align-items:center;padding:6px;background:#f8fbfb;border:1px solid var(--line);border-radius:8px}.object-item input,.object-item select{height:29px}.object-item form{margin:0}
.user-tools{display:flex;gap:8px;align-items:center;margin:10px 0}.user-tools input{max-width:300px}.user-list{display:grid;gap:5px;overflow-x:auto}.user-card{min-width:1080px;display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:6px;align-items:center;padding:6px 8px;border:1px solid var(--line);border-radius:8px;background:#fff}.user-main{display:grid;grid-template-columns:105px minmax(150px,1.3fr) 120px minmax(140px,1fr) 88px 130px 150px;gap:6px;align-items:end}.user-main label{font-size:9px}.user-main input,.user-main select{height:30px}.user-main .btn{width:100%;height:30px}.mini-form{margin:0}.mini-form input{width:115px}.status{font-size:10px;font-weight:700;padding:4px 7px;border-radius:20px;white-space:nowrap}.active{color:var(--green);background:var(--green-bg)}.off{color:var(--red);background:var(--red-bg)}.created{font-size:10px;color:var(--muted);white-space:nowrap}.alert{padding:9px 11px;border-radius:8px;margin:0 0 10px;background:var(--red-bg);color:var(--red);font-size:12px}.empty{padding:18px;text-align:center;color:var(--muted);border:1px dashed #d8e3e5;border-radius:9px}
@media(max-width:760px){.page{padding:12px}.fields{grid-template-columns:repeat(2,minmax(0,1fr))}.objects{grid-template-columns:1fr}.object-item{grid-template-columns:minmax(0,1fr) 90px auto auto}.panel{padding:11px}}
</style>
</head>
<body>
<div class="page">
<header class="header">
  <div class="heading"><div class="logo">СБ</div><div><h1>Управление</h1><div class="subtitle">Пользователи и торговые объекты</div></div></div>
  <a class="btn secondary" href="index.html">← К отчетам</a>
</header>

<?php if ($err): ?><div class="alert"><?= h($err) ?></div><?php endif; ?>

<section class="panel">
  <div class="panel-head"><h2 class="panel-title">Торговые объекты</h2><span class="counter"><?= count($objects) ?> объектов</span></div>
  <form method="post" class="fields">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="object_save">
    <div class="field wide"><label>Название нового объекта</label><input name="name" placeholder="Введите название" required></div>
    <div class="field"><label>Статус</label><select name="is_active"><option value="1" selected>Активен</option><option value="0">Отключен</option></select></div>
    <div class="field"><label>&nbsp;</label><button class="btn" type="submit">＋ Добавить объект</button></div>
  </form>
  <?php if ($objects): ?><div class="objects">
    <?php foreach ($objects as $o): ?>
    <form method="post" class="object-item">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="object_save"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
      <input name="name" aria-label="Название объекта" value="<?= h($o['name']) ?>" required>
      <select name="is_active" aria-label="Статус объекта"><option value="1" <?= $o['is_active'] ? 'selected' : '' ?>>Активен</option><option value="0" <?= !$o['is_active'] ? 'selected' : '' ?>>Отключен</option></select>
      <button class="btn secondary" type="submit">Сохранить</button>
    </form>
    <?php endforeach; ?>
  </div><?php endif; ?>
</section>

<section class="panel">
  <div class="panel-head"><h2 class="panel-title">Пользователи</h2><span class="counter"><?= count($users) ?> учётных записей</span></div>
  <form method="post" class="fields">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_save">
    <div class="field"><label>Логин</label><input name="username" placeholder="Логин" required></div>
    <div class="field"><label>ФИО</label><input name="full_name" placeholder="Фамилия Имя Отчество" required></div>
    <div class="field"><label>Пароль</label><input name="password" type="password" minlength="6" placeholder="От 6 символов" required></div>
    <div class="field"><label>Роль</label><select name="role"><option value="user">Пользователь</option><option value="moderator">Модератор</option><option value="admin">Администратор</option></select></div>
    <div class="field"><label>Объект</label><select name="object_id"><option value="">— не назначен —</option><?php foreach ($objects as $o): ?><option value="<?= (int)$o['id'] ?>"><?= h($o['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>&nbsp;</label><button class="btn" type="submit">＋ Создать пользователя</button></div>
  </form>

  <div class="user-tools" style="margin-top:16px"><input id="userSearch" type="search" placeholder="Поиск по логину или ФИО…" aria-label="Поиск пользователей"><span class="counter" id="visibleCount"><?= count($users) ?> показано</span></div>
  <div class="user-list" id="userList">
  <?php foreach ($users as $u): ?>
    <article class="user-card" data-search="<?= h(mb_strtolower($u['username'].' '.$u['full_name'].' '.($objName[(int)$u['object_id']] ?? ''), 'UTF-8')) ?>">
      <form method="post" class="user-main">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_save"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
        <div class="field"><label>Логин · ID <?= (int)$u['id'] ?></label><input name="username" value="<?= h($u['username']) ?>" required></div>
        <div class="field"><label>ФИО</label><input name="full_name" value="<?= h($u['full_name']) ?>" required></div>
        <div class="field"><label>Роль</label><select name="role"><?php foreach ($roleNames as $rk => $rn): ?><option value="<?= h($rk) ?>" <?= $u['role'] === $rk ? 'selected' : '' ?>><?= h($rn) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Объект</label><select name="object_id"><option value="">— не назначен —</option><?php foreach ($objects as $o): ?><option value="<?= (int)$o['id'] ?>" <?= (int)$o['id'] === (int)$u['object_id'] ? 'selected' : '' ?>><?= h($o['name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Статус</label><select name="is_active"><option value="1" <?= $u['is_active'] ? 'selected' : '' ?>>Активен</option><option value="0" <?= !$u['is_active'] ? 'selected' : '' ?>>Отключен</option></select></div>
        <div class="field"><label>Новый пароль (необязательно)</label><input name="password" type="password" minlength="6" placeholder="Не менять"></div>
        <div class="field"><label>&nbsp;</label><button class="btn secondary" type="submit">Сохранить</button></div>
      </form>
      <span class="status <?= $u['is_active'] ? 'active' : 'off' ?>"><?= $u['is_active'] ? 'Активен' : 'Отключен' ?></span>
      <div style="display:flex;gap:5px;align-items:center">
        <form method="post" class="mini-form"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn danger" type="submit" <?= (int)$u['id'] === (int)$me['id'] ? 'disabled' : '' ?>><?= $u['is_active'] ? 'Отключить' : 'Включить' ?></button></form>
        <form method="post" onsubmit="return confirm('Удалить пользователя «<?= h($u['username']) ?>»? Это действие нельзя отменить.');"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn danger" type="submit" <?= (int)$u['id'] === (int)$me['id'] ? 'disabled' : '' ?>>Удалить</button></form>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
  <div class="empty" id="noUsers" hidden>Пользователи не найдены.</div>
</section>
</div>
<script>
(function(){
  const input=document.getElementById('userSearch');
  const cards=Array.from(document.querySelectorAll('.user-card'));
  const count=document.getElementById('visibleCount');
  const empty=document.getElementById('noUsers');
  if(!input)return;
  input.addEventListener('input',function(){
    const q=this.value.trim().toLocaleLowerCase('ru');
    let shown=0;
    cards.forEach(card=>{
      const match=(card.dataset.search||'').includes(q);
      card.hidden=!match;
      if(match)shown++;
    });
    count.textContent=shown+' показано';
    empty.hidden=shown!==0;
  });
})();
</script>
</body>
</html>