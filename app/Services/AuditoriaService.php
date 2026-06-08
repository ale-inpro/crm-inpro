<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class AuditoriaService
{
    public function log(?int $usuarioId, string $entidad, ?int $entidadId, string $accion, ?array $nuevo = null): void
    {
        $db = Database::connection();
        $db->prepare('
            INSERT INTO auditoria_log (usuario_id, entidad, entidad_id, accion, datos_nuevos, ip_address)
            VALUES (?,?,?,?,?,?)
        ')->execute([
            $usuarioId,
            $entidad,
            $entidadId,
            $accion,
            $nuevo ? json_encode($nuevo, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
