<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;

$router = new Router(require APP_PATH . '/config/routes.php');
$router->dispatch($_GET['url'] ?? '');