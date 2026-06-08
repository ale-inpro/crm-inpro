<div class="page-toolbar animate-fade-up mb-3">
    <h1 class="h4 mb-0"><i class="bi bi-kanban me-1"></i> Pipeline comercial</h1>
    <div class="page-toolbar-right ms-auto">
        <a href="<?= url('clientes/nuevo') ?>" class="btn btn-inpro btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo cliente
        </a>
    </div>
</div>

<div class="kanban-board" id="pipeline-kanban">
<?php foreach ($columnas as $col):
    $estado  = $col['estado'];
    $items   = $col['clientes'];
    $color   = $estado['color_hex'] ?? '#6c757d';
?>
    <div class="kanban-col-wrap">
        <div class="kanban-col-header" style="background:<?= e($color) ?>;color:#fff;">
            <i class="bi bi-circle-fill" style="font-size:.5rem;opacity:.85"></i>
            <?= e($estado['nombre']) ?>
            <span class="badge rounded-pill ms-auto" style="background:rgba(255,255,255,.25);color:#fff;font-size:.72rem">
                <?= count($items) ?>
            </span>
        </div>
        <div class="kanban-col-body" data-estado-id="<?= (int) $estado['id'] ?>">
            <?php if (empty($items)): ?>
                <div class="kanban-empty">Sin clientes</div>
            <?php else: ?>
            <?php foreach ($items as $c): ?>
            <div class="kanban-card" data-cliente-id="<?= (int) $c['id'] ?>">
                <a href="<?= url('clientes/ver?id=' . $c['id']) ?>"
                   class="kc-title text-decoration-none text-dark d-block pe-2"
                   onclick="event.stopPropagation()">
                    <?= e($c['razon_social']) ?>
                </a>
                <div class="kc-meta mt-1">
                    <?php if ($c['ciudad']): ?>
                        <span class="kc-client"><i class="bi bi-geo-alt"></i> <?= e($c['ciudad']) ?></span>
                    <?php endif; ?>
                    <?php if (!$c['primera_visita_realizada']): ?>
                        <span class="badge badge-primera-visita" style="font-size:.68rem">Sin 1ª visita</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function actualizarBadgesPipeline() {
        document.querySelectorAll('#pipeline-kanban .kanban-col-body').forEach(function (col) {
            var header = col.closest('.kanban-col-wrap').querySelector('.kanban-col-header .badge');
            var cards  = col.querySelectorAll('.kanban-card');
            var count  = cards.length;
            if (header) header.textContent = count;
            var empty  = col.querySelector('.kanban-empty');
            if (count === 0 && !empty) {
                col.insertAdjacentHTML('beforeend', '<div class="kanban-empty">Sin clientes</div>');
            } else if (count > 0 && empty) {
                empty.remove();
            }
        });
    }

    document.querySelectorAll('#pipeline-kanban .kanban-col-body').forEach(function (col) {
        if (typeof Sortable === 'undefined') return;
        Sortable.create(col, {
            group: 'pipeline-kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            draggable: '.kanban-card',
            onEnd: function (evt) {
                if (evt.from === evt.to) return;
                var clienteId = evt.item.dataset.clienteId;
                var estadoId  = evt.to.dataset.estadoId;
                if (!clienteId || !estadoId) return;

                fetch('<?= url('pipeline/mover') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '_csrf=' + encodeURIComponent(getCsrfToken())
                        + '&cliente_id=' + clienteId
                        + '&estado_id=' + estadoId
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        showToast('Cliente movido de etapa', 'success');
                        actualizarBadgesPipeline();
                    } else {
                        evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                        actualizarBadgesPipeline();
                        showToast(data.error || 'No se pudo mover el cliente', 'error');
                    }
                })
                .catch(function () {
                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                    actualizarBadgesPipeline();
                    showToast('Error de red al mover el cliente', 'error');
                });
            }
        });
    });
});
</script>
