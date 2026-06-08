<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\DashboardModel;

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $model = new DashboardModel();

        $tareas = $model->tareasPendientes($user);
        $tareasVencidas = $model->tareasVencidasCount($user);
        $pipelineChart = $model->clientesPorEstado();

        if ($user['rol'] === 'inpro') {
            $this->view('dashboard/inpro', [
                'title' => 'Dashboard',
                'stats' => $model->statsInpro(),
                'tareas' => $tareas,
                'tareasVencidas' => $tareasVencidas,
                'pipelineChart' => $pipelineChart,
                'breadcrumbs' => [['label' => 'Dashboard']],
            ]);
        } else {
            $this->view('dashboard/empresa', [
                'title' => 'Mi panel',
                'stats' => $model->statsEmpresa(
                    (int) $user['empresa_colaboradora_id'],
                    (int) $user['id']
                ),
                'tareas' => $tareas,
                'tareasVencidas' => $tareasVencidas,
                'breadcrumbs' => [['label' => 'Mi panel']],
            ]);
        }
    }
}
