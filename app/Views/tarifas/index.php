<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 mobile-hide-heading"><i class="bi bi-tags me-2"></i>Tarifas por volumen</h1>
    <a href="<?= url('tarifas/nuevo') ?>" class="btn btn-inpro"><i class="bi bi-plus-lg"></i> Nueva tarifa</a>
</div>

<?php if (empty($tarifas)): ?>
    <div class="alert alert-info">No hay tarifas configuradas. Crea la primera o ejecuta la migración SQL.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($tarifas as $t): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="panel h-100">
                <div class="panel-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h2 class="h6 mb-0"><?= e($t['nombre']) ?></h2>
                        <?php if ($t['activa']): ?>
                            <span class="badge bg-success">Activa</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactiva</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($t['es_default'])): ?>
                        <span class="badge bg-inpro mb-2">Por defecto</span>
                    <?php endif; ?>
                    <p class="small text-muted mb-3">
                        <?= (int) $t['num_tramos'] ?> tramo(s) · <?= (int) $t['num_empresas'] ?> empresa(s)
                    </p>
                    <a href="<?= url('tarifas/ver?id=' . $t['id']) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Gestionar
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
