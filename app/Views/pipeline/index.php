<div class="d-lg-none alert alert-light border small py-2 mb-3 mb-lg-0">
    <i class="bi bi-lightbulb text-warning"></i>
    En móvil puedes filtrar clientes por etapa desde <a href="<?= url('clientes') ?>" class="alert-link">Clientes → Filtrar</a>.
</div>

<div class="page-toolbar animate-fade-up mb-3">
    <h1 class="h4 mb-0 mobile-hide-heading"><i class="bi bi-kanban me-1"></i> Pipeline comercial</h1>
    <div class="page-toolbar-right ms-auto">
        <a href="<?= url('clientes/nuevo') ?>" class="btn btn-inpro btn-sm">
            <i class="bi bi-plus-lg"></i><span class="d-none d-sm-inline"> Nuevo cliente</span>
        </a>
    </div>
</div>

<?php
$estadosLista = array_values($columnas);
$primerEstadoId = $estadosLista[0]['estado']['id'] ?? 0;
?>

<!-- Móvil: una etapa a la vez -->
<div class="pipeline-mobile d-lg-none mb-3">
    <div class="pipeline-stage-pills d-flex gap-2 overflow-auto pb-2">
        <?php foreach ($columnas as $col):
            $estado = $col['estado'];
            $eid = (int) $estado['id'];
        ?>
        <button type="button"
                class="btn btn-sm flex-shrink-0 pipeline-stage-btn <?= $eid === $primerEstadoId ? 'btn-inpro' : 'btn-outline-secondary' ?>"
                data-stage-id="<?= $eid ?>">
            <?= e($estado['nombre']) ?>
            <span class="badge bg-light text-dark ms-1"><?= count($col['clientes']) ?></span>
        </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($columnas as $col):
        $estado = $col['estado'];
        $eid = (int) $estado['id'];
        $items = $col['clientes'];
    ?>
    <div class="pipeline-stage-panel <?= $eid === $primerEstadoId ? '' : 'd-none' ?>" data-stage-id="<?= $eid ?>">
        <?php if (empty($items)): ?>
            <div class="text-muted text-center py-4 small">Sin clientes en esta etapa.</div>
        <?php else: ?>
            <?php foreach ($items as $c): ?>
            <div class="mobile-card pipeline-mobile-card">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <a href="<?= url('clientes/ver?id=' . $c['id']) ?>" class="text-decoration-none text-dark min-w-0">
                        <div class="mobile-card-title"><?= e($c['razon_social']) ?></div>
                        <?php if ($c['ciudad']): ?>
                            <div class="mobile-card-sub"><i class="bi bi-geo-alt"></i> <?= e($c['ciudad']) ?></div>
                        <?php endif; ?>
                        <?php if (!$c['primera_visita_realizada']): ?>
                            <span class="badge badge-primera-visita mt-1">Sin 1ª visita</span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown flex-shrink-0">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="dropdown" aria-label="Mover etapa">
                            <i class="bi bi-arrow-left-right"></i><span class="d-none d-sm-inline"> Mover</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Mover a</h6></li>
                            <?php foreach ($columnas as $dest): ?>
                                <?php if ((int) $dest['estado']['id'] === $eid) continue; ?>
                                <li>
                                    <button type="button" class="dropdown-item pipeline-mover-btn"
                                            data-cliente-id="<?= (int) $c['id'] ?>"
                                            data-estado-id="<?= (int) $dest['estado']['id'] ?>">
                                        <?= e($dest['estado']['nombre']) ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Desktop: kanban -->
<div class="kanban-board d-none d-lg-flex" id="pipeline-kanban">
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
                <div class="dropdown mt-2">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="dropdown" onclick="event.stopPropagation()">
                        <i class="bi bi-arrow-left-right"></i> Mover etapa
                    </button>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Mover a</h6></li>
                        <?php foreach ($columnas as $dest): ?>
                            <?php if ((int) $dest['estado']['id'] === (int) $estado['id']) continue; ?>
                            <li>
                                <button type="button" class="dropdown-item pipeline-mover-btn"
                                        data-cliente-id="<?= (int) $c['id'] ?>"
                                        data-estado-id="<?= (int) $dest['estado']['id'] ?>">
                                    <?= e($dest['estado']['nombre']) ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
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
    // Móvil: cambiar etapa
    document.querySelectorAll('.pipeline-stage-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.stageId;
            document.querySelectorAll('.pipeline-stage-btn').forEach(function (b) {
                b.classList.remove('btn-inpro');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.add('btn-inpro');
            this.classList.remove('btn-outline-secondary');
            document.querySelectorAll('.pipeline-stage-panel').forEach(function (p) {
                p.classList.toggle('d-none', p.dataset.stageId !== id);
            });
        });
    });

    // Móvil: mover cliente
    document.querySelectorAll('.pipeline-mover-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var clienteId = this.dataset.clienteId;
            var estadoId  = this.dataset.estadoId;
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
                    location.reload();
                } else {
                    showToast(data.error || 'No se pudo mover', 'error');
                }
            });
        });
    });

    // Desktop: drag & drop
    function actualizarBadgesPipeline() {
        document.querySelectorAll('#pipeline-kanban .kanban-col-body').forEach(function (col) {
            var header = col.closest('.kanban-col-wrap').querySelector('.kanban-col-header .badge');
            var cards  = col.querySelectorAll('.kanban-card');
            if (header) header.textContent = cards.length;
            var empty  = col.querySelector('.kanban-empty');
            if (cards.length === 0 && !empty) {
                col.insertAdjacentHTML('beforeend', '<div class="kanban-empty">Sin clientes</div>');
            } else if (cards.length > 0 && empty) {
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
