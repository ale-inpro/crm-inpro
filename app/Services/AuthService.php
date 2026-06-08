<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\UsuarioModel;

class AuthService
{
    public function attempt(string $email, string $password): bool
    {
        $model = new UsuarioModel();
        $user = $model->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $model->touchLastAccess((int) $user['id']);
        return true;
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
    }
}