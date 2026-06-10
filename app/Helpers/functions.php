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

function email_normalize(?string $email): ?string
{
    if ($email === null || trim($email) === '') {
        return null;
    }
    return strtolower(trim($email));
}

function telefono_normalize(?string $telefono): ?string
{
    if ($telefono === null || trim($telefono) === '') {
        return null;
    }
    return preg_replace('/[^\d]/', '', $telefono) ?: null;
}

/**
 * @return string|null Mensaje de error
 */
function contacto_validar_principal(?string $nombre, ?string $email, ?string $telefono): ?string
{
    if (trim($nombre ?? '') === '') {
        return 'El nombre del contacto principal es obligatorio.';
    }

    $emailNorm = email_normalize($email);
    $telNorm = telefono_normalize($telefono);

    if ($emailNorm === null && $telNorm === null) {
        return 'Indica al menos un email o un teléfono del contacto principal.';
    }

    if ($emailNorm !== null && !filter_var($emailNorm, FILTER_VALIDATE_EMAIL)) {
        return 'El email del contacto principal no es válido.';
    }

    return null;
}

/** @param array<int, array<string, mixed>> $contactos */
function contacto_principal_desde_lista(array $contactos): ?array
{
    foreach ($contactos as $ct) {
        if (!empty($ct['es_principal'])) {
            return $ct;
        }
    }
    return $contactos[0] ?? null;
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

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['_csrf'] ?? '';
    if ($token === '' || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }
}

/**
 * Quién gestiona el cliente: 'inpro' o 'empresa'.
 * Se deduce de modo_acceso_empresa (sin campo extra en BD).
 */
function cliente_gestor_activo(array $cliente): string
{
    if (empty($cliente['empresa_colaboradora_id'])) {
        return 'inpro';
    }
    return ($cliente['modo_acceso_empresa'] ?? 'edicion') === 'edicion' ? 'empresa' : 'inpro';
}

function cliente_gestor_etiqueta(array $cliente): string
{
    return cliente_gestor_activo($cliente) === 'empresa'
        ? ($cliente['empresa_colaboradora_nombre'] ?? 'Empresa colaboradora')
        : 'INPRO';
}

function cliente_acceso_colaborador_etiqueta(array $cliente): string
{
    if (empty($cliente['empresa_colaboradora_id'])) {
        return '—';
    }
    return ($cliente['modo_acceso_empresa'] ?? 'edicion') === 'edicion' ? 'Edición' : 'Solo lectura';
}

function cliente_can_manage(array $cliente): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    return (new \App\Services\ClienteService())->canManageCliente($user, $cliente);
}