<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class VentaModel extends Model
{
    private const SQL_CONCEPTO = "
        CASE
            WHEN v.num_obras IS NOT NULL THEN
                CONCAT(v.num_obras, ' obras — ', REPLACE(FORMAT(v.precio_mes_eur, 2), '.', ','), ' €/mes')
            ELSE '—'
        END AS concepto_venta
    ";

    public function listForUser(array $user): array
    {
        $sql = '
            SELECT v.*, c.razon_social, ' . self::SQL_CONCEPTO . ',
                   u.nombre AS registrado_por_nombre
            FROM ventas v
            JOIN clientes c ON c.id = v.cliente_id
            JOIN usuarios u ON u.id = v.registrado_por_id
            WHERE c.deleted_at IS NULL
        ';
        $params = [];

        if ($user['rol'] === 'empresa') {
            $sql .= ' AND c.empresa_colaboradora_id = ?';
            $params[] = $user['empresa_colaboradora_id'];
        }

        $sql .= ' ORDER BY v.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function byCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare('
            SELECT v.*, ' . self::SQL_CONCEPTO . ', u.nombre AS registrado_por_nombre
            FROM ventas v
            JOIN usuarios u ON u.id = v.registrado_por_id
            WHERE v.cliente_id = ?
            ORDER BY v.created_at DESC
        ');
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public function pendientesValidacion(): array
    {
        return $this->db->query('
            SELECT v.*, c.razon_social, ' . self::SQL_CONCEPTO . ',
                   u.nombre AS registrado_por_nombre,
                   ec.nombre AS empresa_colaboradora_nombre
            FROM ventas v
            JOIN clientes c ON c.id = v.cliente_id
            JOIN usuarios u ON u.id = v.registrado_por_id
            LEFT JOIN empresas_colaboradoras ec ON ec.id = c.empresa_colaboradora_id
            WHERE v.estado = \'pendiente_validacion\'
            ORDER BY v.created_at ASC
        ')->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT v.*, c.empresa_colaboradora_id, c.razon_social FROM ventas v JOIN clientes c ON c.id = v.cliente_id WHERE v.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO ventas (
                cliente_id, num_obras, tarifa_tramo_id,
                precio_mes_eur, stripe_price_id, registrado_por_id,
                importe_anual_eur, descuento_pct, importe_final_eur,
                fecha_propuesta, fecha_cierre, estado, atribucion_cierre, notas
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ');
        $stmt->execute([
            $data['cliente_id'],
            $data['num_obras'],
            $data['tarifa_tramo_id'],
            $data['precio_mes_eur'],
            $data['stripe_price_id'],
            $data['registrado_por_id'],
            $data['importe_anual_eur'],
            $data['descuento_pct'],
            $data['importe_final_eur'],
            $data['fecha_propuesta'],
            $data['fecha_cierre'],
            'pendiente_validacion',
            $data['atribucion_cierre'],
            $data['notas'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function validar(int $ventaId, int $inproUserId, int $reglaComisionId): void
    {
        $venta = $this->findById($ventaId);
        if (!$venta || $venta['estado'] !== 'pendiente_validacion') {
            return;
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare("
                UPDATE ventas SET estado = 'validada', validado_por_id = ?, validado_at = NOW(),
                    regla_comision_id = ?
                WHERE id = ?
            ")->execute([$inproUserId, $reglaComisionId, $ventaId]);

            $this->db->prepare("
                UPDATE clientes SET estado_pipeline_id = (SELECT id FROM estados_pipeline WHERE codigo = 'ganado' LIMIT 1)
                WHERE id = ?
            ")->execute([$venta['cliente_id']]);

            $regla = $this->db->prepare('SELECT * FROM reglas_comision WHERE id = ?');
            $regla->execute([$reglaComisionId]);
            $reglaRow = $regla->fetch();

            if ($venta['empresa_colaboradora_id'] && $reglaRow && (float) $reglaRow['porcentaje'] > 0) {
                $importe = (float) $venta['importe_final_eur'];
                $pct = (float) $reglaRow['porcentaje'];
                $this->db->prepare('
                    INSERT INTO comisiones (
                        venta_id, empresa_colaboradora_id, regla_comision_id,
                        base_importe_eur, porcentaje, importe_comision_eur, estado, aprobado_por_id, aprobado_at
                    ) VALUES (?,?,?,?,?,?,\'aprobada\',?,NOW())
                ')->execute([
                    $ventaId, $venta['empresa_colaboradora_id'], $reglaComisionId,
                    $importe, $pct, round($importe * $pct / 100, 2), $inproUserId,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
