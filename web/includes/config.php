<?php
// Ensure utils.php is included only once
require_once 'utils.php';

$config = json_decode(file_get_contents('../data/config.json'), true);

// Path and other configurations
$CSV_FILE_PATH = "../" . $config["CSV_FILE_PATH"];
$ADMIN_PWD = $config['ADMIN_PWD'] ?? 'zendns';
$PWD_HASH = $config['ADMIN_PWD_HASH'] ?? null;
$REDIRECT_PORTAL_IP = $config['REDIRECT_PORTAL_IP'] ?? '127.0.0.1';
$DEVICES = $config['DEVICES'] ?? array();

// If there's no existing password hash in the config, generate and save one
if ($PWD_HASH === null) {
    $PWD_HASH = password_hash($ADMIN_PWD, PASSWORD_DEFAULT);
    set_config_pwd_hash($PWD_HASH); // Store the newly generated hash in the config
}
