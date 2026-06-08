<h1 class="h3 mb-4"><i class="bi bi-cart-check me-2"></i>Ventas</h1>
<div class="panel">
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Producto</th>
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
                        <td><?= e($v['producto_nombre']) ?></td>
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
