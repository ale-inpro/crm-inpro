<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (current_user()) {
            redirect('dashboard');
        }
        $this->view('auth/login', ['title' => 'Iniciar sesión'], 'auth');
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            flash('error', 'Email y contraseña son obligatorios.');
            redirect('login');
        }

        $auth = new AuthService();
        if (!$auth->attempt($email, $password)) {
            flash('error', 'Credenciales incorrectas.');
            redirect('login');
        }

        flash('success', 'Bienvenido/a.');
        redirect('dashboard');
    }

    public function logout(): void
    {
        (new AuthService())->logout();
        redirect('login');
    }
}