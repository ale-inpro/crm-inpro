<?php
$user = current_user();
$current = trim($_GET['url'] ?? 'dashboard', '/');
$logo = file_exists(PUBLIC_PATH . '/assets/img/logo-inpro.png')
    ? url('assets/img/logo-inpro.png')
    : url('assets/img/logo-inpro.svg');

function navActive(string $path, string $current): string
{
    if ($current === $path) {
        return 'active';
    }
    if ($path === 'clientes' && str_starts_with($current, 'clientes')) {
        return 'active';
    }
    return '';
}
?>
<aside class="app-sidebar">
    <div class="logo">
        <a href="<?= url('dashboard') ?>">
            <img src="<?= $logo ?>" alt="INPRO" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <span style="display:none;color:#fff;font-weight:700;font-size:1.1rem">INPRO CRM</span>
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
        </a>

        <div class="nav-section">Comercial</div>
        <a href="<?= url('ventas') ?>" class="<?= navActive('ventas', $current) ?>">
            <i class="bi bi-cart-check"></i><span>Ventas</span>
        </a>
        <?php if (is_inpro()): ?>
        <a href="<?= url('ventas/validar') ?>" class="<?= navActive('ventas/validar', $current) ?>">
            <i class="bi bi-patch-check"></i><span>Validar ventas</span>
        </a>
        <?php endif; ?>
        <a href="<?= url('comisiones') ?>" class="<?= navActive('comisiones', $current) ?>">
            <i class="bi bi-currency-euro"></i><span><?= is_inpro() ? 'Comisiones' : 'Mis comisiones' ?></span>
        </a>
        <?php if (is_inpro()): ?>
        <a href="<?= url('export/clientes') ?>" class="">
            <i class="bi bi-download"></i><span>Exportar CSV</span>
        </a>
        <?php endif; ?>
    </nav>
    <div class="app-sidebar-footer">
        <div><i class="bi bi-person-circle"></i> <span><?= e($user['nombre'] ?? '') ?></span></div>
        <a href="<?= url('logout') ?>" class="text-white-50 small text-decoration-none">Cerrar sesión</a>
    </div>
</aside>
