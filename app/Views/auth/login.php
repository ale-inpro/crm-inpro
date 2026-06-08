<?php require APP_PATH . '/Views/partials/alerts.php'; ?>
<form method="post" action="<?= url('login') ?>">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required autofocus
               value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-inpro w-100 py-2">Iniciar sesión</button>
</form>
