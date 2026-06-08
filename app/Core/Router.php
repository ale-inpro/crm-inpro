<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    public function __construct(private array $routes) {}

    public function dispatch(string $url): void
    {
        $path = trim($url, '/');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $map = $this->routes[$method] ?? [];

        if (!isset($map[$path])) {
            http_response_code(404);
            View::render('errors/404', [], 'main');
            return;
        }

        [$controllerName, $action] = $map[$path];
        $class = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($class) || !method_exists($class, $action)) {
            http_response_code(500);
            echo "Ruta mal configurada: {$controllerName}@{$action}";
            return;
        }

        (new $class())->$action();
    }
}