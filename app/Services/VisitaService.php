<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\ClienteModel;
use App\Models\TareaModel;
use App\Models\VisitaModel;

class VisitaService
{
    public function registrar(array $user, array $input): int
    {
        $clienteId = (int) $input['cliente_id'];
        $fecha = $input['fecha_visita'];
        $modo = $input['modo'] ?? 'realizada'; // programar | realizada
        $esPrimera = !empty($input['es_primera_visita']);
        $esFutura = strtotime($fecha) > time();

        if ($modo === 'programar' || ($esFutura && $modo !== 'realizada')) {
            $estado = 'programada';
        } else {
            $estado = 'realizada';
        }

        $permitidos = ['interesado', 'muy_interesado', 'neutral', 'reagendar', 'sin_interes', 'no_interes'];
        $resultado  = null;
        if ($estado === 'realizada') {
            $r = $input['resultado'] ?? 'neutral';
            $resultado = in_array($r, $permitidos, true) ? $r : 'neutral';
        }

        $db = Database::connection();
        $stmt = $db->prepare('
            INSERT INTO visitas (cliente_id, usuario_id, tipo, estado, es_primera_visita, fecha_visita,
                duracion_min, resultado, notas, es_remota)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ');
        $stmt->execute([
            $clienteId,
            (int) $user['id'],
            $esPrimera ? 'primera_visita' : ($input['tipo'] ?? 'seguimiento'),
            $estado,
            $esPrimera ? 1 : 0,
            $fecha,
            $input['duracion_min'] ?? null,
            $resultado,
            $input['notas'] ?? null,
            !empty($input['es_remota']) ? 1 : 0,
        ]);
        $visitaId = (int) $db->lastInsertId();

        if ($estado === 'programada') {
            $cliente = (new ClienteModel())->findById($clienteId);
            $titulo = 'Visita: ' . ($cliente['razon_social'] ?? 'Cliente');
            (new TareaModel())->create([
                'cliente_id' => $clienteId,
                'visita_id' => $visitaId,
                'tipo' => 'visita',
                'asignado_a_id' => (int) $user['id'],
                'creado_por_id' => (int) $user['id'],
                'titulo' => $titulo,
                'descripcion' => $input['notas'] ?? null,
                'fecha_vencimiento' => date('Y-m-d', strtotime($fecha)),
                'fecha_hora' => $fecha,
                'prioridad' => 'alta',
            ]);
        }

        if ($estado === 'realizada' && $esPrimera) {
            (new VisitaModel())->markPrimeraVisita($clienteId, (int) $user['id'], $fecha);
        }

        (new AuditoriaService())->log((int) $user['id'], 'visitas', $visitaId, 'visita_' . $estado);

        return $visitaId;
    }

    public function marcarRealizada(int $visitaId, array $user, array $input = []): void
    {
        $visita = (new VisitaModel())->findById($visitaId);
        if (!$visita || $visita['estado'] !== 'programada') {
            throw new \RuntimeException('Visita no válida.');
        }

        $cliente = (new ClienteModel())->findById((int) $visita['cliente_id']);
        if (!(new ClienteService())->canEdit($user, $cliente)) {
            throw new \RuntimeException('Sin permiso.');
        }

        Database::connection()->prepare("
            UPDATE visitas SET estado = 'realizada', resultado = ?, notas = CONCAT(COALESCE(notas,''), ?)
            WHERE id = ?
        ")->execute([
            $input['resultado'] ?? 'interesado',
            !empty($input['notas_extra']) ? "\n" . $input['notas_extra'] : '',
            $visitaId,
        ]);

        if ($visita['es_primera_visita']) {
            (new VisitaModel())->markPrimeraVisita((int) $visita['cliente_id'], (int) $visita['usuario_id'], $visita['fecha_visita']);
        }

        (new TareaModel())->completarByVisitaId($visitaId);
        (new AuditoriaService())->log((int) $user['id'], 'visitas', $visitaId, 'visita_realizada');
    }

    public function cancelar(int $visitaId, array $user): void
    {
        $visita = (new VisitaModel())->findById($visitaId);
        if (!$visita) {
            throw new \RuntimeException('Visita no encontrada.');
        }
        $cliente = (new ClienteModel())->findById((int) $visita['cliente_id']);
        if (!(new ClienteService())->canEdit($user, $cliente)) {
            throw new \RuntimeException('Sin permiso.');
        }
        Database::connection()->prepare("UPDATE visitas SET estado = 'cancelada' WHERE id = ?")->execute([$visitaId]);
        (new TareaModel())->cancelarByVisitaId($visitaId);
    }

    public function reagendar(int $visitaId, array $user, string $nuevaFecha): void
    {
        $visita = (new VisitaModel())->findById($visitaId);
        $cliente = (new ClienteModel())->findById((int) $visita['cliente_id']);
        if (!(new ClienteService())->canEdit($user, $cliente)) {
            throw new \RuntimeException('Sin permiso.');
        }
        $db = Database::connection();
        $db->prepare('UPDATE visitas SET fecha_visita = ? WHERE id = ?')->execute([$nuevaFecha, $visitaId]);
        $db->prepare('
            UPDATE tareas SET fecha_vencimiento = ?, fecha_hora = ?
            WHERE visita_id = ? AND estado = \'pendiente\'
        ')->execute([
            date('Y-m-d', strtotime($nuevaFecha)),
            $nuevaFecha,
            $visitaId,
        ]);
    }
}