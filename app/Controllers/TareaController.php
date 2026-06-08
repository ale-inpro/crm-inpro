<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ClienteModel;
use App\Models\TareaModel;
use App\Services\AuditoriaService;
use App\Services\ClienteService;

class TareaController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $estado = $_GET['estado'] ?? null;
        if ($estado !== null && !in_array($estado, ['pendiente', 'completada'], true)) {
            $estado = null;
        }

        $this->view('tareas/index', [
            'title' => 'Tareas',
            'tareas' => (new TareaModel())->listForUser($user, $estado),
            'filtroEstado' => $estado,
            'breadcrumbs' => [['label' => 'Tareas']],
        ]);
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente = (new ClienteModel())->findById($clienteId);

        if (!$cliente || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes crear tareas en este cliente.');
            redirect('clientes');
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $fecha = $_POST['fecha_vencimiento'] ?? '';
        $asignadoId = (int) ($_POST['asignado_a_id'] ?? $user['id']);

        if ($titulo === '' || $fecha === '') {
            flash('error', 'Título y fecha de vencimiento son obligatorios.');
            redirect('clientes/ver?id=' . $clienteId);
        }

        $prioridad = $_POST['prioridad'] ?? 'media';
        if (!in_array($prioridad, ['baja', 'media', 'alta'], true)) {
            $prioridad = 'media';
        }

        (new TareaModel())->create([
            'cliente_id' => $clienteId,
            'asignado_a_id' => $asignadoId,
            'creado_por_id' => (int) $user['id'],
            'titulo' => $titulo,
            'descripcion' => trim($_POST['descripcion'] ?? '') ?: null,
            'fecha_vencimiento' => $fecha,
            'prioridad' => $prioridad,
        ]);

        (new AuditoriaService())->log((int) $user['id'], 'tareas', $clienteId, 'tarea_creada', [
            'titulo' => $titulo,
        ]);

        flash('success', 'Tarea creada correctamente.');
        redirect('clientes/ver?id=' . $clienteId);
    }

    public function completar(): void
    {
        $user = $this->requireAuth();
        $tareaId = (int) ($_POST['tarea_id'] ?? 0);

        $tarea = (new TareaModel())->findById($tareaId);
        if (!$tarea) {
            flash('error', 'Tarea no encontrada.');
            redirect('tareas');
        }

        if ($user['rol'] === 'empresa' && (int) $tarea['asignado_a_id'] !== (int) $user['id']) {
            flash('error', 'No puedes completar esta tarea.');
            redirect('tareas');
        }

        (new TareaModel())->completar($tareaId);
        (new AuditoriaService())->log((int) $user['id'], 'tareas', $tareaId, 'tarea_completada');

        flash('success', 'Tarea completada.');
        redirect($_POST['redirect'] ?? 'tareas');
    }
}
