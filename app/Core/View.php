<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';

        if (!is_file($viewFile)) {
            throw new \RuntimeException("Vista no encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if (is_file($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }
}