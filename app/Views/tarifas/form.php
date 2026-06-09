<?php
$tarifa = $tarifa ?? null;
$esEdicion = !empty($tarifa);
$tid = $esEdicion ? (int) $tarifa['id'] : 0;
?>
<h1 class="h3 mb-4 mobile-hide-heading"><?= $esEdicion ? 'Editar tarifa' : 'Nueva tarifa' ?></h1>
<form method="post" action="<?= url($esEdicion ? 'tarifas/actualizar' : 'tarifas/guardar') ?>" class="panel form-panel-sticky">
    <?= csrf_field() ?>
    <?php if ($esEdicion): ?>
        <input type="hidden" name="id" value="<?= $tid ?>">
    <?php endif; ?>
    <div class="panel-body">
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" class="form-control" required
                       value="<?= e($tarifa['nombre'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="activa" value="1" class="form-check-input" id="tarifaActiva"
                        <?= ($tarifa['activa'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="tarifaActiva">Tarifa activa</label>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-header border-top">
        <button class="btn btn-inpro">Guardar</button>
        <a href="<?= url($esEdicion ? 'tarifas/ver?id=' . $tid : 'tarifas') ?>" class="btn btn-link">Cancelar</a>
    </div>
</form>
