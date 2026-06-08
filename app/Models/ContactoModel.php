<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ContactoModel extends Model
{
    public function byCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM contactos WHERE cliente_id = ? AND activo = 1 ORDER BY es_principal DESC, nombre');
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public function create(int $clienteId, array $data): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO contactos (cliente_id, nombre, cargo, email, telefono, es_principal)
            VALUES (?,?,?,?,?,?)
        ');
        $stmt->execute([
            $clienteId, $data['nombre'], $data['cargo'], $data['email'], $data['telefono'],
            $data['es_principal'] ?? 0,
        ]);
    }
}