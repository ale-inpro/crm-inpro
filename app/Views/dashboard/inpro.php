<?php require APP_PATH . '/Views/partials/dashboard-hoy.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?= url('clientes') ?>" class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Clientes</div>
                    <div class="stat-value"><?= (int)$stats['clientes'] ?></div>
                </div>
                <div class="stat-icon"><i class="bi bi-building"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('clientes?filtro=sin_primera_visita') ?>" class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Sin 1ª visita</div>
                    <div class="stat-value text-warning"><?= (int)$stats['sin_primera_visita'] ?></div>
                </div>
                <div class="stat-icon"><i class="bi bi-geo-alt"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('ventas/validar') ?>" class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Ventas por validar</div>
                    <div class="stat-value"><?= (int)$stats['ventas_pendientes'] ?></div>
                </div>
                <div class="stat-icon"><i class="bi bi-patch-check"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('tareas') ?>" class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-label">Tareas vencidas</div>
                    <div class="stat-value text-danger"><?= (int)$tareasVencidas ?></div>
                </div>
                <div class="stat-icon"><i class="bi bi-alarm"></i></div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 d-none d-lg-flex">
    <div class="col-12 col-lg-8">
        <div class="panel">
            <div class="panel-header">Pipeline por etapa</div>
            <div class="panel-body">
                <canvas id="chartPipeline" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.matchMedia('(min-width: 992px)').matches) return;
    var chartEl = document.getElementById('chartPipeline');
    if (!chartEl || typeof Chart === 'undefined') return;
    new Chart(chartEl, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($pipelineChart ?? [], 'nombre')) ?>,
            datasets: [{
                label: 'Clientes',
                data: <?= json_encode(array_column($pipelineChart ?? [], 'total')) ?>,
                backgroundColor: <?= json_encode(array_column($pipelineChart ?? [], 'color_hex')) ?>,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            maintainAspectRatio: false,
            responsive: true
        }
    });
});
</script>