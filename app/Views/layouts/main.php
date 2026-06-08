<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'CRM INPRO') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="app-body">
<div class="app-wrapper">
    <?php require APP_PATH . '/Views/partials/sidebar.php'; ?>
    <div class="app-main">
        <?php
        $pendingVentas = 0;
        if (function_exists('is_inpro') && is_inpro()) {
            $pendingVentas = (int) (new \App\Models\DashboardModel())->statsInpro()['ventas_pendientes'];
        }
        require APP_PATH . '/Views/partials/topbar.php';
        ?>
        <div class="app-content">
            <?php require APP_PATH . '/Views/partials/alerts.php'; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
<?= $scripts ?? '' ?>
</body>
</html>