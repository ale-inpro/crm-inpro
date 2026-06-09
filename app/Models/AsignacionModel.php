<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AsignacionModel extends Model
{
    public function registrar(array $data): void
    {
        $this->db->prepare('
            INSERT INTO asignaciones (cliente_id, tipo, responsable_inpro_id, responsable_empresa_id,
                empresa_colaboradora_id, modo_acceso_empresa, motivo, realizado_por_id)
            VALUES (?,?,?,?,?,?,?,?)
        ')->execute([
            $data['cliente_id'], $data['tipo'], $data['responsable_inpro_id'],
            $data['responsable_empresa_id'], $data['empresa_colaboradora_id'],
            $data['modo_acceso_empresa'], $data['motivo'], $data['realizado_por_id'],
        ]);
    }

    public function transferirAInpro(int $clienteId, int $inproUserId, int $realizadoPorId, string $motivo): void
    {
        $cliente = (new ClienteModel())->findById($clienteId);
        if (!$cliente) return;

        $this->db->prepare('
            UPDATE clientes SET responsable_inpro_id = ?, modo_acceso_empresa = \'lectura\', updated_at = NOW()
            WHERE id = ?
        ')->execute([$inproUserId, $clienteId]);

        $this->registrar([
            'cliente_id' => $clienteId,
            'tipo' => 'transferencia_inpro',
            'responsable_inpro_id' => $inproUserId,
            'responsable_empresa_id' => $cliente['responsable_empresa_id'],
            'empresa_colaboradora_id' => $cliente['empresa_colaboradora_id'],
            'modo_acceso_empresa' => 'lectura',
            'motivo' => $motivo,
            'realizado_por_id' => $realizadoPorId,
        ]);
    }

    public function transferirAEmpresa(int $clienteId, int $empresaUserId, int $realizadoPorId, string $motivo): void
    {
        $cliente = (new ClienteModel())->findById($clienteId);
        if (!$cliente) {
            return;
        }

        $this->db->prepare('
            UPDATE clientes
            SET responsable_empresa_id = ?, modo_acceso_empresa = \'edicion\', updated_at = NOW()
            WHERE id = ?
        ')->execute([$empresaUserId, $clienteId]);

        $this->registrar([
            'cliente_id' => $clienteId,
            'tipo' => 'transferencia_empresa',
            'responsable_inpro_id' => $cliente['responsable_inpro_id'],
            'responsable_empresa_id' => $empresaUserId,
            'empresa_colaboradora_id' => $cliente['empresa_colaboradora_id'],
            'modo_acceso_empresa' => 'edicion',
            'motivo' => $motivo,
            'realizado_por_id' => $realizadoPorId,
        ]);
    }
}