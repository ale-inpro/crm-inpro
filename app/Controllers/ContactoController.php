<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ClienteModel;
use App\Models\ContactoModel;
use App\Services\ClienteService;

class ContactoController extends Controller
{
    public function store(): void
    {
        csrf_verify();
        $user      = $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente   = (new ClienteModel())->findById($clienteId);

        if (!$cliente || !(new ClienteService())->canEdit($user, $cliente)) {
            flash('error', 'No puedes añadir contactos en este cliente.');
            redirect('clientes');
        }

        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '') ?: null;
        $telefono = trim($_POST['telefono'] ?? '') ?: null;
        $esPrincipal = isset($_POST['es_principal']);

        if ($nombre === '') {
            flash('error', 'El nombre del contacto es obligatorio.');
            redirect('clientes/ver?id=' . $clienteId . '#tabContactos');
        }

        if ($esPrincipal) {
            if ($err = contacto_validar_principal($nombre, $email, $telefono)) {
                flash('error', $err);
                redirect('clientes/ver?id=' . $clienteId . '#tabContactos');
            }
            if ($err = (new ContactoModel())->validarUnicidadPrincipal($email, $telefono)) {
                flash('error', $err);
                redirect('clientes/ver?id=' . $clienteId . '#tabContactos');
            }
        }

        try {
            (new ContactoModel())->create($clienteId, [
                'nombre'       => $nombre,
                'cargo'        => trim($_POST['cargo'] ?? '') ?: null,
                'email'        => $email,
                'telefono'     => $telefono,
                'es_principal' => $esPrincipal ? 1 : 0,
            ]);
        } catch (\Throwable $e) {
            flash('error', 'No se pudo guardar el contacto. Comprueba que el email o teléfono no estén duplicados.');
            redirect('clientes/ver?id=' . $clienteId . '#tabContactos');
        }

        flash('success', 'Contacto añadido correctamente.');
        redirect('clientes/ver?id=' . $clienteId . '#tabContactos');
    }
}