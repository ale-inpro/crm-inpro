<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$root = dirname(__DIR__);
$envFile = $root . '/.env';

function loadEnv(string $path): array {
    if (!is_file($path)) {
        return [];
    }
    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $env[$k] = $v;
    }
    return $env;
}

function badge(bool $ok): string {
    return $ok
        ? '<span class="badge bg-success">OK</span>'
        : '<span class="badge bg-danger">FALLO</span>';
}

$env = loadEnv($envFile);
$rows = [];

$rows[] = ['PHP', badge(true), PHP_VERSION];
$rows[] = ['pdo_mysql', badge(extension_loaded('pdo_mysql')), extension_loaded('pdo_mysql') ? 'Activo' : 'Activa en php.ini'];

$hasEnv = is_file($envFile);
$rows[] = ['.env', badge($hasEnv), $hasEnv ? 'Encontrado' : 'Copia .env.example → .env'];

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3307';
$db   = $env['DB_DATABASE'] ?? 'crm_inpro';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

$pdo = null;
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    $rows[] = ['Conexión MariaDB', badge(true), "Conectado — {$version}"];
} catch (Throwable $e) {
    $rows[] = ['Conexión MariaDB', badge(false), $e->getMessage()];
}

if ($pdo) {
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $rows[] = ['Tablas en crm_inpro', badge(count($tables) > 0), count($tables) . ' tablas'];

    if (in_array('usuarios', $tables, true)) {
        $n = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        $rows[] = ['Datos usuarios', badge($n > 0), "{$n} registros"];
    } else {
        $rows[] = ['Tabla usuarios', badge(false), 'Importa database/schema.sql'];
    }

    if (in_array('clientes', $tables, true)) {
        $n = (int) $pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn();
        $rows[] = ['Datos clientes', badge($n > 0), "{$n} registros"];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Health — CRM INPRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">
<div class="container" style="max-width:720px">
    <h1 class="h4">CRM INPRO — Prueba de conexión</h1>
    <p class="text-muted small">MariaDB: 127.0.0.1:3307 · contenedor crm-mariadb</p>
    <table class="table table-sm table-bordered bg-white mt-3">
        <thead><tr><th>Check</th><th></th><th>Detalle</th></tr></thead>
        <tbody>
        <?php foreach ($rows as [$a, $b, $c]): ?>
            <tr><td><?= htmlspecialchars($a) ?></td><td><?= $b ?></td><td><small><?= htmlspecialchars($c) ?></small></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($pdo && count($tables ?? []) > 0): ?>
        <div class="alert alert-success">Listo para Fase 1: conexión y tablas OK.</div>
    <?php elseif ($pdo): ?>
        <div class="alert alert-warning">Conexión OK, pero faltan tablas. Rellena e importa <code>database/schema.sql</code>.</div>
    <?php else: ?>
        <div class="alert alert-danger">Revisa .env y que crm-mariadb esté en marcha (<code>docker ps</code>).</div>
    <?php endif; ?>
</div>
</body>
</html>