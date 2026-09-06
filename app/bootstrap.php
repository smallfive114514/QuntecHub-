<?php
define('BASE_PATH', dirname(__DIR__));

$configFile = BASE_PATH.'/config/config.php';
$config = file_exists($configFile) ? require $configFile : require BASE_PATH.'/config/config.sample.php';

if (!empty($config['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

date_default_timezone_set('Asia/Hong_Kong');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

spl_autoload_register(function (string $class): void {
    $dirs = ['app/core', 'app/Models', 'app/Controllers'];
    foreach ($dirs as $dir) {
        $file = BASE_PATH.'/'.$dir.'/'.$class.'.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require BASE_PATH.'/app/core/helpers.php';

function app_base_url(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir = str_replace('\\', '/', dirname($scriptName));
    if ($dir === '/' || $dir === '.') {
        return '';
    }
    return rtrim($dir, '/');
}
