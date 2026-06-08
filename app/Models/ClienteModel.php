<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ClienteModel extends Model
{
    public function listForUser(array $user, array $filtros = []): array
    {
        $sql = '
            SELECT c.*, ep.nombre AS estado_nombre, ep.color_hex,
                   ec.nombre AS empresa_colaboradora_nombre,
                   ui.nombre AS responsable_inpro_nombre,
                   ue.nombre AS responsable_empresa_nombre
            FROM clientes c
            JOIN estados_pipeline ep ON ep.id = c.estado_pipeline_id
            LEFT JOIN empresas_colaboradoras ec ON ec.id = c.empresa_colaboradora_id
            LEFT JOIN usuarios ui ON ui.id = c.responsable_inpro_id
            LEFT JOIN usuarios ue ON ue.id = c.responsable_empresa_id
            WHERE c.deleted_at IS NULL
        ';
        $params = [];

        if ($user['rol'] === 'empresa') {
            $sql .= ' AND c.empresa_colaboradora_id = ?';
            $params[] = $user['empresa_colaboradora_id'];
        }

        if (!empty($filtros['sin_primera_visita'])) {
            $sql .= ' AND c.primera_visita_realizada = 0';
        }
        if (!empty($filtros['empresa_colaboradora_id'])) {
            $sql .= ' AND c.empresa_colaboradora_id = ?';
            $params[] = $filtros['empresa_colaboradora_id'];
        }
        if (!empty($filtros['estado_pipeline_id'])) {
            $sql .= ' AND c.estado_pipeline_id = ?';
            $params[] = $filtros['estado_pipeline_id'];
        }

        $sql .= ' ORDER BY c.updated_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT c.*, ep.nombre AS estado_nombre, ep.color_hex,
                   ec.nombre AS empresa_colaboradora_nombre
            FROM clientes c
            JOIN estados_pipeline ep ON ep.id = c.estado_pipeline_id
            LEFT JOIN empresas_colaboradoras ec ON ec.id = c.empresa_colaboradora_id
            WHERE c.id = ? AND c.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function existsCif(string $cifNormalizado, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM clientes WHERE cif_normalizado = ? AND deleted_at IS NULL';
        $params = [$cifNormalizado];
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO clientes (
                razon_social, nombre_comercial, cif, cif_normalizado,
                ciudad, provincia, telefono_principal, email_principal,
                estado_pipeline_id, empresa_colaboradora_id,
                responsable_inpro_id, responsable_empresa_id,
                modo_acceso_empresa, origen_lead, creado_por_usuario_id
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ');
        $stmt->execute([
            $data['razon_social'], $data['nombre_comercial'], $data['cif'], $data['cif_normalizado'],
            $data['ciudad'], $data['provincia'], $data['telefono_principal'], $data['email_principal'],
            $data['estado_pipeline_id'], $data['empresa_colaboradora_id'],
            $data['responsable_inpro_id'], $data['responsable_empresa_id'],
            $data['modo_acceso_empresa'], $data['origen_lead'], $data['creado_por_usuario_id'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function timeline(int $clienteId): array
    {
        $items = [];

        $visitas = $this->db->prepare('
            SELECT v.*, u.nombre AS usuario_nombre FROM visitas v
            JOIN usuarios u ON u.id = v.usuario_id
            WHERE v.cliente_id = ? ORDER BY v.fecha_visita DESC
        ');
        $visitas->execute([$clienteId]);
        foreach ($visitas->fetchAll() as $v) {
            $items[] = ['tipo' => 'visita', 'fecha' => $v['fecha_visita'], 'data' => $v];
        }

        $notas = $this->db->prepare('
            SELECT n.*, u.nombre AS usuario_nombre FROM notas n
            JOIN usuarios u ON u.id = n.usuario_id
            WHERE n.cliente_id = ? ORDER BY n.created_at DESC
        ');
        $notas->execute([$clienteId]);
        foreach ($notas->fetchAll() as $n) {
            if (!is_inpro() && $n['visibilidad'] === 'solo_inpro') continue;
            $items[] = ['tipo' => 'nota', 'fecha' => $n['created_at'], 'data' => $n];
        }

        $asig = $this->db->prepare('
            SELECT a.*, u.nombre AS realizado_por_nombre FROM asignaciones a
            JOIN usuarios u ON u.id = a.realizado_por_id
            WHERE a.cliente_id = ? ORDER BY a.created_at DESC
        ');
        $asig->execute([$clienteId]);
        foreach ($asig->fetchAll() as $a) {
            $items[] = ['tipo' => 'asignacion', 'fecha' => $a['created_at'], 'data' => $a];
        }

        usort($items, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));
        return $items;
    }
}