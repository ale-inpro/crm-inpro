<h1 class="h3 mb-4"><i class="bi bi-currency-euro me-2"></i><?= e($title) ?></h1>
<div class="panel">
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
