<?php
declare(strict_types=1);

/*
 * Безопасный загрузчик настроек SB.
 * Рабочие параметры хранятся в config.local.php на сервере и не коммитятся.
 */
$localConfigPath = __DIR__ . '/config.local.php';

if (!is_file($localConfigPath) || !is_readable($localConfigPath)) {
    error_log('SB configuration error: config.local.php is missing or unreadable.');
    throw new RuntimeException('Серверная конфигурация SB не настроена.');
}

$localConfig = require $localConfigPath;
if (!is_array($localConfig)) {
    error_log('SB configuration error: config.local.php must return an array.');
    throw new RuntimeException('Некорректная серверная конфигурация SB.');
}

foreach (['db_host', 'db_name', 'db_user', 'db_pass'] as $requiredKey) {
    if (!array_key_exists($requiredKey, $localConfig) || !is_string($localConfig[$requiredKey])) {
        error_log('SB configuration error: required setting is missing or invalid.');
        throw new RuntimeException('Некорректная серверная конфигурация SB.');
    }
}

if (!isset($localConfig['setup_token']) || !is_string($localConfig['setup_token'])) {
    $localConfig['setup_token'] = '';
}

return $localConfig;
