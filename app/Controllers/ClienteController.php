<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\AsignacionModel;
use App\Models\CatalogoModel;
use App\Models\ClienteModel;
use App\Models\ContactoModel;
use App\Models\TareaModel;
use App\Models\UsuarioModel;
use App\Models\VentaModel;
use App\Models\VisitaModel;
use App\Services\AuditoriaService;
use App\Services\ClienteService;
use App\Services\TarifaService;
use App\Models\TarifaModel;

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
        $busqueda = trim($_GET['q'] ?? '');
        if ($busqueda !== '') {
            $filtros['busqueda'] = $busqueda;
        }

        $clienteModel = new ClienteModel();
        $clientes = $clienteModel->listForUser($user, $filtros);
        $actividadBloqueantePorCliente = [];
        foreach ($clientes as $c) {
            if (cliente_can_manage($c)) {
                $actividadBloqueantePorCliente[(int) $c['id']] = $clienteModel->countActividadBloqueante((int) $c['id']);
            }
        }

        $this->view('clientes/index', [
            'title' => 'Clientes',
            'busqueda' => $busqueda,
            'clientes' => $clientes,
            'actividadBloqueantePorCliente' => $actividadBloqueantePorCliente,
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
        $empresas = is_inpro() ? $catalogo->empresasColaboradoras() : [];
        $this->view('clientes/form', [
            'title' => 'Nuevo cliente',
            'cliente' => null,
            'estados' => $catalogo->estadosPipeline(),
            'empresas' => $empresas,
            'usuariosPorEmpresa' => $this->usuariosPorEmpresaMap($catalogo, $empresas),
            'breadcrumbs' => [
                ['label' => 'Clientes', 'url' => 'clientes'],
                ['label' => 'Nuevo'],
            ],
            'backUrl' => 'clientes',
            'backLabel' => 'Clientes',
        ]);
    }

    public function store(): void
    {
        csrf_verify();
        $user = $this->requireAuth();

        $razonSocial = trim($_POST['razon_social'] ?? '');
        if ($razonSocial === '') {
            flash('error', 'La razón social es obligatoria.');
            redirect('clientes/nuevo');
        }

        $contactoNombre = trim($_POST['contacto_nombre'] ?? '');
        $contactoEmail = trim($_POST['contacto_email'] ?? '') ?: null;
        $contactoTelefono = trim($_POST['contacto_telefono'] ?? '') ?: null;

        if ($err = contacto_validar_principal($contactoNombre, $contactoEmail, $contactoTelefono)) {
            flash('error', $err);
            redirect('clientes/nuevo');
        }

        $contactoModel = new ContactoModel();
        $clienteModel = new ClienteModel();
        $clienteDuplicadoId = $contactoModel->findClienteIdDuplicadoPorContacto($contactoEmail, $contactoTelefono);
        $clienteDuplicado = null;
        $reemplazo = null;

        if ($clienteDuplicadoId) {
            $clienteDuplicado = $clienteModel->findById($clienteDuplicadoId);
            if (!$clienteDuplicado) {
                flash('error', 'No se pudo verificar el cliente duplicado.');
                redirect('clientes/nuevo');
            }

            $reemplazo = $clienteModel->evaluarReemplazoDuplicado($clienteDuplicadoId);
            if (!$reemplazo['puede']) {
                flash('error', $reemplazo['mensaje']);
                redirect('clientes/nuevo');
            }
        }

        $cifNorm = cif_normalize($_POST['cif'] ?? null);
        if ($cifNorm && $clienteModel->existsCif($cifNorm, $clienteDuplicadoId)) {
            flash('error', 'Ya existe un cliente con ese CIF.');
            redirect('clientes/nuevo');
        }

        $estadoId = (int) ($_POST['estado_pipeline_id'] ?? 1);
        $empresaColabId = is_inpro()
            ? (($_POST['empresa_colaboradora_id'] ?? '') !== '' ? (int) $_POST['empresa_colaboradora_id'] : null)
            : (int) $user['empresa_colaboradora_id'];

        $responsableEmpresaId = null;
        if ($empresaColabId && is_inpro()) {
            $responsableEmpresaId = (int) ($_POST['responsable_empresa_id'] ?? 0);
            if (!$this->validarResponsableEmpresa($responsableEmpresaId, $empresaColabId)) {
                flash('error', 'Debes seleccionar un responsable válido de la empresa colaboradora.');
                redirect('clientes/nuevo');
            }
        } elseif (is_empresa()) {
            $responsableEmpresaId = (int) $user['id'];
        }

        $db = Database::connection();
        $db->beginTransaction();

        try {
            if ($clienteDuplicadoId && ($reemplazo['puede'] ?? false)) {
                $clienteModel->archivarParaReemplazo($clienteDuplicadoId);
                (new AuditoriaService())->log((int) $user['id'], 'clientes', $clienteDuplicadoId, 'reemplazar_duplicado', [
                    'razon_social_anterior' => $clienteDuplicado['razon_social'] ?? '',
                    'motivo' => $reemplazo['motivo'],
                    'reemplazado_por_usuario_id' => (int) $user['id'],
                    'email_contacto' => $contactoEmail,
                    'telefono_contacto' => $contactoTelefono,
                ]);
            }
            $clienteId = $clienteModel->create([
                'razon_social' => $razonSocial,
                'nombre_comercial' => trim($_POST['nombre_comercial'] ?? '') ?: null,
                'cif' => trim($_POST['cif'] ?? '') ?: null,
                'cif_normalizado' => $cifNorm,
                'ciudad' => trim($_POST['ciudad'] ?? '') ?: null,
                'provincia' => trim($_POST['provincia'] ?? '') ?: null,
                'telefono_principal' => $contactoTelefono,
                'email_principal' => $contactoEmail,
                'estado_pipeline_id' => $estadoId,
                'empresa_colaboradora_id' => $empresaColabId,
                'responsable_inpro_id' => is_inpro() ? (int) $user['id'] : null,
                'responsable_empresa_id' => $responsableEmpresaId,
                'modo_acceso_empresa' => 'edicion',
                'origen_lead' => is_empresa() ? 'empresa_colaboradora' : 'inpro',
                'creado_por_usuario_id' => (int) $user['id'],
            ]);

            $contactoModel->create($clienteId, [
                'nombre' => $contactoNombre,
                'cargo' => trim($_POST['contacto_cargo'] ?? '') ?: null,
                'email' => $contactoEmail,
                'telefono' => $contactoTelefono,
                'es_principal' => 1,
            ]);

            if ($empresaColabId && $responsableEmpresaId) {
                (new AsignacionModel())->registrar([
                    'cliente_id' => $clienteId,
                    'tipo' => 'asignacion_inicial',
                    'responsable_inpro_id' => is_inpro() ? (int) $user['id'] : null,
                    'responsable_empresa_id' => $responsableEmpresaId,
                    'empresa_colaboradora_id' => $empresaColabId,
                    'modo_acceso_empresa' => 'edicion',
                    'motivo' => 'Asignación inicial a empresa colaboradora',
                    'realizado_por_id' => (int) $user['id'],
                ]);
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            flash('error', 'No se pudo crear el cliente. Comprueba que el email o teléfono no estén duplicados.');
            redirect('clientes/nuevo');
        }

        if ($clienteDuplicadoId) {
            flash('success', 'Cliente creado. Se archivó un registro anterior duplicado sin actividad relevante.');
        } else {
            flash('success', 'Cliente creado correctamente.');
        }
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
        $estadosPipeline = array_values(array_filter(
            $catalogo->estadosPipeline(),
            static fn(array $e): bool => empty($e['es_final'])
        ));
        $usuariosAsignables = is_inpro()
            ? array_merge($catalogo->usuariosInpro(), $catalogo->usuariosEmpresa((int) ($cliente['empresa_colaboradora_id'] ?? 0)))
            : [['id' => $user['id'], 'nombre' => $user['nombre']]];

        $clienteSvc = new ClienteService();

        $this->view('clientes/show', [
            'title' => $cliente['razon_social'],
            'cliente' => $cliente,
            'estadosPipeline' => $estadosPipeline,
            'contactos' => (new ContactoModel())->byCliente($id),
            'timeline' => $clienteModel->timeline($id),
            'ventas' => (new VentaModel())->byCliente($id),
            'tareas' => (new TareaModel())->byCliente($id),
            'tarifaCliente' => (function () use ($id) {
                $tarifaId = (new TarifaService())->tarifaIdParaCliente($id);
                return $tarifaId ? (new TarifaModel())->findById($tarifaId) : null;
            })(),
            'usuariosInpro' => $catalogo->usuariosInpro(),
            'usuariosEmpresa' => $catalogo->usuariosEmpresa((int) ($cliente['empresa_colaboradora_id'] ?? 0)),
            'usuariosAsignables' => $usuariosAsignables,
            'canEdit' => $clienteSvc->canEdit($user, $cliente),
            'canManageCliente' => $clienteSvc->canManageCliente($user, $cliente),
            'actividadBloqueante' => $clienteSvc->canManageCliente($user, $cliente)
                ? $clienteModel->countActividadBloqueante($id)
                : [],
            'canTransferirAInpro' => $clienteSvc->canTransferirAInpro($user, $cliente),
            'canTransferirAEmpresa' => $clienteSvc->canTransferirAEmpresa($user, $cliente),
            'gestorEtiqueta' => cliente_gestor_etiqueta($cliente),
            'breadcrumbs' => [
                ['label' => 'Clientes', 'url' => 'clientes'],
                ['label' => $cliente['razon_social']],
            ],
            'backUrl' => 'clientes',
            'backLabel' => 'Clientes',
            'visitasProgramadas' => (new VisitaModel())->programadasByCliente($id),
        ]);
    }

    public function edit(): void
    {
        $user = $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $cliente = (new ClienteModel())->findById($id);
        $svc = new ClienteService();

        if (!$cliente || !$svc->canManageCliente($user, $cliente)) {
            flash('error', 'No puedes editar este cliente.');
            redirect('clientes');
        }

        $catalogo = new CatalogoModel();
        $empresas = is_inpro() && empty($cliente['empresa_colaboradora_id'])
            ? $catalogo->empresasColaboradoras()
            : [];
        $this->view('clientes/form', [
            'title' => 'Editar cliente',
            'cliente' => $cliente,
            'estados' => $catalogo->estadosPipeline(),
            'empresas' => $empresas,
            'usuariosPorEmpresa' => $this->usuariosPorEmpresaMap($catalogo, $empresas),
            'breadcrumbs' => [
                ['label' => 'Clientes', 'url' => 'clientes'],
                ['label' => $cliente['razon_social'], 'url' => 'clientes/ver?id=' . $id],
                ['label' => 'Editar'],
            ],
            'backUrl' => 'clientes/ver?id=' . $id,
            'backLabel' => $cliente['razon_social'],
        ]);
    }

    public function update(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->findById($id);
        $svc = new ClienteService();

        if (!$cliente || !$svc->canManageCliente($user, $cliente)) {
            flash('error', 'No puedes editar este cliente.');
            redirect('clientes');
        }

        $cifNorm = cif_normalize($_POST['cif'] ?? null);
        if ($cifNorm && $clienteModel->existsCif($cifNorm, $id)) {
            flash('error', 'Ya existe otro cliente con ese CIF.');
            redirect('clientes/editar?id=' . $id);
        }

        $clienteModel->update($id, [
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'nombre_comercial' => trim($_POST['nombre_comercial'] ?? '') ?: null,
            'cif' => trim($_POST['cif'] ?? '') ?: null,
            'cif_normalizado' => $cifNorm,
            'ciudad' => trim($_POST['ciudad'] ?? '') ?: null,
            'provincia' => trim($_POST['provincia'] ?? '') ?: null,
            'estado_pipeline_id' => (int) ($_POST['estado_pipeline_id'] ?? $cliente['estado_pipeline_id']),
        ]);

        $asignoColaboradora = false;
        if (
            is_inpro()
            && empty($cliente['empresa_colaboradora_id'])
            && ($_POST['empresa_colaboradora_id'] ?? '') !== ''
        ) {
            $empresaColabId = (int) $_POST['empresa_colaboradora_id'];
            $responsableEmpresaId = (int) ($_POST['responsable_empresa_id'] ?? 0);

            if (!$this->validarResponsableEmpresa($responsableEmpresaId, $empresaColabId)) {
                flash('error', 'Debes seleccionar un responsable válido de la empresa colaboradora.');
                redirect('clientes/editar?id=' . $id);
            }

            $clienteModel->assignColaboradora($id, $empresaColabId, $responsableEmpresaId);
            (new AsignacionModel())->registrar([
                'cliente_id' => $id,
                'tipo' => 'asignacion_inicial',
                'responsable_inpro_id' => $cliente['responsable_inpro_id'],
                'responsable_empresa_id' => $responsableEmpresaId,
                'empresa_colaboradora_id' => $empresaColabId,
                'modo_acceso_empresa' => 'edicion',
                'motivo' => 'Asignación a empresa colaboradora desde edición',
                'realizado_por_id' => (int) $user['id'],
            ]);
            $asignoColaboradora = true;
        }

        (new AuditoriaService())->log((int) $user['id'], 'clientes', $id, 'actualizar', [
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'asigno_colaboradora' => $asignoColaboradora,
        ]);

        if ($asignoColaboradora) {
            flash('success', 'Cliente actualizado y asignado a empresa colaboradora.');
        } else {
            flash('success', 'Cliente actualizado correctamente.');
        }
        redirect('clientes/ver?id=' . $id);
    }

    public function destroy(): void
    {
        csrf_verify();
        $user = $this->requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->findById($id);
        $svc = new ClienteService();

        if (!$cliente || !$svc->canManageCliente($user, $cliente)) {
            flash('error', 'No puedes eliminar este cliente.');
            redirect('clientes');
        }

        $actividad = $clienteModel->countActividadBloqueante($id);
        if (!$clienteModel->puedeEliminarse($id)) {
            flash('error', $clienteModel->mensajeBloqueoEliminacion($actividad));
            redirect('clientes/ver?id=' . $id);
        }

        $clienteModel->softDelete($id);
        (new AuditoriaService())->log((int) $user['id'], 'clientes', $id, 'eliminar', [
            'razon_social' => $cliente['razon_social'],
        ]);

        flash('success', 'Cliente eliminado correctamente.');
        redirect('clientes');
    }

    /** @param array<int, array{id: int, nombre: string}> $empresas */
    private function usuariosPorEmpresaMap(CatalogoModel $catalogo, array $empresas): array
    {
        $map = [];
        foreach ($empresas as $emp) {
            $map[(int) $emp['id']] = $catalogo->usuariosEmpresa((int) $emp['id']);
        }
        return $map;
    }

    private function validarResponsableEmpresa(int $userId, int $empresaColabId): bool
    {
        if ($userId <= 0 || $empresaColabId <= 0) {
            return false;
        }
        $responsable = (new UsuarioModel())->findById($userId);
        return $responsable
            && $responsable['rol'] === 'empresa'
            && (int) $responsable['empresa_colaboradora_id'] === $empresaColabId;
    }
}
