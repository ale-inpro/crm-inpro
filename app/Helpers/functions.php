<?php
declare(strict_types=1);

function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $default;
}

function url(string $path = ''): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');
    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function cif_normalize(?string $cif): ?string
{
    if ($cif === null || trim($cif) === '') {
        return null;
    }
    return strtoupper(preg_replace('/[\s\-]/', '', $cif));
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_inpro(): bool
{
    return (current_user()['rol'] ?? '') === 'inpro';
}

function is_empresa(): bool
{
    return (current_user()['rol'] ?? '') === 'empresa';
}