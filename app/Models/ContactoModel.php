<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ContactoModel extends Model
{
    public function byCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM contactos
            WHERE cliente_id = ? AND activo = 1
            ORDER BY es_principal DESC, nombre
        ');
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public function principalByCliente(int $clienteId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM contactos
            WHERE cliente_id = ? AND activo = 1 AND es_principal = 1
            ORDER BY id ASC
            LIMIT 1
        ');
        $stmt->execute([$clienteId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existsEmail(string $emailNormalizado, ?int $excludeContactoId = null): bool
    {
        $sql = '
            SELECT 1 FROM contactos ct
            JOIN clientes c ON c.id = ct.cliente_id
            WHERE ct.activo = 1
              AND c.deleted_at IS NULL
              AND ct.email_normalizado = ?
        ';
        $params = [$emailNormalizado];
        if ($excludeContactoId) {
            $sql .= ' AND ct.id != ?';
            $params[] = $excludeContactoId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function existsTelefono(string $telefonoNormalizado, ?int $excludeContactoId = null): bool
    {
        $sql = '
            SELECT 1 FROM contactos ct
            JOIN clientes c ON c.id = ct.cliente_id
            WHERE ct.activo = 1
              AND c.deleted_at IS NULL
              AND ct.telefono_normalizado = ?
        ';
        $params = [$telefonoNormalizado];
        if ($excludeContactoId) {
            $sql .= ' AND ct.id != ?';
            $params[] = $excludeContactoId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    /** @return string|null Mensaje de error por duplicado */
    public function validarUnicidadPrincipal(?string $email, ?string $telefono, ?int $excludeContactoId = null): ?string
    {
        $emailNorm = email_normalize($email);
        $telNorm = telefono_normalize($telefono);

        if ($emailNorm && $this->existsEmail($emailNorm, $excludeContactoId)) {
            return 'Ya existe un cliente con ese email de contacto principal.';
        }
        if ($telNorm && $this->existsTelefono($telNorm, $excludeContactoId)) {
            return 'Ya existe un cliente con ese teléfono de contacto principal.';
        }
        return null;
    }

    /**
     * Cliente activo cuyo contacto principal coincide por email o teléfono.
     */
    public function findClienteIdDuplicadoPorContacto(?string $email, ?string $telefono): ?int
    {
        $emailNorm = email_normalize($email);
        $telNorm = telefono_normalize($telefono);
        if (!$emailNorm && !$telNorm) {
            return null;
        }

        $conditions = [];
        $params = [];
        if ($emailNorm) {
            $conditions[] = 'ct.email_normalizado = ?';
            $params[] = $emailNorm;
        }
        if ($telNorm) {
            $conditions[] = 'ct.telefono_normalizado = ?';
            $params[] = $telNorm;
        }

        $sql = '
            SELECT c.id
            FROM clientes c
            JOIN contactos ct ON ct.cliente_id = c.id AND ct.es_principal = 1 AND ct.activo = 1
            WHERE c.deleted_at IS NULL
              AND (' . implode(' OR ', $conditions) . ')
            ORDER BY c.id DESC
            LIMIT 1
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    /** Libera email/teléfono normalizados al archivar un cliente duplicado. */
    public function liberarNormalizadosPorCliente(int $clienteId): void
    {
        $this->db->prepare('
            UPDATE contactos
            SET email_normalizado = NULL, telefono_normalizado = NULL, activo = 0
            WHERE cliente_id = ?
        ')->execute([$clienteId]);
    }

    public function create(int $clienteId, array $data): int
    {
        $esPrincipal = !empty($data['es_principal']) ? 1 : 0;
        if ($esPrincipal) {
            $this->db->prepare('UPDATE contactos SET es_principal = 0 WHERE cliente_id = ?')
                ->execute([$clienteId]);
        }

        $email = trim($data['email'] ?? '') ?: null;
        $telefono = trim($data['telefono'] ?? '') ?: null;
        $emailNorm = email_normalize($email);
        $telNorm = telefono_normalize($telefono);

        $stmt = $this->db->prepare('
            INSERT INTO contactos (
                cliente_id, nombre, cargo, email, telefono,
                email_normalizado, telefono_normalizado, es_principal
            ) VALUES (?,?,?,?,?,?,?,?)
        ');
        $stmt->execute([
            $clienteId,
            $data['nombre'],
            $data['cargo'],
            $email,
            $telefono,
            $emailNorm,
            $telNorm,
            $esPrincipal,
        ]);

        $contactoId = (int) $this->db->lastInsertId();

        if ($esPrincipal) {
            $this->syncClienteCache($clienteId);
        }

        return $contactoId;
    }

    public function syncClienteCache(int $clienteId): void
    {
        $principal = $this->principalByCliente($clienteId);
        $this->db->prepare('
            UPDATE clientes
            SET email_principal = ?, telefono_principal = ?, updated_at = NOW()
            WHERE id = ? AND deleted_at IS NULL
        ')->execute([
            $principal ? ($principal['email'] ?? null) : null,
            $principal ? ($principal['telefono'] ?? null) : null,
            $clienteId,
        ]);
    }
}