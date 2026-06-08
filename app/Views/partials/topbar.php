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
    <div class="ms-auto">
        <?php if (is_inpro() && ($pendingVentas ?? 0) > 0): ?>
            <a href="<?= url('ventas/validar') ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-bell"></i> <?= (int)$pendingVentas ?> ventas por validar
            </a>
        <?php endif; ?>
    </div>
</header>