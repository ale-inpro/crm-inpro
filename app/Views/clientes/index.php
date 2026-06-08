<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-building me-2"></i>Clientes</h1>
    <div class="d-flex gap-2">
        <?php if (is_inpro()): ?>
            <a href="<?= url('export/clientes') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Exportar CSV</a>
        <?php endif; ?>
        <a href="<?= url('clientes/nuevo') ?>" class="btn btn-inpro"><i class="bi bi-plus-lg"></i> Nuevo cliente</a>
    </div>
</div>

<div class="panel mb-3">
    <div class="panel-body py-2">
        <form method="get" action="<?= url('clientes') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">Filtro rápido</label>
                <select name="filtro" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="sin_primera_visita" <?= !empty($filtros['sin_primera_visita']) ? 'selected' : '' ?>>Sin 1ª visita</option>
                </select>
            </div>
            <?php if (is_inpro() && !empty($empresas)): ?>
            <div class="col-md-3">
                <label class="form-label small mb-0">Empresa colaboradora</label>
                <select name="empresa" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php foreach ($empresas as $emp): ?>
                        <option value="<?= (int) $emp['id'] ?>" <?= ($filtros['empresa_colaboradora_id'] ?? 0) == $emp['id'] ? 'selected' : '' ?>><?= e($emp['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <label class="form-label small mb-0">Estado</label>
                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($estados as $ep): ?>
                        <option value="<?= (int) $ep['id'] ?>" <?= ($filtros['estado_pipeline_id'] ?? 0) == $ep['id'] ? 'selected' : '' ?>><?= e($ep['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>1ª visita</th>
                        <th>Empresa colab.</th>
                        <th>Acceso</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td>
                            <strong><?= e($c['razon_social']) ?></strong>
                            <?php if ($c['ciudad']): ?><br><small class="text-muted"><?= e($c['ciudad']) ?></small><?php endif; ?>
                        </td>
                        <td><span class="badge" style="background:<?= e($c['color_hex']) ?>"><?= e($c['estado_nombre']) ?></span></td>
                        <td>
                            <?php if ($c['primera_visita_realizada']): ?>
                                <span class="badge bg-success">Sí</span>
                            <?php else: ?>
                                <span class="badge badge-primera-visita">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($c['empresa_colaboradora_nombre'] ?? '—') ?></td>
                        <td><?= $c['modo_acceso_empresa'] === 'lectura' ? '<span class="badge bg-secondary">Lectura</span>' : '<span class="badge bg-info">Edición</span>' ?></td>
                        <td><a href="<?= url('clientes/ver?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">Abrir</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
