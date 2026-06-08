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
        $esPrincipal = !empty($data['es_principal']) ? 1 : 0;
        if ($esPrincipal) {
            $this->db->prepare('UPDATE contactos SET es_principal = 0 WHERE cliente_id = ?')->execute([$clienteId]);
        }
        $stmt = $this->db->prepare('
            INSERT INTO contactos (cliente_id, nombre, cargo, email, telefono, es_principal)
            VALUES (?,?,?,?,?,?)
        ');
        $stmt->execute([
            $clienteId, $data['nombre'], $data['cargo'], $data['email'], $data['telefono'],
            $esPrincipal,
        ]);
    }
}