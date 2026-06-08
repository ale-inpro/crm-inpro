<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class VisitaModel extends Model
{
    public function create(array $data): void
    {
        $this->db->prepare('
            INSERT INTO visitas (cliente_id, usuario_id, tipo, es_primera_visita, fecha_visita, duracion_min, resultado, notas, es_remota)
            VALUES (?,?,?,?,?,?,?,?,?)
        ')->execute([
            $data['cliente_id'], $data['usuario_id'], $data['tipo'], $data['es_primera_visita'],
            $data['fecha_visita'], $data['duracion_min'], $data['resultado'], $data['notas'],
            $data['es_remota'] ?? 0,
        ]);
    }

    public function markPrimeraVisita(int $clienteId, int $usuarioId, string $fecha): void
    {
        $this->db->prepare('
            UPDATE clientes SET primera_visita_realizada = 1,
                primera_visita_fecha = ?, primera_visita_usuario_id = ?,
                estado_pipeline_id = (SELECT id FROM estados_pipeline WHERE codigo = \'primera_visita\' LIMIT 1)
            WHERE id = ? AND primera_visita_realizada = 0
        ')->execute([$fecha, $usuarioId, $clienteId]);
    }
}