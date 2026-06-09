<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TareaModel;
use App\Models\VisitaModel;

class ApiController extends Controller
{
    public function calendarioEventos(): void
    {
        $user      = $this->requireAuth();
        $clienteId = isset($_GET['cliente_id']) ? (int) $_GET['cliente_id'] : null;

        $visitas = (new VisitaModel())->forCalendario($user, $clienteId);
        $tareas  = (new TareaModel())->forCalendario($user, $clienteId);

        $fc = [];

        foreach ($visitas as $e) {
            $fc[] = [
                'id'              => 'visita-' . $e['id'],
                'title'           => '📍 ' . ($e['razon_social'] ?? ''),
                'start'           => $e['fecha_hora'],
                'url'             => url('clientes/ver?id=' . $e['cliente_id']),
                'backgroundColor' => '#1a7f4b',
                'borderColor'     => '#145f38',
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'tipo'       => 'visita',
                    'cliente'    => $e['razon_social'] ?? '',
                    'descripcion' => $e['tipo'] ?? '',
                ],
            ];
        }

        foreach ($tareas as $e) {
            $prioColor = match($e['prioridad'] ?? '') {
                'alta'  => ['bg' => '#dc3545', 'border' => '#b02a37'],
                'media' => ['bg' => '#f59e0b', 'border' => '#d97706'],
                default => ['bg' => '#0d6efd', 'border' => '#0b5ed7'],
            };
            $fc[] = [
                'id'              => 'tarea-' . $e['id'],
                'title'           => '✓ ' . ($e['titulo'] ?? ''),
                'start'           => $e['fecha_hora'],
                'url'             => url('clientes/ver?id=' . $e['cliente_id']),
                'backgroundColor' => $prioColor['bg'],
                'borderColor'     => $prioColor['border'],
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'tipo'        => 'tarea',
                    'cliente'     => $e['razon_social'] ?? '',
                    'descripcion' => $e['descripcion'] ?? '',
                    'prioridad'   => $e['prioridad'] ?? '',
                    'asignado'    => $e['asignado_nombre'] ?? '',
                    'destacada'   => (bool) ($e['destacada'] ?? false),
                ],
            ];
        }

        $this->json($fc);
    }

    public function tarifaPreview(): void
    {
        $this->requireAuth();
        $clienteId = (int) ($_GET['cliente_id'] ?? 0);
        $numObras = (int) ($_GET['num_obras'] ?? 0);

        if ($clienteId <= 0 || $numObras <= 0) {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }

        $svc = new \App\Services\TarifaService();
        $tarifaId = $svc->tarifaIdParaCliente($clienteId);
        if (!$tarifaId) {
            $this->json(['ok' => false, 'error' => 'No hay tarifa configurada'], 404);
        }

        $tramo = $svc->resolverTramo($tarifaId, $numObras);
        if (!$tramo) {
            $this->json(['ok' => false, 'error' => 'No hay tramo para ese número de obras'], 404);
        }

        $importes = $svc->calcularImportes($tramo, 0);
        $this->json([
            'ok' => true,
            'tarifa_tramo_id' => (int) $tramo['id'],
            'etiqueta' => $svc->etiquetaTramo($tramo, $numObras),
            'precio_mes_eur' => $importes['precio_mes_eur'],
            'importe_anual_eur' => $importes['importe_anual_eur'],
            'stripe_price_id' => $importes['stripe_price_id'],
        ]);
    }
}
