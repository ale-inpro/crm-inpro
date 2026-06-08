<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="<?= url('clientes') ?>" class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Mis clientes</div>
                    <div class="stat-value"><?= (int) $stats['mis_clientes'] ?></div>
                </div>
                <div class="stat-icon"><i class="bi bi-building"></i></div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('clientes?filtro=sin_primera_visita') ?>" class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Sin 1ª visita</div>
                    <div class="stat-value text-warning"><?= (int) $stats['sin_primera_visita'] ?></div>
                </div>
                <div class="stat-icon"><i class="bi bi-geo-alt"></i></div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('comisiones') ?>" class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Comisiones aprobadas</div>
                    <div class="stat-value"><?= number_format($stats['comisiones_aprobadas'], 0, ',', '.') ?> €</div>
                </div>
                <div class="stat-icon"><i class="bi bi-currency-euro"></i></div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-header">
                Tareas vencidas
                <span class="badge bg-danger"><?= (int) $tareasVencidas ?></span>
            </div>
            <div class="panel-body">
                <a href="<?= url('tareas') ?>" class="btn btn-sm btn-inpro">Ir a tareas</a>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-header">
                Próximas tareas
                <a href="<?= url('tareas') ?>" class="btn btn-sm btn-inpro">Ver todas</a>
            </div>
            <div class="panel-body p-0 px-3">
                <?php foreach ($tareas as $t): ?>
                    <div class="task-item <?= $t['prioridad'] === 'alta' ? 'task-priority-alta' : ($t['prioridad'] === 'media' ? 'task-priority-media' : '') ?>">
                        <div class="flex-grow-1">
                            <a href="<?= url('clientes/ver?id=' . $t['cliente_id']) ?>"><?= e($t['titulo']) ?></a>
                            <div class="small text-muted"><?= e($t['razon_social']) ?> · <?= e($t['fecha_vencimiento']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($tareas)): ?>
                    <p class="text-muted small py-3">Sin tareas pendientes</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
