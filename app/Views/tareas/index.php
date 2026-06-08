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
<div class="page-toolbar animate-fade-up">
    <!-- Izquierda: título -->
    <h1 class="h4 mb-0 me-2">Tareas</h1>

    <!-- Centro: filtros -->
    <div class="page-toolbar-center d-flex align-items-center gap-2 flex-wrap justify-content-center">
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
        <div class="btn-group btn-group-sm">
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

    <!-- Derecha: selector de vista -->
    <div class="page-toolbar-right">
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
            <div class="task-item px-3
                <?= $t['estado'] === 'completada' ? 'done' : '' ?>
                <?= $t['estado'] === 'cancelada'  ? 'opacity-50' : '' ?>
                <?= $t['prioridad'] === 'alta'  ? 'task-priority-alta'  : '' ?>
                <?= $t['prioridad'] === 'media' ? 'task-priority-media' : '' ?>">

                <!-- Estrella -->
                <button class="btn-star <?= $t['destacada'] ? 'active' : '' ?>"
                        data-tarea-id="<?= (int) $t['id'] ?>"
                        onclick="toggleStar(this)"
                        title="<?= $t['destacada'] ? 'Quitar destacado' : 'Destacar' ?>">★</button>

                <div class="flex-grow-1 min-w-0">
                    <a href="<?= url('clientes/ver?id=' . (int) $t['cliente_id']) ?>"
                       class="fw-semibold text-decoration-none text-dark text-truncate d-block">
                        <?= e($t['titulo']) ?>
                    </a>
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
                <?php if ($t['estado'] === 'pendiente'): ?>
                <div class="d-flex gap-1 flex-shrink-0">
                    <form method="post" action="<?= url('tareas/completar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                        <input type="hidden" name="redirect" value="tareas?vista=lista<?= $filtroEstado ? '&estado=' . urlencode($filtroEstado) : '' ?>">
                        <button class="btn btn-xs btn-ghost text-success" title="Completar"><i class="bi bi-check-lg"></i></button>
                    </form>
                    <form method="post" action="<?= url('tareas/cancelar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                        <input type="hidden" name="redirect" value="tareas?vista=lista<?= $filtroEstado ? '&estado=' . urlencode($filtroEstado) : '' ?>">
                        <button class="btn btn-xs btn-ghost text-secondary" title="Cancelar"><i class="bi bi-x-lg"></i></button>
                    </form>
                </div>
                <?php elseif ($t['estado'] === 'completada'): ?>
                    <span class="badge bg-success rounded-pill ms-2">Hecha</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php elseif ($vista === 'pipeline'): ?>
<!-- ── VISTA KANBAN ── -->
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

<?php if ($vista !== 'pipeline'): ?>
<!-- toggleStar para lista/calendario -->
<script>
window.toggleStar = function (btn) {
    var tareaId = btn.dataset.tareaId;
    var csrfToken = getCsrfToken();
    fetch('<?= url('tareas/destacar') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(csrfToken) + '&tarea_id=' + tareaId
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) {
            btn.classList.toggle('active', data.destacada);
            btn.title = data.destacada ? 'Quitar destacado' : 'Destacar';
        }
    });
};
</script>
<?php endif; ?>
