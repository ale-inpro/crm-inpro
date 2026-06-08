<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class VisitaModel extends Model
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT v.*, u.nombre AS usuario_nombre FROM visitas v JOIN usuarios u ON u.id = v.usuario_id WHERE v.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function programadasByCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare("SELECT v.*, u.nombre AS usuario_nombre FROM visitas v JOIN usuarios u ON u.id = v.usuario_id WHERE v.cliente_id = ? AND v.estado = 'programada' ORDER BY v.fecha_visita ASC");
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
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

    public function forCalendario(array $user, ?int $clienteId = null): array
    {
        $sql = "
            SELECT v.id, v.fecha_visita AS fecha_hora, v.tipo, v.cliente_id, c.razon_social, 'visita' AS evento_tipo
            FROM visitas v JOIN clientes c ON c.id = v.cliente_id
            WHERE v.estado = 'programada' AND c.deleted_at IS NULL
        ";
        $params = [];
        if ($clienteId) { $sql .= ' AND v.cliente_id = ?'; $params[] = $clienteId; }
        if ($user['rol'] === 'empresa') {
            $sql .= ' AND c.empresa_colaboradora_id = ?';
            $params[] = $user['empresa_colaboradora_id'];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}