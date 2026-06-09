<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AsignacionModel;
use App\Models\ClienteModel;
use App\Models\UsuarioModel;
use App\Services\AuditoriaService;
use App\Services\ClienteService;

class AsignacionController extends Controller
{
    public function transferirInpro(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Transferencia a INPRO');
        $inproId = (int) ($_POST['responsable_inpro_id'] ?? 0);

        $cliente = (new ClienteModel())->findById($clienteId);
        $svc = new ClienteService();

        if (!$cliente || !$svc->canTransferirAInpro($user, $cliente)) {
            flash('error', 'No puedes transferir este cliente a INPRO.');
            redirect('clientes');
        }

        if ($inproId <= 0) {
            flash('error', 'Debes seleccionar un responsable INPRO.');
            redirect('clientes/ver?id=' . $clienteId);
        }

        $responsable = (new UsuarioModel())->findById($inproId);
        if (!$responsable || $responsable['rol'] !== 'inpro') {
            flash('error', 'El responsable INPRO seleccionado no es válido.');
            redirect('clientes/ver?id=' . $clienteId);
        }

        (new AsignacionModel())->transferirAInpro($clienteId, $inproId, (int) $user['id'], $motivo);
        (new AuditoriaService())->log((int) $user['id'], 'clientes', $clienteId, 'transferencia_inpro', [
            'responsable_inpro_id' => $inproId,
            'motivo' => $motivo,
        ]);
        flash('success', 'Cliente transferido a INPRO. La empresa colaboradora queda en solo lectura.');
        redirect('clientes/ver?id=' . $clienteId);
    }

    public function transferirEmpresa(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Transferencia a empresa colaboradora');
        $empresaUserId = (int) ($_POST['responsable_empresa_id'] ?? 0);

        $cliente = (new ClienteModel())->findById($clienteId);
        $svc = new ClienteService();

        if (!$cliente || !$svc->canTransferirAEmpresa($user, $cliente)) {
            flash('error', 'No puedes transferir este cliente a la empresa colaboradora.');
            redirect('clientes');
        }

        if ($empresaUserId <= 0) {
            flash('error', 'Debes seleccionar un responsable de la empresa colaboradora.');
            redirect('clientes/ver?id=' . $clienteId);
        }

        $responsable = (new UsuarioModel())->findById($empresaUserId);
        if (
            !$responsable
            || $responsable['rol'] !== 'empresa'
            || (int) $responsable['empresa_colaboradora_id'] !== (int) $cliente['empresa_colaboradora_id']
        ) {
            flash('error', 'El responsable de empresa seleccionado no es válido.');
            redirect('clientes/ver?id=' . $clienteId);
        }

        (new AsignacionModel())->transferirAEmpresa($clienteId, $empresaUserId, (int) $user['id'], $motivo);
        (new AuditoriaService())->log((int) $user['id'], 'clientes', $clienteId, 'transferencia_empresa', [
            'responsable_empresa_id' => $empresaUserId,
            'motivo' => $motivo,
        ]);
        flash('success', 'Cliente transferido a la empresa colaboradora. INPRO mantiene visibilidad total.');
        redirect('clientes/ver?id=' . $clienteId);
    }
}