<h1 class="h3 mb-4">Nuevo cliente</h1>
<form method="post" action="<?= url('clientes/guardar') ?>" class="panel">
    <?= csrf_field() ?>
    <div class="panel-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Razón social *</label>
                <input type="text" name="razon_social" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">CIF</label>
                <input type="text" name="cif" class="form-control">
            </div>
            <div class="col-md-4"><label class="form-label">Ciudad</label><input type="text" name="ciudad" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Provincia</label><input type="text" name="provincia" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Teléfono</label><input type="text" name="telefono_principal" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email_principal" class="form-control"></div>
            <?php if (is_inpro() && !empty($empresas)): ?>
            <div class="col-md-6">
                <label class="form-label">Empresa colaboradora</label>
                <select name="empresa_colaboradora_id" class="form-select">
                    <option value="">— Ninguna —</option>
                    <?php foreach ($empresas as $emp): ?>
                        <option value="<?= (int) $emp['id'] ?>"><?= e($emp['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-12"><hr><h2 class="h6">Contacto principal</h2></div>
            <div class="col-md-4"><label class="form-label">Nombre</label><input type="text" name="contacto_nombre" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Cargo</label><input type="text" name="contacto_cargo" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Teléfono</label><input type="text" name="contacto_telefono" class="form-control"></div>
        </div>
    </div>
    <div class="panel-header border-top">
        <button class="btn btn-inpro">Guardar cliente</button>
        <a href="<?= url('clientes') ?>" class="btn btn-link">Cancelar</a>
    </div>
</form>
