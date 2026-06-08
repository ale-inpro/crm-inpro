<?php
$cols = [
    'pendiente'  => ['label' => 'Pendiente',   'color' => '#1a7f4b', 'bg' => '#e8f5ee', 'icon' => 'bi-hourglass-split'],
    'completada' => ['label' => 'Completada',  'color' => '#198754', 'bg' => '#d1e7dd', 'icon' => 'bi-check-circle-fill'],
    'cancelada'  => ['label' => 'Cancelada',   'color' => '#6c757d', 'bg' => '#e9ecef', 'icon' => 'bi-x-circle-fill'],
];
$hoy = date('Y-m-d');
?>

<div class="kanban-board" id="tareas-kanban">
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

<!-- Modal Detalle Tarea -->
<div class="modal fade" id="modalDetalleTarea" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header tarea-modal-header">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <button class="btn-star" id="detalle-star-btn" onclick="toggleStarModal()">★</button>
                    <h5 class="modal-title mb-0" id="detalle-titulo">—</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalle-body">
                <div class="tarea-modal-field">
                    <label>Cliente</label>
                    <span class="value" id="detalle-cliente">—</span>
                </div>
                <div class="tarea-modal-field">
                    <label>Descripción</label>
                    <span class="value text-muted" id="detalle-desc">—</span>
                </div>
                <div class="row g-3 mb-2">
                    <div class="col-sm-4">
                        <div class="tarea-modal-field flex-column gap-1">
                            <label>Vencimiento</label>
                            <span class="value fw-semibold" id="detalle-fecha">—</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="tarea-modal-field flex-column gap-1">
                            <label>Prioridad</label>
                            <span class="value" id="detalle-prioridad">—</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="tarea-modal-field flex-column gap-1">
                            <label>Asignado a</label>
                            <span class="value" id="detalle-asignado">—</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex gap-2">
                    <!-- Acciones de estado -->
                    <form method="post" action="<?= url('tareas/completar') ?>" id="formCompletarDetalle">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" id="detalle-completar-id">
                        <input type="hidden" name="redirect" value="tareas?vista=pipeline">
                        <button type="submit" class="btn btn-success btn-sm" id="btnCompletarDetalle">
                            <i class="bi bi-check-circle-fill"></i> Completar
                        </button>
                    </form>
                    <form method="post" action="<?= url('tareas/cancelar') ?>" id="formCancelarDetalle">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" id="detalle-cancelar-id">
                        <input type="hidden" name="redirect" value="tareas?vista=pipeline">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    </form>
                    <form method="post" action="<?= url('tareas/eliminar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" id="detalle-eliminar-id">
                        <input type="hidden" name="redirect" value="tareas?vista=pipeline">
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                data-confirm="¿Eliminar esta tarea? Esta acción no se puede deshacer.">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" id="btnAbrirEditar">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Tarea (desde Kanban) -->
<div class="modal fade" id="modalEditarTareaKanban" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= url('tareas/actualizar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="tarea_id" id="editKanbanId">
            <input type="hidden" name="redirect" value="tareas?vista=pipeline">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Título *</label>
                    <input type="text" name="titulo" id="editKanbanTitulo" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="editKanbanDesc" class="form-control" rows="3"></textarea>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label">Fecha vencimiento</label>
                        <input type="date" name="fecha_vencimiento" id="editKanbanFecha" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" id="editKanbanPrioridad" class="form-select">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Asignado a</label>
                    <select name="asignado_a_id" id="editKanbanAsignado" class="form-select">
                        <?php foreach ($usuariosAsignables ?? [] as $u): ?>
                            <option value="<?= (int) $u['id'] ?>"><?= e($u['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-inpro"><i class="bi bi-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tareaActiva = null;

    // Drag & drop con SortableJS
    document.querySelectorAll('.kanban-col-body').forEach(function (col) {
        if (typeof Sortable === 'undefined') return;
        Sortable.create(col, {
            group: 'tareas-kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function (evt) {
                var tareaId = evt.item.dataset.tareaId;
                var nuevoEstado = evt.to.dataset.estado;
                var csrfToken = getCsrfToken();
                fetch('<?= url('tareas/mover') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '_csrf=' + encodeURIComponent(csrfToken) + '&tarea_id=' + tareaId + '&estado=' + nuevoEstado
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

    function actualizarBadges() {
        document.querySelectorAll('.kanban-col-body').forEach(function (col) {
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

    // Modal detalle tarea
    window.abrirDetalleTarea = function (t) {
        tareaActiva = t;
        document.getElementById('detalle-titulo').textContent   = t.titulo || '—';
        document.getElementById('detalle-cliente').textContent  = t.razon_social || '—';
        document.getElementById('detalle-desc').textContent     = t.descripcion || 'Sin descripción';
        document.getElementById('detalle-asignado').textContent = t.asignado_nombre || '—';

        var fecha = t.fecha_vencimiento ? new Date(t.fecha_vencimiento + 'T00:00:00').toLocaleDateString('es-ES') : '—';
        document.getElementById('detalle-fecha').textContent = fecha;

        var prioBadge = '<span class="prio-badge ' + (t.prioridad||'') + '">' + (t.prioridad||'—') + '</span>';
        document.getElementById('detalle-prioridad').innerHTML = prioBadge;

        // Estrella
        var starBtn = document.getElementById('detalle-star-btn');
        starBtn.classList.toggle('active', !!parseInt(t.destacada));
        starBtn.title = parseInt(t.destacada) ? 'Quitar destacado' : 'Destacar';
        starBtn.dataset.tareaId = t.id;

        // IDs en formularios
        ['completar','cancelar','eliminar'].forEach(function(accion) {
            var el = document.getElementById('detalle-' + accion + '-id');
            if (el) el.value = t.id;
        });

        // Ocultar botón completar si no está pendiente
        var btnCompletar = document.getElementById('btnCompletarDetalle');
        if (btnCompletar) btnCompletar.closest('form').style.display = t.estado === 'pendiente' ? '' : 'none';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleTarea')).show();
    };

    // Botón editar desde modal detalle (esperar cierre completo antes de abrir editar)
    document.getElementById('btnAbrirEditar').addEventListener('click', function () {
        if (!tareaActiva) return;
        var detalleEl = document.getElementById('modalDetalleTarea');
        var editEl    = document.getElementById('modalEditarTareaKanban');
        function abrirEditar() {
            detalleEl.removeEventListener('hidden.bs.modal', abrirEditar);
            document.getElementById('editKanbanId').value        = tareaActiva.id;
            document.getElementById('editKanbanTitulo').value    = tareaActiva.titulo || '';
            document.getElementById('editKanbanDesc').value      = tareaActiva.descripcion || '';
            document.getElementById('editKanbanFecha').value     = tareaActiva.fecha_vencimiento || '';
            document.getElementById('editKanbanPrioridad').value = tareaActiva.prioridad || 'media';
            document.getElementById('editKanbanAsignado').value  = tareaActiva.asignado_a_id || '';
            bootstrap.Modal.getOrCreateInstance(editEl).show();
        }
        detalleEl.addEventListener('hidden.bs.modal', abrirEditar);
        bootstrap.Modal.getOrCreateInstance(detalleEl).hide();
    });

    // Toggle estrella desde modal detalle
    window.toggleStarModal = function () {
        if (!tareaActiva) return;
        var btn = document.getElementById('detalle-star-btn');
        btn.dataset.tareaId = tareaActiva.id;
        toggleStar(btn);
        tareaActiva.destacada = parseInt(tareaActiva.destacada) ? 0 : 1;
    };
});

// Toggle estrella (global, usado también en show.php)
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
            // Actualizar card visual
            var card = btn.closest('.kanban-card');
            if (card) card.classList.toggle('destacada', data.destacada);
        }
    });
};
</script>
