<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class TareaModel extends Model
{
    public function listForUser(array $user, ?string $estado = null, ?int $clienteId = null): array
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
        if ($clienteId) {
            $sql .= ' AND t.cliente_id = ?';
            $params[] = $clienteId;
        }
        if ($estado) {
            $sql .= ' AND t.estado = ?';
            $params[] = $estado;
        }

        $sql .= " ORDER BY t.destacada DESC, FIELD(t.prioridad,'alta','media','baja'), t.fecha_vencimiento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT t.*, c.razon_social, u.nombre AS asignado_nombre
            FROM tareas t
            JOIN clientes c ON c.id = t.cliente_id
            JOIN usuarios u ON u.id = t.asignado_a_id
            WHERE t.id = ? LIMIT 1
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO tareas (cliente_id, visita_id, tipo, asignado_a_id, creado_por_id, titulo, descripcion, fecha_vencimiento, fecha_hora, prioridad)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ');
        $stmt->execute([
            $data['cliente_id'],
            $data['visita_id'] ?? null,
            $data['tipo'] ?? 'general',
            $data['asignado_a_id'],
            $data['creado_por_id'],
            $data['titulo'],
            $data['descripcion'] ?? null,
            $data['fecha_vencimiento'],
            $data['fecha_hora'] ?? ($data['fecha_vencimiento'] . ' 09:00:00'),
            $data['prioridad'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function completar(int $id): void
    {
        $this->db->prepare("UPDATE tareas SET estado = 'completada', completada_at = NOW() WHERE id = ?")->execute([$id]);
    }

    public function updateEstado(int $id, string $estado): void
    {
        $extra = $estado === 'completada' ? ', completada_at = NOW()' : '';
        $this->db->prepare("UPDATE tareas SET estado = ? $extra WHERE id = ?")->execute([$estado, $id]);
    }

    public function update(int $id, array $data): void
    {
        $this->db->prepare('
            UPDATE tareas SET titulo = ?, descripcion = ?, fecha_vencimiento = ?, fecha_hora = ?, prioridad = ?, asignado_a_id = ?
            WHERE id = ?
        ')->execute([
            $data['titulo'],
            $data['descripcion'] ?? null,
            $data['fecha_vencimiento'],
            $data['fecha_hora'] ?? ($data['fecha_vencimiento'] . ' 09:00:00'),
            $data['prioridad'],
            $data['asignado_a_id'],
            $id,
        ]);
    }

    public function toggleDestacada(int $id): bool
    {
        $this->db->prepare('UPDATE tareas SET destacada = IF(destacada=1,0,1) WHERE id = ?')->execute([$id]);
        $stmt = $this->db->prepare('SELECT destacada FROM tareas WHERE id = ?');
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    public function byCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, u.nombre AS asignado_nombre FROM tareas t
            JOIN usuarios u ON u.id = t.asignado_a_id
            WHERE t.cliente_id = ? ORDER BY t.destacada DESC, FIELD(t.prioridad,'alta','media','baja'), t.estado ASC, t.fecha_vencimiento ASC
        ");
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public function completarByVisitaId(int $visitaId): void
    {
        $this->db->prepare("UPDATE tareas SET estado = 'completada', completada_at = NOW() WHERE visita_id = ? AND estado = 'pendiente'")->execute([$visitaId]);
    }

    public function cancelarByVisitaId(int $visitaId): void
    {
        $this->db->prepare("UPDATE tareas SET estado = 'cancelada' WHERE visita_id = ? AND estado = 'pendiente'")->execute([$visitaId]);
    }

    public function countPendienteForUser(array $user): int
    {
        $sql = "SELECT COUNT(*) FROM tareas t JOIN clientes c ON c.id = t.cliente_id WHERE c.deleted_at IS NULL AND t.estado = 'pendiente'";
        $params = [];
        if ($user['rol'] === 'empresa') {
            $sql .= ' AND t.asignado_a_id = ?';
            $params[] = $user['id'];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function forCalendario(array $user, ?int $clienteId = null): array
    {
        $sql = "
            SELECT t.id, t.titulo, t.descripcion, t.fecha_hora, t.estado, t.prioridad, t.destacada,
                   t.cliente_id, c.razon_social, u.nombre AS asignado_nombre, 'tarea' AS evento_tipo
            FROM tareas t JOIN clientes c ON c.id = t.cliente_id
            JOIN usuarios u ON u.id = t.asignado_a_id
            WHERE c.deleted_at IS NULL AND t.estado = 'pendiente' AND t.fecha_hora IS NOT NULL
        ";
        $params = [];
        if ($clienteId) {
            $sql .= ' AND t.cliente_id = ?';
            $params[] = $clienteId;
        }
        if ($user['rol'] === 'empresa') {
            $sql .= ' AND t.asignado_a_id = ?';
            $params[] = $user['id'];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
