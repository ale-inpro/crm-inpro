<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ClienteModel;
use App\Models\TareaModel;

class TareaService
{
    public function cambiarEstado(array $user, int $tareaId, string $estado): void
    {
        if (!in_array($estado, ['pendiente', 'completada', 'cancelada'], true)) {
            throw new \RuntimeException('Estado no válido.');
        }
        $tarea = (new TareaModel())->findById($tareaId);
        if (!$tarea) {
            throw new \RuntimeException('Tarea no encontrada.');
        }
        $cliente = (new ClienteModel())->findById((int) $tarea['cliente_id']);
        if ($user['rol'] === 'empresa' && (int) $tarea['asignado_a_id'] !== (int) $user['id']) {
            throw new \RuntimeException('Sin permiso.');
        }
        if (!(new ClienteService())->canEdit($user, $cliente) && $user['rol'] !== 'empresa') {
            throw new \RuntimeException('Sin permiso.');
        }
        (new TareaModel())->updateEstado($tareaId, $estado);
        (new AuditoriaService())->log((int) $user['id'], 'tareas', $tareaId, 'tarea_' . $estado);
    }

    public function actualizar(array $user, int $tareaId, array $data): void
    {
        $tarea = (new TareaModel())->findById($tareaId);
        if (!$tarea) {
            throw new \RuntimeException('Tarea no encontrada.');
        }
        $cliente = (new ClienteModel())->findById((int) $tarea['cliente_id']);
        if (!(new ClienteService())->canEdit($user, $cliente)) {
            throw new \RuntimeException('Sin permiso.');
        }
        (new TareaModel())->update($tareaId, $data);
    }
}