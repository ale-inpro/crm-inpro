<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TarifaModel;
use App\Services\AuditoriaService;

class TarifaController extends Controller
{
    public function index(): void
    {
        $this->requireInpro();
        $this->view('tarifas/index', [
            'title' => 'Tarifas por volumen',
            'tarifas' => (new TarifaModel())->listar(),
            'breadcrumbs' => [['label' => 'Tarifas']],
            'backUrl' => 'dashboard',
            'backLabel' => 'Inicio',
        ]);
    }

    public function show(): void
    {
        $this->requireInpro();
        $id = (int) ($_GET['id'] ?? 0);
        $model = new TarifaModel();
        $tarifa = $model->findById($id);
        if (!$tarifa) {
            flash('error', 'Tarifa no encontrada.');
            redirect('tarifas');
        }

        $this->view('tarifas/show', [
            'title' => $tarifa['nombre'],
            'tarifa' => $tarifa,
            'tramos' => $model->tramosPorTarifa($id),
            'empresas' => $model->empresasAsignadas($id),
            'empresasAsignables' => $model->empresasAsignables($id),
            'breadcrumbs' => [
                ['label' => 'Tarifas', 'url' => 'tarifas'],
                ['label' => $tarifa['nombre']],
            ],
            'backUrl' => 'tarifas',
            'backLabel' => 'Tarifas',
        ]);
    }

    public function create(): void
    {
        $this->requireInpro();
        $this->view('tarifas/form', [
            'title' => 'Nueva tarifa',
            'tarifa' => null,
            'breadcrumbs' => [
                ['label' => 'Tarifas', 'url' => 'tarifas'],
                ['label' => 'Nueva'],
            ],
            'backUrl' => 'tarifas',
            'backLabel' => 'Tarifas',
        ]);
    }

    public function store(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            flash('error', 'El nombre es obligatorio.');
            redirect('tarifas/nuevo');
        }

        $id = (new TarifaModel())->create([
            'nombre' => $nombre,
            'activa' => !empty($_POST['activa']) ? 1 : 0,
            'es_default' => 0,
        ]);

        (new AuditoriaService())->log((int) $user['id'], 'tarifas', $id, 'crear', ['nombre' => $nombre]);
        flash('success', 'Tarifa creada.');
        redirect('tarifas/ver?id=' . $id);
    }

    public function edit(): void
    {
        $this->requireInpro();
        $id = (int) ($_GET['id'] ?? 0);
        $tarifa = (new TarifaModel())->findById($id);
        if (!$tarifa) {
            flash('error', 'Tarifa no encontrada.');
            redirect('tarifas');
        }

        $this->view('tarifas/form', [
            'title' => 'Editar tarifa',
            'tarifa' => $tarifa,
            'breadcrumbs' => [
                ['label' => 'Tarifas', 'url' => 'tarifas'],
                ['label' => $tarifa['nombre'], 'url' => 'tarifas/ver?id=' . $id],
                ['label' => 'Editar'],
            ],
            'backUrl' => 'tarifas/ver?id=' . $id,
            'backLabel' => $tarifa['nombre'],
        ]);
    }

    public function update(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $id = (int) ($_POST['id'] ?? 0);
        $model = new TarifaModel();
        $tarifa = $model->findById($id);
        if (!$tarifa) {
            flash('error', 'Tarifa no encontrada.');
            redirect('tarifas');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            flash('error', 'El nombre es obligatorio.');
            redirect('tarifas/editar?id=' . $id);
        }

        $model->update($id, [
            'nombre' => $nombre,
            'activa' => !empty($_POST['activa']) ? 1 : 0,
        ]);

        (new AuditoriaService())->log((int) $user['id'], 'tarifas', $id, 'actualizar', ['nombre' => $nombre]);
        flash('success', 'Tarifa actualizada.');
        redirect('tarifas/ver?id=' . $id);
    }

    public function destroy(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $id = (int) ($_POST['id'] ?? 0);
        $tarifa = (new TarifaModel())->findById($id);
        if (!$tarifa || !empty($tarifa['es_default'])) {
            flash('error', 'No se puede eliminar esta tarifa.');
            redirect('tarifas');
        }

        (new TarifaModel())->delete($id);
        (new AuditoriaService())->log((int) $user['id'], 'tarifas', $id, 'eliminar', ['nombre' => $tarifa['nombre']]);
        flash('success', 'Tarifa eliminada.');
        redirect('tarifas');
    }

    public function storeTramo(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $tarifaId = (int) ($_POST['tarifa_id'] ?? 0);
        if (!(new TarifaModel())->findById($tarifaId)) {
            flash('error', 'Tarifa no válida.');
            redirect('tarifas');
        }

        $obrasHasta = trim($_POST['obras_hasta'] ?? '');
        (new TarifaModel())->createTramo([
            'tarifa_id' => $tarifaId,
            'obras_desde' => (int) ($_POST['obras_desde'] ?? 1),
            'obras_hasta' => $obrasHasta === '' ? null : (int) $obrasHasta,
            'precio_mes_eur' => (float) str_replace(',', '.', $_POST['precio_mes_eur'] ?? '0'),
            'stripe_price_id' => trim($_POST['stripe_price_id'] ?? '') ?: null,
            'orden' => (int) ($_POST['orden'] ?? 0),
        ]);

        (new AuditoriaService())->log((int) $user['id'], 'tarifas', $tarifaId, 'tramo_crear', []);
        flash('success', 'Tramo añadido.');
        redirect('tarifas/ver?id=' . $tarifaId);
    }

    public function updateTramo(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $tramoId = (int) ($_POST['tramo_id'] ?? 0);
        $tarifaId = (int) ($_POST['tarifa_id'] ?? 0);
        $model = new TarifaModel();
        $tramo = $model->findTramoById($tramoId);
        if (!$tramo || (int) $tramo['tarifa_id'] !== $tarifaId) {
            flash('error', 'Tramo no válido.');
            redirect('tarifas');
        }

        $obrasHasta = trim($_POST['obras_hasta'] ?? '');
        $model->updateTramo($tramoId, [
            'obras_desde' => (int) ($_POST['obras_desde'] ?? 1),
            'obras_hasta' => $obrasHasta === '' ? null : (int) $obrasHasta,
            'precio_mes_eur' => (float) str_replace(',', '.', $_POST['precio_mes_eur'] ?? '0'),
            'stripe_price_id' => trim($_POST['stripe_price_id'] ?? '') ?: null,
            'orden' => (int) ($_POST['orden'] ?? 0),
        ]);

        (new AuditoriaService())->log((int) $user['id'], 'tarifas', $tarifaId, 'tramo_actualizar', ['tramo_id' => $tramoId]);
        flash('success', 'Tramo actualizado.');
        redirect('tarifas/ver?id=' . $tarifaId);
    }

    public function destroyTramo(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $tramoId = (int) ($_POST['tramo_id'] ?? 0);
        $tarifaId = (int) ($_POST['tarifa_id'] ?? 0);
        $model = new TarifaModel();
        $tramo = $model->findTramoById($tramoId);
        if (!$tramo || (int) $tramo['tarifa_id'] !== $tarifaId) {
            flash('error', 'Tramo no válido.');
            redirect('tarifas');
        }

        $model->deleteTramo($tramoId);
        (new AuditoriaService())->log((int) $user['id'], 'tarifas', $tarifaId, 'tramo_eliminar', ['tramo_id' => $tramoId]);
        flash('success', 'Tramo eliminado.');
        redirect('tarifas/ver?id=' . $tarifaId);
    }

    public function asignarEmpresa(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $tarifaId = (int) ($_POST['tarifa_id'] ?? 0);
        $empresaId = (int) ($_POST['empresa_id'] ?? 0);
        if (!(new TarifaModel())->findById($tarifaId) || $empresaId <= 0) {
            flash('error', 'Datos no válidos.');
            redirect('tarifas');
        }

        (new TarifaModel())->asignarEmpresa($tarifaId, $empresaId);
        (new AuditoriaService())->log((int) $user['id'], 'tarifas', $tarifaId, 'asignar_empresa', ['empresa_id' => $empresaId]);
        flash('success', 'Empresa asignada a la tarifa.');
        redirect('tarifas/ver?id=' . $tarifaId);
    }

    public function quitarEmpresa(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $tarifaId = (int) ($_POST['tarifa_id'] ?? 0);
        $empresaId = (int) ($_POST['empresa_id'] ?? 0);

        (new TarifaModel())->quitarEmpresa($empresaId);
        (new AuditoriaService())->log((int) $user['id'], 'tarifas', $tarifaId, 'quitar_empresa', ['empresa_id' => $empresaId]);
        flash('success', 'Empresa movida a la tarifa por defecto.');
        redirect('tarifas/ver?id=' . $tarifaId);
    }
}
