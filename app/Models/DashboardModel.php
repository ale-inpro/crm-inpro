<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class DashboardModel extends Model
{
    public function statsInpro(): array
    {
        return [
            'clientes' => (int) $this->db->query('SELECT COUNT(*) FROM clientes WHERE deleted_at IS NULL')->fetchColumn(),
            'sin_primera_visita' => (int) $this->db->query('SELECT COUNT(*) FROM clientes WHERE primera_visita_realizada = 0 AND deleted_at IS NULL')->fetchColumn(),
            'ventas_pendientes' => (int) $this->db->query("SELECT COUNT(*) FROM ventas WHERE estado = 'pendiente_validacion'")->fetchColumn(),
            'comisiones_pendientes' => (int) $this->db->query("SELECT COUNT(*) FROM comisiones WHERE estado IN ('calculada','pendiente_validacion')")->fetchColumn(),
        ];
    }

    public function statsEmpresa(int $empresaColaboradoraId, int $usuarioId): array
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM clientes
            WHERE deleted_at IS NULL
              AND empresa_colaboradora_id = ?
              AND (responsable_empresa_id = ? OR creado_por_usuario_id = ?)
        ');
        $stmt->execute([$empresaColaboradoraId, $usuarioId, $usuarioId]);

        $stmt2 = $this->db->prepare('
            SELECT COUNT(*) FROM clientes
            WHERE deleted_at IS NULL AND empresa_colaboradora_id = ?
              AND primera_visita_realizada = 0
        ');
        $stmt2->execute([$empresaColaboradoraId]);

        $stmt3 = $this->db->prepare('
            SELECT COALESCE(SUM(importe_comision_eur), 0) FROM comisiones
            WHERE empresa_colaboradora_id = ? AND estado = \'aprobada\'
        ');
        $stmt3->execute([$empresaColaboradoraId]);

        return [
            'mis_clientes' => (int) $stmt->fetchColumn(),
            'sin_primera_visita' => (int) $stmt2->fetchColumn(),
            'comisiones_aprobadas' => (float) $stmt3->fetchColumn(),
        ];
    }

    public function tareasUrgentes(array $user, int $limit = 8): array
    {
        $sql = '
            SELECT t.*, c.razon_social
            FROM tareas t
            JOIN clientes c ON c.id = t.cliente_id
            WHERE t.estado = \'pendiente\'
              AND c.deleted_at IS NULL
              AND t.fecha_vencimiento <= CURDATE()
        ';
        $params = [];

        if ($user['rol'] === 'empresa') {
            $sql .= ' AND t.asignado_a_id = ?';
            $params[] = $user['id'];
        }

        $sql .= ' ORDER BY t.fecha_vencimiento ASC LIMIT ' . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function tareasPendientes(array $user, int $limit = 5): array
    {
        $sql = '
            SELECT t.*, c.razon_social
            FROM tareas t
            JOIN clientes c ON c.id = t.cliente_id
            WHERE t.estado = \'pendiente\' AND c.deleted_at IS NULL
        ';
        $params = [];

        if ($user['rol'] === 'empresa') {
            $sql .= ' AND t.asignado_a_id = ?';
            $params[] = $user['id'];
        }

        $sql .= ' ORDER BY t.fecha_vencimiento ASC LIMIT ' . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function tareasVencidasCount(array $user): int
    {
        $sql = 'SELECT COUNT(*) FROM tareas t JOIN clientes c ON c.id = t.cliente_id
                WHERE t.estado = \'pendiente\' AND t.fecha_vencimiento < CURDATE() AND c.deleted_at IS NULL';
        $params = [];
        if ($user['rol'] === 'empresa') {
            $sql .= ' AND t.asignado_a_id = ?';
            $params[] = $user['id'];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function clientesPorEstado(): array
    {
        return $this->db->query('
            SELECT ep.nombre, ep.color_hex, COUNT(c.id) AS total
            FROM estados_pipeline ep
            LEFT JOIN clientes c ON c.estado_pipeline_id = ep.id AND c.deleted_at IS NULL
            WHERE ep.activo = 1 AND ep.es_final = 0
            GROUP BY ep.id ORDER BY ep.orden
        ')->fetchAll();
    }
}