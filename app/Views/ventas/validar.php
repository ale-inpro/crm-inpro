<h1 class="h3 mb-4"><i class="bi bi-patch-check me-2"></i>Validar ventas</h1>

<?php if (empty($ventas)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> No hay ventas pendientes de validación.</div>
<?php else: ?>
    <?php foreach ($ventas as $v): ?>
        <?php $sugerida = $sugerencias[$v['id']] ?? null; ?>
        <div class="panel mb-3">
            <div class="panel-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h2 class="h6 mb-1">
                            <a href="<?= url('clientes/ver?id=' . $v['cliente_id']) ?>"><?= e($v['razon_social']) ?></a>
                            — <?= e($v['producto_nombre']) ?>
                        </h2>
                        <p class="small text-muted mb-0">
                            <strong><?= number_format((float) $v['importe_final_eur'], 2, ',', '.') ?> €</strong> ·
                            Registrada por <?= e($v['registrado_por_nombre']) ?> ·
                            Colaborador: <?= e($v['empresa_colaboradora_nombre'] ?? '—') ?>
                        </p>
                    </div>
                    <span class="badge bg-warning text-dark">Pendiente</span>
                </div>
                <form method="post" action="<?= url('ventas/validar') ?>" class="row g-2 align-items-end">
                    <?= csrf_field() ?>
                    <input type="hidden" name="venta_id" value="<?= (int) $v['id'] ?>">
                    <div class="col-md-8">
                        <label class="form-label small">Regla de comisión</label>
                        <select name="regla_comision_id" class="form-select" required>
                            <?php foreach ($reglas as $r): ?>
                                <option value="<?= (int) $r['id'] ?>" <?= $sugerida === (int) $r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['nombre']) ?> (<?= $r['porcentaje'] ?>%)
                                    <?= $sugerida === (int) $r['id'] ? ' — sugerida' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-success w-100"><i class="bi bi-check-lg"></i> Validar venta</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
