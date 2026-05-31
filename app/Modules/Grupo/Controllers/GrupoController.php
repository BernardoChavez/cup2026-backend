<?php

namespace App\Modules\Grupo\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GrupoNivelacion;
use App\Modules\Grupo\Services\GrupoService;
use App\Modules\Audit\Services\AuditLogger;

class GrupoController extends Controller
{
    protected $grupoService;

    public function __construct(GrupoService $grupoService)
    {
        $this->grupoService = $grupoService;
    }

    /**
     * List all parallel groups with their assigned teacher, classroom, and student counts.
     */
    public function index()
    {
        $grupos = GrupoNivelacion::with(['materia', 'aula', 'docente.usuario'])
            ->withCount('postulantes')
            ->get();

        return response()->json($grupos);
    }

    /**
     * Trigger the automatic load-balanced grouping algorithm.
     */
    public function generarGrupos(Request $request)
    {
        $request->validate([
            'id_gestion_academica' => 'required|integer|exists:gestiones_academicas,id'
        ]);

        $idGestion = $request->id_gestion_academica;

        try {
            $result = $this->grupoService->generarGruposParaGestion($idGestion);

            // Audit log
            AuditLogger::log(
                $request->user()->id,
                'GENERAR_GRUPOS',
                'grupos_nivelacion',
                null,
                "Se generaron automáticamente {$result['grupos_creados']} grupos para {$result['total_postulantes']} estudiantes en la gestión ID: {$idGestion}."
            );

            return response()->json([
                'success' => true,
                'message' => "Grupos generados con éxito. Se crearon {$result['grupos_creados']} paralelos para {$result['total_postulantes']} estudiantes.",
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar grupos: ' . $e->getMessage()
            ], 400);
        }
    }
}
