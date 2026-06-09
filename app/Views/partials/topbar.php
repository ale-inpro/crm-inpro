<?php
$pageTitleMobile = $pageTitle ?? null;
if (!$pageTitleMobile && !empty($breadcrumbs)) {
    $last = end($breadcrumbs);
    $pageTitleMobile = $last['label'] ?? ($title ?? 'CRM INPRO');
} else {
    $pageTitleMobile = $pageTitleMobile ?? ($title ?? 'CRM INPRO');
}
?>
<header class="app-topbar">
    <button type="button"
            class="btn btn-sm btn-outline-secondary d-lg-none app-menu-btn flex-shrink-0"
            data-bs-toggle="offcanvas"
            data-bs-target="#appSidebarOffcanvas"
            aria-controls="appSidebarOffcanvas"
            aria-label="Abrir menú">
        <i class="bi bi-list"></i>
    </button>
    <?php if (!empty($backUrl)): ?>
    <a href="<?= url($backUrl) ?>" class="btn btn-sm btn-link text-muted d-lg-none flex-shrink-0 px-1" title="<?= e($backLabel ?? 'Volver') ?>">
        <i class="bi bi-arrow-left fs-5"></i>
    </a>
    <?php endif; ?>
    <nav class="app-breadcrumb text-truncate">
        <strong class="d-lg-none app-page-title"><?= e($pageTitleMobile) ?></strong>
        <span class="d-none d-lg-inline">
        <?php if (!empty($breadcrumbs)): ?>
            <?php foreach ($breadcrumbs as $i => $crumb): ?>
                <?php if ($i > 0): ?> / <?php endif; ?>
                <?php if (!empty($crumb['url'])): ?>
                    <a href="<?= url($crumb['url']) ?>" class="text-decoration-none text-muted"><?= e($crumb['label']) ?></a>
                <?php else: ?>
                    <strong><?= e($crumb['label']) ?></strong>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <strong><?= e($title ?? 'CRM INPRO') ?></strong>
        <?php endif; ?>
        </span>
    </nav>
    <div class="ms-auto d-flex align-items-center gap-1 gap-sm-2">
        <?php if (($proximasVisitasCount ?? 0) > 0): ?>
            <a href="<?= url('tareas?vista=calendario') ?>" class="btn btn-sm btn-outline-inpro position-relative" title="Próximas visitas programadas">
                <i class="bi bi-calendar-event"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-inpro" style="font-size:.65rem">
                    <?= (int) $proximasVisitasCount ?>
                </span>
            </a>
        <?php endif; ?>
        <?php if (is_inpro() && ($pendingVentas ?? 0) > 0): ?>
            <a href="<?= url('ventas/validar') ?>" class="btn btn-sm btn-warning" title="Ventas por validar">
                <i class="bi bi-bell"></i>
                <span class="d-none d-sm-inline ms-1"><?= (int) $pendingVentas ?></span>
            </a>
        <?php endif; ?>
        <span class="text-muted small d-none d-lg-inline"><?= e(current_user()['nombre'] ?? '') ?></span>
        <a href="<?= url('logout') ?>" class="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex" title="Cerrar sesión"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</header>
