<?php $cid = (int) $cliente['id']; ?>

<!-- ── CABECERA COMPACTA ── -->
<div class="client-header animate-fade-up">
    <div class="client-header-top">
        <div>
            <h1 class="mb-1"><?= e($cliente['razon_social']) ?></h1>
            <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                <span class="badge rounded-pill px-3 py-1" style="background:<?= e($cliente['color_hex'] ?? '#6c757d') ?>;font-size:.78rem">
                    <?= e($cliente['estado_nombre'] ?? '—') ?>
                </span>
                <?php if (is_empresa() && $cliente['modo_acceso_empresa'] === 'lectura'): ?>
                    <span class="badge bg-secondary rounded-pill"><i class="bi bi-eye me-1"></i>Solo lectura</span>
                <?php elseif (is_inpro() && $cliente['modo_acceso_empresa'] === 'lectura'): ?>
                    <span class="badge bg-light text-secondary border rounded-pill"><i class="bi bi-building me-1"></i>Empresa: solo lectura</span>
                <?php endif; ?>
                <?php if (!$cliente['primera_visita_realizada']): ?>
                    <span class="badge badge-primera-visita rounded-pill"><i class="bi bi-flag me-1"></i>Sin 1ª visita</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($canEdit): ?>
                <button class="btn btn-inpro btn-sm" data-bs-toggle="modal" data-bs-target="#modalVisita">
                    <i class="bi bi-geo-alt-fill"></i> Visita
                </button>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalVenta">
                    <i class="bi bi-cart-plus-fill"></i> Venta
                </button>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTarea">
                    <i class="bi bi-check2-square"></i> Tarea
                </button>
                <?php if ($cliente['modo_acceso_empresa'] === 'edicion'): ?>
                <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalTransferir">
                    <i class="bi bi-arrow-left-right"></i> Transferir INPRO
                </button>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?= url('clientes') ?>" class="btn btn-ghost btn-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Metadatos en strip -->
    <div class="client-meta-strip">
        <?php if ($cliente['cif'] ?? null): ?>
            <span class="meta-item"><i class="bi bi-file-text"></i> <?= e($cliente['cif']) ?></span>
            <span class="meta-sep"></span>
        <?php endif; ?>
        <?php if ($cliente['ciudad'] ?? null): ?>
            <span class="meta-item"><i class="bi bi-geo-alt"></i> <?= e($cliente['ciudad']) ?></span>
            <span class="meta-sep"></span>
        <?php endif; ?>
        <?php if ($cliente['telefono_principal'] ?? null): ?>
            <span class="meta-item"><i class="bi bi-telephone"></i> <?= e($cliente['telefono_principal']) ?></span>
            <span class="meta-sep"></span>
        <?php endif; ?>
        <?php if ($cliente['email_principal'] ?? null): ?>
            <span class="meta-item"><i class="bi bi-envelope"></i> <?= e($cliente['email_principal']) ?></span>
            <span class="meta-sep"></span>
        <?php endif; ?>
        <?php if ($cliente['empresa_colaboradora_nombre'] ?? null): ?>
            <span class="meta-item"><i class="bi bi-building"></i> <?= e($cliente['empresa_colaboradora_nombre']) ?></span>
            <span class="meta-sep"></span>
        <?php endif; ?>
        <span class="meta-item">
            <i class="bi bi-<?= $cliente['primera_visita_realizada'] ? 'check-circle-fill text-success' : 'clock text-warning' ?>"></i>
            1ª visita: <?= $cliente['primera_visita_realizada'] ? 'Realizada' : 'Pendiente' ?>
        </span>
    </div>
</div>

<!-- Visitas programadas próximas (si hay) -->
<?php if (!empty($visitasProgramadas)): ?>
<div class="panel animate-fade-up mb-3">
    <div class="panel-header">
        <span><i class="bi bi-calendar-check text-inpro"></i> Próximas visitas programadas</span>
        <span class="badge bg-inpro rounded-pill"><?= count($visitasProgramadas) ?></span>
    </div>
    <div class="panel-body py-2">
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($visitasProgramadas as $vp): ?>
            <div class="d-flex align-items-center gap-2 border rounded-pill px-3 py-2 bg-light" style="font-size:.83rem">
                <i class="bi bi-calendar3 text-inpro"></i>
                <strong><?= date('d/m/Y H:i', strtotime($vp['fecha_visita'])) ?></strong>
                <span class="text-muted">·</span>
                <span><?= e($vp['usuario_nombre']) ?></span>
                <?php if (!empty($vp['recordatorio_enviado_at'])): ?>
                    <span class="small text-success" title="Recordatorio enviado">
                        <i class="bi bi-envelope-check-fill"></i>
                        <?= date('d/m/Y', strtotime($vp['recordatorio_enviado_at'])) ?>
                    </span>
                <?php endif; ?>
                <?php if ($canEdit): ?>
                <div class="d-flex gap-1 ms-1">
                    <button class="btn btn-xs btn-outline-success"
                        data-bs-toggle="modal" data-bs-target="#modalRealizar"
                        data-visita-id="<?= (int) $vp['id'] ?>" title="Marcar realizada">
                        <i class="bi bi-check-lg"></i>
                    </button>
                    <button class="btn btn-xs btn-outline-warning"
                        data-bs-toggle="modal" data-bs-target="#modalReagendar"
                        data-visita-id="<?= (int) $vp['id'] ?>"
                        data-fecha="<?= date('Y-m-d\TH:i', strtotime($vp['fecha_visita'])) ?>"
                        title="Reagendar">
                        <i class="bi bi-calendar2-week"></i>
                    </button>
                    <button class="btn btn-xs btn-outline-primary btn-recordatorio"
                        data-bs-toggle="modal" data-bs-target="#modalRecordatorio"
                        data-visita-id="<?= (int) $vp['id'] ?>"
                        data-fecha="<?= e(date('d/m/Y H:i', strtotime($vp['fecha_visita']))) ?>"
                        data-fecha-iso="<?= e(date('d/m/Y', strtotime($vp['fecha_visita']))) ?>"
                        data-es-remota="<?= (int) $vp['es_remota'] ?>"
                        data-usuario="<?= e($vp['usuario_nombre']) ?>"
                        title="Enviar recordatorio por email">
                        <i class="bi bi-envelope"></i>
                    </button>
                    <form method="post" action="<?= url('visitas/cancelar') ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="visita_id" value="<?= (int) $vp['id'] ?>">
                        <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabAgenda">
                        <button class="btn btn-xs btn-outline-danger" title="Cancelar"
                                data-confirm="¿Cancelar esta visita programada?">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── TABS FULL-WIDTH ── -->
<ul class="nav nav-tabs-inpro mb-3 animate-fade-up" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="btnTabAgenda" data-bs-toggle="tab" data-bs-target="#tabAgenda">
            <i class="bi bi-calendar3"></i> Agenda
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHistorial">
            <i class="bi bi-clock-history"></i> Historial
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTareas">
            <i class="bi bi-check2-square"></i> Tareas
            <?php $pendTareas = count(array_filter($tareas, fn($t) => $t['estado'] === 'pendiente')); ?>
            <?php if ($pendTareas > 0): ?>
                <span class="badge bg-inpro rounded-pill ms-1"><?= $pendTareas ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabContactos">
            <i class="bi bi-people"></i> Contactos
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabVentas">
            <i class="bi bi-cart-check"></i> Ventas
            <?php if (!empty($ventas)): ?>
                <span class="badge bg-secondary rounded-pill ms-1"><?= count($ventas) ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content animate-fade">

    <!-- TAB AGENDA -->
    <div class="tab-pane fade show active" id="tabAgenda">
        <div class="panel">
            <div class="panel-header"><i class="bi bi-calendar3"></i> Agenda y programación</div>
            <div class="panel-body">
                <?php require APP_PATH . '/Views/partials/calendario.php'; ?>
            </div>
        </div>
    </div>

    <!-- TAB HISTORIAL -->
    <div class="tab-pane fade" id="tabHistorial">
        <div class="panel">
            <div class="panel-header"><i class="bi bi-clock-history"></i> Historial de actividad</div>
            <div class="panel-body">
                <?php require APP_PATH . '/Views/partials/chatter.php'; ?>
            </div>
        </div>
    </div>

    <!-- TAB TAREAS -->
    <div class="tab-pane fade" id="tabTareas">
        <div class="panel">
            <div class="panel-header">
                <span><i class="bi bi-check2-square"></i> Tareas del cliente</span>
                <?php if ($canEdit): ?>
                <button class="btn btn-inpro btn-sm" data-bs-toggle="modal" data-bs-target="#modalTarea">
                    <i class="bi bi-plus-lg"></i> Nueva tarea
                </button>
                <?php endif; ?>
            </div>
            <div class="panel-body p-0">
                <?php if (empty($tareas)): ?>
                    <p class="text-muted small p-3 mb-0">Sin tareas registradas.</p>
                <?php else: ?>
                    <?php foreach ($tareas as $t): ?>
                    <div class="task-item px-3
                        <?= $t['estado'] === 'completada' ? 'done' : '' ?>
                        <?= $t['estado'] === 'cancelada' ? 'opacity-50' : '' ?>
                        <?= $t['prioridad'] === 'alta' ? 'task-priority-alta' : ($t['prioridad'] === 'media' ? 'task-priority-media' : '') ?>">

                        <!-- Estrella -->
                        <button class="btn-star <?= $t['destacada'] ? 'active' : '' ?>"
                                data-tarea-id="<?= (int) $t['id'] ?>"
                                onclick="toggleStar(this)"
                                title="<?= $t['destacada'] ? 'Quitar destacado' : 'Destacar' ?>">★</button>

                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate"><?= e($t['titulo']) ?></div>
                            <?php if (!empty($t['descripcion'])): ?>
                                <div class="small text-muted text-truncate"><?= e($t['descripcion']) ?></div>
                            <?php endif; ?>
                            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                <span class="prio-badge <?= e($t['prioridad']) ?>"><?= e($t['prioridad']) ?></span>
                                <span class="small text-muted"><i class="bi bi-calendar2"></i> <?= e(date('d/m/Y', strtotime($t['fecha_vencimiento']))) ?></span>
                                <span class="small text-muted"><i class="bi bi-person"></i> <?= e($t['asignado_nombre']) ?></span>
                                <?php if ($t['estado'] !== 'pendiente'): ?>
                                    <span class="badge bg-<?= $t['estado'] === 'completada' ? 'success' : 'secondary' ?> rounded-pill" style="font-size:.68rem"><?= e($t['estado']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <?php if ($canEdit): ?>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <button class="btn btn-xs btn-ghost text-primary"
                                    title="Editar"
                                    onclick="abrirEditarTarea(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php if ($t['estado'] === 'pendiente'): ?>
                            <form method="post" action="<?= url('tareas/completar') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                                <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabTareas">
                                <button class="btn btn-xs btn-ghost text-success" title="Completar"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form method="post" action="<?= url('tareas/cancelar') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                                <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabTareas">
                                <button class="btn btn-xs btn-ghost text-secondary" title="Cancelar"><i class="bi bi-x-lg"></i></button>
                            </form>
                            <?php endif; ?>
                            <form method="post" action="<?= url('tareas/eliminar') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                                <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabTareas">
                                <button class="btn btn-xs btn-ghost text-danger" title="Eliminar"
                                        data-confirm="¿Eliminar esta tarea? Esta acción no se puede deshacer.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TAB CONTACTOS -->
    <div class="tab-pane fade" id="tabContactos">
        <div class="panel">
        <div class="panel-header">
                <span><i class="bi bi-people"></i> Contactos</span>
                <?php if ($canEdit): ?>
                <button class="btn btn-inpro btn-sm" data-bs-toggle="modal" data-bs-target="#modalContacto">
                    <i class="bi bi-plus-lg"></i> Nuevo contacto
                </button>
                <?php endif; ?>
            </div>
            <div class="panel-body p-0">
                <?php if (empty($contactos)): ?>
                    <p class="text-muted small p-3 mb-0">Sin contactos registrados.</p>
                <?php else: ?>
                    <?php foreach ($contactos as $ct): ?>
                    <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom">
                        <div class="chatter-avatar"><?= strtoupper(mb_substr($ct['nombre'], 0, 1)) ?></div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">
                                <?= e($ct['nombre']) ?>
                                <?php if (!empty($ct['es_principal'])): ?>
                                    <span class="badge bg-inpro rounded-pill ms-1" style="font-size:.65rem">Principal</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($ct['cargo']): ?><div class="small text-muted"><?= e($ct['cargo']) ?></div><?php endif; ?>
                        </div>
                        <div class="text-end small text-muted">
                            <?php if ($ct['email']): ?><div><i class="bi bi-envelope"></i> <?= e($ct['email']) ?></div><?php endif; ?>
                            <?php if ($ct['telefono']): ?><div><i class="bi bi-telephone"></i> <?= e($ct['telefono']) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TAB VENTAS -->
    <div class="tab-pane fade" id="tabVentas">
        <div class="panel">
            <div class="panel-header"><i class="bi bi-cart-check"></i> Ventas registradas</div>
            <?php if (empty($ventas)): ?>
                <p class="text-muted small p-3 mb-0">Sin ventas registradas.</p>
            <?php else: ?>
                <div class="panel-body p-0">
                    <?php foreach ($ventas as $v): ?>
                    <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom">
                        <div>
                            <div class="fw-semibold"><?= e($v['producto_nombre']) ?></div>
                            <div class="small text-muted"><?= e(date('d/m/Y', strtotime($v['fecha_propuesta'] ?? $v['created_at']))) ?></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <strong><?= number_format((float) $v['importe_final_eur'], 2, ',', '.') ?> €</strong>
                            <span class="badge rounded-pill bg-<?= $v['estado'] === 'validada' ? 'success' : 'warning text-dark' ?>">
                                <?= e($v['estado']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($canEdit): ?>
<!-- ── MODALES ── -->

<!-- Modal Nuevo Contacto -->
<div class="modal fade" id="modalContacto" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('contactos/guardar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Nuevo contacto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Cargo</label>
                    <input type="text" name="cargo" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="es_principal" id="contactoPrincipal" value="1">
                    <label class="form-check-label" for="contactoPrincipal">Contacto principal</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro"><i class="bi bi-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Transferir INPRO -->
<div class="modal fade" id="modalTransferir" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('clientes/transferir-inpro') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right text-warning"></i> Transferir a INPRO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">La empresa quedará en modo solo lectura sobre este cliente.</p>
                <div class="mb-2">
                    <label class="form-label">Responsable INPRO</label>
                    <select name="responsable_inpro_id" class="form-select" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($usuariosInpro as $u): ?>
                            <option value="<?= (int) $u['id'] ?>"><?= e($u['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Motivo</label>
                    <input type="text" name="motivo" class="form-control" value="Empresa deja gestión a INPRO">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning"><i class="bi bi-arrow-left-right"></i> Transferir</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Visita -->
<div class="modal fade" id="modalVisita" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('visitas/guardar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-geo-alt-fill text-inpro"></i> Registrar visita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Modo</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="modo" id="modoRealizada" value="realizada" checked>
                            <label class="form-check-label" for="modoRealizada">Registrar realizada</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="modo" id="modoProgramar" value="programar">
                            <label class="form-check-label" for="modoProgramar">Programar futura</label>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Fecha y hora</label>
                    <input type="datetime-local" name="fecha_visita" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>
                <div class="mb-2 campo-resultado">
                    <label class="form-label">Resultado</label>
                    <select name="resultado" class="form-select">
                        <option value="interesado">Interesado</option>
                        <option value="muy_interesado">Muy interesado</option>
                        <option value="neutral">Neutral</option>
                        <option value="reagendar">Reagendar</option>
                        <option value="sin_interes">Sin interés</option>
                    </select>
                </div>
                <?php if (!$cliente['primera_visita_realizada']): ?>
                <div class="form-check mb-2 campo-resultado">
                    <input class="form-check-input" type="checkbox" name="es_primera_visita" id="pv" checked>
                    <label class="form-check-label" for="pv"><strong>Primera visita</strong></label>
                </div>
                <?php endif; ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="es_remota" id="esRemota">
                    <label class="form-check-label" for="esRemota">Visita remota (videollamada)</label>
                </div>
                <textarea name="notas" class="form-control" rows="3" placeholder="Notas / descripción"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro" id="btnGuardarVisita"><i class="bi bi-geo-alt-fill"></i> Guardar visita</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Marcar Realizada -->
<div class="modal fade" id="modalRealizar" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('visitas/realizar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="visita_id" id="realizarVisitaId">
            <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabAgenda">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill text-success"></i> Marcar como realizada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Resultado</label>
                    <select name="resultado" class="form-select">
                        <option value="interesado">Interesado</option>
                        <option value="muy_interesado">Muy interesado</option>
                        <option value="neutral">Neutral</option>
                        <option value="reagendar">Reagendar</option>
                        <option value="sin_interes">Sin interés</option>
                    </select>
                </div>
                <textarea name="notas_extra" class="form-control" rows="2" placeholder="Notas adicionales (opcional)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Confirmar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reagendar -->
<div class="modal fade" id="modalReagendar" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('visitas/reagendar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="visita_id" id="reagendarVisitaId">
            <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabAgenda">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar2-week text-warning"></i> Reagendar visita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Nueva fecha y hora</label>
                <input type="datetime-local" name="fecha_visita" id="reagendarFecha" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning"><i class="bi bi-calendar2-week"></i> Reagendar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Recordatorio -->
<div class="modal fade" id="modalRecordatorio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= url('visitas/recordatorio') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="visita_id" id="recordatorioVisitaId">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-envelope-fill text-primary"></i> Enviar recordatorio al cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small mb-3" id="recordatorioResumen">
                    <div><strong>Visita:</strong> <span id="recordatorioTipo">—</span></div>
                    <div><strong>Fecha:</strong> <span id="recordatorioFecha">—</span></div>
                    <div><strong>Comercial:</strong> <span id="recordatorioComercial">—</span></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Destinatario *</label>
                        <select name="destinatario" id="recordatorioEmail" class="form-select" required>
                            <?php
                            $emailsRecordatorio = [];
                            if (!empty($cliente['email_principal'])) {
                                $emailsRecordatorio['principal'] = $cliente['email_principal'];
                            }
                            foreach ($contactos as $ct) {
                                if (!empty($ct['email'])) {
                                    $emailsRecordatorio['contacto-' . $ct['id']] = $ct['email'];
                                }
                            }
                            if (empty($emailsRecordatorio)): ?>
                                <option value="">Sin emails disponibles</option>
                            <?php else: ?>
                                <?php foreach ($emailsRecordatorio as $key => $em):
                                    $label = $key === 'principal'
                                        ? 'Email principal — ' . $em
                                        : 'Contacto — ' . $em;
                                ?>
                                <option value="<?= e($em) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Asunto</label>
                        <input type="text" name="asunto" id="recordatorioAsunto" class="form-control"
                               value="Recordatorio de visita INPRO — <?= e($cliente['razon_social']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mensaje adicional <span class="text-muted fw-normal">(opcional)</span></label>
                        <textarea name="mensaje_extra" id="recordatorioMensaje" class="form-control" rows="3"
                                  placeholder="Ej: Por favor, confírmenos si la fecha sigue siendo conveniente o si prefieren videollamada."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Vista previa del correo</label>
                        <div class="border rounded p-3 bg-light small" id="recordatorioPreview" style="max-height:220px;overflow-y:auto;line-height:1.5">
                            <p class="mb-2">Estimado/a equipo de <strong><?= e($cliente['razon_social']) ?></strong>,</p>
                            <p class="mb-2 text-muted">Le recordamos su cita comercial programada con INPRO…</p>
                            <div class="p-2 rounded" style="background:#f4f7f5;border-left:3px solid #1a7f4b">
                                <div id="previewDetalle">Seleccione una visita para ver el detalle.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" <?= empty($emailsRecordatorio) ? 'disabled' : '' ?>>
                    <i class="bi bi-send-fill"></i> Enviar recordatorio
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Venta -->
<div class="modal fade" id="modalVenta" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('ventas/guardar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cart-plus-fill text-success"></i> Registrar venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select name="producto_id" class="form-select mb-2" required>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['nombre']) ?> (<?= number_format((float) $p['precio_anual_eur'], 0, ',', '.') ?> €/año)</option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="descuento_pct" class="form-control" placeholder="Descuento %" min="0" max="100" step="0.01" value="0">
                <small class="text-muted mt-1 d-block">Quedará pendiente de validación INPRO.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success"><i class="bi bi-cart-plus-fill"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tarea -->
<div class="modal fade" id="modalTarea" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('tareas/guardar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check2-square"></i> Nueva tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="titulo" class="form-control mb-2" placeholder="Título *" required>
                <textarea name="descripcion" class="form-control mb-2" rows="2" placeholder="Descripción (opcional)"></textarea>
                <input type="date" name="fecha_vencimiento" class="form-control mb-2" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                <div class="row g-2">
                    <div class="col-6">
                        <select name="prioridad" class="form-select">
                            <option value="baja">Prioridad baja</option>
                            <option value="media" selected>Prioridad media</option>
                            <option value="alta">Prioridad alta</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <select name="asignado_a_id" class="form-select">
                            <?php foreach ($usuariosAsignables as $u): ?>
                                <option value="<?= (int) $u['id'] ?>" <?= (int) $u['id'] === (int) current_user()['id'] ? 'selected' : '' ?>>
                                    <?= e($u['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro"><i class="bi bi-plus-lg"></i> Crear tarea</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Tarea -->
<div class="modal fade" id="modalEditarTarea" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('tareas/actualizar') ?>" class="modal-content" id="formEditarTarea">
            <?= csrf_field() ?>
            <input type="hidden" name="tarea_id" id="editTareaId">
            <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabTareas">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Título *</label>
                    <input type="text" name="titulo" id="editTituloTarea" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="editDescTarea" class="form-control" rows="3"></textarea>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label">Fecha vencimiento</label>
                        <input type="date" name="fecha_vencimiento" id="editFechaTarea" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" id="editPrioridadTarea" class="form-select">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Asignado a</label>
                    <select name="asignado_a_id" id="editAsignadoTarea" class="form-select">
                        <?php foreach ($usuariosAsignables as $u): ?>
                            <option value="<?= (int) $u['id'] ?>"><?= e($u['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro"><i class="bi bi-save"></i> Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inyectar visita_id en modales de acción
    document.querySelectorAll('[data-bs-target="#modalRealizar"]').forEach(function (btn) {
        btn.addEventListener('click', function () { document.getElementById('realizarVisitaId').value = btn.dataset.visitaId; });
    });
    document.querySelectorAll('[data-bs-target="#modalReagendar"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('reagendarVisitaId').value = btn.dataset.visitaId;
            document.getElementById('reagendarFecha').value = btn.dataset.fecha || '';
        });
    });
    document.querySelectorAll('.btn-recordatorio').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var fecha     = btn.dataset.fecha || '—';
            var fechaIso  = btn.dataset.fechaIso || '';
            var esRemota  = btn.dataset.esRemota === '1';
            var usuario   = btn.dataset.usuario || '—';
            var cliente   = <?= json_encode($cliente['razon_social'] ?? '') ?>;

            document.getElementById('recordatorioVisitaId').value = btn.dataset.visitaId;
            document.getElementById('recordatorioFecha').textContent = fecha;
            document.getElementById('recordatorioTipo').textContent = esRemota ? 'Videollamada' : 'Visita presencial';
            document.getElementById('recordatorioComercial').textContent = usuario;

            var asuntoEl = document.getElementById('recordatorioAsunto');
            if (asuntoEl && fechaIso) {
                asuntoEl.value = 'Recordatorio de visita INPRO — ' + cliente + ' — ' + fechaIso;
            }

            var detalle = document.getElementById('previewDetalle');
            if (detalle) {
                detalle.innerHTML = '<strong>' + (esRemota ? '💻 Videollamada' : '📍 Visita presencial') + '</strong><br>'
                    + 'Fecha y hora: <strong>' + fecha + '</strong><br>'
                    + 'Comercial: <strong>' + usuario + '</strong>';
            }
        });
    });

    // Toggle modo visita (realizada / programar)
    var modos = document.querySelectorAll('[name="modo"]');
    var camposResultado = document.querySelectorAll('.campo-resultado');
    function toggleResultado() {
        var programar = document.getElementById('modoProgramar') && document.getElementById('modoProgramar').checked;
        camposResultado.forEach(function (el) {
            el.style.display = programar ? 'none' : '';
            el.querySelectorAll('input, select, textarea').forEach(function (inp) {
                inp.disabled = programar;
            });
        });
        var btn = document.getElementById('btnGuardarVisita');
        if (btn) btn.innerHTML = programar
            ? '<i class="bi bi-calendar-plus"></i> Programar visita'
            : '<i class="bi bi-geo-alt-fill"></i> Guardar visita';
    }
    modos.forEach(function (r) { r.addEventListener('change', toggleResultado); });
    toggleResultado();

    // Activar pestaña desde hash (#tabAgenda, #tabTareas, etc.)
    var hash = location.hash;
    if (hash) {
        var tabBtn = document.querySelector('[data-bs-target="' + hash + '"]');
        if (tabBtn) bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    }
});

// Abrir modal editar tarea con datos pre-cargados
function abrirEditarTarea(t) {
    document.getElementById('editTareaId').value      = t.id;
    document.getElementById('editTituloTarea').value  = t.titulo || '';
    document.getElementById('editDescTarea').value    = t.descripcion || '';
    document.getElementById('editFechaTarea').value   = t.fecha_vencimiento || '';
    document.getElementById('editPrioridadTarea').value = t.prioridad || 'media';
    document.getElementById('editAsignadoTarea').value  = t.asignado_a_id || '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarTarea')).show();
}

// Toggle estrella
function toggleStar(btn) {
    var tareaId = btn.dataset.tareaId;
    var csrfToken = getCsrfToken();
    fetch('<?= url('tareas/destacar') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(csrfToken) + '&tarea_id=' + tareaId
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            btn.classList.toggle('active', data.destacada);
            btn.title = data.destacada ? 'Quitar destacado' : 'Destacar';
        }
    });
}
</script>
