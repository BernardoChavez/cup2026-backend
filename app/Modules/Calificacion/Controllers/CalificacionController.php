<?php

namespace App\Modules\Calificacion\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Postulante;
use App\Models\Calificacion;
use App\Modules\Audit\Services\AuditLogger;

class CalificacionController extends Controller
{
    /**
     * List all grades.
     */
    public function index()
    {
        $calificaciones = Calificacion::with(['postulante.usuario'])->get();
        return response()->json($calificaciones);
    }

    /**
     * Show grades for a specific postulante.
     */
    public function show(Request $request, $id_postulante)
    {
        $postulante = Postulante::findOrFail($id_postulante);
        $user = $request->user();

        // Security check: Postulante can only view their own grades
        if ($user->rol === 'POSTULANTE' && $user->id !== $postulante->id_usuario) {
            return response()->json([
                'message' => 'No tiene autorización para ver estas calificaciones.'
            ], 403);
        }

        $calificacion = Calificacion::where('id_postulante', $id_postulante)->first();
        if (!$calificacion) {
            return response()->json([
                'message' => 'Calificaciones no registradas aún para este postulante.'
            ], 404);
        }

        return response()->json($calificacion);
    }

    /**
     * Register or update grades for a postulante (Admin/Docente only).
     */
    public function registrarCalificaciones(Request $request)
    {
        $fields = $request->validate([
            'id_postulante' => 'required|integer|exists:postulantes,id',
            // Computación
            'comp_n1' => 'nullable|numeric|between:0,100',
            'comp_n2' => 'nullable|numeric|between:0,100',
            'comp_n3' => 'nullable|numeric|between:0,100',
            // Matemáticas
            'mat_n1' => 'nullable|numeric|between:0,100',
            'mat_n2' => 'nullable|numeric|between:0,100',
            'mat_n3' => 'nullable|numeric|between:0,100',
            // Inglés
            'ing_n1' => 'nullable|numeric|between:0,100',
            'ing_n2' => 'nullable|numeric|between:0,100',
            'ing_n3' => 'nullable|numeric|between:0,100',
            // Física
            'fis_n1' => 'nullable|numeric|between:0,100',
            'fis_n2' => 'nullable|numeric|between:0,100',
            'fis_n3' => 'nullable|numeric|between:0,100',
        ]);

        $postulante = Postulante::findOrFail($fields['id_postulante']);

        // Check if grade sheet already exists
        $calificacion = Calificacion::firstOrNew(['id_postulante' => $postulante->id]);

        // Assign raw notes (observer calculates averages automatically)
        $calificacion->comp_n1 = $fields['comp_n1'] ?? $calificacion->comp_n1 ?? 0;
        $calificacion->comp_n2 = $fields['comp_n2'] ?? $calificacion->comp_n2 ?? 0;
        $calificacion->comp_n3 = $fields['comp_n3'] ?? $calificacion->comp_n3 ?? 0;

        $calificacion->mat_n1 = $fields['mat_n1'] ?? $calificacion->mat_n1 ?? 0;
        $calificacion->mat_n2 = $fields['mat_n2'] ?? $calificacion->mat_n2 ?? 0;
        $calificacion->mat_n3 = $fields['mat_n3'] ?? $calificacion->mat_n3 ?? 0;

        $calificacion->ing_n1 = $fields['ing_n1'] ?? $calificacion->ing_n1 ?? 0;
        $calificacion->ing_n2 = $fields['ing_n2'] ?? $calificacion->ing_n2 ?? 0;
        $calificacion->ing_n3 = $fields['ing_n3'] ?? $calificacion->ing_n3 ?? 0;

        $calificacion->fis_n1 = $fields['fis_n1'] ?? $calificacion->fis_n1 ?? 0;
        $calificacion->fis_n2 = $fields['fis_n2'] ?? $calificacion->fis_n2 ?? 0;
        $calificacion->fis_n3 = $fields['fis_n3'] ?? $calificacion->fis_n3 ?? 0;

        $calificacion->save();

        // Audit log
        AuditLogger::log(
            $request->user()->id,
            'REGISTRAR_CALIFICACIONES',
            'calificaciones',
            $calificacion->id,
            "Se registraron calificaciones para el postulante CI: {$postulante->ci}. Promedio Final Global: {$calificacion->promedio_final_global}."
        );

        return response()->json([
            'success' => true,
            'message' => 'Calificaciones registradas con éxito.',
            'calificacion' => $calificacion
        ]);
    }
}
