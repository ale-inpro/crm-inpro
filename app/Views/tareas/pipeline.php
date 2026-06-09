<?php
$cols = [
    'pendiente'  => ['label' => 'Pendiente',   'color' => '#1a7f4b', 'bg' => '#e8f5ee', 'icon' => 'bi-hourglass-split'],
    'completada' => ['label' => 'Completada',  'color' => '#198754', 'bg' => '#d1e7dd', 'icon' => 'bi-check-circle-fill'],
    'cancelada'  => ['label' => 'Cancelada',   'color' => '#6c757d', 'bg' => '#e9ecef', 'icon' => 'bi-x-circle-fill'],
];

$hoy = date('Y-m-d');
$tareasRedirect = 'tareas?vista=pipeline';

?>

<!-- Móvil: lista agrupada por estado -->
<div class="d-lg-none mobile-tareas-groups mb-3">
<?php foreach ($cols as $colEstado => $col):
    $items = $columnas[$colEstado] ?? [];
?>
    <div class="panel mb-2">
        <div class="panel-header py-2" style="background:<?= $col['bg'] ?>;color:<?= $col['color'] ?>;">
            <i class="bi <?= $col['icon'] ?>"></i> <?= $col['label'] ?>
            <span class="badge rounded-pill ms-auto" style="background:<?= $col['color'] ?>"><?= count($items) ?></span>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($items)): ?>
                <p class="text-muted small p-3 mb-0">Sin tareas</p>
            <?php else: ?>
                <?php foreach ($items as $t):
                    $vencida = $t['fecha_vencimiento'] < $hoy && $t['estado'] === 'pendiente';
                ?>
                <div class="task-item px-3 prio-<?= e($t['prioridad']) ?> <?= $t['destacada'] ? 'destacada' : '' ?>"
                     onclick="abrirDetalleTarea(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">
                    <button class="btn-star <?= $t['destacada'] ? 'active' : '' ?>"
                            data-tarea-id="<?= (int) $t['id'] ?>"
                            onclick="event.stopPropagation(); toggleStar(this)">★</button>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate"><?= e($t['titulo']) ?></div>
                        <div class="small text-muted">
                            <?= e($t['razon_social']) ?> · <?= date('d/m/Y', strtotime($t['fecha_vencimiento'])) ?>
                            <?php if ($vencida): ?> · <span class="text-danger">vencida</span><?php endif; ?>
                        </div>
                    </div>
                    <div class="dropdown flex-shrink-0" onclick="event.stopPropagation()">
                        <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="dropdown" title="Cambiar estado">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Cambiar a</h6></li>
                            <?php foreach (['pendiente','completada','cancelada'] as $est): ?>
                                <?php if ($est === $colEstado) continue; ?>
                                <li>
                                    <button type="button" class="dropdown-item"
                                            onclick="tareaMoverEstado(<?= (int) $t['id'] ?>, '<?= $est ?>')">
                                        <?= ucfirst($est) ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div class="kanban-board d-none d-lg-flex" id="tareas-kanban">
<?php foreach ($cols as $colEstado => $col):
    $items = $columnas[$colEstado] ?? [];
?>
    <div class="kanban-col-wrap">
        <div class="kanban-col-header" style="background:<?= $col['bg'] ?>;color:<?= $col['color'] ?>;">
            <i class="bi <?= $col['icon'] ?>"></i>
            <?= $col['label'] ?>
            <span class="badge rounded-pill ms-auto" style="background:<?= $col['color'] ?>;color:#fff;font-size:.72rem"><?= count($items) ?></span>
        </div>
        <div class="kanban-col-body" data-estado="<?= $colEstado ?>" id="col-<?= $colEstado ?>">
            <?php if (empty($items)): ?>
                <div class="kanban-empty">Sin tareas</div>
            <?php else: ?>
            <?php foreach ($items as $t):
                $vencida = $t['fecha_vencimiento'] < $hoy && $t['estado'] === 'pendiente';
                $inicial = strtoupper(mb_substr($t['asignado_nombre'] ?? '?', 0, 1));
            ?>
            <div class="kanban-card prio-<?= e($t['prioridad']) ?> <?= $t['destacada'] ? 'destacada' : '' ?>"
                 data-tarea-id="<?= (int) $t['id'] ?>"
                 onclick="abrirDetalleTarea(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">

                <!-- Estrella -->
                <div class="kc-star" onclick="event.stopPropagation()">
                    <button class="btn-star <?= $t['destacada'] ? 'active' : '' ?>"
                            data-tarea-id="<?= (int) $t['id'] ?>"
                            onclick="toggleStar(this)"
                            title="<?= $t['destacada'] ? 'Quitar destacado' : 'Destacar' ?>">★</button>
                </div>

                <div class="kc-title pe-4"><?= e($t['titulo']) ?></div>
                <?php if (!empty($t['descripcion'])): ?>
                    <div class="kc-desc"><?= e($t['descripcion']) ?></div>
                <?php endif; ?>
                <div class="kc-meta">
                    <span class="kc-client"><i class="bi bi-building"></i> <?= e($t['razon_social']) ?></span>
                    <span class="kc-date <?= $vencida ? 'vencida' : '' ?>">
                        <i class="bi bi-calendar2"></i> <?= date('d/m/Y', strtotime($t['fecha_vencimiento'])) ?>
                    </span>
                    <span class="kc-avatar ms-auto" title="<?= e($t['asignado_nombre']) ?>"><?= $inicial ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php require APP_PATH . '/Views/tareas/_tarea-modals.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.matchMedia('(min-width: 992px)').matches) return;

    function actualizarBadges() {
        document.querySelectorAll('#tareas-kanban .kanban-col-body').forEach(function (col) {
            var header = col.closest('.kanban-col-wrap').querySelector('.kanban-col-header .badge');
            var cards  = col.querySelectorAll('.kanban-card');
            var count  = cards.length;
            if (header) header.textContent = count;
            var empty  = col.querySelector('.kanban-empty');
            if (count === 0 && !empty) {
                col.insertAdjacentHTML('beforeend', '<div class="kanban-empty">Sin tareas</div>');
            } else if (count > 0 && empty) {
                empty.remove();
            }
        });
    }

    document.querySelectorAll('#tareas-kanban .kanban-col-body').forEach(function (col) {
        if (typeof Sortable === 'undefined') return;
        Sortable.create(col, {
            group: 'tareas-kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function (evt) {
                var tareaId = evt.item.dataset.tareaId;
                var nuevoEstado = evt.to.dataset.estado;
                fetch('<?= url('tareas/mover') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '_csrf=' + encodeURIComponent(getCsrfToken()) + '&tarea_id=' + tareaId + '&estado=' + nuevoEstado
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        showToast('Estado actualizado', 'success');
                        actualizarBadges();
                    } else {
                        evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                        actualizarBadges();
                        showToast(data.error || 'Error al mover la tarea', 'error');
                    }
                })
                .catch(function () {
                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                    actualizarBadges();
                    showToast('Error de red al mover la tarea', 'error');
                });
            }
        });
    });
});
</script>
