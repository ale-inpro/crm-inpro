<?php
declare(strict_types=1);

session_name($_ENV['SESSION_NAME'] ?? 'crm_inpro_session');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

require APP_PATH . '/Helpers/functions.php';

$envFile = ROOT_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $_ENV[$k] = $v;
    }
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}