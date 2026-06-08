<?php
declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function requireAuth(): array
    {
        $user = current_user();
        if (!$user) {
            flash('error', 'Debes iniciar sesión.');
            redirect('login');
        }
        return $user;
    }

    protected function requireInpro(): array
    {
        $user = $this->requireAuth();
        if ($user['rol'] !== 'inpro') {
            http_response_code(403);
            $this->view('errors/403', [], 'main');
            exit;
        }
        return $user;
    }

    protected function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}