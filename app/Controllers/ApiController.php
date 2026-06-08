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
}
