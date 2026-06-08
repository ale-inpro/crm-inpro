<?php $cid = (int) $cliente['id']; ?>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h3 mb-2"><?= e($cliente['razon_social']) ?></h1>
        <span class="badge" style="background:<?= e($cliente['color_hex']) ?>"><?= e($cliente['estado_nombre']) ?></span>
        <?php if ($cliente['modo_acceso_empresa'] === 'lectura'): ?>
            <span class="badge bg-secondary">Empresa: solo lectura</span>
        <?php endif; ?>
        <?php if (!$cliente['primera_visita_realizada']): ?>
            <span class="badge badge-primera-visita">Sin 1ª visita</span>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canEdit): ?>
            <button class="btn btn-inpro btn-sm" data-bs-toggle="modal" data-bs-target="#modalVisita"><i class="bi bi-geo-alt"></i> Visita</button>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalVenta"><i class="bi bi-cart-plus"></i> Venta</button>
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTarea"><i class="bi bi-check2-square"></i> Tarea</button>
        <?php endif; ?>
        <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>
</div>

<?php if (!$canEdit): ?>
    <div class="alert alert-info py-2"><i class="bi bi-eye"></i> Acceso en solo lectura.</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel">
            <div class="panel-header">Información</div>
            <div class="panel-body small">
                <p class="mb-1"><strong>CIF:</strong> <?= e($cliente['cif'] ?? '—') ?></p>
                <p class="mb-1"><strong>Ciudad:</strong> <?= e($cliente['ciudad'] ?? '—') ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= e($cliente['email_principal'] ?? '—') ?></p>
                <p class="mb-1"><strong>Teléfono:</strong> <?= e($cliente['telefono_principal'] ?? '—') ?></p>
                <p class="mb-1"><strong>Colaborador:</strong> <?= e($cliente['empresa_colaboradora_nombre'] ?? '—') ?></p>
                <p class="mb-0"><strong>1ª visita:</strong> <?= $cliente['primera_visita_realizada'] ? 'Realizada' : 'Pendiente' ?></p>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">Contactos</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($contactos as $ct): ?>
                    <li class="list-group-item">
                        <strong><?= e($ct['nombre']) ?></strong>
                        <?php if ($ct['cargo']): ?><br><small class="text-muted"><?= e($ct['cargo']) ?></small><?php endif; ?>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($contactos)): ?><li class="list-group-item text-muted">Sin contactos</li><?php endif; ?>
            </ul>
        </div>
        <?php if ($canEdit && $cliente['modo_acceso_empresa'] === 'edicion'): ?>
        <div class="panel border-warning">
            <div class="panel-header text-warning"><i class="bi bi-arrow-left-right"></i> Transferir a INPRO</div>
            <div class="panel-body">
                <form method="post" action="<?= url('clientes/transferir-inpro') ?>">
                    <input type="hidden" name="cliente_id" value="<?= $cid ?>">
                    <select name="responsable_inpro_id" class="form-select form-select-sm mb-2" required>
                        <option value="">Responsable INPRO...</option>
                        <?php foreach ($usuariosInpro as $u): ?>
                            <option value="<?= (int) $u['id'] ?>"><?= e($u['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="motivo" class="form-control form-control-sm mb-2" value="Empresa deja gestión a INPRO">
                    <button class="btn btn-warning btn-sm w-100">Transferir</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-8">
        <ul class="nav nav-tabs nav-tabs-inpro mb-3" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabActividad">Actividad</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTareas">Tareas <span class="badge bg-secondary"><?= count(array_filter($tareas, fn($t) => $t['estado'] === 'pendiente')) ?></span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabVentas">Ventas</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tabActividad">
                <div class="panel">
                    <div class="panel-header">Historial de actividad</div>
                    <div class="panel-body">
                        <?php $timeline = $timeline; require APP_PATH . '/Views/partials/chatter.php'; ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tabTareas">
                <div class="panel">
                    <div class="panel-header">Tareas del cliente</div>
                    <div class="panel-body p-0 px-3">
                        <?php foreach ($tareas as $t): ?>
                            <div class="task-item <?= $t['estado'] === 'completada' ? 'done' : '' ?>">
                                <div class="flex-grow-1">
                                    <span class="fw-semibold"><?= e($t['titulo']) ?></span>
                                    <div class="small text-muted"><?= e($t['fecha_vencimiento']) ?> · <?= e($t['asignado_nombre']) ?></div>
                                </div>
                                <?php if ($t['estado'] === 'pendiente' && (is_inpro() || (int) $t['asignado_a_id'] === (int) current_user()['id'])): ?>
                                    <form method="post" action="<?= url('tareas/completar') ?>">
                                        <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                                        <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>">
                                        <button class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($tareas)): ?><p class="text-muted small py-3">Sin tareas</p><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tabVentas">
                <div class="panel">
                    <div class="panel-header">Ventas registradas</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($ventas as $v): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= e($v['producto_nombre']) ?> — <strong><?= number_format((float) $v['importe_final_eur'], 2, ',', '.') ?> €</strong></span>
                                <span class="badge bg-<?= $v['estado'] === 'validada' ? 'success' : 'warning text-dark' ?>"><?= e($v['estado']) ?></span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($ventas)): ?><li class="list-group-item text-muted">Sin ventas</li><?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($canEdit): ?>
<!-- Modal Visita -->
<div class="modal fade" id="modalVisita" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('visitas/guardar') ?>" class="modal-content">
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header"><h5 class="modal-title">Registrar visita</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Fecha</label>
                    <input type="datetime-local" name="fecha_visita" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Resultado</label>
                    <select name="resultado" class="form-select">
                        <option value="interesado">Interesado</option>
                        <option value="muy_interesado">Muy interesado</option>
                        <option value="neutral">Neutral</option>
                        <option value="reagendar">Reagendar</option>
                    </select>
                </div>
                <?php if (!$cliente['primera_visita_realizada']): ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="es_primera_visita" id="pv" checked>
                        <label class="form-check-label" for="pv"><strong>Primera visita</strong></label>
                    </div>
                <?php endif; ?>
                <textarea name="notas" class="form-control" rows="3" placeholder="Notas"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro">Guardar visita</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Venta -->
<div class="modal fade" id="modalVenta" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('ventas/guardar') ?>" class="modal-content">
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header"><h5 class="modal-title">Registrar venta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <select name="producto_id" class="form-select mb-2" required>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['nombre']) ?> (<?= number_format((float) $p['precio_anual_eur'], 0, ',', '.') ?> €/año)</option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="descuento_pct" class="form-control" placeholder="Descuento %" min="0" max="100" step="0.01" value="0">
                <small class="text-muted">Quedará pendiente de validación INPRO.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success">Registrar venta</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tarea -->
<div class="modal fade" id="modalTarea" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('tareas/guardar') ?>" class="modal-content">
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header"><h5 class="modal-title">Nueva tarea</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="text" name="titulo" class="form-control mb-2" placeholder="Título *" required>
                <input type="date" name="fecha_vencimiento" class="form-control mb-2" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                <select name="prioridad" class="form-select mb-2">
                    <option value="baja">Baja</option>
                    <option value="media" selected>Media</option>
                    <option value="alta">Alta</option>
                </select>
                <select name="asignado_a_id" class="form-select mb-2">
                    <?php foreach ($usuariosAsignables as $u): ?>
                        <option value="<?= (int) $u['id'] ?>" <?= (int) $u['id'] === (int) current_user()['id'] ? 'selected' : '' ?>><?= e($u['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="descripcion" class="form-control" rows="2" placeholder="Descripción"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro">Crear tarea</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
