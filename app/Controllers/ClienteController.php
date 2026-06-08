<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CatalogoModel;
use App\Models\ClienteModel;
use App\Models\ContactoModel;
use App\Models\TareaModel;
use App\Models\VentaModel;
use App\Services\ClienteService;

class ClienteController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $catalogo = new CatalogoModel();

        $filtros = [];
        if (($_GET['filtro'] ?? '') === 'sin_primera_visita') {
            $filtros['sin_primera_visita'] = true;
        }
        if (!empty($_GET['empresa'])) {
            $filtros['empresa_colaboradora_id'] = (int) $_GET['empresa'];
        }
        if (!empty($_GET['estado'])) {
            $filtros['estado_pipeline_id'] = (int) $_GET['estado'];
        }

        $this->view('clientes/index', [
            'title' => 'Clientes',
            'clientes' => (new ClienteModel())->listForUser($user, $filtros),
            'empresas' => is_inpro() ? $catalogo->empresasColaboradoras() : [],
            'estados' => $catalogo->estadosPipeline(),
            'filtros' => $filtros,
            'breadcrumbs' => [['label' => 'Clientes']],
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $catalogo = new CatalogoModel();
        $this->view('clientes/form', [
            'title' => 'Nuevo cliente',
            'estados' => $catalogo->estadosPipeline(),
            'empresas' => is_inpro() ? $catalogo->empresasColaboradoras() : [],
            'breadcrumbs' => [
                ['label' => 'Clientes', 'url' => 'clientes'],
                ['label' => 'Nuevo'],
            ],
        ]);
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        $cifNorm = cif_normalize($_POST['cif'] ?? null);

        if ($cifNorm && (new ClienteModel())->existsCif($cifNorm)) {
            flash('error', 'Ya existe un cliente con ese CIF.');
            redirect('clientes/nuevo');
        }

        $estadoId = (int) ($_POST['estado_pipeline_id'] ?? 1);
        $empresaColabId = is_inpro()
            ? (($_POST['empresa_colaboradora_id'] ?? '') !== '' ? (int) $_POST['empresa_colaboradora_id'] : null)
            : (int) $user['empresa_colaboradora_id'];

        $clienteModel = new ClienteModel();
        $clienteId = $clienteModel->create([
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'nombre_comercial' => trim($_POST['nombre_comercial'] ?? '') ?: null,
            'cif' => trim($_POST['cif'] ?? '') ?: null,
            'cif_normalizado' => $cifNorm,
            'ciudad' => trim($_POST['ciudad'] ?? '') ?: null,
            'provincia' => trim($_POST['provincia'] ?? '') ?: null,
            'telefono_principal' => trim($_POST['telefono_principal'] ?? '') ?: null,
            'email_principal' => trim($_POST['email_principal'] ?? '') ?: null,
            'estado_pipeline_id' => $estadoId,
            'empresa_colaboradora_id' => $empresaColabId,
            'responsable_inpro_id' => is_inpro() ? (int) $user['id'] : null,
            'responsable_empresa_id' => is_empresa() ? (int) $user['id'] : null,
            'modo_acceso_empresa' => 'edicion',
            'origen_lead' => is_empresa() ? 'empresa_colaboradora' : 'inpro',
            'creado_por_usuario_id' => (int) $user['id'],
        ]);

        if (trim($_POST['contacto_nombre'] ?? '') !== '') {
            (new ContactoModel())->create($clienteId, [
                'nombre' => trim($_POST['contacto_nombre']),
                'cargo' => trim($_POST['contacto_cargo'] ?? '') ?: null,
                'email' => trim($_POST['contacto_email'] ?? '') ?: null,
                'telefono' => trim($_POST['contacto_telefono'] ?? '') ?: null,
                'es_principal' => 1,
            ]);
        }

        flash('success', 'Cliente creado correctamente.');
        redirect('clientes/ver?id=' . $clienteId);
    }

    public function show(): void
    {
        $user = $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->findById($id);

        if (!$cliente || !(new ClienteService())->canView($user, $cliente)) {
            http_response_code(404);
            $this->view('errors/404', [], 'main');
            return;
        }

        $catalogo = new CatalogoModel();
        $usuariosAsignables = is_inpro()
            ? array_merge($catalogo->usuariosInpro(), $catalogo->usuariosEmpresa((int) ($cliente['empresa_colaboradora_id'] ?? 0)))
            : [['id' => $user['id'], 'nombre' => $user['nombre']]];

        $this->view('clientes/show', [
            'title' => $cliente['razon_social'],
            'cliente' => $cliente,
            'contactos' => (new ContactoModel())->byCliente($id),
            'timeline' => $clienteModel->timeline($id),
            'ventas' => (new VentaModel())->byCliente($id),
            'tareas' => (new TareaModel())->byCliente($id),
            'productos' => $catalogo->productos(),
            'usuariosInpro' => $catalogo->usuariosInpro(),
            'usuariosAsignables' => $usuariosAsignables,
            'canEdit' => (new ClienteService())->canEdit($user, $cliente),
            'breadcrumbs' => [
                ['label' => 'Clientes', 'url' => 'clientes'],
                ['label' => $cliente['razon_social']],
            ],
        ]);
    }
}
