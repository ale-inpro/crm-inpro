<h1 class="h3 mb-3 mb-lg-4 mobile-hide-heading"><i class="bi bi-patch-check me-2"></i>Validar ventas</h1>

<?php if (empty($ventas)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> No hay ventas pendientes de validación.</div>
<?php else: ?>
    <?php foreach ($ventas as $v): ?>
        <?php $sugerida = $sugerencias[$v['id']] ?? null; ?>
        <div class="panel mb-3">
            <div class="panel-body">
                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                    <div class="min-w-0">
                        <h2 class="h6 mb-1">
                            <a href="<?= url('clientes/ver?id=' . $v['cliente_id']) ?>"><?= e($v['razon_social']) ?></a>
                        </h2>
                        <p class="small mb-1"><?= e($v['concepto_venta'] ?? '—') ?></p>
                        <p class="small text-muted mb-0">
                            <strong class="text-dark"><?= number_format((float) $v['importe_final_eur'], 2, ',', '.') ?> €</strong> ·
                            <?= e($v['registrado_por_nombre']) ?> ·
                            Colab.: <?= e($v['empresa_colaboradora_nombre'] ?? '—') ?>
                        </p>
                    </div>
                    <span class="badge bg-warning text-dark flex-shrink-0">Pendiente</span>
                </div>
                <form method="post" action="<?= url('ventas/validar') ?>" class="row g-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="venta_id" value="<?= (int) $v['id'] ?>">
                    <div class="col-12 col-md-8">
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
                    <div class="col-12 col-md-4 d-grid">
                        <label class="form-label small d-none d-md-block">&nbsp;</label>
                        <button class="btn btn-success"><i class="bi bi-check-lg"></i> Validar venta</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
