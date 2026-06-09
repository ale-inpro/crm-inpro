<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\DashboardModel;
use App\Services\NotificacionService;

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $model = new DashboardModel();

        $tareas = $model->tareasPendientes($user);
        $tareasVencidas = $model->tareasVencidasCount($user);
        $tareasUrgentes = $model->tareasUrgentes($user);
        $pipelineChart = $model->clientesPorEstado();
        $horasVisitas = (int) env('AVISO_VISITAS_HORAS', 48);
        $proximasVisitas = (new NotificacionService())->proximasVisitas($user, $horasVisitas);

        if ($user['rol'] === 'inpro') {
            $stats = $model->statsInpro();
            $this->view('dashboard/inpro', [
                'title' => 'Dashboard',
                'stats' => $stats,
                'tareas' => $tareas,
                'tareasVencidas' => $tareasVencidas,
                'tareasUrgentes' => $tareasUrgentes,
                'proximasVisitas' => $proximasVisitas,
                'pendingVentas' => $stats['ventas_pendientes'],
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
                'tareasUrgentes' => $tareasUrgentes,
                'proximasVisitas' => $proximasVisitas,
                'breadcrumbs' => [['label' => 'Mi panel']],
            ]);
        }
    }
}
