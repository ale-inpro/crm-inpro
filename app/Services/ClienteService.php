<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\ClienteModel;

class ClienteService
{
    public function canView(array $user, array $cliente): bool
    {
        if ($user['rol'] === 'inpro') {
            return true;
        }
        return (int) $cliente['empresa_colaboradora_id'] === (int) $user['empresa_colaboradora_id'];
    }

    public function canEdit(array $user, array $cliente): bool
    {
        if ($user['rol'] === 'inpro') {
            return true;
        }
        if (!$this->canView($user, $cliente)) {
            return false;
        }
        return $cliente['modo_acceso_empresa'] === 'edicion';
    }

    public function gestorActivo(array $cliente): string
    {
        return cliente_gestor_activo($cliente);
    }

    public function canTransferirAInpro(array $user, array $cliente): bool
    {
        if (empty($cliente['empresa_colaboradora_id'])) {
            return false;
        }
        if ($this->gestorActivo($cliente) !== 'empresa') {
            return false;
        }
        if ($user['rol'] !== 'empresa') {
            return false;
        }
        return $this->canEdit($user, $cliente);
    }

    public function canTransferirAEmpresa(array $user, array $cliente): bool
    {
        if (empty($cliente['empresa_colaboradora_id'])) {
            return false;
        }
        if ($this->gestorActivo($cliente) !== 'inpro') {
            return false;
        }
        if ($user['rol'] !== 'inpro') {
            return false;
        }
        return $this->canEdit($user, $cliente);
    }

    /**
     * Editar/eliminar ficha del cliente: responsable INPRO o responsable empresa asignado, con permiso de edición.
     */
    public function canManageCliente(array $user, array $cliente): bool
    {
        if (!$this->canEdit($user, $cliente)) {
            return false;
        }
        if ($user['rol'] === 'inpro') {
            return (int) ($cliente['responsable_inpro_id'] ?? 0) === (int) $user['id'];
        }
        if ($user['rol'] === 'empresa') {
            return (int) ($cliente['responsable_empresa_id'] ?? 0) === (int) $user['id'];
        }
        return false;
    }

    public function moverEstado(array $user, int $clienteId, int $nuevoEstadoId): void
    {
        $cliente = (new ClienteModel())->findById($clienteId);
        if (!$cliente || !$this->canEdit($user, $cliente)) {
            throw new \RuntimeException('Sin permiso para mover este cliente.');
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM estados_pipeline WHERE id = ? AND activo = 1 LIMIT 1');
        $stmt->execute([$nuevoEstadoId]);
        $estado = $stmt->fetch();

        if (!$estado) {
            throw new \RuntimeException('Estado no válido.');
        }

        $requierePrimeraVisita = in_array($estado['codigo'], ['negociacion', 'propuesta'], true);
        if ($requierePrimeraVisita && !$cliente['primera_visita_realizada']) {
            throw new \RuntimeException('No se puede avanzar sin registrar la primera visita.');
        }

        if ((int) $cliente['estado_pipeline_id'] === $nuevoEstadoId) {
            return;
        }

        $db->prepare('UPDATE clientes SET estado_pipeline_id = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$nuevoEstadoId, $clienteId]);

        (new AuditoriaService())->log(
            (int) $user['id'],
            'clientes',
            $clienteId,
            'cambio_estado',
            ['estado_pipeline_id' => $nuevoEstadoId, 'codigo' => $estado['codigo']]
        );
    }
}
