<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'CRM INPRO') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="app-body app-has-mobile-nav">
<?php
$pendingVentas = 0;
if (function_exists('is_inpro') && is_inpro()) {
    $pendingVentas = (int) (new \App\Models\DashboardModel())->statsInpro()['ventas_pendientes'];
}
$proximasVisitasCount = 0;
$tareasPendientesCount = 0;
$currentUser = current_user();
if ($currentUser) {
    $horas = (int) env('AVISO_VISITAS_HORAS', 48);
    $proximasVisitasCount  = count((new \App\Services\NotificacionService())->proximasVisitas($currentUser, $horas));
    $tareasPendientesCount = (new \App\Models\TareaModel())->countPendienteForUser($currentUser);
}
?>
<div class="app-wrapper">
    <?php require APP_PATH . '/Views/partials/sidebar.php'; ?>
    <div class="app-main">
        <?php require APP_PATH . '/Views/partials/topbar.php'; ?>
        <div class="app-content animate-fade-up">
            <?php require APP_PATH . '/Views/partials/alerts.php'; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<?php require APP_PATH . '/Views/partials/bottom-nav.php'; ?>

<!-- CSRF global para peticiones AJAX -->
<input type="hidden" name="_csrf" id="globalCsrf" value="<?= e(csrf_token()) ?>">

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Modal confirmación genérico -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body pt-4 pb-2 px-4 text-center">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:2rem"></i>
                <p class="mt-3 mb-0 fw-semibold" id="modalConfirmarMensaje"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-3 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm px-4" id="modalConfirmarOk">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
<script>
// Helper: mostrar toast
function showToast(msg, type) {
    type = type || 'success';
    var icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill' };
    var colors = { success: '#1a7f4b', error: '#dc3545', warning: '#f59e0b' };
    var icon   = icons[type] || icons.success;
    var color  = colors[type] || colors.success;
    var id     = 'toast-' + Date.now();
    var html   = '<div id="' + id + '" class="toast toast-inpro show mb-2" role="alert" aria-live="assertive">' +
        '<div class="toast-body d-flex align-items-center gap-2 fw-semibold" style="color:' + color + ';background:#fff;border-radius:8px;border-left:4px solid ' + color + ';padding:.75rem 1rem">' +
        '<i class="bi ' + icon + '"></i>' + msg + '</div></div>';
    var container = document.getElementById('toastContainer');
    container.insertAdjacentHTML('beforeend', html);
    setTimeout(function () {
        var el = document.getElementById(id);
        if (el) { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(function() { el.remove(); }, 300); }
    }, 3500);
}

// CSRF helper global
function getCsrfToken() {
    var el = document.querySelector('[name="_csrf"]');
    return el ? el.value : '';
}

// Confirmación genérica — reemplaza confirm() nativo
function confirmarAccion(msg, callback) {
    document.getElementById('modalConfirmarMensaje').textContent = msg;
    var okBtn = document.getElementById('modalConfirmarOk');
    var nuevo = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(nuevo, okBtn);
    nuevo.addEventListener('click', function () {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirmar')).hide();
        callback();
    });
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirmar')).show();
}

// Delegación: botones con data-confirm interceptan el submit del formulario
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-confirm]');
    if (!btn) return;
    e.preventDefault();
    var form = btn.closest('form');
    confirmarAccion(btn.dataset.confirm, function () { if (form) form.submit(); });
});

// Mover modales al body para que Bootstrap no quede atrapado por contenedores con transform
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal').forEach(function (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
});

// Limpieza defensiva de backdrops huérfanos
document.addEventListener('hidden.bs.modal', function () {
    if (!document.querySelector('.modal.show')) {
        document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }
});

</script>
<?= $scripts ?? '' ?>
</body>
</html>
