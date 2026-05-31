<?php

namespace App\Modules\Admision\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Postulante;
use App\Models\Carrera;
use App\Modules\Audit\Services\AuditLogger;

class AdmisionController extends Controller
{
    /**
     * Run the automatic admission algorithm for a specific academic cycle.
     */
    public function procesarAdmision(Request $request)
    {
        $request->validate([
            'id_gestion_academica' => 'required|integer|exists:gestiones_academicas,id'
        ]);

        $idGestion = $request->id_gestion_academica;

        return DB::transaction(function () use ($idGestion, $request) {
            // Get all students of the given cycle whose payment is reconciled, sorted by global grade DESC
            // Sorting by average DESC ensures merit-based career slot allocation!
            $postulantes = Postulante::with(['calificacion', 'carreraOpcion1', 'carreraOpcion2'])
                ->where('id_gestion_academica', $idGestion)
                ->where('pago_procesado', true)
                ->where('estado_final', 'CURSANDO')
                ->get()
                ->sortByDesc(function ($p) {
                    return $p->calificacion ? floatval($p->calificacion->promedio_final_global) : 0.00;
                });

            if ($postulantes->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No hay postulantes con pagos conciliados listos para procesar en esta gestión.'
                ]);
            }

            $procesados = 0;
            $aprobados = 0;
            $reprobados = 0;

            foreach ($postulantes as $postulante) {
                $promedio = $postulante->calificacion ? floatval($postulante->calificacion->promedio_final_global) : 0.00;

                if ($promedio >= 60.00) {
                    $postulante->estado_final = 'APROBADO';
                    $aprobados++;

                    // Try to assign Carrera Opción 1
                    $carrera1 = $postulante->carreraOpcion1;
                    $cuposOcupados1 = Postulante::where('id_gestion_academica', $idGestion)
                        ->where('id_carrera_asignada', $carrera1->id)
                        ->where('estado_final', 'APROBADO')
                        ->count();

                    if ($cuposOcupados1 < $carrera1->cupos_maximos) {
                        $postulante->id_carrera_asignada = $carrera1->id;
                    } else {
                        // Option 1 is full, try option 2
                        $carrera2 = $postulante->carreraOpcion2;
                        $cuposOcupados2 = Postulante::where('id_gestion_academica', $idGestion)
                            ->where('id_carrera_asignada', $carrera2->id)
                            ->where('estado_final', 'APROBADO')
                            ->count();

                        if ($cuposOcupados2 < $carrera2->cupos_maximos) {
                            $postulante->id_carrera_asignada = $carrera2->id;
                        } else {
                            // Both options are full!
                            $postulante->id_carrera_asignada = null;
                            $postulante->otros_requisitos = ($postulante->otros_requisitos ?? '') . "\n[SISTEMA] Cupos saturados para opción 1 ({$carrera1->codigo}) y opción 2 ({$carrera2->codigo}).";
                        }
                    }
                } else {
                    $postulante->estado_final = 'REPROBADO';
                    $postulante->id_carrera_asignada = null;
                    $reprobados++;
                }

                $postulante->save();
                $procesados++;
            }

            // Audit log
            AuditLogger::log(
                $request->user()->id,
                'PROCESAR_ADMISION',
                'postulantes',
                null,
                "Se procesó la admisión automática de {$procesados} postulantes de la gestión ID: {$idGestion}. Aprobados: {$aprobados}, Reprobados: {$reprobados}."
            );

            return response()->json([
                'success' => true,
                'message' => "Proceso completado. Alumnos evaluados: {$procesados}. Aprobados: {$aprobados}. Reprobados: {$reprobados}."
            ]);
        });
    }
}
