<?php
$isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'secure' => $isHttps,
    'httponly' => true, 'samesite' => 'Lax',
]);
session_name('sbapp_session');
session_start();
$_SESSION['n'] = ($_SESSION['n'] ?? 0) + 1;
echo 'Обращений в этой сессии: ' . $_SESSION['n'] . '<br>';
echo 'session_id: ' . session_id() . '<br>';
echo 'Cookie-заголовок запроса: ' . htmlspecialchars($_SERVER['HTTP_COOKIE'] ?? '(пусто)');
