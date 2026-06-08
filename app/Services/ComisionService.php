<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\ClienteModel;
use App\Models\UsuarioModel;

class ComisionService
{
    public function sugerirReglaId(int $clienteId, array $venta): ?int
    {
        $cliente = (new ClienteModel())->findById($clienteId);
        if (!$cliente || !$cliente['empresa_colaboradora_id']) {
            return $this->reglaIdPorCodigo('INPRO_DIRECTO');
        }

        if (($venta['atribucion_cierre'] ?? '') === 'empresa') {
            return $this->reglaIdPorCodigo('EMPRESA_CIERRA');
        }

        if (!empty($cliente['primera_visita_realizada']) && !empty($cliente['primera_visita_usuario_id'])) {
            $usuario = (new UsuarioModel())->findById((int) $cliente['primera_visita_usuario_id']);
            if ($usuario && $usuario['rol'] === 'empresa') {
                return $this->reglaIdPorCodigo('EMPRESA_PRIMERA_VISITA_INPRO_CIERRA');
            }
        }

        if (empty($cliente['primera_visita_realizada'])) {
            return $this->reglaIdPorCodigo('EMPRESA_SOLO_ALTA_INPRO_CIERRA');
        }

        return $this->reglaIdPorCodigo('INPRO_DIRECTO_CON_ORIGEN');
    }

    private function reglaIdPorCodigo(string $codigo): ?int
    {
        $stmt = Database::connection()->prepare(
            'SELECT id FROM reglas_comision WHERE codigo = ? AND activo = 1 LIMIT 1'
        );
        $stmt->execute([$codigo]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }
}
