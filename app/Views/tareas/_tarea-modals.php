<?php
$tareasRedirect = $tareasRedirect ?? 'tareas?vista=pipeline';
$estadosTarea = ['pendiente' => 'Pendiente', 'completada' => 'Completada', 'cancelada' => 'Cancelada'];
?>

<!-- Modal Detalle Tarea -->
<div class="modal fade" id="modalDetalleTarea" tabindex="-1">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header tarea-modal-header">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <button class="btn-star" id="detalle-star-btn" onclick="toggleStarModal()">★</button>
                    <h5 class="modal-title mb-0 text-truncate" id="detalle-titulo">—</h5>
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
                <div class="mb-2" id="wrapCambiarEstado">
                    <label class="form-label small fw-semibold">Cambiar estado</label>
                    <div class="d-flex flex-wrap gap-1" id="detalle-estados-btns"></div>
                </div>
            </div>
            <div class="modal-footer flex-column flex-sm-row justify-content-between gap-2">
                <div class="d-flex flex-wrap gap-2 w-100 w-sm-auto">
                    <form method="post" action="<?= url('tareas/completar') ?>" id="formCompletarDetalle">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" id="detalle-completar-id">
                        <input type="hidden" name="redirect" value="<?= e($tareasRedirect) ?>">
                        <button type="submit" class="btn btn-success btn-sm" id="btnCompletarDetalle">
                            <i class="bi bi-check-circle-fill"></i> Completar
                        </button>
                    </form>
                    <form method="post" action="<?= url('tareas/cancelar') ?>" id="formCancelarDetalle">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" id="detalle-cancelar-id">
                        <input type="hidden" name="redirect" value="<?= e($tareasRedirect) ?>">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    </form>
                    <form method="post" action="<?= url('tareas/eliminar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tarea_id" id="detalle-eliminar-id">
                        <input type="hidden" name="redirect" value="<?= e($tareasRedirect) ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                data-confirm="¿Eliminar esta tarea? Esta acción no se puede deshacer.">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
                <div class="d-flex gap-2 w-100 w-sm-auto">
                    <button class="btn btn-outline-primary btn-sm" id="btnAbrirEditar">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Tarea -->
<div class="modal fade" id="modalEditarTareaKanban" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <form method="post" action="<?= url('tareas/actualizar') ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="tarea_id" id="editKanbanId">
            <input type="hidden" name="redirect" id="editKanbanRedirect" value="<?= e($tareasRedirect) ?>">
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
window.tareaMoverEstado = function (tareaId, estado) {
    fetch('<?= url('tareas/mover') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(getCsrfToken()) + '&tarea_id=' + tareaId + '&estado=' + estado
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) {
            showToast('Estado actualizado', 'success');
            location.reload();
        } else {
            showToast(data.error || 'Error al cambiar estado', 'error');
        }
    });
};

window.abrirEditarTareaDirect = function (t) {
    document.getElementById('editKanbanId').value        = t.id;
    document.getElementById('editKanbanTitulo').value    = t.titulo || '';
    document.getElementById('editKanbanDesc').value      = t.descripcion || '';
    document.getElementById('editKanbanFecha').value     = t.fecha_vencimiento || '';
    document.getElementById('editKanbanPrioridad').value = t.prioridad || 'media';
    document.getElementById('editKanbanAsignado').value  = t.asignado_a_id || '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarTareaKanban')).show();
};

document.addEventListener('DOMContentLoaded', function () {
    var tareaActiva = null;
    var estadosMap = <?= json_encode($estadosTarea, JSON_UNESCAPED_UNICODE) ?>;

    window.abrirDetalleTarea = function (t) {
        tareaActiva = t;
        document.getElementById('detalle-titulo').textContent    = t.titulo || '—';
        document.getElementById('detalle-cliente').textContent   = t.razon_social || '—';
        document.getElementById('detalle-desc').textContent      = t.descripcion || 'Sin descripción';
        document.getElementById('detalle-asignado').textContent = t.asignado_nombre || '—';
        document.getElementById('detalle-fecha').textContent = t.fecha_vencimiento
            ? new Date(t.fecha_vencimiento + 'T00:00:00').toLocaleDateString('es-ES') : '—';
        document.getElementById('detalle-prioridad').innerHTML =
            '<span class="prio-badge ' + (t.prioridad || '') + '">' + (t.prioridad || '—') + '</span>';

        var starBtn = document.getElementById('detalle-star-btn');
        starBtn.classList.toggle('active', !!parseInt(t.destacada));
        starBtn.dataset.tareaId = t.id;

        ['completar', 'cancelar', 'eliminar'].forEach(function (accion) {
            var el = document.getElementById('detalle-' + accion + '-id');
            if (el) el.value = t.id;
        });

        var btnCompletar = document.getElementById('btnCompletarDetalle');
        if (btnCompletar) btnCompletar.closest('form').style.display = t.estado === 'pendiente' ? '' : 'none';

        var wrap = document.getElementById('detalle-estados-btns');
        wrap.innerHTML = '';
        Object.keys(estadosMap).forEach(function (est) {
            if (est === t.estado) return;
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'btn btn-sm btn-outline-secondary';
            b.textContent = estadosMap[est];
            b.onclick = function () { tareaMoverEstado(t.id, est); };
            wrap.appendChild(b);
        });

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleTarea')).show();
    };

    var btnEditar = document.getElementById('btnAbrirEditar');
    if (btnEditar) {
        btnEditar.addEventListener('click', function () {
            if (!tareaActiva) return;
            var detalleEl = document.getElementById('modalDetalleTarea');
            function abrirEditar() {
                detalleEl.removeEventListener('hidden.bs.modal', abrirEditar);
                abrirEditarTareaDirect(tareaActiva);
            }
            detalleEl.addEventListener('hidden.bs.modal', abrirEditar);
            bootstrap.Modal.getOrCreateInstance(detalleEl).hide();
        });
    }

    window.toggleStarModal = function () {
        if (!tareaActiva) return;
        var btn = document.getElementById('detalle-star-btn');
        btn.dataset.tareaId = tareaActiva.id;
        toggleStar(btn);
        tareaActiva.destacada = parseInt(tareaActiva.destacada) ? 0 : 1;
    };
});

window.toggleStar = function (btn) {
    fetch('<?= url('tareas/destacar') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf=' + encodeURIComponent(getCsrfToken()) + '&tarea_id=' + btn.dataset.tareaId
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) {
            btn.classList.toggle('active', data.destacada);
            var card = btn.closest('.kanban-card, .task-item');
            if (card) card.classList.toggle('destacada', data.destacada);
        }
    });
};
</script>