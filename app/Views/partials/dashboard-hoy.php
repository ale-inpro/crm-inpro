<?php
$hoy = date('Y-m-d');
$tieneAlgo = !empty($tareasUrgentes) || !empty($proximasVisitas) || (!empty($pendingVentas) && $pendingVentas > 0);
?>
<div class="dashboard-hoy d-lg-none mb-3">
    <div class="panel">
        <div class="panel-header">
            <span><i class="bi bi-sun text-warning"></i> Hoy</span>
            <a href="<?= url('tareas') ?>" class="btn btn-sm btn-outline-secondary">Tareas</a>
        </div>
        <div class="panel-body p-0">
            <?php if (!$tieneAlgo): ?>
                <p class="text-muted small p-3 mb-0">Sin pendientes urgentes para hoy.</p>
            <?php else: ?>
                <?php if (!empty($pendingVentas) && $pendingVentas > 0 && is_inpro()): ?>
                <a href="<?= url('ventas/validar') ?>" class="dashboard-hoy-item text-decoration-none">
                    <i class="bi bi-patch-check text-warning"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">Ventas por validar</div>
                        <div class="small text-muted"><?= (int) $pendingVentas ?> pendiente<?= $pendingVentas > 1 ? 's' : '' ?></div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <?php endif; ?>
                <?php foreach (array_slice($tareasUrgentes ?? [], 0, 5) as $t):
                    $vencida = $t['fecha_vencimiento'] < $hoy;
                ?>
                <a href="<?= url('clientes/ver?id=' . (int) $t['cliente_id'] . '#tabTareas') ?>" class="dashboard-hoy-item text-decoration-none">
                    <i class="bi bi-<?= $vencida ? 'alarm text-danger' : 'check2-square text-inpro' ?>"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate"><?= e($t['titulo']) ?></div>
                        <div class="small text-muted text-truncate">
                            <?= e($t['razon_social']) ?> · <?= date('d/m/Y', strtotime($t['fecha_vencimiento'])) ?>
                            <?= $vencida ? ' · vencida' : ' · hoy' ?>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <?php endforeach; ?>
                <?php foreach (array_slice($proximasVisitas ?? [], 0, 5) as $v): ?>
                <a href="<?= url('clientes/ver?id=' . (int) $v['cliente_id'] . '#tabActividadMob') ?>" class="dashboard-hoy-item text-decoration-none">
                    <i class="bi bi-geo-alt text-inpro"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate"><?= e($v['razon_social']) ?></div>
                        <div class="small text-muted">
                            Visita · <?= date('d/m H:i', strtotime($v['fecha_visita'])) ?>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
