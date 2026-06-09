<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\CatalogoModel;
use App\Models\ClienteModel;
use App\Models\TareaModel;
use App\Services\AuditoriaService;
use App\Services\ClienteService;

class TareaController extends Controller
{
    public function index(): void
    {
        $user      = $this->requireAuth();
        $vista     = $_GET['vista'] ?? 'lista';
        $clienteId = !empty($_GET['cliente_id']) ? (int) $_GET['cliente_id'] : null;
        $model     = new TareaModel();
        $todas     = $model->listForUser($user, null, $clienteId);

        $this->view('tareas/index', [
            'title'    => 'Tareas',
            'vista'    => $vista,
            'clienteId' => $clienteId,
            'clientes' => (new ClienteModel())->optionsForUser($user),
            'tareas'   => $model->listForUser($user, $_GET['estado'] ?? null, $clienteId),
            'columnas' => [
                'pendiente'  => array_values(array_filter($todas, fn($t) => $t['estado'] === 'pendiente')),
                'completada' => array_values(array_filter($todas, fn($t) => $t['estado'] === 'completada')),
                'cancelada'  => array_values(array_filter($todas, fn($t) => $t['estado'] === 'cancelada')),
            ],
            'usuariosAsignables' => is_inpro()
                ? array_merge((new CatalogoModel())->usuariosInpro(), (new CatalogoModel())->usuariosEmpresa(0))
                : [['id' => $user['id'], 'nombre' => $user['nombre']]],
            'breadcrumbs' => [['label' => 'Tareas']],
            'backUrl' => 'dashboard',
            'backLabel' => 'Inicio',
        ]);
    }

    public function mover(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        try {
            (new \App\Services\TareaService())->cambiarEstado($user, (int) $_POST['tarea_id'], $_POST['estado']);
            $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function destacar(): void
    {
        csrf_verify();
        $user    = $this->requireAuth();
        $tareaId = (int) ($_POST['tarea_id'] ?? 0);
        $tarea   = (new TareaModel())->findById($tareaId);
        if (!$tarea) {
            $this->json(['ok' => false, 'error' => 'Tarea no encontrada'], 404);
            return;
        }
        $cliente = (new ClienteModel())->findById((int) $tarea['cliente_id']);
        $puede   = $cliente && (
            (new ClienteService())->canEdit($user, $cliente)
            || ($user['rol'] === 'empresa' && (int) $tarea['asignado_a_id'] === (int) $user['id'])
        );
        if (!$puede) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
            return;
        }
        $destacada = (new TareaModel())->toggleDestacada($tareaId);
        $this->json(['ok' => true, 'destacada' => $destacada]);
    }

    public function actualizar(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        $id   = (int) $_POST['tarea_id'];
        (new \App\Services\TareaService())->actualizar($user, $id, [
            'titulo'           => trim($_POST['titulo']),
            'descripcion'      => trim($_POST['descripcion'] ?? '') ?: null,
            'fecha_vencimiento' => $_POST['fecha_vencimiento'],
            'fecha_hora'       => $_POST['fecha_hora'] ?? $_POST['fecha_vencimiento'] . ' 09:00:00',
            'prioridad'        => $_POST['prioridad'],
            'asignado_a_id'    => (int) $_POST['asignado_a_id'],
        ]);
        flash('success', 'Tarea actualizada.');
        redirect($_POST['redirect'] ?? 'tareas');
    }

    public function eliminar(): void
    {
        csrf_verify();
        $user    = $this->requireAuth();
        $tareaId = (int) ($_POST['tarea_id'] ?? 0);
        $tarea   = (new TareaModel())->findById($tareaId);
        if (!$tarea) {
            flash('error', 'Tarea no encontrada.');
            redirect($_POST['redirect'] ?? 'tareas');
        }
        $cliente = (new ClienteModel())->findById((int) $tarea['cliente_id']);
        if (!(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'Sin permiso para eliminar esta tarea.');
            redirect($_POST['redirect'] ?? 'tareas');
        }
        Database::connection()->prepare('DELETE FROM tareas WHERE id = ?')->execute([$tareaId]);
        (new AuditoriaService())->log((int) $user['id'], 'tareas', $tareaId, 'tarea_eliminada');
        flash('success', 'Tarea eliminada.');
        redirect($_POST['redirect'] ?? 'tareas');
    }

    public function cancelar(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        (new \App\Services\TareaService())->cambiarEstado($user, (int) $_POST['tarea_id'], 'cancelada');
        flash('success', 'Tarea cancelada.');
        redirect($_POST['redirect'] ?? 'tareas');
    }

    public function store(): void
    {
        csrf_verify();
        $user      = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente   = (new ClienteModel())->findById($clienteId);

        if (!$cliente || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes crear tareas en este cliente.');
            redirect('clientes');
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $fecha  = $_POST['fecha_vencimiento'] ?? '';

        if ($titulo === '' || $fecha === '') {
            flash('error', 'Título y fecha de vencimiento son obligatorios.');
            redirect('clientes/ver?id=' . $clienteId . '#tabTareas');
        }

        $prioridad = $_POST['prioridad'] ?? 'media';
        if (!in_array($prioridad, ['baja', 'media', 'alta'], true)) {
            $prioridad = 'media';
        }

        (new TareaModel())->create([
            'cliente_id'    => $clienteId,
            'asignado_a_id' => (int) ($_POST['asignado_a_id'] ?? $user['id']),
            'creado_por_id' => (int) $user['id'],
            'titulo'        => $titulo,
            'descripcion'   => trim($_POST['descripcion'] ?? '') ?: null,
            'fecha_vencimiento' => $fecha,
            'prioridad'     => $prioridad,
        ]);

        (new AuditoriaService())->log((int) $user['id'], 'tareas', $clienteId, 'tarea_creada', ['titulo' => $titulo]);

        flash('success', 'Tarea creada correctamente.');
        redirect('clientes/ver?id=' . $clienteId . '#tabTareas');
    }

    public function completar(): void
    {
        csrf_verify();
        $user    = $this->requireAuth();
        $tareaId = (int) ($_POST['tarea_id'] ?? 0);
        $tarea   = (new TareaModel())->findById($tareaId);

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
