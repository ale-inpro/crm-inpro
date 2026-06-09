<?php
$vista        = $vista ?? 'pipeline';
$filtroEstado = $_GET['estado'] ?? null;
$filtroCliente = $clienteId ?? null;
$hoy          = date('Y-m-d');

function vistaUrl(string $v, ?string $estado = null, ?int $clienteId = null): string {
    $q = 'tareas?vista=' . $v;
    if ($clienteId) {
        $q .= '&cliente_id=' . $clienteId;
    }
    if ($estado && $v !== 'calendario') {
        $q .= '&estado=' . urlencode($estado);
    }
    return url($q);
}
?>

<!-- ── TOOLBAR UNIFICADA ── -->
<div class="page-toolbar animate-fade-up flex-column flex-lg-row align-items-stretch align-items-lg-center">
    <div class="d-flex align-items-center justify-content-between w-100 w-lg-auto">
        <h1 class="h4 mb-0 me-2 mobile-hide-heading">Tareas</h1>
        <div class="d-lg-none">
            <div class="btn-group btn-group-sm">
                <a href="<?= vistaUrl('lista', $filtroEstado, $filtroCliente) ?>"
                   class="btn <?= $vista === 'lista' ? 'btn-inpro' : 'btn-outline-secondary' ?>"
                   title="Lista"><i class="bi bi-list-ul"></i></a>
                <a href="<?= vistaUrl('calendario', null, $filtroCliente) ?>"
                   class="btn <?= $vista === 'calendario' ? 'btn-inpro' : 'btn-outline-secondary' ?>"
                   title="Calendario"><i class="bi bi-calendar3"></i></a>
            </div>
        </div>
    </div>

    <!-- Centro: filtros -->
    <div class="page-toolbar-center d-flex align-items-center gap-2 flex-wrap justify-content-center w-100">
        <?php if (!empty($clientes)): ?>
        <select class="form-select form-select-sm" style="max-width:220px" id="filtroClienteTareas">
            <option value="">Todos los clientes</option>
            <?php foreach ($clientes as $cl): ?>
                <option value="<?= (int) $cl['id'] ?>" <?= $filtroCliente === (int) $cl['id'] ? 'selected' : '' ?>>
                    <?= e($cl['razon_social']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <?php if ($vista === 'lista'): ?>
        <div class="d-lg-none filter-chips-scroll w-100">
            <div class="filter-chips">
                <a href="<?= vistaUrl($vista, null, $filtroCliente) ?>"
                   class="filter-chip <?= !$filtroEstado ? 'active' : '' ?>">Todas</a>
                <a href="<?= vistaUrl($vista, 'pendiente', $filtroCliente) ?>"
                   class="filter-chip <?= $filtroEstado === 'pendiente' ? 'active' : '' ?>">Pendientes</a>
                <a href="<?= vistaUrl($vista, 'completada', $filtroCliente) ?>"
                   class="filter-chip <?= $filtroEstado === 'completada' ? 'active' : '' ?>">Completadas</a>
                <a href="<?= vistaUrl($vista, 'cancelada', $filtroCliente) ?>"
                   class="filter-chip <?= $filtroEstado === 'cancelada' ? 'active' : '' ?>">Canceladas</a>
            </div>
        </div>
        <div class="btn-group btn-group-sm d-none d-lg-flex">
            <a href="<?= vistaUrl($vista, null, $filtroCliente) ?>"
               class="btn <?= !$filtroEstado ? 'btn-inpro' : 'btn-outline-secondary' ?>">Todas</a>
            <a href="<?= vistaUrl($vista, 'pendiente', $filtroCliente) ?>"
               class="btn <?= $filtroEstado === 'pendiente' ? 'btn-inpro' : 'btn-outline-secondary' ?>">Pendientes</a>
            <a href="<?= vistaUrl($vista, 'completada', $filtroCliente) ?>"
               class="btn <?= $filtroEstado === 'completada' ? 'btn-inpro' : 'btn-outline-secondary' ?>">Completadas</a>
            <a href="<?= vistaUrl($vista, 'cancelada', $filtroCliente) ?>"
               class="btn <?= $filtroEstado === 'cancelada' ? 'btn-inpro' : 'btn-outline-secondary' ?>">Canceladas</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Derecha: selector de vista (desktop) -->
    <div class="page-toolbar-right d-none d-lg-block">
        <div class="btn-group btn-group-sm">
            <a href="<?= vistaUrl('lista', $filtroEstado, $filtroCliente) ?>"
               class="btn <?= $vista === 'lista' ? 'btn-inpro' : 'btn-outline-secondary' ?>"
               title="Vista lista">
                <i class="bi bi-list-ul"></i>
            </a>
            <a href="<?= vistaUrl('pipeline', $filtroEstado, $filtroCliente) ?>"
               class="btn <?= $vista === 'pipeline' ? 'btn-inpro' : 'btn-outline-secondary' ?>"
               title="Vista Kanban">
                <i class="bi bi-kanban"></i>
            </a>
            <a href="<?= vistaUrl('calendario', null, $filtroCliente) ?>"
               class="btn <?= $vista === 'calendario' ? 'btn-inpro' : 'btn-outline-secondary' ?>"
               title="Vista Calendario">
                <i class="bi bi-calendar3"></i>
            </a>
        </div>
    </div>
</div>

<?php if ($vista === 'lista'): ?>
<?php
$listaRedirect = 'tareas?vista=lista'
    . ($filtroEstado ? '&estado=' . urlencode($filtroEstado) : '')
    . ($filtroCliente ? '&cliente_id=' . $filtroCliente : '');
$tareasRedirect = $listaRedirect;
?>
<!-- ── VISTA LISTA ── -->
<div class="panel animate-fade-up">
    <div class="panel-header">
        <span><i class="bi bi-list-ul me-1"></i>
            <?php if ($filtroEstado): ?>
                Tareas <span class="text-capitalize"><?= e($filtroEstado) ?>s</span>
            <?php else: ?>
                Todas las tareas
            <?php endif; ?>
        </span>
        <span class="badge bg-secondary rounded-pill"><?= count($tareas) ?></span>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($tareas)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check2-all" style="font-size:2rem;opacity:.3"></i>
                <p class="mt-2 mb-0 small">No hay tareas<?= $filtroEstado ? ' con este filtro' : '' ?>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tareas as $t):
                $vencida = $t['fecha_vencimiento'] < $hoy && $t['estado'] === 'pendiente';
            ?>
            <div class="task-item px-3 task-item-tappable
                <?= $t['estado'] === 'completada' ? 'done' : '' ?>
                <?= $t['estado'] === 'cancelada'  ? 'opacity-50' : '' ?>
                <?= $t['prioridad'] === 'alta'  ? 'task-priority-alta'  : '' ?>
                <?= $t['prioridad'] === 'media' ? 'task-priority-media' : '' ?>"
                onclick="abrirDetalleTarea(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">

                <!-- Estrella -->
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
                        <span class="small text-muted"><i class="bi bi-building"></i> <?= e($t['razon_social']) ?></span>
                        <span class="prio-badge <?= e($t['prioridad']) ?>"><?= e($t['prioridad']) ?></span>
                        <span class="small <?= $vencida ? 'text-danger fw-semibold' : 'text-muted' ?>">
                            <i class="bi bi-calendar2"></i> <?= date('d/m/Y', strtotime($t['fecha_vencimiento'])) ?>
                            <?= $vencida ? ' · vencida' : '' ?>
                        </span>
                        <span class="small text-muted"><i class="bi bi-person"></i> <?= e($t['asignado_nombre']) ?></span>
                        <?php if ($t['estado'] !== 'pendiente'): ?>
                            <span class="badge rounded-pill bg-<?= $t['estado'] === 'completada' ? 'success' : 'secondary' ?>" style="font-size:.68rem">
                                <?= e($t['estado']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="d-flex gap-1 flex-shrink-0 align-items-center" onclick="event.stopPropagation()">
                    <button type="button" class="btn btn-xs btn-ghost text-primary" title="Editar"
                            onclick="abrirEditarTareaDirect(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <div class="dropdown">
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
                    <form method="post" action="<?= url('tareas/completar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                        <input type="hidden" name="redirect" value="<?= e($listaRedirect) ?>">
                        <button class="btn btn-xs btn-ghost text-success" title="Completar"><i class="bi bi-check-lg"></i></button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require APP_PATH . '/Views/tareas/_tarea-modals.php'; ?>

<?php elseif ($vista === 'pipeline'): ?>
<script>
if (window.matchMedia('(max-width: 991.98px)').matches) {
    location.replace('<?= vistaUrl('lista', $filtroEstado, $filtroCliente) ?>');
}
</script>
<!-- ── VISTA KANBAN (solo desktop) ── -->
<?php require APP_PATH . '/Views/tareas/pipeline.php'; ?>

<?php elseif ($vista === 'calendario'): ?>
<!-- ── VISTA CALENDARIO ── -->
<div class="panel animate-fade-up">
    <div class="panel-header"><i class="bi bi-calendar3 me-1"></i> Calendario de tareas y visitas</div>
    <div class="panel-body">
        <?php require APP_PATH . '/Views/partials/calendario.php'; ?>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('filtroClienteTareas');
    if (!sel) return;
    sel.addEventListener('change', function () {
        var u = '<?= url('tareas?vista=' . e($vista)) ?>';
        if (sel.value) u += '&cliente_id=' + sel.value;
        <?php if ($filtroEstado): ?>u += '&estado=<?= urlencode($filtroEstado) ?>';<?php endif; ?>
        location.href = u;
    });
});
</script>

