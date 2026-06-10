<?php
$busqueda = $busqueda ?? '';
$filtrosActivos = 0;
if (!empty($filtros['sin_primera_visita'])) $filtrosActivos++;
if (!empty($filtros['empresa_colaboradora_id'])) $filtrosActivos++;
if (!empty($filtros['estado_pipeline_id'])) $filtrosActivos++;
if ($busqueda !== '') $filtrosActivos++;
?>
<div class="d-flex justify-content-between align-items-center mb-3 mb-lg-4 flex-wrap gap-2">
    <h1 class="h3 mb-0 mobile-hide-heading"><i class="bi bi-building me-2"></i>Clientes</h1>
    <div class="d-flex gap-2">
        <?php if (is_inpro()): ?>
            <a href="<?= url('export/clientes') ?>" class="btn btn-outline-secondary btn-sm d-none d-md-inline-flex">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
        <?php endif; ?>
        <a href="<?= url('clientes/nuevo') ?>" class="btn btn-inpro btn-sm">
            <i class="bi bi-plus-lg"></i><span class="d-none d-sm-inline"> Nuevo cliente</span>
        </a>
    </div>
</div>

<!-- Móvil: búsqueda + filtros -->
<div class="d-lg-none mb-3">
    <form method="get" action="<?= url('clientes') ?>" class="mobile-search-form mb-2">
        <?php if (!empty($filtros['sin_primera_visita'])): ?><input type="hidden" name="filtro" value="sin_primera_visita"><?php endif; ?>
        <?php if (!empty($filtros['empresa_colaboradora_id'])): ?><input type="hidden" name="empresa" value="<?= (int) $filtros['empresa_colaboradora_id'] ?>"><?php endif; ?>
        <?php if (!empty($filtros['estado_pipeline_id'])): ?><input type="hidden" name="estado" value="<?= (int) $filtros['estado_pipeline_id'] ?>"><?php endif; ?>
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" name="q" class="form-control" placeholder="Buscar cliente, ciudad o CIF…"
                   value="<?= e($busqueda) ?>" autocomplete="off">
            <?php if ($busqueda !== ''): ?>
            <a href="<?= url('clientes' . (!empty($filtros) ? '?' . http_build_query(array_filter([
                'filtro' => !empty($filtros['sin_primera_visita']) ? 'sin_primera_visita' : null,
                'empresa' => $filtros['empresa_colaboradora_id'] ?? null,
                'estado' => $filtros['estado_pipeline_id'] ?? null,
            ])) : '')) ?>" class="btn btn-outline-secondary" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>
    <button type="button"
            class="btn btn-outline-secondary btn-sm w-100"
            data-bs-toggle="offcanvas"
            data-bs-target="#filtrosClientesOffcanvas">
        <i class="bi bi-funnel"></i> Filtrar
        <?php if ($filtrosActivos > 0): ?>
            <span class="badge bg-inpro ms-1"><?= $filtrosActivos ?></span>
        <?php endif; ?>
    </button>
</div>

<!-- Desktop: filtros visibles -->
<div class="panel mb-3 d-none d-lg-block">
    <div class="panel-body py-2">
        <form method="get" action="<?= url('clientes') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-0">Buscar</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Nombre, ciudad, CIF…"
                       value="<?= e($busqueda) ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-0">Filtro rápido</label>
                <select name="filtro" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="sin_primera_visita" <?= !empty($filtros['sin_primera_visita']) ? 'selected' : '' ?>>Sin 1ª visita</option>
                </select>
            </div>
            <?php if (is_inpro() && !empty($empresas)): ?>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-0">Empresa colaboradora</label>
                <select name="empresa" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php foreach ($empresas as $emp): ?>
                        <option value="<?= (int) $emp['id'] ?>" <?= ($filtros['empresa_colaboradora_id'] ?? 0) == $emp['id'] ? 'selected' : '' ?>><?= e($emp['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-12 col-md-3">
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

<!-- Móvil: sheet de filtros -->
<div class="offcanvas offcanvas-bottom app-filter-offcanvas d-lg-none"
     tabindex="-1"
     id="filtrosClientesOffcanvas"
     aria-labelledby="filtrosClientesOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filtrosClientesOffcanvasLabel">Filtrar clientes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" action="<?= url('clientes') ?>" class="vstack gap-3">
            <div>
                <label class="form-label small">Buscar</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Nombre, ciudad, CIF…"
                       value="<?= e($busqueda) ?>">
            </div>
            <div>
                <label class="form-label small">Filtro rápido</label>
                <select name="filtro" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="sin_primera_visita" <?= !empty($filtros['sin_primera_visita']) ? 'selected' : '' ?>>Sin 1ª visita</option>
                </select>
            </div>
            <?php if (is_inpro() && !empty($empresas)): ?>
            <div>
                <label class="form-label small">Empresa colaboradora</label>
                <select name="empresa" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($empresas as $emp): ?>
                        <option value="<?= (int) $emp['id'] ?>" <?= ($filtros['empresa_colaboradora_id'] ?? 0) == $emp['id'] ? 'selected' : '' ?>><?= e($emp['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div>
                <label class="form-label small">Estado pipeline</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($estados as $ep): ?>
                        <option value="<?= (int) $ep['id'] ?>" <?= ($filtros['estado_pipeline_id'] ?? 0) == $ep['id'] ? 'selected' : '' ?>><?= e($ep['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary flex-grow-1">Limpiar</a>
                <button type="submit" class="btn btn-inpro flex-grow-1">Aplicar</button>
            </div>
        </form>
    </div>
</div>

<!-- Móvil: cards -->
<div class="d-lg-none mobile-list">
    <?php if (empty($clientes)): ?>
        <div class="text-muted text-center py-4 small">No hay clientes con estos filtros.</div>
    <?php else: ?>
        <?php foreach ($clientes as $c): ?>
        <a href="<?= url('clientes/ver?id=' . $c['id']) ?>" class="mobile-card mobile-card-clickable text-decoration-none">
            <div class="mobile-card-title"><?= e($c['razon_social']) ?></div>
            <?php if ($c['ciudad']): ?>
                <div class="mobile-card-sub"><i class="bi bi-geo-alt"></i> <?= e($c['ciudad']) ?></div>
            <?php endif; ?>
            <div class="mobile-card-badges">
                <span class="badge" style="background:<?= e($c['color_hex']) ?>"><?= e($c['estado_nombre']) ?></span>
                <?php if (!$c['primera_visita_realizada']): ?>
                    <span class="badge badge-primera-visita">Sin 1ª visita</span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Desktop: tabla -->
<div class="panel d-none d-lg-block">
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable datatable-clientes table-clientes">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>1ª visita</th>
                        <th>Gestionado por</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td>
                            <a href="<?= url('clientes/ver?id=' . $c['id']) ?>" class="cliente-link">
                                <strong><?= e($c['razon_social']) ?></strong>
                            </a>
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
                        <td>
                            <?php if (empty($c['empresa_colaboradora_id']) || cliente_gestor_activo($c) === 'inpro'): ?>
                                <span class="badge bg-inpro">INPRO</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark"><?= e($c['empresa_colaboradora_nombre']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <div class="d-inline-flex gap-1 table-actions">
                                <a href="<?= url('clientes/ver?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary" title="Ver ficha">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </a>
                                <?php if (cliente_can_manage($c)): ?>
                                <a href="<?= url('clientes/editar?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php
                                $actBloq = $actividadBloqueantePorCliente[(int) $c['id']] ?? null;
                                $noEliminar = $actBloq !== null && array_sum($actBloq) > 0;
                                ?>
                                <form method="post" action="<?= url('clientes/eliminar') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="<?= $noEliminar ? 'No se puede eliminar: tiene actividad comercial' : 'Eliminar cliente' ?>"
                                            <?= $noEliminar ? 'disabled' : '' ?>
                                            <?php if (!$noEliminar): ?>
                                            data-confirm="¿Eliminar el cliente «<?= e($c['razon_social']) ?>»? No tiene visitas, ventas ni tareas registradas."
                                            <?php endif; ?>>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.mobile-search-form');
    if (!form) return;
    var input = form.querySelector('input[name="q"]');
    if (!input) return;
    var timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { form.submit(); }, 450);
    });
});
</script>
