<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class NotificacionService
{
    public function proximasVisitas(array $user, int $horas = 48): array
    {
        $db = Database::connection();
        $sql = "
            SELECT v.*, c.razon_social, u.nombre AS registrado_por, u.rol AS registrado_rol
            FROM visitas v
            JOIN clientes c ON c.id = v.cliente_id
            JOIN usuarios u ON u.id = v.usuario_id
            WHERE v.estado = 'programada'
              AND c.deleted_at IS NULL
              AND v.fecha_visita BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? HOUR)
        ";
        $params = [$horas];

        if ($user['rol'] === 'empresa') {
            $sql .= ' AND v.usuario_id = ?';
            $params[] = $user['id'];
        } else {
            $sql .= ' AND (c.responsable_inpro_id = ? OR v.usuario_id = ? OR u.rol = \'empresa\')';
            $params[] = $user['id'];
            $params[] = $user['id'];
        }

        $sql .= ' ORDER BY v.fecha_visita ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function visitasHoy(array $user): array
    {
        return array_filter(
            $this->proximasVisitas($user, 24),
            fn($v) => date('Y-m-d', strtotime($v['fecha_visita'])) === date('Y-m-d')
        );
    }
}