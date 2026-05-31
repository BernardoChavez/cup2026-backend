<?php

namespace App\Modules\Audit\Services;

use App\Models\Bitacora;

class AuditLogger
{
    /**
     * Log an action to the bitacora table.
     */
    public static function log(
        int $idUsuario,
        string $accion,
        ?string $tablaAfectada = null,
        ?int $registroId = null,
        ?string $descripcion = null,
        ?string $ip = null
    ) {
        return Bitacora::create([
            'id_usuario' => $idUsuario,
            'accion' => $accion,
            'tabla_afectada' => $tablaAfectada,
            'registro_id' => $registroId,
            'descripcion' => $descripcion,
            'ip_direccion' => $ip ?? request()->ip(),
        ]);
    }
}
