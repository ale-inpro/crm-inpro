<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ClienteModel;
use App\Models\VisitaModel;
use App\Services\ClienteService;

class VisitaController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        redirect('clientes');
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente = (new ClienteModel())->findById($clienteId);

        if (!$cliente || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes registrar visitas en este cliente.');
            redirect('clientes');
        }

        $esPrimera = isset($_POST['es_primera_visita']) ? 1 : 0;
        $fecha = $_POST['fecha_visita'] ?? date('Y-m-d H:i:s');

        (new VisitaModel())->create([
            'cliente_id' => $clienteId,
            'usuario_id' => (int) $user['id'],
            'tipo' => $esPrimera ? 'primera_visita' : ($_POST['tipo'] ?? 'seguimiento'),
            'es_primera_visita' => $esPrimera,
            'fecha_visita' => $fecha,
            'duracion_min' => ($_POST['duracion_min'] ?? '') !== '' ? (int) $_POST['duracion_min'] : null,
            'resultado' => $_POST['resultado'] ?? 'neutral',
            'notas' => trim($_POST['notas'] ?? '') ?: null,
            'es_remota' => isset($_POST['es_remota']) ? 1 : 0,
        ]);

        if ($esPrimera) {
            (new VisitaModel())->markPrimeraVisita($clienteId, (int) $user['id'], $fecha);
        }

        flash('success', 'Visita registrada.');
        redirect('clientes/ver?id=' . $clienteId);
    }
}