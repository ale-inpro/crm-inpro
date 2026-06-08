<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Tareas</h1>
    <div class="btn-group">
        <a href="<?= url('tareas') ?>" class="btn btn-sm <?= empty($filtroEstado) ? 'btn-inpro' : 'btn-outline-secondary' ?>">Todas</a>
        <a href="<?= url('tareas?estado=pendiente') ?>" class="btn btn-sm <?= $filtroEstado === 'pendiente' ? 'btn-inpro' : 'btn-outline-secondary' ?>">Pendientes</a>
        <a href="<?= url('tareas?estado=completada') ?>" class="btn btn-sm <?= $filtroEstado === 'completada' ? 'btn-inpro' : 'btn-outline-secondary' ?>">Completadas</a>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span><i class="bi bi-check2-square me-1"></i> Listado de tareas</span>
    </div>
    <div class="panel-body p-0">
        <?php if (empty($tareas)): ?>
            <p class="text-muted p-3 mb-0">No hay tareas<?= $filtroEstado ? ' con este filtro' : '' ?>.</p>
        <?php else: ?>
            <?php foreach ($tareas as $t): ?>
                <div class="task-item px-3 <?= $t['estado'] === 'completada' ? 'done' : '' ?> <?= $t['prioridad'] === 'alta' ? 'task-priority-alta' : ($t['prioridad'] === 'media' ? 'task-priority-media' : '') ?>">
                    <div class="flex-grow-1">
                        <a href="<?= url('clientes/ver?id=' . $t['cliente_id']) ?>" class="fw-semibold text-decoration-none"><?= e($t['titulo']) ?></a>
                        <div class="small text-muted">
                            <?= e($t['razon_social']) ?> · Vence: <?= e(date('d/m/Y', strtotime($t['fecha_vencimiento']))) ?>
                            · <?= e($t['asignado_nombre']) ?>
                            · <span class="text-capitalize"><?= e($t['prioridad']) ?></span>
                        </div>
                    </div>
                    <?php if ($t['estado'] === 'pendiente'): ?>
                        <form method="post" action="<?= url('tareas/completar') ?>" class="ms-2">
                            <input type="hidden" name="tarea_id" value="<?= (int) $t['id'] ?>">
                            <input type="hidden" name="redirect" value="tareas<?= $filtroEstado ? '?estado=' . e($filtroEstado) : '' ?>">
                            <button class="btn btn-sm btn-outline-success" title="Completar"><i class="bi bi-check-lg"></i></button>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-success">Hecha</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
