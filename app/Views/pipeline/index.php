<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-kanban me-2"></i>Pipeline comercial</h1>
    <a href="<?= url('clientes/nuevo') ?>" class="btn btn-inpro"><i class="bi bi-plus-lg"></i> Nuevo cliente</a>
</div>

<div class="kanban-board" id="kanbanBoard">
<?php foreach ($columnas as $col): ?>
    <div class="kanban-column">
        <div class="kanban-column-header" style="background:<?= e($col['estado']['color_hex']) ?>">
            <?= e($col['estado']['nombre']) ?>
            <span class="badge bg-light text-dark ms-1"><?= count($col['clientes']) ?></span>
        </div>
        <div class="kanban-column-body" data-estado-id="<?= (int) $col['estado']['id'] ?>">
            <?php foreach ($col['clientes'] as $c): ?>
                <div class="kanban-card" draggable="true" data-cliente-id="<?= (int) $c['id'] ?>">
                    <a href="<?= url('clientes/ver?id=' . $c['id']) ?>" class="card-title text-decoration-none text-dark d-block"><?= e($c['razon_social']) ?></a>
                    <div class="card-meta">
                        <?php if ($c['ciudad']): ?><span><?= e($c['ciudad']) ?></span><?php endif; ?>
                        <?php if (!$c['primera_visita_realizada']): ?>
                            <span class="badge badge-primera-visita ms-1">Sin 1ª visita</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php
$scripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.querySelectorAll('.kanban-column-body').forEach(function (col) {
    new Sortable(col, {
        group: 'kanban',
        animation: 150,
        draggable: '.kanban-card',
        onEnd: function (evt) {
            const clienteId = evt.item.dataset.clienteId;
            const estadoId = evt.to.dataset.estadoId;
            if (!clienteId || !estadoId || evt.from === evt.to) return;
            fetch('PIPELINE_MOVER_URL', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'cliente_id=' + clienteId + '&estado_id=' + estadoId
            })
            .then(r => r.json())
            .then(d => {
                if (!d.ok) {
                    alert(d.error || 'No se pudo mover el cliente');
                    location.reload();
                }
            })
            .catch(() => { alert('Error de red'); location.reload(); });
        }
    });
});
</script>
HTML;
$scripts = str_replace('PIPELINE_MOVER_URL', url('pipeline/mover'), $scripts);
?>
