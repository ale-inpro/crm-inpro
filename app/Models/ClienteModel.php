<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ClienteModel extends Model
{
    public function optionsForUser(array $user): array
    {
        $sql = 'SELECT c.id, c.razon_social FROM clientes c WHERE c.deleted_at IS NULL';
        $params = [];
        if ($user['rol'] === 'empresa') {
            $sql .= ' AND c.empresa_colaboradora_id = ?';
            $params[] = $user['empresa_colaboradora_id'];
        }
        $sql .= ' ORDER BY c.razon_social ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

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
        if (!empty($filtros['busqueda'])) {
            $sql .= ' AND (
                c.razon_social LIKE ? OR c.nombre_comercial LIKE ? OR c.ciudad LIKE ? OR c.cif LIKE ?
                OR EXISTS (
                    SELECT 1 FROM contactos ct
                    WHERE ct.cliente_id = c.id AND ct.activo = 1 AND ct.es_principal = 1
                      AND (
                          ct.nombre LIKE ? OR ct.email LIKE ? OR ct.telefono LIKE ?
                      )
                )
            )';
            $q = '%' . $filtros['busqueda'] . '%';
            array_push($params, $q, $q, $q, $q, $q, $q, $q);
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

        $visitas = $this->db->prepare("
            SELECT v.*, u.nombre AS usuario_nombre FROM visitas v
            JOIN usuarios u ON u.id = v.usuario_id
            WHERE v.cliente_id = ? AND v.estado IN ('realizada', 'cancelada')
            ORDER BY v.fecha_visita DESC
        ");
        $visitas->execute([$clienteId]);
        foreach ($visitas->fetchAll() as $v) {
            $items[] = ['tipo' => 'visita', 'fecha' => $v['fecha_visita'], 'data' => $v];
        }

        $tareas = $this->db->prepare("
            SELECT t.*, uc.nombre AS usuario_nombre
            FROM tareas t
            JOIN usuarios uc ON uc.id = t.creado_por_id
            WHERE t.cliente_id = ? AND t.estado IN ('completada', 'cancelada')
            ORDER BY COALESCE(t.completada_at, t.created_at) DESC
        ");
        $tareas->execute([$clienteId]);
        foreach ($tareas->fetchAll() as $t) {
            $fecha = $t['completada_at'] ?? $t['created_at'];
            $items[] = ['tipo' => 'tarea', 'fecha' => $fecha, 'data' => $t];
        }

        $ventas = $this->db->prepare("
            SELECT v.*, u.nombre AS usuario_nombre,
                CASE
                    WHEN v.num_obras IS NOT NULL THEN
                        CONCAT(v.num_obras, ' obras — ', REPLACE(FORMAT(v.precio_mes_eur, 2), '.', ','), ' €/mes')
                    ELSE 'Venta registrada'
                END AS concepto_venta
            FROM ventas v
            JOIN usuarios u ON u.id = v.registrado_por_id
            WHERE v.cliente_id = ?
            ORDER BY COALESCE(v.validado_at, v.created_at) DESC
        ");
        $ventas->execute([$clienteId]);
        foreach ($ventas->fetchAll() as $v) {
            $fecha = $v['validado_at'] ?? $v['created_at'];
            $items[] = ['tipo' => 'venta', 'fecha' => $fecha, 'data' => $v];
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

        $emails = $this->db->prepare('
            SELECT ae.*, u.nombre AS usuario_nombre FROM actividad_emails ae
            JOIN usuarios u ON u.id = ae.usuario_id
            WHERE ae.cliente_id = ? ORDER BY ae.enviado_at DESC
        ');
        $emails->execute([$clienteId]);
        foreach ($emails->fetchAll() as $e) {
            $items[] = ['tipo' => 'email', 'fecha' => $e['enviado_at'], 'data' => $e];
        }

        usort($items, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));
        return $items;
    }

    public function update(int $id, array $data): void
    {
        $this->db->prepare('
            UPDATE clientes SET
                razon_social = ?,
                nombre_comercial = ?,
                cif = ?,
                cif_normalizado = ?,
                ciudad = ?,
                provincia = ?,
                estado_pipeline_id = ?,
                updated_at = NOW()
            WHERE id = ? AND deleted_at IS NULL
        ')->execute([
            $data['razon_social'],
            $data['nombre_comercial'],
            $data['cif'],
            $data['cif_normalizado'],
            $data['ciudad'],
            $data['provincia'],
            $data['estado_pipeline_id'],
            $id,
        ]);
    }

    public function softDelete(int $id): void
    {
        $this->archivarCliente($id);
    }

    public function assignColaboradora(int $id, int $empresaColabId, int $responsableEmpresaId): void
    {
        $this->db->prepare('
            UPDATE clientes SET
                empresa_colaboradora_id = ?,
                responsable_empresa_id = ?,
                modo_acceso_empresa = \'edicion\',
                updated_at = NOW()
            WHERE id = ? AND deleted_at IS NULL
        ')->execute([$empresaColabId, $responsableEmpresaId, $id]);
    }

    public function tieneVentaValidada(int $clienteId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM ventas WHERE cliente_id = ? AND estado = 'validada' LIMIT 1
        ");
        $stmt->execute([$clienteId]);
        return (bool) $stmt->fetch();
    }

    public function countVisitasRealizadas(int $clienteId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM visitas WHERE cliente_id = ? AND estado = 'realizada'
        ");
        $stmt->execute([$clienteId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array{puede: bool, mensaje: string, motivo: ?string}
     */
    public function evaluarReemplazoDuplicado(int $clienteId): array
    {
        if ($this->tieneVentaValidada($clienteId)) {
            return [
                'puede' => false,
                'mensaje' => 'Ya existe un cliente con ese contacto y tiene una venta formalizada.',
                'motivo' => null,
            ];
        }

        if ($this->countVisitasRealizadas($clienteId) === 0) {
            return [
                'puede' => true,
                'mensaje' => '',
                'motivo' => 'sin_visitas_realizadas',
            ];
        }

        $cliente = $this->findById($clienteId);
        $fechaPrimera = $cliente['primera_visita_fecha'] ?? null;
        if ($fechaPrimera) {
            $limite = (new \DateTimeImmutable($fechaPrimera))->modify('+6 months');
            if (new \DateTimeImmutable('today') > $limite) {
                return [
                    'puede' => true,
                    'mensaje' => '',
                    'motivo' => 'primera_visita_antigua',
                ];
            }
        }

        return [
            'puede' => false,
            'mensaje' => 'Ya existe un cliente con ese contacto con actividad comercial reciente.',
            'motivo' => null,
        ];
    }

    public function archivarParaReemplazo(int $id): void
    {
        $this->archivarCliente($id);
    }

    /** Soft delete + liberar identificadores únicos (CIF, email, teléfono de contactos). */
    public function archivarCliente(int $id): void
    {
        $this->db->prepare('
            UPDATE contactos
            SET email_normalizado = NULL, telefono_normalizado = NULL, activo = 0
            WHERE cliente_id = ?
        ')->execute([$id]);
        $this->db->prepare('
            UPDATE clientes
            SET cif_normalizado = NULL, deleted_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ')->execute([$id]);
    }

    /** @return array{visitas: int, ventas: int, tareas: int} */
    public function countActividadBloqueante(int $clienteId): array
    {
        $counts = ['visitas' => 0, 'ventas' => 0, 'tareas' => 0];
        foreach (['visitas' => 'visitas', 'ventas' => 'ventas', 'tareas' => 'tareas'] as $key => $table) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE cliente_id = ?");
            $stmt->execute([$clienteId]);
            $counts[$key] = (int) $stmt->fetchColumn();
        }
        return $counts;
    }

    public function puedeEliminarse(int $clienteId): bool
    {
        $actividad = $this->countActividadBloqueante($clienteId);
        return array_sum($actividad) === 0;
    }

    /** @param array{visitas: int, ventas: int, tareas: int} $actividad */
    public function mensajeBloqueoEliminacion(array $actividad): string
    {
        return sprintf(
            'No se puede eliminar: tiene %d visita(s), %d venta(s) y %d tarea(s) registradas.',
            $actividad['visitas'],
            $actividad['ventas'],
            $actividad['tareas']
        );
    }

    /** @return array{visitas: int, ventas: int, tareas: int, contactos: int} */
    public function countActividad(int $clienteId): array
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM contactos WHERE cliente_id = ?');
        $stmt->execute([$clienteId]);
        return array_merge($this->countActividadBloqueante($clienteId), [
            'contactos' => (int) $stmt->fetchColumn(),
        ]);
    }
}