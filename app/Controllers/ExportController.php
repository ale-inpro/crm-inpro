<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ClienteModel;
use App\Models\ContactoModel;

class ExportController extends Controller
{
    public function clientes(): void
    {
        $user = $this->requireInpro();
        $clientes = (new ClienteModel())->listForUser($user);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=clientes_' . date('Y-m-d') . '.csv');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, [
            'ID', 'Razón social', 'CIF', 'Ciudad', 'Estado', '1ª visita',
            'Empresa colaboradora', 'Gestionado por', 'Email', 'Teléfono',
        ], ';');

        $contactoModel = new ContactoModel();

        foreach ($clientes as $c) {
            $principal = $contactoModel->principalByCliente((int) $c['id']);
            fputcsv($out, [
                $c['id'],
                $c['razon_social'],
                $c['cif'] ?? '',
                $c['ciudad'] ?? '',
                $c['estado_nombre'],
                $c['primera_visita_realizada'] ? 'Sí' : 'No',
                $c['empresa_colaboradora_nombre'] ?? '',
                cliente_gestor_etiqueta($c),
                $principal['email'] ?? '',
                $principal['telefono'] ?? '',
            ], ';');
        }

        fclose($out);
        exit;
    }
}
