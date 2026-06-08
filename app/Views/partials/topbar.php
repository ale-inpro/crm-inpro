<header class="app-topbar">
    <nav class="app-breadcrumb">
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
    </nav>
    <div class="ms-auto d-flex align-items-center gap-2">
        <?php if (($proximasVisitasCount ?? 0) > 0): ?>
            <a href="<?= url('tareas?vista=calendario') ?>" class="btn btn-sm btn-outline-inpro position-relative" title="Próximas visitas programadas">
                <i class="bi bi-calendar-event"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-inpro" style="font-size:.65rem">
                    <?= (int) $proximasVisitasCount ?>
                </span>
            </a>
        <?php endif; ?>
        <?php if (is_inpro() && ($pendingVentas ?? 0) > 0): ?>
            <a href="<?= url('ventas/validar') ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-bell"></i> <?= (int) $pendingVentas ?> ventas por validar
            </a>
        <?php endif; ?>
        <span class="text-muted small d-none d-md-inline"><?= e(current_user()['nombre'] ?? '') ?></span>
        <a href="<?= url('logout') ?>" class="btn btn-sm btn-outline-secondary" title="Cerrar sesión"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</header>
