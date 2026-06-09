<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CatalogoModel;
use App\Models\ClienteModel;
use App\Models\VentaModel;
use App\Services\AuditoriaService;
use App\Services\ClienteService;
use App\Services\ComisionService;
use App\Services\TarifaService;

class VentaController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $this->view('ventas/index', [
            'title' => 'Ventas',
            'ventas' => (new VentaModel())->listForUser($user),
            'breadcrumbs' => [['label' => 'Ventas']],
            'backUrl' => 'dashboard',
            'backLabel' => 'Inicio',
        ]);
    }

    public function pendientesValidacion(): void
    {
        $this->requireInpro();
        $ventas = (new VentaModel())->pendientesValidacion();
        $comisionService = new ComisionService();
        $sugerencias = [];
        foreach ($ventas as $v) {
            $sugerencias[$v['id']] = $comisionService->sugerirReglaId((int) $v['cliente_id'], $v);
        }

        $this->view('ventas/validar', [
            'title' => 'Validar ventas',
            'ventas' => $ventas,
            'reglas' => (new CatalogoModel())->reglasComision(),
            'sugerencias' => $sugerencias,
            'breadcrumbs' => [
                ['label' => 'Ventas', 'url' => 'ventas'],
                ['label' => 'Validar'],
            ],
            'backUrl' => 'ventas',
            'backLabel' => 'Ventas',
        ]);
    }

    public function store(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente = (new ClienteModel())->findById($clienteId);

        if (!$cliente || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes registrar ventas en este cliente.');
            redirect('clientes');
        }

        $numObras = (int) ($_POST['num_obras'] ?? 0);
        if ($numObras <= 0) {
            flash('error', 'Indica el número de obras.');
            redirect('clientes/ver?id=' . $clienteId . '#tabVentas');
        }

        $tarifaSvc = new TarifaService();
        $tarifaId = $tarifaSvc->tarifaIdParaCliente($clienteId);
        $tramo = $tarifaId ? $tarifaSvc->resolverTramo($tarifaId, $numObras) : null;

        if (!$tramo) {
            flash('error', 'No hay tramo de tarifa para ese número de obras.');
            redirect('clientes/ver?id=' . $clienteId . '#tabVentas');
        }

        $descuento = (float) ($_POST['descuento_pct'] ?? 0);
        $importes = $tarifaSvc->calcularImportes($tramo, $descuento);

        $atribucion = is_empresa() ? 'empresa' : 'inpro';
        if (is_inpro() && $cliente['empresa_colaboradora_id']) {
            $atribucion = $_POST['atribucion_cierre'] ?? 'inpro';
        }

        (new VentaModel())->create([
            'cliente_id' => $clienteId,
            'num_obras' => $numObras,
            'tarifa_tramo_id' => (int) $tramo['id'],
            'precio_mes_eur' => $importes['precio_mes_eur'],
            'stripe_price_id' => $importes['stripe_price_id'],
            'registrado_por_id' => (int) $user['id'],
            'importe_anual_eur' => $importes['importe_anual_eur'],
            'descuento_pct' => $descuento,
            'importe_final_eur' => $importes['importe_final_eur'],
            'fecha_propuesta' => $_POST['fecha_propuesta'] ?? date('Y-m-d'),
            'fecha_cierre' => $_POST['fecha_cierre'] ?? date('Y-m-d'),
            'atribucion_cierre' => $atribucion,
            'notas' => trim($_POST['notas'] ?? '') ?: null,
        ]);

        flash('success', 'Venta registrada. Pendiente de validación INPRO.');
        redirect('clientes/ver?id=' . $clienteId . '#tabVentas');
    }

    public function validar(): void
    {
        csrf_verify();
        $user = $this->requireInpro();
        $ventaId = (int) ($_POST['venta_id'] ?? 0);
        $reglaId = (int) ($_POST['regla_comision_id'] ?? 0);

        if ($ventaId <= 0 || $reglaId <= 0) {
            flash('error', 'Venta y regla de comisión son obligatorias.');
            redirect('ventas/validar');
        }

        (new VentaModel())->validar($ventaId, (int) $user['id'], $reglaId);
        (new AuditoriaService())->log((int) $user['id'], 'ventas', $ventaId, 'venta_validada', [
            'regla_comision_id' => $reglaId,
        ]);
        flash('success', 'Venta validada correctamente.');
        redirect('ventas/validar');
    }
}
