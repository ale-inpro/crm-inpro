<?php
/** Interior compartido: sidebar desktop + offcanvas móvil */
$user    = $user ?? current_user();
$current = $current ?? trim($_GET['url'] ?? 'dashboard', '/');

if (!function_exists('navActive')) {
    function navActive(string $path, string $current): string
    {
        if ($current === $path) return 'active';
        if ($path === 'clientes' && str_starts_with($current, 'clientes')) return 'active';
        if ($path === 'tareas'   && str_starts_with($current, 'tareas'))   return 'active';
        if ($path === 'tarifas'  && str_starts_with($current, 'tarifas'))  return 'active';
        if ($path === 'pipeline' && str_starts_with($current, 'pipeline')) return 'active';
        if ($path === 'ventas/validar' && str_starts_with($current, 'ventas/validar')) return 'active';
        if ($path === 'ventas' && $current === 'ventas') return 'active';
        if ($path === 'comisiones' && str_starts_with($current, 'comisiones')) return 'active';
        return '';
    }
}

$logo = file_exists(PUBLIC_PATH . '/assets/img/logo-inpro.png')
    ? url('assets/img/logo-inpro.png')
    : url('assets/img/logo-inpro.svg');
?>
<div class="logo">
    <a href="<?= url('dashboard') ?>" class="logo-link">
        <img src="<?= $logo ?>" alt="INPRO" class="logo-img"
             onerror="this.style.display='none';this.nextElementSibling.style.setProperty('display','inline','important')">
        <span class="logo-fallback" aria-hidden="true">INPRO</span>
    </a>
</div>

<nav class="app-nav">
    <div class="nav-section">Principal</div>
    <a href="<?= url('dashboard') ?>" class="<?= navActive('dashboard', $current) ?>">
        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
    </a>

    <div class="nav-section">CRM</div>
    <a href="<?= url('clientes') ?>" class="<?= navActive('clientes', $current) ?>">
        <i class="bi bi-building"></i><span>Clientes</span>
    </a>
    <a href="<?= url('pipeline') ?>" class="<?= navActive('pipeline', $current) ?>">
        <i class="bi bi-kanban"></i><span>Pipeline</span>
    </a>
    <a href="<?= url('tareas') ?>" class="<?= navActive('tareas', $current) ?>">
        <i class="bi bi-check2-square"></i><span>Tareas</span>
        <?php if (($tareasPendientesCount ?? 0) > 0): ?>
            <span class="nav-badge"><?= (int) $tareasPendientesCount ?></span>
        <?php endif; ?>
    </a>

    <div class="nav-section">Comercial</div>
    <a href="<?= url('ventas') ?>" class="<?= navActive('ventas', $current) ?>">
        <i class="bi bi-cart-check"></i><span>Ventas</span>
    </a>
    <?php if (is_inpro()): ?>
    <a href="<?= url('tarifas') ?>" class="<?= navActive('tarifas', $current) ?>">
        <i class="bi bi-tags"></i><span>Tarifas por volumen</span>
    </a>
    <a href="<?= url('ventas/validar') ?>" class="<?= navActive('ventas/validar', $current) ?>">
        <i class="bi bi-patch-check"></i><span>Validar ventas</span>
        <?php if (($pendingVentas ?? 0) > 0): ?>
            <span class="nav-badge"><?= (int) $pendingVentas ?></span>
        <?php endif; ?>
    </a>
    <?php endif; ?>
    <a href="<?= url('comisiones') ?>" class="<?= navActive('comisiones', $current) ?>">
        <i class="bi bi-currency-euro"></i><span><?= is_inpro() ? 'Comisiones' : 'Mis comisiones' ?></span>
    </a>
    <?php if (is_inpro()): ?>
    <a href="<?= url('export/clientes') ?>">
        <i class="bi bi-download"></i><span>Exportar CSV</span>
    </a>
    <?php endif; ?>
</nav>

<div class="app-sidebar-footer">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-person-circle" style="font-size:1.1rem"></i>
        <span class="text-truncate" style="max-width:140px"><?= e($user['nombre'] ?? '') ?></span>
    </div>
    <a href="<?= url('logout') ?>" class="text-white-50 small text-decoration-none mt-1 d-inline-block">
        <i class="bi bi-box-arrow-right"></i> <span>Cerrar sesión</span>
    </a>
</div>
