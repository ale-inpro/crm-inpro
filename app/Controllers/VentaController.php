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

class VentaController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $this->view('ventas/index', [
            'title' => 'Ventas',
            'ventas' => (new VentaModel())->listForUser($user),
            'breadcrumbs' => [['label' => 'Ventas']],
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
        ]);
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente = (new ClienteModel())->findById($clienteId);

        if (!$cliente || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes registrar ventas en este cliente.');
            redirect('clientes');
        }

        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $productos = (new CatalogoModel())->productos();
        $producto = null;
        foreach ($productos as $p) {
            if ((int) $p['id'] === $productoId) {
                $producto = $p;
                break;
            }
        }

        if (!$producto) {
            flash('error', 'Producto no válido.');
            redirect('clientes/ver?id=' . $clienteId);
        }

        $importe = (float) $producto['precio_anual_eur'];
        $descuento = (float) ($_POST['descuento_pct'] ?? 0);
        $final = round($importe * (1 - $descuento / 100), 2);

        $atribucion = is_empresa() ? 'empresa' : 'inpro';
        if (is_inpro() && $cliente['empresa_colaboradora_id']) {
            $atribucion = $_POST['atribucion_cierre'] ?? 'inpro';
        }

        (new VentaModel())->create([
            'cliente_id' => $clienteId,
            'producto_id' => $productoId,
            'registrado_por_id' => (int) $user['id'],
            'importe_anual_eur' => $importe,
            'descuento_pct' => $descuento,
            'importe_final_eur' => $final,
            'fecha_propuesta' => $_POST['fecha_propuesta'] ?? date('Y-m-d'),
            'fecha_cierre' => $_POST['fecha_cierre'] ?? date('Y-m-d'),
            'atribucion_cierre' => $atribucion,
            'notas' => trim($_POST['notas'] ?? '') ?: null,
        ]);

        flash('success', 'Venta registrada. Pendiente de validación INPRO.');
        redirect('clientes/ver?id=' . $clienteId);
    }

    public function validar(): void
    {
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