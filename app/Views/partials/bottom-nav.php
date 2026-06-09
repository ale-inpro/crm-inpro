<?php
$current = trim($_GET['url'] ?? 'dashboard', '/');

function bottomNavActive(string $path, string $current): string
{
    if ($path === 'more') {
        $morePaths = ['ventas', 'comisiones', 'tarifas', 'export', 'pipeline'];
        foreach ($morePaths as $p) {
            if ($current === $p || str_starts_with($current, $p . '/')) {
                return 'active';
            }
        }
        if (is_inpro() && str_starts_with($current, 'ventas/validar')) {
            return 'active';
        }
        return '';
    }
    if ($current === $path) return 'active';
    if ($path === 'clientes' && str_starts_with($current, 'clientes')) return 'active';
    if ($path === 'tareas'   && str_starts_with($current, 'tareas'))   return 'active';
    return '';
}
?>

<nav class="app-bottom-nav d-lg-none" aria-label="Navegación principal">
    <a href="<?= url('dashboard') ?>" class="bottom-nav-item <?= bottomNavActive('dashboard', $current) ?>">
        <i class="bi bi-speedometer2"></i>
        <span>Inicio</span>
    </a>
    <a href="<?= url('clientes') ?>" class="bottom-nav-item <?= bottomNavActive('clientes', $current) ?>">
        <i class="bi bi-building"></i>
        <span>Clientes</span>
    </a>
    <a href="<?= url('tareas') ?>" class="bottom-nav-item <?= bottomNavActive('tareas', $current) ?>">
        <i class="bi bi-check2-square"></i>
        <span>Tareas</span>
        <?php if (($tareasPendientesCount ?? 0) > 0): ?>
            <em class="bottom-nav-badge"><?= (int) $tareasPendientesCount ?></em>
        <?php endif; ?>
    </a>
    <button type="button"
            class="bottom-nav-item <?= bottomNavActive('more', $current) ?>"
            data-bs-toggle="offcanvas"
            data-bs-target="#appMoreOffcanvas"
            aria-controls="appMoreOffcanvas">
        <i class="bi bi-three-dots"></i>
        <span>Más</span>
        <?php if (is_inpro() && ($pendingVentas ?? 0) > 0): ?>
            <em class="bottom-nav-badge"><?= (int) $pendingVentas ?></em>
        <?php endif; ?>
    </button>
</nav>

<!-- Sheet inferior: enlaces secundarios -->
<div class="offcanvas offcanvas-bottom app-more-offcanvas d-lg-none"
     tabindex="-1"
     id="appMoreOffcanvas"
     aria-labelledby="appMoreOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="appMoreOffcanvasLabel">Más opciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body pt-0">
        <div class="list-group list-group-flush">
            <a href="<?= url('pipeline') ?>" class="list-group-item list-group-item-action">
                <i class="bi bi-kanban me-2"></i> Pipeline comercial
            </a>
            <a href="<?= url('ventas') ?>" class="list-group-item list-group-item-action">
                <i class="bi bi-cart-check me-2"></i> Ventas
            </a>
            <a href="<?= url('comisiones') ?>" class="list-group-item list-group-item-action">
                <i class="bi bi-currency-euro me-2"></i> <?= is_inpro() ? 'Comisiones' : 'Mis comisiones' ?>
            </a>
            <?php if (is_inpro()): ?>
            <a href="<?= url('tarifas') ?>" class="list-group-item list-group-item-action">
                <i class="bi bi-tags me-2"></i> Tarifas por volumen
            </a>
            <a href="<?= url('ventas/validar') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span><i class="bi bi-patch-check me-2"></i> Validar ventas</span>
                <?php if (($pendingVentas ?? 0) > 0): ?>
                    <span class="badge bg-warning text-dark"><?= (int) $pendingVentas ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('export/clientes') ?>" class="list-group-item list-group-item-action">
                <i class="bi bi-download me-2"></i> Exportar clientes CSV
            </a>
            <?php endif; ?>
            <a href="<?= url('logout') ?>" class="list-group-item list-group-item-action text-danger">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
            </a>
        </div>
    </div>
</div>