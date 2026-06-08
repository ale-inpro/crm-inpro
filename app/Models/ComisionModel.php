<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ComisionModel extends Model
{
    public function listForUser(array $user): array
    {
        $sql = '
            SELECT co.*, c.razon_social, rc.nombre AS regla_nombre, ec.nombre AS empresa_nombre
            FROM comisiones co
            JOIN ventas v ON v.id = co.venta_id
            JOIN clientes c ON c.id = v.cliente_id
            JOIN reglas_comision rc ON rc.id = co.regla_comision_id
            JOIN empresas_colaboradoras ec ON ec.id = co.empresa_colaboradora_id
        ';
        $params = [];

        if ($user['rol'] === 'empresa') {
            $sql .= ' WHERE co.empresa_colaboradora_id = ?';
            $params[] = $user['empresa_colaboradora_id'];
        }

        $sql .= ' ORDER BY co.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}