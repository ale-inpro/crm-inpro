<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Iniciar sesión') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="logo">
            <?php
            $logo = file_exists(PUBLIC_PATH . '/assets/img/logo-inpro.png')
                ? url('assets/img/logo-inpro.png')
                : url('assets/img/logo-inpro.svg');
            ?>
            <img src="<?= $logo ?>" alt="INPRO CRM">
        </div>
        <?= $content ?>
    </div>
</body>
</html>