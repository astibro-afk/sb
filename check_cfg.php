<?php
$c = require __DIR__ . '/config.php';
$token = (string)($c['setup_token'] ?? '');
echo 'Папка: ' . __DIR__ . '<br>';
echo 'Длина токена: ' . strlen($token) . '<br>';
echo 'Проверка setup.php сработает (ошибка): ' .
    (($token === '' || strpos($token, 'ЗАМЕНИТЕ_') === 0) ? 'ДА' : 'НЕТ') . '<br>';
