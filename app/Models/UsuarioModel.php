<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class UsuarioModel extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.*, ec.nombre AS empresa_colaboradora_nombre
            FROM usuarios u
            LEFT JOIN empresas_colaboradoras ec ON ec.id = u.empresa_colaboradora_id
            WHERE u.email = ? AND u.activo = 1
            LIMIT 1
        ');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function touchLastAccess(int $id): void
    {
        $this->db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?')->execute([$id]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id = ? AND activo = 1 LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}