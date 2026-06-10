<?php
$cid = (int) $cliente['id'];
$estadoActualId = (int) ($cliente['estado_pipeline_id'] ?? 0);
$totalActividad = !empty($actividadBloqueante) ? array_sum($actividadBloqueante) : 0;
$msgEliminar = $totalActividad > 0
    ? 'Este cliente tiene visitas, ventas o tareas registradas y no puede eliminarse.'
    : '¿Eliminar el cliente «' . ($cliente['razon_social'] ?? '') . '»? No tiene visitas, ventas ni tareas registradas.';
$pendTareas = count(array_filter($tareas, fn($t) => $t['estado'] === 'pendiente'));
$tareasRedirect = 'clientes/ver?id=' . $cid . '#tabTareas';

$contactoPrincipal = contacto_principal_desde_lista($contactos ?? []);
$telPrincipal = $contactoPrincipal ? ($contactoPrincipal['telefono'] ?? null) : null;
$emailPrincipal = $contactoPrincipal ? ($contactoPrincipal['email'] ?? null) : null;
?>

<!-- ── CABECERA COMPACTA ── -->
<div class="client-header animate-fade-up">
    <div class="client-header-top">
        <div>
            <h1 class="mb-1 mobile-hide-heading"><?= e($cliente['razon_social']) ?></h1>
            <!-- Móvil: badges reducidos -->
            <div class="d-flex align-items-center gap-2 flex-wrap mt-1 d-lg-none">
                <?php if ($canEdit): ?>
                <button type="button"
                        class="badge rounded-pill px-3 py-2 border-0 cliente-estado-badge"
                        style="background:<?= e($cliente['color_hex'] ?? '#6c757d') ?>;font-size:.78rem"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#cambiarEtapaOffcanvas">
                    <?= e($cliente['estado_nombre'] ?? '—') ?> <i class="bi bi-chevron-down ms-1"></i>
                </button>
                <?php else: ?>
                <span class="badge rounded-pill px-3 py-1" style="background:<?= e($cliente['color_hex'] ?? '#6c757d') ?>;font-size:.78rem">
                    <?= e($cliente['estado_nombre'] ?? '—') ?>
                </span>
                <?php endif; ?>
                <?php if (!$cliente['primera_visita_realizada']): ?>
                    <span class="badge badge-primera-visita rounded-pill">Sin 1ª visita</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($cliente['empresa_colaboradora_id'])): ?>
            <p class="d-lg-none small text-muted mb-0 mt-1 cliente-sub-meta">
                <i class="bi bi-person-gear"></i> <?= e($gestorEtiqueta) ?>
                <?php if (is_empresa()): ?>
                    · <?= $cliente['modo_acceso_empresa'] === 'lectura' ? 'Solo lectura' : 'Edición' ?>
                <?php elseif (is_inpro()): ?>
                    · <?= e($cliente['empresa_colaboradora_nombre'] ?? 'Empresa') ?>: <?= $cliente['modo_acceso_empresa'] === 'lectura' ? 'lectura' : 'edición' ?>
                <?php endif; ?>
            </p>
            <?php endif; ?>
            <!-- Desktop: badges completos -->
            <div class="d-none d-lg-flex align-items-center gap-2 flex-wrap mt-1">
                <?php if ($canEdit): ?>
                <button type="button"
                        class="badge rounded-pill px-3 py-2 border-0 cliente-estado-badge"
                        style="background:<?= e($cliente['color_hex'] ?? '#6c757d') ?>;font-size:.78rem"
                        data-bs-toggle="modal"
                        data-bs-target="#cambiarEtapaModal">
                    <?= e($cliente['estado_nombre'] ?? '—') ?> <i class="bi bi-chevron-down ms-1"></i>
                </button>
                <?php else: ?>
                <span class="badge rounded-pill px-3 py-1" style="background:<?= e($cliente['color_hex'] ?? '#6c757d') ?>;font-size:.78rem">
                    <?= e($cliente['estado_nombre'] ?? '—') ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($cliente['empresa_colaboradora_id'])): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                        <i class="bi bi-person-gear me-1"></i>Gestionado por: <?= e($gestorEtiqueta) ?>
                    </span>
                    <?php if (is_empresa() && $cliente['modo_acceso_empresa'] === 'lectura'): ?>
                        <span class="badge bg-secondary rounded-pill"><i class="bi bi-eye me-1"></i>Tu acceso: solo lectura</span>
                    <?php elseif (is_empresa() && $cliente['modo_acceso_empresa'] === 'edicion'): ?>
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill"><i class="bi bi-pencil me-1"></i>Tu acceso: edición</span>
                    <?php elseif (is_inpro() && $cliente['modo_acceso_empresa'] === 'lectura'): ?>
                        <span class="badge bg-light text-secondary border rounded-pill"><i class="bi bi-building me-1"></i><?= e($cliente['empresa_colaboradora_nombre'] ?? 'Empresa') ?>: solo lectura</span>
                    <?php elseif (is_inpro() && $cliente['modo_acceso_empresa'] === 'edicion'): ?>
                        <span class="badge bg-light text-secondary border rounded-pill"><i class="bi bi-building me-1"></i><?= e($cliente['empresa_colaboradora_nombre'] ?? 'Empresa') ?>: edición</span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!$cliente['primera_visita_realizada']): ?>
                    <span class="badge badge-primera-visita rounded-pill"><i class="bi bi-flag me-1"></i>Sin 1ª visita</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap client-header-actions">
            <?php if ($canEdit): ?>
                <button class="btn btn-inpro btn-sm btn-action-mobile" data-bs-toggle="modal" data-bs-target="#modalVisita">
                    <i class="bi bi-geo-alt-fill"></i><span class="btn-action-label">Visita</span>
                </button>
                <button class="btn btn-success btn-sm btn-action-mobile" data-bs-toggle="modal" data-bs-target="#modalVenta">
                    <i class="bi bi-cart-plus-fill"></i><span class="btn-action-label">Venta</span>
                </button>
                <button class="btn btn-outline-primary btn-sm btn-action-mobile" data-bs-toggle="modal" data-bs-target="#modalTarea">
                    <i class="bi bi-check2-square"></i><span class="btn-action-label">Tarea</span>
                </button>
                <?php
                $tieneMenuMovil = (!empty($canTransferirAInpro) && !empty($usuariosInpro))
                    || (!empty($canTransferirAEmpresa) && !empty($usuariosEmpresa))
                    || !empty($canManageCliente);
                ?>
                <?php if ($tieneMenuMovil): ?>
                <div class="dropdown d-lg-none">
                    <button class="btn btn-outline-secondary btn-sm btn-action-mobile" data-bs-toggle="dropdown" aria-label="Más acciones">
                        <i class="bi bi-three-dots"></i><span class="btn-action-label">Más</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (!empty($canTransferirAInpro) && !empty($usuariosInpro)): ?>
                        <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalTransferirInpro">Transferir a INPRO</button></li>
                        <?php endif; ?>
                        <?php if (!empty($canTransferirAEmpresa) && !empty($usuariosEmpresa)): ?>
                        <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalTransferirEmpresa">Transferir a empresa</button></li>
                        <?php endif; ?>
                        <?php if (!empty($canManageCliente)): ?>
                        <li><a class="dropdown-item" href="<?= url('clientes/editar?id=' . $cid) ?>">Editar cliente</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="<?= url('clientes/eliminar') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $cid ?>">
                                <button type="submit" class="dropdown-item text-danger"
                                    <?= ($totalActividad ?? 0) > 0 ? 'disabled' : '' ?>
                                    <?php if (($totalActividad ?? 0) === 0): ?>data-confirm="<?= e($msgEliminar ?? '') ?>"<?php endif; ?>>
                                    Eliminar cliente
                                </button>
                            </form>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <?php if (!empty($canTransferirAInpro) && !empty($usuariosInpro)): ?>
                <button class="btn btn-outline-warning btn-sm d-none d-lg-inline-flex" data-bs-toggle="modal" data-bs-target="#modalTransferirInpro">
                    <i class="bi bi-arrow-left-right"></i> Transferir a INPRO
                </button>
                <?php endif; ?>
                <?php if (!empty($canTransferirAEmpresa) && !empty($usuariosEmpresa)): ?>
                <button class="btn btn-outline-warning btn-sm d-none d-lg-inline-flex" data-bs-toggle="modal" data-bs-target="#modalTransferirEmpresa">
                    <i class="bi bi-arrow-left-right"></i> Transferir a empresa
                </button>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($canManageCliente)): ?>
                <a href="<?= url('clientes/editar?id=' . $cid) ?>" class="btn btn-outline-secondary btn-sm d-none d-lg-inline-flex" title="Editar cliente">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <form method="post" action="<?= url('clientes/eliminar') ?>" class="d-none d-lg-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $cid ?>">
                    <button type="submit"
                            class="btn btn-outline-danger btn-sm"
                            title="<?= $totalActividad > 0 ? 'No se puede eliminar: tiene actividad' : 'Eliminar cliente' ?>"
                            <?= $totalActividad > 0 ? 'disabled' : '' ?>
                            <?php if ($totalActividad === 0): ?>data-confirm="<?= e($msgEliminar) ?>"<?php endif; ?>>
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            <?php endif; ?>
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
        <?php if ($telPrincipal): ?>
            <a href="tel:<?= e(preg_replace('/\s+/', '', $telPrincipal)) ?>" class="meta-item meta-item-link">
                <i class="bi bi-telephone"></i> <?= e($telPrincipal) ?>
            </a>
            <span class="meta-sep"></span>
        <?php endif; ?>
        <?php if ($emailPrincipal): ?>
            <a href="mailto:<?= e($emailPrincipal) ?>" class="meta-item meta-item-link">
                <i class="bi bi-envelope"></i> <?= e($emailPrincipal) ?>
            </a>
            <span class="meta-sep"></span>
        <?php endif; ?>
        <?php if ($cliente['empresa_colaboradora_nombre'] ?? null): ?>
            <span class="meta-item"><i class="bi bi-building"></i> <?= e($cliente['empresa_colaboradora_nombre']) ?></span>
            <span class="meta-sep"></span>
        <?php endif; ?>
        <span class="meta-item d-none d-lg-inline">
            <i class="bi bi-<?= $cliente['primera_visita_realizada'] ? 'check-circle-fill text-success' : 'clock text-warning' ?>"></i>
            1ª visita: <?= $cliente['primera_visita_realizada'] ? 'Realizada' : 'Pendiente' ?>
        </span>
    </div>
    <?php if ($telPrincipal || $emailPrincipal): ?>
    <div class="d-lg-none client-quick-actions mt-2">
        <?php if ($telPrincipal): ?>
        <a href="tel:<?= e(preg_replace('/\s+/', '', $telPrincipal)) ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">
            <i class="bi bi-telephone-fill"></i> Llamar
        </a>
        <?php endif; ?>
        <?php if ($emailPrincipal): ?>
        <a href="mailto:<?= e($emailPrincipal) ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">
            <i class="bi bi-envelope-fill"></i> Email
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Visitas programadas (desktop: arriba de tabs) -->
<?php if (!empty($visitasProgramadas)): ?>
<div class="panel animate-fade-up mb-3 d-none d-lg-block">
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

<!-- ── TABS DESKTOP (5) ── -->
<ul class="nav nav-tabs-inpro nav-tabs-scroll mb-3 animate-fade-up flex-nowrap d-none d-lg-flex" role="tablist">
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

<!-- ── TABS MÓVIL (3) ── -->
<ul class="nav nav-tabs-inpro mb-3 animate-fade-up d-lg-none" role="tablist" id="clientTabsMobile">
    <li class="nav-item flex-fill">
        <button class="nav-link active w-100" data-bs-toggle="tab" data-bs-target="#tabActividadMob">
            <i class="bi bi-clock-history"></i> Actividad
        </button>
    </li>
    <li class="nav-item flex-fill">
        <button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#tabTareas">
            <i class="bi bi-check2-square"></i> Tareas
            <?php if ($pendTareas > 0): ?>
                <span class="badge bg-inpro rounded-pill ms-1"><?= $pendTareas ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item flex-fill">
        <button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#tabDatosMob">
            <i class="bi bi-folder2"></i> Datos
        </button>
    </li>
</ul>

<div class="tab-content animate-fade">

    <!-- TAB ACTIVIDAD (móvil) -->
    <div class="tab-pane fade show active d-lg-none" id="tabActividadMob">
        <?php if (!empty($visitasProgramadas)): ?>
        <div class="panel mb-3">
            <div class="panel-header">
                <span><i class="bi bi-calendar-check text-inpro"></i> Próximas visitas</span>
                <span class="badge bg-inpro rounded-pill"><?= count($visitasProgramadas) ?></span>
            </div>
            <div class="panel-body p-0">
                <?php foreach ($visitasProgramadas as $vp): ?>
                <div class="visita-mobile-card">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold"><?= date('d/m/Y H:i', strtotime($vp['fecha_visita'])) ?></div>
                        <div class="small text-muted"><?= e($vp['usuario_nombre']) ?></div>
                        <?php if (!empty($vp['recordatorio_enviado_at'])): ?>
                            <div class="small text-success"><i class="bi bi-envelope-check"></i> Recordatorio enviado</div>
                        <?php endif; ?>
                    </div>
                    <?php if ($canEdit): ?>
                    <div class="dropdown flex-shrink-0">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-label="Acciones visita">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalRealizar"
                                        data-visita-id="<?= (int) $vp['id'] ?>">
                                    <i class="bi bi-check-lg me-2"></i> Marcar realizada
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalReagendar"
                                        data-visita-id="<?= (int) $vp['id'] ?>"
                                        data-fecha="<?= date('Y-m-d\TH:i', strtotime($vp['fecha_visita'])) ?>">
                                    <i class="bi bi-calendar2-week me-2"></i> Reagendar
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item btn-recordatorio" data-bs-toggle="modal" data-bs-target="#modalRecordatorio"
                                        data-visita-id="<?= (int) $vp['id'] ?>"
                                        data-fecha="<?= e(date('d/m/Y H:i', strtotime($vp['fecha_visita']))) ?>"
                                        data-fecha-iso="<?= e(date('d/m/Y', strtotime($vp['fecha_visita']))) ?>"
                                        data-es-remota="<?= (int) $vp['es_remota'] ?>"
                                        data-usuario="<?= e($vp['usuario_nombre']) ?>">
                                    <i class="bi bi-envelope me-2"></i> Enviar recordatorio
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= url('visitas/cancelar') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="visita_id" value="<?= (int) $vp['id'] ?>">
                                    <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabActividadMob">
                                    <button type="submit" class="dropdown-item text-danger"
                                            data-confirm="¿Cancelar esta visita programada?">
                                        <i class="bi bi-x-lg me-2"></i> Cancelar visita
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="panel">
            <div class="panel-header"><i class="bi bi-clock-history"></i> Historial de actividad</div>
            <div class="panel-body">
                <?php require APP_PATH . '/Views/partials/chatter.php'; ?>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="<?= url('tareas?vista=calendario&cliente_id=' . $cid) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar3"></i> Ver calendario completo
            </a>
        </div>
    </div>

    <!-- TAB AGENDA (desktop) -->
    <div class="tab-pane fade show active client-tab-desktop" id="tabAgenda">
        <div class="panel">
            <div class="panel-header"><i class="bi bi-calendar3"></i> Agenda y programación</div>
            <div class="panel-body">
                <?php $calAutoInit = false; require APP_PATH . '/Views/partials/calendario.php'; ?>
            </div>
        </div>
    </div>

    <!-- TAB HISTORIAL (desktop) -->
    <div class="tab-pane fade client-tab-desktop" id="tabHistorial">
        <div class="panel">
            <div class="panel-header"><i class="bi bi-clock-history"></i> Historial de actividad</div>
            <div class="panel-body">
                <?php require APP_PATH . '/Views/partials/chatter.php'; ?>
            </div>
        </div>
    </div>

    <!-- TAB TAREAS (compartido) -->
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
                    <div class="task-item px-3 task-item-tappable
                        <?= $t['estado'] === 'completada' ? 'done' : '' ?>
                        <?= $t['estado'] === 'cancelada' ? 'opacity-50' : '' ?>
                        <?= $t['prioridad'] === 'alta' ? 'task-priority-alta' : ($t['prioridad'] === 'media' ? 'task-priority-media' : '') ?>"
                        onclick="abrirDetalleTarea(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">

                        <button class="btn-star <?= $t['destacada'] ? 'active' : '' ?>"
                                data-tarea-id="<?= (int) $t['id'] ?>"
                                onclick="event.stopPropagation(); toggleStar(this)"
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

                        <?php if ($canEdit): ?>
                        <div class="d-flex gap-1 flex-shrink-0 align-items-center" onclick="event.stopPropagation()">
                            <button type="button" class="btn btn-xs btn-ghost text-primary d-lg-none" title="Editar"
                                    onclick="abrirEditarTareaDirect(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-ghost text-primary d-none d-lg-inline-block" title="Editar"
                                    onclick="abrirEditarTarea(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <div class="dropdown d-lg-none">
                                <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="dropdown" title="Cambiar estado">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Cambiar a</h6></li>
                                    <?php foreach (['pendiente','completada','cancelada'] as $est): ?>
                                        <?php if ($est === $t['estado']) continue; ?>
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                    onclick="tareaMoverEstado(<?= (int) $t['id'] ?>, '<?= $est ?>')">
                                                <?= ucfirst($est) ?>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php if ($t['estado'] === 'pendiente'): ?>
                            <form method="post" action="<?= url('tareas/completar') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                                <input type="hidden" name="redirect" value="clientes/ver?id=<?= $cid ?>#tabTareas">
                                <button class="btn btn-xs btn-ghost text-success" title="Completar"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <?php endif; ?>
                            <form method="post" action="<?= url('tareas/eliminar') ?>" class="d-none d-lg-inline">
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

    <!-- TAB CONTACTOS (desktop) -->
    <div class="tab-pane fade client-tab-desktop" id="tabContactos">
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

    <!-- TAB VENTAS (desktop) -->
    <div class="tab-pane fade client-tab-desktop" id="tabVentas">
        <div class="panel">
            <div class="panel-header"><i class="bi bi-cart-check"></i> Ventas registradas</div>
            <?php if (empty($ventas)): ?>
                <p class="text-muted small p-3 mb-0">Sin ventas registradas.</p>
            <?php else: ?>
                <div class="panel-body p-0">
                    <?php foreach ($ventas as $v): ?>
                    <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom">
                        <div>
                            <div class="fw-semibold"><?= e($v['concepto_venta'] ?? '—') ?></div>
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

    <!-- TAB DATOS (móvil: contactos + ventas) -->
    <div class="tab-pane fade d-lg-none" id="tabDatosMob">
        <div class="panel mb-3">
            <div class="panel-header">
                <span><i class="bi bi-people"></i> Contactos</span>
                <?php if ($canEdit): ?>
                <button class="btn btn-inpro btn-sm" data-bs-toggle="modal" data-bs-target="#modalContacto">
                    <i class="bi bi-plus-lg"></i>
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
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate">
                                <?= e($ct['nombre']) ?>
                                <?php if (!empty($ct['es_principal'])): ?>
                                    <span class="badge bg-inpro rounded-pill ms-1" style="font-size:.65rem">Principal</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($ct['cargo']): ?><div class="small text-muted"><?= e($ct['cargo']) ?></div><?php endif; ?>
                            <div class="small text-muted mt-1">
                                <?php if ($ct['telefono']): ?>
                                    <a href="tel:<?= e(preg_replace('/\s+/', '', $ct['telefono'])) ?>" class="text-muted me-2"><i class="bi bi-telephone"></i> <?= e($ct['telefono']) ?></a>
                                <?php endif; ?>
                                <?php if ($ct['email']): ?>
                                    <a href="mailto:<?= e($ct['email']) ?>" class="text-muted"><i class="bi bi-envelope"></i> <?= e($ct['email']) ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header"><i class="bi bi-cart-check"></i> Ventas</div>
            <?php if (empty($ventas)): ?>
                <p class="text-muted small p-3 mb-0">Sin ventas registradas.</p>
            <?php else: ?>
                <div class="panel-body p-0">
                    <?php foreach ($ventas as $v): ?>
                    <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom gap-2">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate"><?= e($v['concepto_venta'] ?? '—') ?></div>
                            <div class="small text-muted"><?= e(date('d/m/Y', strtotime($v['fecha_propuesta'] ?? $v['created_at']))) ?></div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <strong class="d-block"><?= number_format((float) $v['importe_final_eur'], 2, ',', '.') ?> €</strong>
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

<?php require APP_PATH . '/Views/tareas/_tarea-modals.php'; ?>

<?php if ($canEdit): ?>
<!-- Offcanvas móvil: cambiar etapa pipeline -->
<div class="offcanvas offcanvas-bottom app-filter-offcanvas d-lg-none" tabindex="-1" id="cambiarEtapaOffcanvas" aria-labelledby="cambiarEtapaOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="cambiarEtapaOffcanvasLabel">Cambiar etapa comercial</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body pt-0">
        <p class="small text-muted mb-3">Etapa actual: <strong><?= e($cliente['estado_nombre'] ?? '—') ?></strong></p>
        <div class="list-group list-group-flush">
            <?php foreach ($estadosPipeline ?? [] as $ep): ?>
                <?php if ((int) $ep['id'] === $estadoActualId) continue; ?>
                <button type="button"
                        class="list-group-item list-group-item-action d-flex align-items-center gap-2 cliente-etapa-btn"
                        data-estado-id="<?= (int) $ep['id'] ?>"
                        data-estado-nombre="<?= e($ep['nombre']) ?>">
                    <span class="rounded-circle d-inline-block flex-shrink-0" style="width:10px;height:10px;background:<?= e($ep['color_hex'] ?? '#6c757d') ?>"></span>
                    <?= e($ep['nombre']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal desktop: cambiar etapa pipeline -->
<div class="modal fade" id="cambiarEtapaModal" tabindex="-1" aria-labelledby="cambiarEtapaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cambiarEtapaModalLabel">Cambiar etapa comercial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="small text-muted mb-3">Etapa actual: <strong><?= e($cliente['estado_nombre'] ?? '—') ?></strong></p>
                <div class="list-group list-group-flush">
                    <?php foreach ($estadosPipeline ?? [] as $ep): ?>
                        <?php if ((int) $ep['id'] === $estadoActualId) continue; ?>
                        <button type="button"
                                class="list-group-item list-group-item-action d-flex align-items-center gap-2 cliente-etapa-btn"
                                data-estado-id="<?= (int) $ep['id'] ?>"
                                data-estado-nombre="<?= e($ep['nombre']) ?>">
                            <span class="rounded-circle d-inline-block flex-shrink-0" style="width:10px;height:10px;background:<?= e($ep['color_hex'] ?? '#6c757d') ?>"></span>
                            <?= e($ep['nombre']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── MODALES ── -->

<!-- Modal Nuevo Contacto -->
<div class="modal fade" id="modalContacto" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
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
                <p class="small text-muted mb-0 mt-2">Si marcas contacto principal, indica al menos email o teléfono.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro"><i class="bi bi-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Transferir a INPRO -->
<div class="modal fade" id="modalTransferirInpro" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <form method="post" action="<?= url('clientes/transferir-inpro') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right text-warning"></i> Transferir gestión a INPRO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    La empresa colaboradora <strong><?= e($cliente['empresa_colaboradora_nombre'] ?? '') ?></strong>
                    pasará a <strong>solo lectura</strong>. INPRO asumirá la gestión activa.
                </p>
                <div class="mb-2">
                    <label class="form-label">Responsable INPRO</label>
                    <select name="responsable_inpro_id" class="form-select" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($usuariosInpro as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" <?= (int) ($cliente['responsable_inpro_id'] ?? 0) === (int) $u['id'] ? 'selected' : '' ?>>
                                <?= e($u['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Motivo</label>
                    <input type="text" name="motivo" class="form-control" value="Empresa cede gestión a INPRO">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning"><i class="bi bi-arrow-left-right"></i> Transferir a INPRO</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Transferir a empresa colaboradora -->
<div class="modal fade" id="modalTransferirEmpresa" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <form method="post" action="<?= url('clientes/transferir-empresa') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right text-warning"></i> Transferir gestión a empresa colaboradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    INPRO cede la gestión activa a
                    <strong><?= e($cliente['empresa_colaboradora_nombre'] ?? 'la empresa colaboradora') ?></strong>.
                    La empresa recuperará permiso de <strong>edición</strong>.
                </p>
                <div class="mb-2">
                    <label class="form-label">Responsable empresa colaboradora</label>
                    <select name="responsable_empresa_id" class="form-select" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($usuariosEmpresa as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" <?= (int) ($cliente['responsable_empresa_id'] ?? 0) === (int) $u['id'] ? 'selected' : '' ?>>
                                <?= e($u['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Motivo</label>
                    <input type="text" name="motivo" class="form-control" value="INPRO cede gestión a empresa colaboradora">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning"><i class="bi bi-arrow-left-right"></i> Transferir a empresa</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Visita -->
<div class="modal fade" id="modalVisita" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
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
    <div class="modal-dialog modal-fullscreen-sm-down">
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
    <div class="modal-dialog modal-fullscreen-sm-down">
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
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
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
                            $hayEmailRecordatorio = false;
                            foreach ($contactos as $ct):
                                if (empty($ct['email'])) {
                                    continue;
                                }
                                $hayEmailRecordatorio = true;
                                $em = $ct['email'];
                                $label = !empty($ct['es_principal'])
                                    ? 'Contacto principal — ' . $em
                                    : ($ct['nombre'] ?? 'Contacto') . ' — ' . $em;
                            ?>
                                <option value="<?= e($em) ?>"><?= e($label) ?></option>
                            <?php endforeach;
                            if (!$hayEmailRecordatorio): ?>
                                <option value="">Sin emails disponibles</option>
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
    <div class="modal-dialog modal-fullscreen-sm-down">
        <form method="post" action="<?= url('ventas/guardar') ?>" class="modal-content" id="formVenta">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" value="<?= $cid ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cart-plus-fill text-success"></i> Registrar venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($tarifaCliente)): ?>
                    <p class="small text-muted mb-2">
                        Tarifa aplicada: <strong><?= e($tarifaCliente['nombre']) ?></strong>
                    </p>
                <?php else: ?>
                    <div class="alert alert-warning py-2 small">No hay tarifa configurada para este cliente.</div>
                <?php endif; ?>
                <label class="form-label">Nº de obras *</label>
                <input type="number" name="num_obras" id="ventaNumObras" class="form-control mb-2"
                       min="1" required placeholder="Ej. 8" <?= empty($tarifaCliente) ? 'disabled' : '' ?>>
                <div id="ventaPreview" class="alert alert-light border small d-none mb-2"></div>
                <label class="form-label">Descuento adicional % (opcional)</label>
                <input type="number" name="descuento_pct" class="form-control" min="0" max="100" step="0.01" value="0"
                       <?= empty($tarifaCliente) ? 'disabled' : '' ?>>
                <small class="text-muted mt-2 d-block">El precio se calcula por tramos de volumen. Quedará pendiente de validación INPRO.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" <?= empty($tarifaCliente) ? 'disabled' : '' ?>>
                    <i class="bi bi-cart-plus-fill"></i> Registrar
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('ventaNumObras');
    var preview = document.getElementById('ventaPreview');
    if (!input || !preview) return;

    function actualizarPreview() {
        var n = parseInt(input.value, 10);
        if (!n || n < 1) {
            preview.classList.add('d-none');
            return;
        }
        fetch('<?= url('api/tarifas/preview') ?>?cliente_id=<?= $cid ?>&num_obras=' + n)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    preview.className = 'alert alert-warning border small mb-2';
                    preview.textContent = data.error || 'Sin tramo';
                    preview.classList.remove('d-none');
                    return;
                }
                preview.className = 'alert alert-success border small mb-2';
                preview.innerHTML = '<strong>' + data.etiqueta + '</strong><br>Anual: '
                    + Number(data.importe_anual_eur).toLocaleString('es-ES', {minimumFractionDigits: 2}) + ' €';
                preview.classList.remove('d-none');
            });
    }
    input.addEventListener('input', actualizarPreview);
});
</script>

<!-- Modal Tarea -->
<div class="modal fade" id="modalTarea" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
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
    <div class="modal-dialog modal-fullscreen-sm-down">
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
    var mobileMq = window.matchMedia('(max-width: 991.98px)');
    var tabAgenda = document.getElementById('tabAgenda');
    var tabActividadMob = document.getElementById('tabActividadMob');
    var btnTabAgenda = document.getElementById('btnTabAgenda');

    // Inicializa el calendario de Agenda. El ResizeObserver interno se encarga
    // de reajustar el tamaño cuando el panel obtiene ancho real (fin del fade),
    // así que aquí basta con construirlo una vez y pedir un updateSize.
    function ensureAgendaCalendar() {
        if (!tabAgenda || !tabAgenda.classList.contains('show')) return;
        var calEl = tabAgenda.querySelector('.inpro-calendario');
        if (!calEl) return;
        if (typeof initInproCalendario === 'function') {
            initInproCalendario(calEl);
        }
    }

    // Viewport móvil: la pestaña Agenda (desktop) empieza activa en el HTML,
    // pero en móvil hay que ocultarla y mostrar tabActividadMob
    if (mobileMq.matches) {
        if (tabAgenda) {
            tabAgenda.classList.remove('show', 'active');
        }
        if (btnTabAgenda) {
            btnTabAgenda.classList.remove('active');
        }
        if (tabActividadMob) {
            tabActividadMob.classList.add('show', 'active');
        }
        var mobBtn = document.querySelector('#clientTabsMobile .nav-link');
        if (mobBtn) mobBtn.classList.add('active');
    }

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

    // Navegación por hash (redirect desde crear tarea, visita, etc.)
    var hash = location.hash;
    if (hash && mobileMq.matches) {
        // En móvil, remapear las pestañas desktop a las móviles equivalentes
        var mobileMap = {
            '#tabAgenda':   '#tabActividadMob',
            '#tabHistorial':'#tabActividadMob',
            '#tabContactos':'#tabDatosMob',
            '#tabVentas':   '#tabDatosMob'
        };
        hash = mobileMap[hash] || hash;
    }
    if (hash && hash !== '#tabAgenda') {
        // Cambiar a la pestaña del hash; Agenda pierde show/active correctamente
        var hashBtn = document.querySelector('[data-bs-target="' + hash + '"]');
        if (hashBtn) bootstrap.Tab.getOrCreateInstance(hashBtn).show();
    }
    // Si no hay hash (o hash = #tabAgenda), tabAgenda ya tiene show active en el HTML → init directo
    ensureAgendaCalendar();

    // Al volver a Agenda desde otra pestaña
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            if (e.currentTarget.getAttribute('data-bs-target') === '#tabAgenda') {
                ensureAgendaCalendar();
            }
        });
    });

    mobileMq.addEventListener('change', function () {
        ensureAgendaCalendar();
    });

    // Cambiar etapa pipeline (ficha cliente)
    document.querySelectorAll('.cliente-etapa-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var estadoId = this.dataset.estadoId;
            var nombre   = this.dataset.estadoNombre;
            var offcanvasEl = document.getElementById('cambiarEtapaOffcanvas');
            var modalEl = document.getElementById('cambiarEtapaModal');
            if (offcanvasEl) {
                var oc = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (oc) oc.hide();
            }
            if (modalEl) {
                var md = bootstrap.Modal.getInstance(modalEl);
                if (md) md.hide();
            }
            fetch('<?= url('pipeline/mover') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: '_csrf=' + encodeURIComponent(getCsrfToken())
                    + '&cliente_id=<?= $cid ?>'
                    + '&estado_id=' + estadoId
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    showToast('Etapa actualizada: ' + nombre, 'success');
                    location.reload();
                } else {
                    showToast(data.error || 'No se pudo cambiar la etapa', 'error');
                }
            });
        });
    });
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

</script>
