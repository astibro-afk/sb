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
function users_audit_log(PDO $pdo, array $actor, string $action, string $type, ?int $targetId, string $label, array $details = []): void {
    try {
        $st = $pdo->prepare('INSERT INTO sb_audit_log (actor_user_id, actor_name, action, target_type, target_id, target_label, details) VALUES (:uid,:name,:action,:type,:target_id,:label,:details)');
        $st->execute([':uid' => (int)$actor['id'], ':name' => (string)$actor['full_name'], ':action' => $action, ':type' => $type, ':target_id' => $targetId, ':label' => mb_substr($label,0,255), ':details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null]);
    } catch (Throwable $e) { error_log('SB users audit error: '.$e->getMessage()); }
}

function users_notification_add(PDO $pdo, int $userId, string $type, string $title, string $message, string $link = ''): void {
    if ($userId < 1) return;
    try {
        $st=$pdo->prepare('INSERT INTO sb_notifications (user_id,type,title,message,link) VALUES (:uid,:type,:title,:message,:link)');
        $st->execute([':uid'=>$userId,':type'=>$type,':title'=>$title,':message'=>$message,':link'=>$link]);
    } catch (Throwable $e) { error_log('SB users notification error: '.$e->getMessage()); }
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
    $pdo->exec("CREATE TABLE IF NOT EXISTS sb_user_objects (
        user_id INT UNSIGNED NOT NULL,
        object_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, object_id),
        INDEX idx_user_objects_object (object_id),
        CONSTRAINT fk_user_objects_user FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE,
        CONSTRAINT fk_user_objects_object FOREIGN KEY (object_id) REFERENCES sb_objects(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("INSERT IGNORE INTO sb_user_objects (user_id, object_id)
        SELECT id, object_id FROM sb_users WHERE object_id IS NOT NULL");
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
        INDEX idx_audit_created (created_at), INDEX idx_audit_actor (actor_user_id), INDEX idx_audit_action (action)
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
    $pdo->exec("ALTER TABLE sb_users MODIFY role VARCHAR(64) NOT NULL DEFAULT 'user'");
    $pdo->exec("CREATE TABLE IF NOT EXISTS sb_roles (
        role_key VARCHAR(64) NOT NULL PRIMARY KEY,
        display_name VARCHAR(80) NOT NULL,
        base_role VARCHAR(16) NOT NULL,
        is_system TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_sb_roles_display_name (display_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS sb_role_permissions (
        role_key VARCHAR(64) NOT NULL,
        permission_key VARCHAR(100) NOT NULL,
        is_allowed TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (role_key,permission_key),
        CONSTRAINT fk_sb_role_permission_role FOREIGN KEY (role_key) REFERENCES sb_roles(role_key) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $seedRole = $pdo->prepare("INSERT IGNORE INTO sb_roles (role_key,display_name,base_role,is_system) VALUES (:k,:n,:b,1)");
    foreach ([['admin','Администратор','admin'],['moderator','Модератор','moderator'],['user','Пользователь','user']] as $rr) $seedRole->execute([':k'=>$rr[0],':n'=>$rr[1],':b'=>$rr[2]]);
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

        if ($act === 'role_save') {
            $roleKey = trim((string)($_POST['role_key'] ?? ''));
            $displayName = trim((string)($_POST['display_name'] ?? ''));
            $baseRole = (string)($_POST['base_role'] ?? 'user');
            if ($displayName === '' || mb_strlen($displayName) > 80) {
                $err = 'Название роли должно содержать от 1 до 80 символов.';
            } elseif (!in_array($baseRole, ['user','moderator'], true)) {
                $err = 'Для новой роли выберите основу «Пользователь» или «Модератор».';
            } elseif ($roleKey !== '' && !preg_match('/^[a-z][a-z0-9_]{1,63}$/', $roleKey)) {
                $err = 'Некорректный идентификатор роли.';
            } else {
                $dup = $pdo->prepare('SELECT COUNT(*) FROM sb_roles WHERE display_name=:name AND role_key<>:key');
                $dup->execute([':name'=>$displayName, ':key'=>$roleKey]);
                if ((int)$dup->fetchColumn() > 0) $err = 'Роль с таким названием уже существует.';
                elseif ($roleKey !== '') {
                    $exists = $pdo->prepare('SELECT is_system FROM sb_roles WHERE role_key=:key');
                    $exists->execute([':key'=>$roleKey]);
                    $system = $exists->fetchColumn();
                    if ($system === false) $err = 'Роль не найдена.';
                    else {
                        $st = $pdo->prepare('UPDATE sb_roles SET display_name=:name WHERE role_key=:key');
                        $st->execute([':name'=>$displayName, ':key'=>$roleKey]);
                        users_audit_log($pdo,$me,'role_renamed','role',null,$displayName,['role_key'=>$roleKey]);
                    }
                } else {
                    $newKey = 'custom_' . bin2hex(random_bytes(8));
                    $st = $pdo->prepare('INSERT INTO sb_roles (role_key,display_name,base_role,is_system) VALUES (:key,:name,:base,0)');
                    $st->execute([':key'=>$newKey,':name'=>$displayName,':base'=>$baseRole]);
                    users_audit_log($pdo,$me,'role_created','role',null,$displayName,['role_key'=>$newKey,'base_role'=>$baseRole]);
                }
            }
        } elseif ($act === 'role_delete') {
            $roleKey = trim((string)($_POST['role_key'] ?? ''));
            $st = $pdo->prepare('SELECT display_name,is_system FROM sb_roles WHERE role_key=:key');
            $st->execute([':key'=>$roleKey]);
            $role = $st->fetch();
            if (!$role) $err = 'Роль не найдена.';
            elseif ((int)$role['is_system'] === 1) $err = 'Системные роли нельзя удалять.';
            else {
                $used = $pdo->prepare('SELECT COUNT(*) FROM sb_users WHERE role=:key');
                $used->execute([':key'=>$roleKey]);
                if ((int)$used->fetchColumn() > 0) $err = 'Нельзя удалить роль, пока она назначена сотрудникам.';
                else {
                    $pdo->prepare('DELETE FROM sb_roles WHERE role_key=:key')->execute([':key'=>$roleKey]);
                    users_audit_log($pdo,$me,'role_deleted','role',null,(string)$role['display_name'],['role_key'=>$roleKey]);
                }
            }
        } elseif ($act === 'object_delete') {
            $nameStmt = $pdo->prepare('SELECT name FROM sb_objects WHERE id = :id');
            $nameStmt->execute([':id' => $id]);
            $objectLabel = (string)($nameStmt->fetchColumn() ?: 'Объект #'.$id);
            $check = $pdo->prepare('SELECT COUNT(*) FROM sb_user_objects WHERE object_id = :id');
            $check->execute([':id' => $id]);
            if ($id < 1) {
                $err = 'Не выбран объект для удаления.';
            } elseif ((int)$check->fetchColumn() > 0) {
                $err = 'Нельзя удалить объект: к нему привязаны пользователи. Сначала переназначьте их на другой объект.';
            } else {
                $del = $pdo->prepare('DELETE FROM sb_objects WHERE id = :id');
                $del->execute([':id' => $id]);
                if ($del->rowCount() === 0) $err = 'Объект не найден.';
                else users_audit_log($pdo, $me, 'object_deleted', 'object', $id, $objectLabel);
            }
        } elseif ($act === 'user_delete') {
            if ($id < 1 || $id === (int)$me['id']) {
                $err = 'Нельзя удалить собственную учётную запись.';
            } else {
                $pdo->beginTransaction();
                try {
                    $lock = $pdo->prepare('SELECT id, role, is_active, username, full_name FROM sb_users WHERE id = :id FOR UPDATE');
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
                        users_audit_log($pdo, $me, 'user_deleted', 'user', $id, (string)$target['username'], ['full_name' => $target['full_name']]);
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
                    users_audit_log($pdo, $me, 'object_updated', 'object', $id, $name, ['active' => $active]);
                } else {
                    $st = $pdo->prepare('INSERT INTO sb_objects (name, is_active) VALUES (:n, :a)');
                    $st->execute([':n' => $name, ':a' => $active]);
                    $id = (int)$pdo->lastInsertId();
                    users_audit_log($pdo, $me, 'object_created', 'object', $id, $name, ['active' => $active]);
                }
            }
        } elseif ($act === 'user_save') {
            $username = trim((string)($_POST['username'] ?? ''));
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $role = (string)($_POST['role'] ?? 'user');
            $rawObjectIds = $_POST['object_ids'] ?? [];
            if (!is_array($rawObjectIds)) $rawObjectIds = [];
            $objectIds = array_values(array_unique(array_filter(array_map('intval', $rawObjectIds), function($v) { return $v > 0; })));
            $objectId = $objectIds ? $objectIds[0] : null;
            $isActive = !empty($_POST['is_active']) ? 1 : 0;
            $password = (string)($_POST['password'] ?? '');
            if (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) {
                $err = 'Логин: 3–64 символа (латиница, цифры, . _ -).';
            } elseif ($fullName === '') {
                $err = 'Укажите ФИО.';
            } elseif (!(function() use ($pdo,$role) { $q=$pdo->prepare('SELECT COUNT(*) FROM sb_roles WHERE role_key=:key'); $q->execute([':key'=>$role]); return (int)$q->fetchColumn()===1; })()) {
                $err = 'Недопустимая роль.';
            } elseif ($id === (int)$me['id'] && (!$isActive || $role !== 'admin')) {
                $err = 'Нельзя отключить себя или снять с себя роль администратора.';
            } elseif ($password !== '' && strlen($password) < 8) {
                $err = 'Пароль — не менее 8 символов.';
            } else {
                $dup = $pdo->prepare('SELECT COUNT(*) FROM sb_users WHERE username = :u AND id <> :i');
                $dup->execute([':u' => $username, ':i' => $id]);
                if ((int)$dup->fetchColumn() > 0) {
                    $err = 'Такой логин уже занят.';
                } else {
                    if ($objectIds) {
                        $checkObjects = $pdo->prepare('SELECT COUNT(*) FROM sb_objects WHERE id IN (' . implode(',', array_fill(0, count($objectIds), '?')) . ')');
                        $checkObjects->execute($objectIds);
                        if ((int)$checkObjects->fetchColumn() !== count($objectIds)) $err = 'Один или несколько выбранных объектов не найдены.';
                    }
                    if ($err === '' && $id > 0) {
                        $oldLinksStmt=$pdo->prepare('SELECT object_id FROM sb_user_objects WHERE user_id=:id ORDER BY object_id');
                        $oldLinksStmt->execute([':id'=>$id]);
                        $oldObjectIds=array_map('intval',$oldLinksStmt->fetchAll(PDO::FETCH_COLUMN));
                        $newObjectIds=$objectIds; sort($oldObjectIds); sort($newObjectIds);
                        $pdo->beginTransaction();
                        try {
                            $st = $pdo->prepare('UPDATE sb_users SET username=:u, full_name=:f, role=:r, object_id=:o, is_active=:a WHERE id=:id');
                            $st->execute([':u' => $username, ':f' => $fullName, ':r' => $role, ':o' => $objectId, ':a' => $isActive, ':id' => $id]);
                            $pdo->prepare('DELETE FROM sb_user_objects WHERE user_id=:id')->execute([':id' => $id]);
                            if ($objectIds) {
                                $link = $pdo->prepare('INSERT INTO sb_user_objects (user_id, object_id) VALUES (:u, :o)');
                                foreach ($objectIds as $oid) $link->execute([':u' => $id, ':o' => $oid]);
                            }
                            if ($password !== '') {
                                $st = $pdo->prepare('UPDATE sb_users SET password_hash = :h WHERE id = :id');
                                $st->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $id]);
                            }
                            $pdo->commit();
                            users_audit_log($pdo, $me, 'user_updated', 'user', $id, $username, ['role' => $role, 'object_ids' => $objectIds, 'active' => $isActive, 'password_changed' => $password !== '']);
                            if ($oldObjectIds !== $newObjectIds) users_notification_add($pdo,$id,'access','Изменены назначенные объекты','Администратор обновил список торговых объектов, доступных в вашей учётной записи.','index.html');
                            if (!$isActive) users_notification_add($pdo,$id,'security','Учётная запись отключена','Администратор отключил вашу учётную запись. Для уточнения обратитесь к руководителю.','index.html');
                        } catch (Throwable $e) {
                            if ($pdo->inTransaction()) $pdo->rollBack();
                            throw $e;
                        }
                    } elseif ($err === '' && strlen($password) < 6) {
                        $err = 'Пароль нового пользователя — не менее 8 символов.';
                    } elseif ($err === '') {
                        $pdo->beginTransaction();
                        try {
                            $st = $pdo->prepare('INSERT INTO sb_users (username, password_hash, full_name, role, object_id, is_active) VALUES (:u, :h, :f, :r, :o, 1)');
                            $st->execute([':u' => $username, ':h' => password_hash($password, PASSWORD_DEFAULT), ':f' => $fullName, ':r' => $role, ':o' => $objectId]);
                            $newId = (int)$pdo->lastInsertId();
                            if ($objectIds) {
                                $link = $pdo->prepare('INSERT INTO sb_user_objects (user_id, object_id) VALUES (:u, :o)');
                                foreach ($objectIds as $oid) $link->execute([':u' => $newId, ':o' => $oid]);
                            }
                            $pdo->commit();
                            users_audit_log($pdo, $me, 'user_created', 'user', $newId, $username, ['role' => $role, 'object_ids' => $objectIds, 'active' => 1]);
                            users_notification_add($pdo,$newId,'account','Учётная запись создана','Для вас создана учётная запись на сайте СБ.','index.html');
                        } catch (Throwable $e) {
                            if ($pdo->inTransaction()) $pdo->rollBack();
                            throw $e;
                        }
                    }
                }
            }
        } elseif ($act === 'user_toggle') {
            if ($id === (int)$me['id']) {
                $err = 'Нельзя отключить собственную учётную запись.';
            } else {
                $targetStmt = $pdo->prepare('SELECT username, is_active FROM sb_users WHERE id = :id');
                $targetStmt->execute([':id' => $id]);
                $toggleTarget = $targetStmt->fetch();
                $st = $pdo->prepare('UPDATE sb_users SET is_active = 1 - is_active WHERE id = :id');
                $st->execute([':id' => $id]);
                if ($toggleTarget) users_audit_log($pdo, $me, !empty($toggleTarget['is_active']) ? 'user_disabled' : 'user_enabled', 'user', $id, (string)$toggleTarget['username']);
                if ($toggleTarget) users_notification_add($pdo,$id,!empty($toggleTarget['is_active'])?'security':'account',!empty($toggleTarget['is_active'])?'Учётная запись отключена':'Учётная запись включена',!empty($toggleTarget['is_active'])?'Администратор отключил вашу учётную запись.':'Администратор снова включил вашу учётную запись.','index.html');
            }
        }
    }
}

$users = $pdo->query('SELECT id, username, full_name, role, object_id, is_active, created_at FROM sb_users ORDER BY id')->fetchAll();
$objects = $pdo->query('SELECT id, name, is_active FROM sb_objects ORDER BY name')->fetchAll();
$objName = [];
foreach ($objects as $o) { $objName[(int)$o['id']] = $o['name']; }
$userObjectIds = [];
$linkRows = $pdo->query('SELECT user_id, object_id FROM sb_user_objects ORDER BY object_id')->fetchAll();
foreach ($linkRows as $link) {
    $uid = (int)$link['user_id'];
    if (!isset($userObjectIds[$uid])) $userObjectIds[$uid] = [];
    $userObjectIds[$uid][] = (int)$link['object_id'];
}
foreach ($users as $u) {
    $uid = (int)$u['id'];
    if (empty($userObjectIds[$uid]) && !empty($u['object_id'])) $userObjectIds[$uid] = [(int)$u['object_id']];
}
$activeObjectIds = [];
foreach ($objects as $o) if ((int)$o['is_active'] === 1) $activeObjectIds[(int)$o['id']] = true;
$unassignedUsers = array_values(array_filter($users, function($u) use ($userObjectIds, $activeObjectIds) {
    if ((int)$u['is_active'] !== 1 || !in_array($u['role'], ['user','moderator'], true)) return false;
    foreach ($userObjectIds[(int)$u['id']] ?? [] as $oid) if (isset($activeObjectIds[(int)$oid])) return false;
    return true;
}));
$auditRows = $pdo->query('SELECT actor_name, action, target_label, created_at FROM sb_audit_log ORDER BY id DESC LIMIT 50')->fetchAll();
$roleRows = $pdo->query('SELECT role_key,display_name,base_role,is_system FROM sb_roles ORDER BY is_system DESC,display_name')->fetchAll();
$roleNames = [];
foreach ($roleRows as $roleRow) $roleNames[(string)$roleRow['role_key']] = (string)$roleRow['display_name'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#f3f7f8">
<title>Управление — отчет СБ</title>
<style>
:root{--ink:#18343a;--muted:#71858a;--line:#e4ecee;--teal:#08777b;--teal-dark:#075e62;--pale:#eaf5f5;--bg:#f3f7f8;--white:#fff;--red:#b94b35;--red-bg:#fff0ec;--green:#16734f;--green-bg:#eaf7f0;--shadow:0 5px 20px rgba(24,52,58,.055)}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.45 Inter,Arial,sans-serif}button,input,select{font:inherit}button{cursor:pointer}
.page{width:100%;max-width:1600px;margin:0 auto;padding:24px clamp(14px,2vw,30px) 40px}
.topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}.brand{display:flex;align-items:center;gap:13px}.logo{width:46px;height:46px;display:grid;place-items:center;border-radius:14px;background:linear-gradient(145deg,#0b898b,#075e62);color:#fff;font-weight:800;font-size:15px;box-shadow:0 5px 12px #08777b30}.brand h1{font-size:23px;line-height:1.15;margin:0;letter-spacing:-.4px}.subtitle{font-size:12px;color:var(--muted);margin-top:4px}.top-actions{display:flex;align-items:center;gap:8px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:8px 13px;border:1px solid transparent;border-radius:9px;background:var(--teal);color:#fff;text-decoration:none;font-weight:700;font-size:12px;white-space:nowrap;transition:.16s ease}.btn:hover{background:var(--teal-dark);transform:translateY(-1px)}.btn.secondary{background:#fff;color:var(--ink);border-color:var(--line)}.btn.secondary:hover{background:var(--pale);border-color:#c5dfe0;color:var(--teal-dark)}.btn.danger{background:var(--red-bg);color:var(--red);border-color:#f6ddd6}.btn.danger:hover{background:#ffe4dc}.btn.small{min-height:31px;padding:6px 10px;font-size:11px}.btn:disabled{opacity:.42;cursor:not-allowed;transform:none}
.summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:16px}.summary-card{display:flex;align-items:center;gap:12px;padding:13px 16px;background:#fff;border:1px solid var(--line);border-radius:13px;box-shadow:var(--shadow)}.summary-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:var(--pale);color:var(--teal);font-size:17px}.summary-value{font-size:20px;font-weight:800;line-height:1.1}.summary-label{font-size:11px;color:var(--muted);margin-top:3px}
.panel{background:#fff;border:1px solid var(--line);border-radius:15px;padding:18px;margin-bottom:15px;box-shadow:var(--shadow)}.panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.panel-heading{display:flex;align-items:center;gap:10px}.section-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:var(--pale);color:var(--teal);font-size:16px}.panel-title{font-size:16px;font-weight:800;letter-spacing:-.2px;margin:0}.panel-subtitle{font-size:11px;color:var(--muted);margin-top:2px}.counter{font-size:11px;color:#557176;background:#f1f6f6;border:1px solid #e7eeee;border-radius:30px;padding:5px 9px;white-space:nowrap}
.alert{display:flex;align-items:flex-start;gap:9px;padding:11px 13px;border:1px solid #f4d8d0;border-radius:11px;margin:0 0 14px;background:var(--red-bg);color:var(--red);font-size:12px}.alert:before{content:"!";display:grid;place-items:center;flex:0 0 18px;height:18px;border-radius:50%;background:#f9d9d0;font-weight:800}
.field{min-width:0}.field label,.field-label{display:block;margin:0 0 5px;color:#647c81;font-size:10px;font-weight:800;letter-spacing:.35px;text-transform:uppercase}input,select{width:100%;height:37px;padding:7px 10px;border:1px solid #dce6e8;border-radius:9px;background:#fff;color:var(--ink);outline:none;min-width:0;font-size:12px;transition:border-color .15s,box-shadow .15s}input:focus,select:focus{border-color:#65aeb0;box-shadow:0 0 0 3px #08777b14}input::placeholder{color:#a0afb2}
.add-object{display:grid;grid-template-columns:minmax(220px,1fr) 170px auto;gap:10px;align-items:end;padding:13px;background:#f7fafa;border:1px solid #e9f0f1;border-radius:12px}.object-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:9px;margin-top:13px}.object-card{display:grid;grid-template-columns:minmax(0,1fr) 112px auto auto;gap:7px;align-items:center;padding:9px;background:#fbfdfd;border:1px solid var(--line);border-radius:11px}.object-card input,.object-card select{height:34px}.object-card .btn{height:34px}
.user-create{display:grid;grid-template-columns:1fr 1.35fr 1fr 1fr minmax(210px,1.35fr) auto;gap:10px;align-items:end;padding:14px;background:#f7fafa;border:1px solid #e9f0f1;border-radius:12px}
.object-picker{position:relative;min-width:0}.object-picker summary{height:37px;display:flex;align-items:center;justify-content:space-between;gap:8px;padding:7px 10px;border:1px solid #dce6e8;border-radius:9px;background:#fff;color:var(--ink);font-size:12px;cursor:pointer;list-style:none;white-space:nowrap;overflow:hidden}.object-picker summary::-webkit-details-marker{display:none}.object-picker summary:after{content:"⌄";font-size:16px;color:var(--muted)}.object-picker[open] summary{border-color:#65aeb0;box-shadow:0 0 0 3px #08777b14}.picker-value{overflow:hidden;text-overflow:ellipsis}.picker-menu{position:absolute;z-index:20;top:calc(100% + 5px);left:0;right:0;max-height:245px;overflow:auto;padding:6px;background:#fff;border:1px solid #dce6e8;border-radius:10px;box-shadow:0 12px 30px #18343a20}.picker-option{display:flex;align-items:center;gap:8px;padding:7px 8px;border-radius:7px;color:var(--ink);font-size:12px;cursor:pointer}.picker-option:hover{background:#f1f7f7}.picker-option input{width:15px;height:15px;margin:0;accent-color:var(--teal)}.picker-empty{padding:9px;color:var(--muted);font-size:11px}
.user-tools{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:17px 0 10px}.search-wrap{position:relative;flex:1;max-width:440px}.search-wrap:before{content:"⌕";position:absolute;left:12px;top:5px;color:#8ba0a4;font-size:20px}.search-wrap input{height:39px;padding-left:36px;background:#fbfdfd}.user-list{display:grid;gap:8px}.user-card{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;align-items:center;padding:12px 13px;border:1px solid var(--line);border-radius:12px;background:#fff;transition:border-color .15s,box-shadow .15s}.user-card:hover{border-color:#c9dfe0;box-shadow:0 4px 15px #18343a0b}.user-edit-form{display:grid;grid-template-columns:105px minmax(150px,1.2fr) 140px minmax(190px,1.4fr) 105px minmax(140px,1fr) auto;gap:9px;align-items:end;min-width:0}.user-edit-form .field label{font-size:9px}.user-edit-form input,.user-edit-form select{height:36px}.user-edit-form .object-picker summary{height:36px}.user-id{font-weight:800;color:var(--teal);font-variant-numeric:tabular-nums}.user-id small{display:block;margin-top:2px;color:var(--muted);font-size:9px;font-weight:600}.user-actions{display:flex;align-items:center;gap:6px}.user-actions form{margin:0}.status{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:800;padding:5px 8px;border-radius:30px;white-space:nowrap}.status:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}.active{color:var(--green);background:var(--green-bg)}.off{color:var(--red);background:var(--red-bg)}.empty{padding:24px;text-align:center;color:var(--muted);border:1px dashed #d8e3e5;border-radius:11px;background:#fbfdfd;font-size:12px}
@media(max-width:1180px){.user-create{grid-template-columns:repeat(3,minmax(0,1fr))}.user-edit-form{grid-template-columns:90px minmax(130px,1fr) 125px minmax(170px,1.2fr) 100px auto}.user-edit-form .password-field{grid-column:2/4}.user-edit-form .save-field{grid-column:4/7}.user-edit-form .save-field .btn{width:100%}.user-card{grid-template-columns:1fr}.user-actions{justify-content:flex-end}}
@media(max-width:700px){.page{padding:14px 11px 28px}.topbar{align-items:flex-start}.brand h1{font-size:20px}.logo{width:41px;height:41px}.summary{gap:7px}.summary-card{padding:10px;gap:8px}.summary-icon{width:30px;height:30px;font-size:14px}.summary-value{font-size:17px}.summary-label{font-size:10px}.panel{padding:13px;border-radius:13px}.panel-head{margin-bottom:12px}.add-object{grid-template-columns:1fr 115px}.add-object .submit-field{grid-column:1/-1}.add-object .submit-field .btn{width:100%}.object-grid{grid-template-columns:1fr}.object-card{grid-template-columns:minmax(0,1fr) 95px auto auto;padding:7px}.user-create{grid-template-columns:1fr 1fr}.user-create .picker-field,.user-create .create-field{grid-column:1/-1}.user-create .create-field .btn{width:100%}.user-tools{align-items:stretch;flex-direction:column}.search-wrap{max-width:none}.user-card{padding:11px}.user-edit-form{grid-template-columns:1fr 1fr}.user-edit-form .id-field{grid-column:1/-1}.user-edit-form .full-field{grid-column:1/-1}.user-edit-form .picker-field{grid-column:1/-1}.user-edit-form .password-field{grid-column:1/-1}.user-edit-form .save-field{grid-column:1/-1}.user-edit-form .save-field .btn{width:100%}.user-actions{justify-content:stretch}.user-actions form{flex:1}.user-actions .btn{width:100%}.top-actions .btn{min-height:34px;padding:7px 9px}}
@media(max-width:390px){.summary-card{flex-direction:column;align-items:flex-start}.object-card{grid-template-columns:1fr 85px}.object-card .save-object{grid-column:1}.object-card .delete-object{grid-column:2}.user-create{grid-template-columns:1fr}.user-create .field{grid-column:1/-1}}

/* Новая компактная компоновка: объекты — квадратные карточки, пользователи — строки */
.object-grid{
  grid-template-columns:repeat(auto-fill,minmax(168px,190px));
  justify-content:start;
  align-items:stretch;
  gap:12px;
}
.object-card{
  aspect-ratio:1 / 1;
  min-width:0;
  grid-template-columns:1fr 1fr;
  grid-template-rows:minmax(0,1fr) auto auto;
  align-content:stretch;
  gap:9px;
  padding:13px;
  border-radius:13px;
}
.object-card input[name="name"]{
  grid-column:1 / -1;
  width:100%;
  height:auto;
  min-height:44px;
  align-self:stretch;
  font-size:13px;
  font-weight:700;
  line-height:1.35;
  white-space:normal;
}
.object-card select{grid-column:1 / -1;width:100%}
.object-card .save-object,.object-card .delete-object{
  width:100%;
  min-width:0;
  padding-left:5px;
  padding-right:5px;
}
.user-list{gap:7px}
.user-card{
  grid-template-columns:minmax(0,1fr) auto;
  gap:12px;
  padding:10px 11px;
  border-radius:10px;
}
.user-edit-form{
  grid-template-columns:62px minmax(105px,.85fr) minmax(155px,1.25fr) 118px minmax(260px,2fr) 96px minmax(145px,1.15fr) 116px;
  gap:8px;
  align-items:end;
}
.user-edit-form .field{min-width:0}
.user-edit-form .field label{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.user-edit-form input,.user-edit-form select,.user-edit-form .object-picker summary{height:35px}
.user-edit-form .save-field .btn{width:100%;min-height:35px;padding:7px 8px;white-space:nowrap}
.user-edit-form .password-field,.user-edit-form .save-field{grid-column:auto}
.user-actions{gap:5px;flex-wrap:nowrap}
.user-actions .btn{padding-left:8px;padding-right:8px}
.user-actions .status{padding:5px 7px}
@media(max-width:1450px) and (min-width:1181px){
  .user-card{grid-template-columns:minmax(0,1fr) auto;gap:8px}
  .user-edit-form{grid-template-columns:54px minmax(95px,.8fr) minmax(135px,1.15fr) 108px minmax(220px,1.8fr) 88px minmax(125px,1fr) 105px;gap:6px}
  .user-edit-form .save-field .btn{font-size:10px}
  .user-actions{gap:4px}
  .user-actions .btn{font-size:10px;padding:6px}
  .user-actions .status{font-size:9px}
}
@media(max-width:1180px){
  .object-grid{grid-template-columns:repeat(auto-fill,minmax(160px,180px))}
  .object-card{aspect-ratio:1 / 1}
  .user-card{grid-template-columns:1fr}
  .user-edit-form{grid-template-columns:80px minmax(120px,1fr) minmax(150px,1.2fr) 120px minmax(230px,1.8fr) 100px}
  .user-edit-form .password-field{grid-column:2 / 4}
  .user-edit-form .save-field{grid-column:4 / 7}
  .user-actions{justify-content:flex-end}
}
@media(max-width:700px){
  .object-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
  .object-card{aspect-ratio:auto;min-height:165px;padding:9px;gap:7px}
  .object-card input[name="name"]{min-height:48px}
  .user-edit-form{grid-template-columns:1fr 1fr}
  .user-edit-form .id-field,.user-edit-form .full-field,.user-edit-form .picker-field,.user-edit-form .password-field,.user-edit-form .save-field{grid-column:1 / -1}
}
@media(max-width:390px){
  .object-grid{grid-template-columns:1fr 1fr}
  .object-card{min-height:155px}
}

/* Уточнение размеров карточек объектов и поля названия */
.object-grid{
  grid-template-columns:repeat(auto-fill,minmax(200px,215px));
  gap:12px;
}
.object-card{
  aspect-ratio:1.08 / 1;
  padding:12px;
  gap:8px;
}
.object-card input[name="name"]{
  width:100%;
  min-width:0;
  height:34px;
  min-height:34px;
  padding:6px 9px;
  font-size:12px;
  line-height:1.2;
}
@media(max-width:1180px){
  .object-grid{grid-template-columns:repeat(auto-fill,minmax(190px,205px))}
}
@media(max-width:700px){
  .object-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
  .object-card{aspect-ratio:auto;min-height:150px}
  .object-card input[name="name"]{height:34px;min-height:34px}
}


/* Отключённые объекты выделяются красным свечением */
.object-card-disabled{
  background:#fff7f5;
  border-color:#e6a092;
  box-shadow:0 0 0 1px #d95b4530,0 0 16px #d95b4528;
}
.object-card-disabled:hover{
  border-color:#d97967;
  box-shadow:0 0 0 1px #d95b4550,0 0 20px #d95b4540;
}

</style>
<link rel="stylesheet" href="site-theme.css">
<link rel="stylesheet" href="site-header.css">
</head>
<body>
<header class="sb-site-header"><div class="sb-site-top"><a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a><div class="sb-site-actions"><div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div><a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a><a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a><a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a><button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button></div></div><nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner"><a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a href="/tasks.php">Задачи</a><a href="/employees.php">Сотрудники</a><a href="/notifications.php">Уведомления</a></div></nav></header>
<div class="page">
<div class="sb-page-title"><div><h1>Управление</h1><p>Пользователи, роли и торговые объекты</p></div></div>

<div class="summary">
  <div class="summary-card"><div class="summary-icon">♙</div><div><div class="summary-value"><?= count($users) ?></div><div class="summary-label">Всего пользователей</div></div></div>
  <div class="summary-card"><div class="summary-icon">●</div><div><div class="summary-value"><?= count(array_filter($users, function($u) { return (int)$u['is_active'] === 1; })) ?></div><div class="summary-label">Активных учётных записей</div></div></div>
  <div class="summary-card"><div class="summary-icon">⌖</div><div><div class="summary-value"><?= count($objects) ?></div><div class="summary-label">Торговых объектов</div></div></div>
</div>

<?php if ($err): ?><div class="alert"><?= h($err) ?></div><?php endif; ?>
<?php if ($unassignedUsers): ?><div class="alert"><strong>Требуют назначения объектов:</strong> <?= h(implode(', ', array_map(function($u){ return $u['full_name'].' ('.$u['username'].')'; }, $unassignedUsers))) ?>. Эти сотрудники не смогут работать с отчётами, пока им не назначат активный объект.</div><?php endif; ?>

<section class="panel">
  <div class="panel-head"><div class="panel-heading"><div class="section-icon">♙</div><div><h2 class="panel-title">Роли и права доступа</h2><div class="panel-subtitle">Переименование системных ролей и создание дополнительных ролей</div></div></div><span class="counter"><?= count($roleRows) ?> ролей</span></div>
  <form method="post" class="add-object" style="grid-template-columns:minmax(180px,1fr) minmax(150px,.8fr) auto">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="role_save">
    <div class="field"><label>Название новой роли</label><input name="display_name" maxlength="80" placeholder="Например, Старший специалист" required></div>
    <div class="field"><label>Основа доступа</label><select name="base_role"><option value="user">Пользователь</option><option value="moderator">Модератор</option></select></div>
    <div class="submit-field"><button class="btn" type="submit">Создать роль</button></div>
  </form>
  <div class="user-list" style="margin-top:12px">
    <?php foreach ($roleRows as $roleRow): ?><div class="user-card">
      <form method="post" class="user-edit-form" style="grid-template-columns:minmax(180px,1fr) 150px auto">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="role_save"><input type="hidden" name="role_key" value="<?= h($roleRow['role_key']) ?>">
        <div class="field"><label><?= (int)$roleRow['is_system'] ? 'Системная роль' : 'Пользовательская роль' ?></label><input name="display_name" maxlength="80" value="<?= h($roleRow['display_name']) ?>" required></div>
        <div class="field"><label>Базовая модель</label><input value="<?= h($roleRow['base_role']==='moderator'?'Объектная зона':($roleRow['base_role']==='admin'?'Полный доступ':'Личные данные')) ?>" disabled></div>
        <div class="save-field"><button class="btn secondary" type="submit">Сохранить название</button></div>
      </form>
      <?php if (!(int)$roleRow['is_system']): ?><form method="post" onsubmit="return confirm('Удалить роль?');"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="role_delete"><input type="hidden" name="role_key" value="<?= h($roleRow['role_key']) ?>"><button class="btn danger small" type="submit">Удалить</button></form><?php else: ?><span class="status active">Системная</span><?php endif; ?>
    </div><?php endforeach; ?>
  </div>
</section>

<section class="panel">
  <div class="panel-head">
    <div class="panel-heading"><div class="section-icon">⌖</div><div><h2 class="panel-title">Торговые объекты</h2><div class="panel-subtitle">Создание, переименование и включение объектов</div></div></div>
    <span class="counter"><?= count($objects) ?> объектов</span>
  </div>
  <form method="post" class="add-object">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="object_save">
    <div class="field"><label>Название нового объекта</label><input name="name" placeholder="Например, Группа БФ Волгоград 1" required></div>
    <div class="field"><label>Начальный статус</label><select name="is_active"><option value="1" selected>Активен</option><option value="0">Отключён</option></select></div>
    <div class="field submit-field"><label>&nbsp;</label><button class="btn" type="submit">＋ Добавить объект</button></div>
  </form>
  <?php if ($objects): ?><div class="object-grid">
    <?php foreach ($objects as $o): ?>
    <form method="post" class="object-card<?= empty($o['is_active']) ? ' object-card-disabled' : '' ?>">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="object_save"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
      <input name="name" aria-label="Название объекта" value="<?= h($o['name']) ?>" required>
      <select name="is_active" aria-label="Статус объекта"><option value="1" <?= $o['is_active'] ? 'selected' : '' ?>>Активен</option><option value="0" <?= !$o['is_active'] ? 'selected' : '' ?>>Отключён</option></select>
      <button class="btn secondary small save-object" type="submit">Сохранить</button>
      <button class="btn danger small delete-object" type="submit" name="act" value="object_delete" formnovalidate onclick="return confirm('Удалить объект «<?= h($o['name']) ?>»?');">Удалить</button>
    </form>
    <?php endforeach; ?>
  </div><?php else: ?><div class="empty" style="margin-top:12px">Пока нет торговых объектов. Добавьте первый объект выше.</div><?php endif; ?>
</section>

<section class="panel">
  <div class="panel-head">
    <div class="panel-heading"><div class="section-icon">♙</div><div><h2 class="panel-title">Пользователи</h2><div class="panel-subtitle">Учётные записи, роли, доступ и закреплённые объекты</div></div></div>
    <span class="counter"><?= count($users) ?> учётных записей</span>
  </div>
  <form method="post" class="user-create">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_save">
    <div class="field"><label>Логин</label><input name="username" placeholder="Логин" required></div>
    <div class="field"><label>ФИО</label><input name="full_name" placeholder="Фамилия Имя Отчество" required></div>
    <div class="field"><label>Пароль</label><input name="password" type="password" minlength="8" placeholder="От 8 символов" required></div>
    <div class="field"><label>Роль</label><select name="role"><?php foreach ($roleNames as $rk => $rn): ?><option value="<?= h($rk) ?>" <?= $rk === 'user' ? 'selected' : '' ?>><?= h($rn) ?></option><?php endforeach; ?></select></div>
    <div class="field picker-field"><label>Закрепить объекты</label>
      <details class="object-picker"><summary><span class="picker-value">Выбрать объекты</span></summary><div class="picker-menu">
      <?php foreach ($objects as $o): ?><label class="picker-option"><input type="checkbox" name="object_ids[]" value="<?= (int)$o['id'] ?>"><span><?= h($o['name']) ?></span></label><?php endforeach; ?>
      <?php if (!$objects): ?><div class="picker-empty">Сначала добавьте торговый объект</div><?php endif; ?>
      </div></details>
    </div>
    <div class="field create-field"><label>&nbsp;</label><button class="btn" type="submit">＋ Создать пользователя</button></div>
  </form>

  <div class="user-tools"><div class="search-wrap"><input id="userSearch" type="search" placeholder="Поиск по логину, ФИО или объекту…" aria-label="Поиск пользователей"></div><span class="counter" id="visibleCount"><?= count($users) ?> показано</span></div>
  <div class="user-list" id="userList">
  <?php foreach ($users as $u): ?>
    <article class="user-card" data-search="<?= h(mb_strtolower($u['username'].' '.$u['full_name'].' '.implode(' ', array_map(function($oid) use ($objName) { return $objName[$oid] ?? ''; }, $userObjectIds[(int)$u['id']] ?? [])), 'UTF-8')) ?>">
      <form method="post" class="user-edit-form">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_save"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
        <div class="field id-field"><div class="user-id">#<?= (int)$u['id'] ?><small><?= $u['is_active'] ? 'В системе' : 'Отключена' ?></small></div></div>
        <div class="field"><label>Логин</label><input name="username" value="<?= h($u['username']) ?>" required></div>
        <div class="field full-field"><label>ФИО</label><input name="full_name" value="<?= h($u['full_name']) ?>" required></div>
        <div class="field"><label>Роль</label><select name="role"><?php foreach ($roleNames as $rk => $rn): ?><option value="<?= h($rk) ?>" <?= $u['role'] === $rk ? 'selected' : '' ?>><?= h($rn) ?></option><?php endforeach; ?></select></div>
        <div class="field picker-field"><label>Закреплённые объекты</label>
          <details class="object-picker"><summary><span class="picker-value">Выбрать объекты</span></summary><div class="picker-menu">
          <?php foreach ($objects as $o): ?><label class="picker-option"><input type="checkbox" name="object_ids[]" value="<?= (int)$o['id'] ?>" <?= in_array((int)$o['id'], $userObjectIds[(int)$u['id']] ?? [], true) ? 'checked' : '' ?>><span><?= h($o['name']) ?></span></label><?php endforeach; ?>
          <?php if (!$objects): ?><div class="picker-empty">Нет объектов</div><?php endif; ?>
          </div></details>
        </div>
        <div class="field"><label>Статус</label><select name="is_active"><option value="1" <?= $u['is_active'] ? 'selected' : '' ?>>Активен</option><option value="0" <?= !$u['is_active'] ? 'selected' : '' ?>>Отключён</option></select></div>
        <div class="field password-field"><label>Новый пароль</label><input name="password" type="password" minlength="8" placeholder="Оставьте пустым, чтобы не менять"></div>
        <div class="field save-field"><label>&nbsp;</label><button class="btn secondary" type="submit">Сохранить</button></div>
      </form>
      <div class="user-actions">
        <span class="status <?= $u['is_active'] ? 'active' : 'off' ?>"><?= $u['is_active'] ? 'Активен' : 'Отключён' ?></span>
        <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn danger small" type="submit" <?= (int)$u['id'] === (int)$me['id'] ? 'disabled' : '' ?>><?= $u['is_active'] ? 'Отключить' : 'Включить' ?></button></form>
        <button class="btn secondary small" type="button" data-reset-user="<?= (int)$u['id'] ?>" <?= empty($u['is_active']) ? 'disabled title="Сначала включите учётную запись"' : '' ?>>Сбросить пароль</button>
        <form method="post" onsubmit="return confirm('Удалить пользователя «<?= h($u['username']) ?>»? Это действие нельзя отменить.');"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="act" value="user_delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn danger small" type="submit" <?= (int)$u['id'] === (int)$me['id'] ? 'disabled' : '' ?>>Удалить</button></form>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
  <div class="empty" id="noUsers" hidden>По вашему запросу ничего не найдено.</div>
</section>

<section class="panel">
  <div class="panel-head"><div class="panel-heading"><div class="section-icon">◷</div><div><h2 class="panel-title">История действий</h2><div class="panel-subtitle">Последние 50 изменений пользователей и торговых объектов</div></div></div><span class="counter"><?= count($auditRows) ?> событий</span></div>
  <?php if ($auditRows): ?><div class="table-wrap"><table style="width:100%;border-collapse:collapse"><thead><tr><th style="text-align:left;padding:9px;border-bottom:1px solid var(--line)">Дата и время</th><th style="text-align:left;padding:9px;border-bottom:1px solid var(--line)">Кто</th><th style="text-align:left;padding:9px;border-bottom:1px solid var(--line)">Действие</th><th style="text-align:left;padding:9px;border-bottom:1px solid var(--line)">Объект действия</th></tr></thead><tbody><?php foreach ($auditRows as $log): ?><tr><td style="padding:9px;border-bottom:1px solid var(--line);white-space:nowrap"><?= h($log['created_at']) ?></td><td style="padding:9px;border-bottom:1px solid var(--line)"><?= h($log['actor_name']) ?></td><td style="padding:9px;border-bottom:1px solid var(--line)"><?= h(['user_created'=>'Создание пользователя','user_updated'=>'Изменение пользователя','user_deleted'=>'Удаление пользователя','user_disabled'=>'Отключение пользователя','user_enabled'=>'Включение пользователя','object_created'=>'Создание объекта','object_updated'=>'Изменение объекта','object_deleted'=>'Удаление объекта','login'=>'Вход в систему','logout'=>'Выход из системы','reports_saved'=>'Сохранение отчётов','report_deleted'=>'Удаление отчёта','report_comment_updated'=>'Изменение комментария','password_reset_link_created'=>'Создание ссылки сброса пароля','password_changed'=>'Смена пароля'][$log['action']] ?? $log['action']) ?></td><td style="padding:9px;border-bottom:1px solid var(--line)"><?= h($log['target_label']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty">История пока пуста. Новые действия будут фиксироваться автоматически.</div><?php endif; ?>
</section>
</div>
<script>
(function(){
  const resetCsrf=<?= json_encode(csrf_token()) ?>;
  const input=document.getElementById('userSearch');
  const cards=Array.from(document.querySelectorAll('.user-card'));
  const count=document.getElementById('visibleCount');
  const empty=document.getElementById('noUsers');
  function updatePicker(details){
    const summary=details.querySelector('.picker-value');
    const checked=Array.from(details.querySelectorAll('input[type="checkbox"]:checked'));
    if(!summary)return;
    if(!checked.length){summary.textContent='Выбрать объекты';return;}
    const names=checked.map(x=>x.parentElement.querySelector('span').textContent.trim());
    summary.textContent=names.length===1?names[0]:'Выбрано: '+names.length;
    summary.title=names.join(', ');
  }
  document.querySelectorAll('.object-picker').forEach(details=>{
    updatePicker(details);
    details.querySelectorAll('input[type="checkbox"]').forEach(box=>box.addEventListener('change',()=>updatePicker(details)));
  });
  document.addEventListener('click',event=>{
    document.querySelectorAll('.object-picker[open]').forEach(details=>{
      if(!details.contains(event.target))details.open=false;
    });
  });
  document.querySelectorAll('[data-reset-user]').forEach(button=>button.addEventListener('click',async()=>{
    const userId=Number(button.dataset.resetUser);
    if(!userId||!confirm('Создать одноразовую ссылку для смены пароля? Текущий пароль останется действовать до установки нового.'))return;
    button.disabled=true;
    try{
      const response=await fetch('api.php?action=admin_password_reset',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:resetCsrf,user_id:userId})});
      const data=await response.json();
      if(!response.ok||!data.ok)throw new Error(data.error||'Не удалось создать ссылку.');
      try{await navigator.clipboard.writeText(data.activation_url);alert('Одноразовая ссылка для смены пароля скопирована. Она действует 48 часов.');}
      catch(e){prompt('Скопируйте одноразовую ссылку. Она действует 48 часов:',data.activation_url);}
    }catch(e){alert(e.message);}
    finally{button.disabled=false;}
  }));
  if(input){
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
  }
})();
</script>
<script>
(function(){'use strict';function ping(){if(document.visibilityState==='hidden')return;fetch('api.php?action=presence_ping',{credentials:'same-origin',cache:'no-store'}).catch(function(){});}ping();setInterval(ping,60000);document.addEventListener('visibilitychange',ping);})();
</script><script src="site-header.js"></script>
</body>
</html>