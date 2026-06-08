<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CatalogoModel;
use App\Models\ClienteModel;
use App\Services\ClienteService;

class PipelineController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $estados = (new CatalogoModel())->estadosPipeline();
        $clientes = (new ClienteModel())->listForUser($user);

        $columnas = [];
        foreach ($estados as $e) {
            if ($e['es_final']) {
                continue;
            }
            $columnas[$e['id']] = ['estado' => $e, 'clientes' => []];
        }
        foreach ($clientes as $c) {
            if (isset($columnas[$c['estado_pipeline_id']])) {
                $columnas[$c['estado_pipeline_id']]['clientes'][] = $c;
            }
        }

        $this->view('pipeline/index', [
            'title' => 'Pipeline',
            'columnas' => $columnas,
            'breadcrumbs' => [['label' => 'Pipeline']],
        ]);
    }

    public function mover(): void
    {
        $user = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $estadoId = (int) ($_POST['estado_id'] ?? 0);

        try {
            (new ClienteService())->moverEstado($user, $clienteId, $estadoId);
            $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
