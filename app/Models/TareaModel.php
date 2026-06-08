<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class TareaModel extends Model
{
    public function listForUser(array $user, ?string $estado = null): array
    {
        $sql = '
            SELECT t.*, c.razon_social, u.nombre AS asignado_nombre
            FROM tareas t
            JOIN clientes c ON c.id = t.cliente_id
            JOIN usuarios u ON u.id = t.asignado_a_id
            WHERE c.deleted_at IS NULL
        ';
        $params = [];

        if ($user['rol'] === 'empresa') {
            $sql .= ' AND t.asignado_a_id = ?';
            $params[] = $user['id'];
        }
        if ($estado) {
            $sql .= ' AND t.estado = ?';
            $params[] = $estado;
        }

        $sql .= ' ORDER BY t.fecha_vencimiento ASC, t.prioridad DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO tareas (cliente_id, asignado_a_id, creado_por_id, titulo, descripcion, fecha_vencimiento, prioridad)
            VALUES (?,?,?,?,?,?,?)
        ');
        $stmt->execute([
            $data['cliente_id'], $data['asignado_a_id'], $data['creado_por_id'],
            $data['titulo'], $data['descripcion'], $data['fecha_vencimiento'], $data['prioridad'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function completar(int $id): void
    {
        $this->db->prepare("UPDATE tareas SET estado = 'completada', completada_at = NOW() WHERE id = ?")->execute([$id]);
    }

    public function byCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare('
            SELECT t.*, u.nombre AS asignado_nombre FROM tareas t
            JOIN usuarios u ON u.id = t.asignado_a_id
            WHERE t.cliente_id = ? ORDER BY t.estado ASC, t.fecha_vencimiento ASC
        ');
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tareas WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}