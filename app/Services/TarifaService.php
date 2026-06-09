<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ClienteModel;
use App\Models\TarifaModel;

class TarifaService
{
    public function tarifaIdParaCliente(int $clienteId): ?int
    {
        $cliente = (new ClienteModel())->findById($clienteId);
        if (!$cliente) {
            return null;
        }
        if (!empty($cliente['empresa_colaboradora_id'])) {
            $tarifaId = (new TarifaModel())->tarifaIdPorEmpresa((int) $cliente['empresa_colaboradora_id']);
            if ($tarifaId) {
                return $tarifaId;
            }
        }
        return (new TarifaModel())->tarifaDefaultId();
    }

    public function resolverTramo(int $tarifaId, int $numObras): ?array
    {
        return (new TarifaModel())->tramoParaObras($tarifaId, $numObras);
    }

    public function calcularImportes(array $tramo, float $descuentoPct = 0): array
    {
        $precioMes = (float) $tramo['precio_mes_eur'];
        $importeAnual = round($precioMes * 12, 2);
        $importeFinal = round($importeAnual * (1 - $descuentoPct / 100), 2);

        return [
            'precio_mes_eur' => $precioMes,
            'importe_anual_eur' => $importeAnual,
            'importe_final_eur' => $importeFinal,
            'stripe_price_id' => $tramo['stripe_price_id'] ?? null,
        ];
    }

    public function etiquetaTramo(array $tramo, int $numObras): string
    {
        $hasta = $tramo['obras_hasta'] ? (int) $tramo['obras_hasta'] : '∞';
        return sprintf(
            '%d obra(s) — tramo %d-%s — %s €/mes',
            $numObras,
            (int) $tramo['obras_desde'],
            $hasta,
            number_format((float) $tramo['precio_mes_eur'], 2, ',', '.')
        );
    }
}