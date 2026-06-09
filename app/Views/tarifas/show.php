<?php $tid = (int) $tarifa['id']; ?>

<div class="panel mb-4">
    <div class="panel-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 mobile-hide-heading"><?= e($tarifa['nombre']) ?></h1>
                <?php if ($tarifa['activa']): ?>
                    <span class="badge bg-success">Activa</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Inactiva</span>
                <?php endif; ?>
                <?php if (!empty($tarifa['es_default'])): ?>
                    <span class="badge bg-inpro">Por defecto</span>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= url('tarifas/editar?id=' . $tid) ?>" class="btn btn-outline-secondary btn-sm">Editar</a>
                <?php if (empty($tarifa['es_default'])): ?>
                <form method="post" action="<?= url('tarifas/eliminar') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $tid ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm"
                            data-confirm="¿Eliminar la tarifa «<?= e($tarifa['nombre']) ?>»? Se eliminarán sus tramos.">
                        Eliminar
                    </button>
                </form>
                <?php endif; ?>
                <a href="<?= url('tarifas') ?>" class="btn btn-ghost btn-sm">Volver</a>
            </div>
        </div>
    </div>
</div>

<!-- Tramos -->
<div class="panel mb-4">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bar-chart-steps"></i> Tramos por volumen</span>
        <button class="btn btn-inpro btn-sm" data-bs-toggle="modal" data-bs-target="#modalTramoNuevo">
            <i class="bi bi-plus-lg"></i> Añadir tramo
        </button>
    </div>
    <div class="panel-body p-0">
        <!-- Móvil: cards -->
        <div class="d-lg-none mobile-list p-2">
            <?php if (empty($tramos)): ?>
                <p class="text-muted text-center py-3 small mb-0">Sin tramos definidos.</p>
            <?php else: ?>
                <?php foreach ($tramos as $tr): ?>
                <div class="mobile-card">
                    <div class="fw-semibold mb-1">
                        <?= (int) $tr['obras_desde'] ?> – <?= $tr['obras_hasta'] ? (int) $tr['obras_hasta'] : '∞' ?> obras
                    </div>
                    <div class="text-inpro fw-bold mb-1"><?= number_format((float) $tr['precio_mes_eur'], 2, ',', '.') ?> €/mes</div>
                    <?php if (!empty($tr['stripe_price_id'])): ?>
                        <code class="small d-block text-muted text-truncate mb-2"><?= e($tr['stripe_price_id']) ?></code>
                    <?php endif; ?>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-editar-tramo flex-grow-1"
                                data-tramo='<?= e(json_encode($tr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP)) ?>'>
                            Editar
                        </button>
                        <form method="post" action="<?= url('tarifas/tramo/eliminar') ?>" class="flex-grow-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tarifa_id" value="<?= $tid ?>">
                            <input type="hidden" name="tramo_id" value="<?= (int) $tr['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                    data-confirm="¿Eliminar este tramo?">Eliminar</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Desde (nº obras)</th>
                        <th>Hasta (nº obras)</th>
                        <th>Precio €/mes</th>
                        <th>Stripe price ID</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($tramos)): ?>
                    <tr><td colspan="5" class="text-muted text-center py-4">Sin tramos definidos.</td></tr>
                <?php else: ?>
                    <?php foreach ($tramos as $tr): ?>
                    <tr>
                        <td><?= (int) $tr['obras_desde'] ?></td>
                        <td><?= $tr['obras_hasta'] ? (int) $tr['obras_hasta'] : '∞' ?></td>
                        <td><?= number_format((float) $tr['precio_mes_eur'], 2, ',', '.') ?> €</td>
                        <td><code class="small"><?= e($tr['stripe_price_id'] ?? '—') ?></code></td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-editar-tramo"
                                    data-tramo='<?= e(json_encode($tr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP)) ?>'>
                                Editar
                            </button>
                            <form method="post" action="<?= url('tarifas/tramo/eliminar') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="tarifa_id" value="<?= $tid ?>">
                                <input type="hidden" name="tramo_id" value="<?= (int) $tr['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        data-confirm="¿Eliminar este tramo?">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Empresas asignadas -->
<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-building"></i> Empresas asignadas (<?= count($empresas) ?>)</span>
        <?php if (!empty($empresasAsignables)): ?>
        <button class="btn btn-inpro btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignarEmpresa">
            <i class="bi bi-plus-lg"></i> Asignar empresa
        </button>
        <?php endif; ?>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($empresas)): ?>
            <p class="text-muted small p-3 mb-0">Ninguna empresa usa esta tarifa directamente (las demás usan la tarifa por defecto).</p>
        <?php else: ?>
            <?php foreach ($empresas as $emp): ?>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <span><?= e($emp['nombre']) ?></span>
                <form method="post" action="<?= url('tarifas/quitar-empresa') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="tarifa_id" value="<?= $tid ?>">
                    <input type="hidden" name="empresa_id" value="<?= (int) $emp['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-link text-danger"
                            data-confirm="¿Quitar esta empresa? Pasará a la tarifa por defecto.">Quitar</button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal nuevo tramo -->
<div class="modal fade" id="modalTramoNuevo" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <form method="post" action="<?= url('tarifas/tramo/guardar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="tarifa_id" value="<?= $tid ?>">
            <div class="modal-header">
                <h5 class="modal-title">Añadir tramo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-2">
                <div class="col-6">
                    <label class="form-label">Desde (obras)</label>
                    <input type="number" name="obras_desde" class="form-control" min="1" required value="1">
                </div>
                <div class="col-6">
                    <label class="form-label">Hasta (obras)</label>
                    <input type="number" name="obras_hasta" class="form-control" min="1" placeholder="Vacío = ∞">
                </div>
                <div class="col-6">
                    <label class="form-label">Precio €/mes</label>
                    <input type="text" name="precio_mes_eur" class="form-control" required placeholder="300.00">
                </div>
                <div class="col-6">
                    <label class="form-label">Orden</label>
                    <input type="number" name="orden" class="form-control" value="<?= count($tramos) + 1 ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Stripe price ID</label>
                    <input type="text" name="stripe_price_id" class="form-control" placeholder="price_...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro">Guardar tramo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal editar tramo -->
<div class="modal fade" id="modalTramoEditar" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <form method="post" action="<?= url('tarifas/tramo/actualizar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="tarifa_id" value="<?= $tid ?>">
            <input type="hidden" name="tramo_id" id="editTramoId">
            <div class="modal-header">
                <h5 class="modal-title">Editar tramo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-2">
                <div class="col-6">
                    <label class="form-label">Desde (obras)</label>
                    <input type="number" name="obras_desde" id="editObrasDesde" class="form-control" min="1" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Hasta (obras)</label>
                    <input type="number" name="obras_hasta" id="editObrasHasta" class="form-control" min="1" placeholder="Vacío = ∞">
                </div>
                <div class="col-6">
                    <label class="form-label">Precio €/mes</label>
                    <input type="text" name="precio_mes_eur" id="editPrecioMes" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Orden</label>
                    <input type="number" name="orden" id="editOrden" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Stripe price ID</label>
                    <input type="text" name="stripe_price_id" id="editStripeId" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal asignar empresa -->
<?php if (!empty($empresasAsignables)): ?>
<div class="modal fade" id="modalAsignarEmpresa" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <form method="post" action="<?= url('tarifas/asignar-empresa') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="tarifa_id" value="<?= $tid ?>">
            <div class="modal-header">
                <h5 class="modal-title">Asignar empresa colaboradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select name="empresa_id" class="form-select" required>
                    <option value="">Selecciona...</option>
                    <?php foreach ($empresasAsignables as $emp): ?>
                        <option value="<?= (int) $emp['id'] ?>"><?= e($emp['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro">Asignar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-editar-tramo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tr = JSON.parse(this.getAttribute('data-tramo'));
            document.getElementById('editTramoId').value = tr.id;
            document.getElementById('editObrasDesde').value = tr.obras_desde;
            document.getElementById('editObrasHasta').value = tr.obras_hasta || '';
            document.getElementById('editPrecioMes').value = tr.precio_mes_eur;
            document.getElementById('editOrden').value = tr.orden;
            document.getElementById('editStripeId').value = tr.stripe_price_id || '';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTramoEditar')).show();
        });
    });
});
</script>
