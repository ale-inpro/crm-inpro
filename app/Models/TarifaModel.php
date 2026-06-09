<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class TarifaModel extends Model
{
    public function listar(): array
    {
        return $this->db->query('
            SELECT t.*,
                (SELECT COUNT(*) FROM tarifa_tramos tt WHERE tt.tarifa_id = t.id) AS num_tramos,
                (SELECT COUNT(*) FROM empresas_colaboradoras ec WHERE ec.tarifa_id = t.id) AS num_empresas
            FROM tarifas t
            ORDER BY t.es_default DESC, t.nombre ASC
        ')->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tarifas WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function tarifaDefaultId(): ?int
    {
        $id = $this->db->query('SELECT id FROM tarifas WHERE es_default = 1 AND activa = 1 LIMIT 1')->fetchColumn();
        if ($id) {
            return (int) $id;
        }
        $id = $this->db->query('SELECT id FROM tarifas WHERE activa = 1 ORDER BY id ASC LIMIT 1')->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function tarifaIdPorEmpresa(int $empresaId): ?int
    {
        $stmt = $this->db->prepare('SELECT tarifa_id FROM empresas_colaboradoras WHERE id = ? LIMIT 1');
        $stmt->execute([$empresaId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function tramosPorTarifa(int $tarifaId): array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM tarifa_tramos WHERE tarifa_id = ? ORDER BY orden ASC, obras_desde ASC
        ');
        $stmt->execute([$tarifaId]);
        return $stmt->fetchAll();
    }

    public function tramoParaObras(int $tarifaId, int $numObras): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM tarifa_tramos
            WHERE tarifa_id = ?
              AND obras_desde <= ?
              AND (obras_hasta IS NULL OR obras_hasta >= ?)
            ORDER BY obras_desde DESC
            LIMIT 1
        ');
        $stmt->execute([$tarifaId, $numObras, $numObras]);
        return $stmt->fetch() ?: null;
    }

    public function empresasAsignadas(int $tarifaId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, nombre FROM empresas_colaboradoras WHERE tarifa_id = ? ORDER BY nombre
        ');
        $stmt->execute([$tarifaId]);
        return $stmt->fetchAll();
    }

    public function empresasAsignables(int $tarifaId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, nombre FROM empresas_colaboradoras
            WHERE activa = 1 AND (tarifa_id IS NULL OR tarifa_id = 0 OR tarifa_id != ?)
            ORDER BY nombre
        ');
        $stmt->execute([$tarifaId]);
        return $stmt->fetchAll();
    }

    public function findTramoById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tarifa_tramos WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $this->db->prepare('INSERT INTO tarifas (nombre, activa, es_default) VALUES (?,?,?)')
            ->execute([$data['nombre'], $data['activa'], $data['es_default']]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->db->prepare('UPDATE tarifas SET nombre = ?, activa = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$data['nombre'], $data['activa'], $id]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM tarifas WHERE id = ? AND es_default = 0')->execute([$id]);
    }

    public function createTramo(array $data): int
    {
        $this->db->prepare('
            INSERT INTO tarifa_tramos (tarifa_id, obras_desde, obras_hasta, precio_mes_eur, stripe_price_id, orden)
            VALUES (?,?,?,?,?,?)
        ')->execute([
            $data['tarifa_id'], $data['obras_desde'], $data['obras_hasta'],
            $data['precio_mes_eur'], $data['stripe_price_id'], $data['orden'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateTramo(int $id, array $data): void
    {
        $this->db->prepare('
            UPDATE tarifa_tramos SET
                obras_desde = ?, obras_hasta = ?, precio_mes_eur = ?, stripe_price_id = ?, orden = ?
            WHERE id = ?
        ')->execute([
            $data['obras_desde'], $data['obras_hasta'], $data['precio_mes_eur'],
            $data['stripe_price_id'], $data['orden'], $id,
        ]);
    }

    public function deleteTramo(int $id): void
    {
        $this->db->prepare('DELETE FROM tarifa_tramos WHERE id = ?')->execute([$id]);
    }

    public function asignarEmpresa(int $tarifaId, int $empresaId): void
    {
        $this->db->prepare('UPDATE empresas_colaboradoras SET tarifa_id = ? WHERE id = ?')
            ->execute([$tarifaId, $empresaId]);
    }

    public function quitarEmpresa(int $empresaId): void
    {
        $defaultId = $this->tarifaDefaultId();
        $this->db->prepare('UPDATE empresas_colaboradoras SET tarifa_id = ? WHERE id = ?')
            ->execute([$defaultId, $empresaId]);
    }
}