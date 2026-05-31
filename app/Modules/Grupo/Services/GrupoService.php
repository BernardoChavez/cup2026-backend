<?php

namespace App\Modules\Grupo\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Postulante;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\Docente;
use App\Models\GrupoNivelacion;

class GrupoService
{
    /**
     * Generate groups and allocate students for a specific cycle.
     */
    public function generarGruposParaGestion(int $idGestion)
    {
        return DB::transaction(function () use ($idGestion) {
            // Get all registered students with successful payment
            $postulantes = Postulante::where('id_gestion_academica', $idGestion)
                ->where('pago_procesado', true)
                ->get();

            $totalPostulantes = $postulantes->count();
            if ($totalPostulantes === 0) {
                throw new \Exception('No hay postulantes con pago conciliado en esta gestión.');
            }

            // Ensure materias exist (Computación, Matemáticas, Inglés, Física)
            $materias = Materia::all();
            if ($materias->isEmpty()) {
                // Auto-create default materias to be defensive
                $materias = collect([
                    Materia::create(['codigo' => 'COMP', 'nombre' => 'Computación']),
                    Materia::create(['codigo' => 'MAT', 'nombre' => 'Matemáticas']),
                    Materia::create(['codigo' => 'ING', 'nombre' => 'Inglés']),
                    Materia::create(['codigo' => 'FIS', 'nombre' => 'Física']),
                ]);
            }

            // Ensure classrooms exist
            $aulas = Aula::all();
            if ($aulas->isEmpty()) {
                // Auto-create default classrooms
                $aulas = collect([
                    Aula::create(['nombre' => 'Aula 101 - FICCT', 'capacidad_maxima' => 70]),
                    Aula::create(['nombre' => 'Aula 102 - FICCT', 'capacidad_maxima' => 70]),
                    Aula::create(['nombre' => 'Aula 103 - FICCT', 'capacidad_maxima' => 70]),
                    Aula::create(['nombre' => 'Aula 104 - FICCT', 'capacidad_maxima' => 70]),
                ]);
            }

            // Get contracted teachers
            $docentes = Docente::where('contratado', true)->get();
            if ($docentes->isEmpty()) {
                throw new \Exception('No hay docentes contratados en el sistema para realizar asignaciones.');
            }

            // Clean previous group assignments for this cycle to allow idempotency
            $gruposAnterioresIds = GrupoNivelacion::where('id_gestion_academica', $idGestion)->pluck('id');
            DB::table('inscritos_grupos')->whereIn('id_grupo', $gruposAnterioresIds)->delete();
            GrupoNivelacion::where('id_gestion_academica', $idGestion)->delete();

            // Schedules to assign
            $schedules = [
                '07:00 - 09:15',
                '09:15 - 11:30',
                '11:30 - 13:45',
                '14:00 - 16:15',
                '16:15 - 18:30'
            ];

            // Teacher load tracker (id_docente => groups_assigned)
            $teacherLoads = [];
            foreach ($docentes as $docente) {
                $teacherLoads[$docente->id] = 0;
            }

            $gruposCreadosCount = 0;

            // Generate groups for each subject
            foreach ($materias as $materia) {
                // Maximum 70 students per class (aforo físico)
                $maxCapacity = 70;
                $gruposNecesarios = ceil($totalPostulantes / $maxCapacity);

                // Create the groups
                $gruposObj = [];
                for ($g = 1; $g <= $gruposNecesarios; $g++) {
                    // Assign classroom (cycling through available)
                    $aula = $aulas->get(($g - 1) % $aulas->count());

                    // Assign schedule (cycling through)
                    $horario = $schedules[($g - 1) % count($schedules)];

                    // Assign teacher (must be under 4 groups limit)
                    $selectedDocenteId = null;
                    foreach ($teacherLoads as $docId => $load) {
                        if ($load < 4) {
                            $selectedDocenteId = $docId;
                            $teacherLoads[$docId]++;
                            break;
                        }
                    }

                    // Create GrupoNivelacion
                    $grupo = GrupoNivelacion::create([
                        'id_gestion_academica' => $idGestion,
                        'id_docente' => $selectedDocenteId,
                        'id_aula' => $aula->id,
                        'id_materia' => $materia->id,
                        'nombre' => "{$materia->codigo} - Paralelo " . chr(64 + $g), // Parallel A, B, C...
                        'horario' => $horario
                    ]);

                    $gruposObj[] = $grupo;
                    $gruposCreadosCount++;
                }

                // Distribute students round-robin to ensure balanced groups
                foreach ($postulantes as $index => $postulante) {
                    $grupoAsignado = $gruposObj[$index % count($gruposObj)];
                    
                    DB::table('inscritos_grupos')->insert([
                        'id_postulante' => $postulante->id,
                        'id_grupo' => $grupoAsignado->id
                    ]);
                }
            }

            return [
                'total_postulantes' => $totalPostulantes,
                'grupos_creados' => $gruposCreadosCount
            ];
        });
    }
}
