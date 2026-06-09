<h1 class="h3 mb-3 mb-lg-4 mobile-hide-heading"><i class="bi bi-currency-euro me-2"></i><?= e($title) ?></h1>

<!-- Móvil -->
<div class="d-lg-none mobile-list">
    <?php if (empty($comisiones)): ?>
        <div class="text-muted text-center py-4 small">Sin comisiones.</div>
    <?php else: ?>
        <?php foreach ($comisiones as $co): ?>
        <div class="mobile-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="min-w-0">
                    <div class="mobile-card-title"><?= e($co['razon_social']) ?></div>
                    <?php if (is_inpro()): ?>
                        <div class="mobile-card-sub"><?= e($co['empresa_nombre']) ?></div>
                    <?php endif; ?>
                    <div class="small text-muted"><?= e($co['regla_nombre']) ?> · <?= $co['porcentaje'] ?>%</div>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="fw-bold fs-5 text-inpro"><?= number_format((float) $co['importe_comision_eur'], 2, ',', '.') ?> €</div>
                    <span class="badge bg-success"><?= e($co['estado']) ?></span>
                </div>
            </div>
        </div>
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
                        <?php if (is_inpro()): ?><th>Empresa</th><?php endif; ?>
                        <th>Regla</th>
                        <th>%</th>
                        <th>Importe</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($comisiones as $co): ?>
                    <tr>
                        <td><?= e($co['razon_social']) ?></td>
                        <?php if (is_inpro()): ?><td><?= e($co['empresa_nombre']) ?></td><?php endif; ?>
                        <td><?= e($co['regla_nombre']) ?></td>
                        <td><?= $co['porcentaje'] ?>%</td>
                        <td><strong><?= number_format((float) $co['importe_comision_eur'], 2, ',', '.') ?> €</strong></td>
                        <td><span class="badge bg-success"><?= e($co['estado']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
