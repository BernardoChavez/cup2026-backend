<?php

namespace App\Modules\Docente\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Docente;
use App\Models\GrupoNivelacion;
use App\Models\Asistencia;
use App\Modules\Audit\Services\AuditLogger;

class DocenteController extends Controller
{
    /**
     * List all docentes.
     */
    public function index()
    {
        $docentes = Docente::with(['usuario'])->withCount('grupos')->get();
        return response()->json($docentes);
    }

    /**
     * Create a new docente.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:usuarios,email',
            'password' => 'nullable|string|min:6',
            'profesional_area' => 'boolean',
            'maestria' => 'boolean',
            'diplomado_edu_sup' => 'boolean',
            'contratado' => 'boolean'
        ]);

        return DB::transaction(function () use ($fields, $request) {
            $user = User::create([
                'nombre' => $fields['nombre'],
                'apellido' => $fields['apellido'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password'] ?? '123456'),
                'rol' => 'DOCENTE',
                'activo' => true
            ]);

            $docente = Docente::create([
                'id_usuario' => $user->id,
                'profesional_area' => $fields['profesional_area'] ?? false,
                'maestria' => $fields['maestria'] ?? false,
                'diplomado_edu_sup' => $fields['diplomado_edu_sup'] ?? false,
                'contratado' => $fields['contratado'] ?? false
            ]);

            AuditLogger::log(
                $request->user()->id,
                'CREAR',
                'docentes',
                $docente->id,
                "Se registró el docente: {$user->nombre} {$user->apellido}."
            );

            return response()->json([
                'success' => true,
                'docente' => $docente->load('usuario')
            ], 201);
        });
    }

    /**
     * Update a docente's details and qualifications.
     */
    public function update(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);

        $fields = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:usuarios,email,' . $docente->id_usuario,
            'profesional_area' => 'boolean',
            'maestria' => 'boolean',
            'diplomado_edu_sup' => 'boolean',
            'contratado' => 'boolean'
        ]);

        return DB::transaction(function () use ($docente, $fields, $request) {
            $docente->usuario->update([
                'nombre' => $fields['nombre'],
                'apellido' => $fields['apellido'],
                'email' => $fields['email']
            ]);

            $docente->update([
                'profesional_area' => $fields['profesional_area'] ?? false,
                'maestria' => $fields['maestria'] ?? false,
                'diplomado_edu_sup' => $fields['diplomado_edu_sup'] ?? false,
                'contratado' => $fields['contratado'] ?? false
            ]);

            AuditLogger::log(
                $request->user()->id,
                'ACTUALIZAR',
                'docentes',
                $docente->id,
                "Se actualizó la información del docente ID: {$docente->id}."
            );

            return response()->json([
                'success' => true,
                'docente' => $docente->load('usuario')
            ]);
        });
    }

    /**
     * List all classes assigned to the authenticated teacher.
     */
    public function listarMisGrupos(Request $request)
    {
        $docente = Docente::where('id_usuario', $request->user()->id)->firstOrFail();
        $grupos = GrupoNivelacion::with(['materia', 'aula'])
            ->withCount('postulantes')
            ->where('id_docente', $docente->id)
            ->get();

        return response()->json($grupos);
    }

    /**
     * List students registered in a group.
     */
    public function listarEstudiantesGrupo(Request $request, $id_grupo)
    {
        $grupo = GrupoNivelacion::findOrFail($id_grupo);
        $user = $request->user();

        // Security check: Teachers can only view students of their own groups
        if ($user->rol === 'DOCENTE') {
            $docente = Docente::where('id_usuario', $user->id)->firstOrFail();
            if ($grupo->id_docente !== $docente->id) {
                return response()->json([
                    'message' => 'No tiene autorización para ver los alumnos de este grupo.'
                ], 403);
            }
        }

        $estudiantes = $grupo->postulantes()->with('usuario')->get();
        return response()->json($estudiantes);
    }

    /**
     * Register daily attendance list.
     */
    public function registrarAsistencia(Request $request)
    {
        $fields = $request->validate([
            'id_grupo' => 'required|integer|exists:grupos_nivelacion,id',
            'fecha' => 'required|date',
            'estudiantes' => 'required|array',
            'estudiantes.*.id_postulante' => 'required|integer|exists:postulantes,id',
            'estudiantes.*.estado' => 'required|string|in:PRESENTE,FALTA,LICENCIA'
        ]);

        $user = $request->user();
        $grupo = GrupoNivelacion::findOrFail($fields['id_grupo']);

        // Security check for teachers
        if ($user->rol === 'DOCENTE') {
            $docente = Docente::where('id_usuario', $user->id)->firstOrFail();
            if ($grupo->id_docente !== $docente->id) {
                return response()->json([
                    'message' => 'No está asignado como docente de este grupo.'
                ], 403);
            }
        }

        return DB::transaction(function () use ($fields, $grupo, $user) {
            $registrosCount = 0;
            foreach ($fields['estudiantes'] as $estudiante) {
                Asistencia::updateOrCreate(
                    [
                        'id_grupo' => $grupo->id,
                        'id_postulante' => $estudiante['id_postulante'],
                        'fecha' => $fields['fecha']
                    ],
                    [
                        'estado' => $estudiante['estado']
                    ]
                );
                $registrosCount++;
            }

            AuditLogger::log(
                $user->id,
                'REGISTRAR_ASISTENCIA',
                'asistencias',
                null,
                "Se registró la asistencia para {$registrosCount} alumnos del grupo '{$grupo->nombre}' en la fecha {$fields['fecha']}."
            );

            return response()->json([
                'success' => true,
                'message' => 'Asistencias guardadas correctamente.'
            ]);
        });
    }
}
