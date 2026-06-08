<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ClienteModel;
use App\Models\ContactoModel;
use App\Models\VisitaModel;
use App\Services\ClienteService;
use App\Services\ResendService;
use App\Services\VisitaService;

class VisitaController extends Controller
{
    public function index(): void { $this->requireAuth(); redirect('clientes'); }

    public function store(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente = (new ClienteModel())->findById($clienteId);
        if (!$cliente || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes registrar visitas en este cliente.');
            redirect('clientes');
        }

        try {
            (new VisitaService())->registrar($user, [
                'cliente_id' => $clienteId,
                'modo' => $_POST['modo'] ?? 'realizada',
                'fecha_visita' => $_POST['fecha_visita'] ?? date('Y-m-d H:i:s'),
                'es_primera_visita' => isset($_POST['es_primera_visita']),
                'tipo' => $_POST['tipo'] ?? 'seguimiento',
                'resultado' => $_POST['resultado'] ?? 'neutral',
                'notas' => trim($_POST['notas'] ?? '') ?: null,
                'es_remota' => isset($_POST['es_remota']),
            ]);
        } catch (\Throwable $e) {
            flash('error', 'No se pudo guardar la visita: ' . $e->getMessage());
            redirect('clientes/ver?id=' . $clienteId . '#tabAgenda');
        }

        flash('success', ($_POST['modo'] ?? '') === 'programar' ? 'Visita programada.' : 'Visita registrada.');
        $hash = ($_POST['modo'] ?? '') === 'programar' ? '#tabAgenda' : '#tabHistorial';
        redirect('clientes/ver?id=' . $clienteId . $hash);
    }

    public function realizar(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        $id = (int) ($_POST['visita_id'] ?? 0);
        (new VisitaService())->marcarRealizada($id, $user, $_POST);
        flash('success', 'Visita marcada como realizada.');
        redirect($_POST['redirect'] ?? 'clientes/ver?id=' . (int) ($_POST['cliente_id'] ?? 0) . '#tabAgenda');
    }

    public function cancelar(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        (new VisitaService())->cancelar((int) $_POST['visita_id'], $user);
        flash('success', 'Visita cancelada.');
        redirect($_POST['redirect'] ?? 'clientes');
    }

    public function reagendar(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        (new VisitaService())->reagendar((int) $_POST['visita_id'], $user, $_POST['fecha_visita']);
        flash('success', 'Visita reagendada.');
        redirect($_POST['redirect'] ?? 'clientes');
    }

    public function recordatorio(): void
    {
        csrf_verify();
        $user     = $this->requireAuth();
        $visitaId = (int) $_POST['visita_id'];
        $visita   = (new VisitaModel())->findById($visitaId);
        $cliente  = $visita ? (new ClienteModel())->findById((int) $visita['cliente_id']) : null;

        if (!$visita || !$cliente || $visita['estado'] !== 'programada'
            || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes enviar este recordatorio.');
            redirect($cliente ? 'clientes/ver?id=' . $cliente['id'] . '#tabAgenda' : 'clientes');
        }

        $contactos = (new ContactoModel())->byCliente((int) $cliente['id']);
        $email = trim($_POST['destinatario'] ?? '') ?: ($contactos[0]['email'] ?? $cliente['email_principal'] ?? '');
        if ($email === '') {
            flash('error', 'No hay email de destino para el recordatorio.');
            redirect('clientes/ver?id=' . $cliente['id'] . '#tabAgenda');
        }

        $fechaFmt = date('d/m/Y \a \l\a\s H:i', strtotime($visita['fecha_visita']));
        $asunto   = trim($_POST['asunto'] ?? '')
            ?: 'Recordatorio de visita INPRO — ' . $cliente['razon_social'] . ' — ' . date('d/m/Y', strtotime($visita['fecha_visita']));

        $resend = new ResendService();
        $html   = $resend->plantillaRecordatorioVisita([
            'cliente_nombre'   => $cliente['razon_social'],
            'fecha_formateada' => $fechaFmt,
            'comercial_nombre' => $visita['usuario_nombre'] ?? $user['nombre'],
            'es_remota'        => !empty($visita['es_remota']),
            'mensaje_extra'    => trim($_POST['mensaje_extra'] ?? '') ?: null,
        ]);

        try {
            $resend->enviarRecordatorioVisita($user, $visitaId, $email, $asunto, $html);
        } catch (\Throwable $e) {
            flash('error', 'No se pudo enviar el recordatorio: ' . $e->getMessage());
            redirect('clientes/ver?id=' . $cliente['id'] . '#tabAgenda');
        }

        flash('success', 'Recordatorio enviado a ' . $email);
        redirect('clientes/ver?id=' . $cliente['id'] . '#tabAgenda');
    }
}
