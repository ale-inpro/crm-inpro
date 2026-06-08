<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AsignacionModel;
use App\Models\ClienteModel;
use App\Services\AuditoriaService;
use App\Services\ClienteService;

class AsignacionController extends Controller
{
    public function transferirInpro(): void
    {
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Transferencia a INPRO');
        $inproId = (int) ($_POST['responsable_inpro_id'] ?? 0);

        $cliente = (new ClienteModel())->findById($clienteId);
        if (!$cliente || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes transferir este cliente.');
            redirect('clientes');
        }

        if ($inproId <= 0) {
            flash('error', 'Debes seleccionar un responsable INPRO.');
            redirect('clientes/ver?id=' . $clienteId);
        }

        (new AsignacionModel())->transferirAInpro($clienteId, $inproId, (int) $user['id'], $motivo);
        (new AuditoriaService())->log((int) $user['id'], 'clientes', $clienteId, 'transferencia_inpro', [
            'responsable_inpro_id' => $inproId,
            'motivo' => $motivo,
        ]);
        flash('success', 'Cliente transferido a INPRO. La empresa queda en solo lectura.');
        redirect('clientes/ver?id=' . $clienteId);
    }
}