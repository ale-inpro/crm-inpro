<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CatalogoModel extends Model
{
    public function estadosPipeline(): array
    {
        return $this->db->query('SELECT * FROM estados_pipeline WHERE activo = 1 ORDER BY orden')->fetchAll();
    }

    public function empresasColaboradoras(): array
    {
        return $this->db->query('SELECT id, nombre FROM empresas_colaboradoras WHERE activa = 1 ORDER BY nombre')->fetchAll();
    }

    public function usuariosInpro(): array
    {
        return $this->db->query("SELECT id, nombre FROM usuarios WHERE rol = 'inpro' AND activo = 1")->fetchAll();
    }

    public function usuariosEmpresa(int $empresaId = 0): array
    {
        if ($empresaId === 0) {
            return $this->db->query("SELECT id, nombre FROM usuarios WHERE rol = 'empresa' AND activo = 1 ORDER BY nombre")->fetchAll();
        }
        $stmt = $this->db->prepare("SELECT id, nombre FROM usuarios WHERE rol = 'empresa' AND empresa_colaboradora_id = ? AND activo = 1 ORDER BY nombre");
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll();
    }

    public function productos(): array
    {
        return $this->db->query('SELECT * FROM catalogo_productos WHERE activo = 1 ORDER BY precio_anual_eur')->fetchAll();
    }

    public function reglasComision(): array
    {
        return $this->db->query('SELECT * FROM reglas_comision WHERE activo = 1 ORDER BY porcentaje DESC')->fetchAll();
    }
}