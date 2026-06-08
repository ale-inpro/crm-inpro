<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ComisionModel;

class ComisionController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $this->view('comisiones/index', [
            'title' => is_inpro() ? 'Comisiones' : 'Mis comisiones',
            'comisiones' => (new ComisionModel())->listForUser($user),
            'breadcrumbs' => [['label' => is_inpro() ? 'Comisiones' : 'Mis comisiones']],
        ]);
    }
}