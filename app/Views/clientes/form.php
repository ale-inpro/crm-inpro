<?php
$cliente = $cliente ?? null;
$esEdicion = !empty($cliente);
$cid = $esEdicion ? (int) $cliente['id'] : 0;
$puedeAsignarColab = is_inpro() && (!$esEdicion || empty($cliente['empresa_colaboradora_id']));
$usuariosPorEmpresa = $usuariosPorEmpresa ?? [];
?>
<h1 class="h3 mb-4 mobile-hide-heading"><?= $esEdicion ? 'Editar cliente' : 'Nuevo cliente' ?></h1>
<form method="post" action="<?= url($esEdicion ? 'clientes/actualizar' : 'clientes/guardar') ?>" class="panel form-panel-sticky" id="formCliente">
    <?= csrf_field() ?>
    <?php if ($esEdicion): ?>
        <input type="hidden" name="id" value="<?= $cid ?>">
    <?php endif; ?>
    <div class="panel-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Razón social *</label>
                <input type="text" name="razon_social" class="form-control" required
                       value="<?= e($cliente['razon_social'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Nombre comercial</label>
                <input type="text" name="nombre_comercial" class="form-control"
                       value="<?= e($cliente['nombre_comercial'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">CIF</label>
                <input type="text" name="cif" class="form-control"
                       value="<?= e($cliente['cif'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Estado pipeline</label>
                <select name="estado_pipeline_id" class="form-select">
                    <?php foreach ($estados as $ep): ?>
                        <option value="<?= (int) $ep['id'] ?>"
                            <?= (int) ($cliente['estado_pipeline_id'] ?? 1) === (int) $ep['id'] ? 'selected' : '' ?>>
                            <?= e($ep['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ciudad</label>
                <input type="text" name="ciudad" class="form-control"
                       value="<?= e($cliente['ciudad'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Provincia</label>
                <input type="text" name="provincia" class="form-control"
                       value="<?= e($cliente['provincia'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono_principal" class="form-control"
                       value="<?= e($cliente['telefono_principal'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email_principal" class="form-control"
                       value="<?= e($cliente['email_principal'] ?? '') ?>">
            </div>

            <?php if ($puedeAsignarColab && !empty($empresas)): ?>
            <div class="col-12">
                <?php if ($esEdicion): ?>
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Solo puedes asignar empresa colaboradora si el cliente se creó sin una. Deberás indicar el responsable de esa empresa.
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">Empresa colaboradora</label>
                <select name="empresa_colaboradora_id" id="empresaColaboradora" class="form-select">
                    <option value="">— Ninguna (solo INPRO) —</option>
                    <?php foreach ($empresas as $emp): ?>
                        <option value="<?= (int) $emp['id'] ?>"><?= e($emp['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6" id="wrapResponsableEmpresa" style="display:none">
                <label class="form-label">Responsable empresa colaboradora *</label>
                <select name="responsable_empresa_id" id="responsableEmpresa" class="form-select">
                    <option value="">Selecciona empresa primero...</option>
                </select>
            </div>
            <?php elseif ($esEdicion && !empty($cliente['empresa_colaboradora_nombre'])): ?>
            <div class="col-md-6">
                <label class="form-label">Empresa colaboradora</label>
                <input type="text" class="form-control" value="<?= e($cliente['empresa_colaboradora_nombre']) ?>" readonly disabled>
            </div>
            <?php endif; ?>

            <?php if (!$esEdicion): ?>
            <div class="col-12"><hr><h2 class="h6">Contacto principal</h2></div>
            <div class="col-md-4"><label class="form-label">Nombre</label><input type="text" name="contacto_nombre" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Cargo</label><input type="text" name="contacto_cargo" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Teléfono</label><input type="text" name="contacto_telefono" class="form-control"></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="panel-header border-top">
        <button class="btn btn-inpro"><?= $esEdicion ? 'Guardar cambios' : 'Guardar cliente' ?></button>
        <a href="<?= url($esEdicion ? 'clientes/ver?id=' . $cid : 'clientes') ?>" class="btn btn-link">Cancelar</a>
    </div>
</form>

<?php if ($puedeAsignarColab && !empty($empresas)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var usuariosPorEmpresa = <?= json_encode($usuariosPorEmpresa, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var selEmpresa = document.getElementById('empresaColaboradora');
    var wrapResp = document.getElementById('wrapResponsableEmpresa');
    var selResp = document.getElementById('responsableEmpresa');
    var form = document.getElementById('formCliente');

    function actualizarResponsables() {
        var empId = selEmpresa.value;
        selResp.innerHTML = '<option value="">Selecciona...</option>';
        if (!empId) {
            wrapResp.style.display = 'none';
            selResp.removeAttribute('required');
            return;
        }
        wrapResp.style.display = '';
        selResp.setAttribute('required', 'required');
        (usuariosPorEmpresa[empId] || []).forEach(function (u) {
            var opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = u.nombre;
            selResp.appendChild(opt);
        });
    }

    selEmpresa.addEventListener('change', actualizarResponsables);

    form.addEventListener('submit', function (e) {
        if (selEmpresa.value && !selResp.value) {
            e.preventDefault();
            alert('Debes seleccionar un responsable de la empresa colaboradora.');
        }
    });
});
</script>
<?php endif; ?>
