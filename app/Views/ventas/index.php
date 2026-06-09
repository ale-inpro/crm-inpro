<h1 class="h3 mb-3 mb-lg-4 mobile-hide-heading"><i class="bi bi-cart-check me-2"></i>Ventas</h1>

<!-- Móvil -->
<div class="d-lg-none mobile-list">
    <?php if (empty($ventas)): ?>
        <div class="text-muted text-center py-4 small">Sin ventas registradas.</div>
    <?php else: ?>
        <?php foreach ($ventas as $v): ?>
        <a href="<?= url('clientes/ver?id=' . $v['cliente_id']) ?>" class="mobile-card mobile-card-clickable text-decoration-none text-dark">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="min-w-0">
                    <div class="mobile-card-title"><?= e($v['razon_social']) ?></div>
                    <div class="mobile-card-sub"><?= e($v['concepto_venta'] ?? '—') ?></div>
                    <div class="small text-muted mt-1">
                        <?= e($v['registrado_por_nombre']) ?> · <?= e($v['fecha_cierre'] ?? $v['created_at']) ?>
                    </div>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="fw-bold text-inpro"><?= number_format((float) $v['importe_final_eur'], 2, ',', '.') ?> €</div>
                    <span class="badge bg-<?= $v['estado'] === 'validada' ? 'success' : 'warning text-dark' ?>"><?= e($v['estado']) ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Desktop -->
<div class="panel d-none d-lg-block">
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Obras / Tarifa</th>
                        <th>Importe</th>
                        <th>Estado</th>
                        <th>Registrada por</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><a href="<?= url('clientes/ver?id=' . $v['cliente_id']) ?>"><?= e($v['razon_social']) ?></a></td>
                        <td><?= e($v['concepto_venta'] ?? '—') ?></td>
                        <td><?= number_format((float) $v['importe_final_eur'], 2, ',', '.') ?> €</td>
                        <td><span class="badge bg-<?= $v['estado'] === 'validada' ? 'success' : 'warning text-dark' ?>"><?= e($v['estado']) ?></span></td>
                        <td><?= e($v['registrado_por_nombre']) ?></td>
                        <td><?= e($v['fecha_cierre'] ?? $v['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
