<?php

namespace App\Modules\Postulante\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Postulante;
use App\Modules\Audit\Services\AuditLogger;

class PostulanteController extends Controller
{
    /**
     * List all postulantes.
     */
    public function index()
    {
        $postulantes = Postulante::with(['usuario', 'carreraOpcion1', 'carreraOpcion2', 'carreraAsignada', 'gestionAcademica'])->get();
        return response()->json($postulantes);
    }

    /**
     * Show a single postulante.
     */
    public function show(Request $request, $id)
    {
        $postulante = Postulante::with(['usuario', 'carreraOpcion1', 'carreraOpcion2', 'carreraAsignada', 'gestionAcademica', 'calificacion'])->findOrFail($id);
        $user = $request->user();

        // Security check: Postulante can only view their own profile
        if ($user->rol === 'POSTULANTE' && $user->id !== $postulante->id_usuario) {
            return response()->json([
                'message' => 'No tiene autorización para ver este perfil.'
            ], 403);
        }

        return response()->json($postulante);
    }

    /**
     * Store a new postulante (Admin/Coordinador only).
     */
    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:usuarios,email',
            'password' => 'nullable|string|min:6',
            'id_gestion_academica' => 'required|integer|exists:gestiones_academicas,id',
            'ci' => 'required|string|max:20|unique:postulantes,ci',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|string|in:M,F',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'colegio_procedencia' => 'nullable|string|max:150',
            'ciudad' => 'nullable|string|max:100',
            'titulo_bachiller' => 'boolean',
            'otros_requisitos' => 'nullable|string',
            'id_carrera_opcion1' => 'required|integer|exists:carreras,id',
            'id_carrera_opcion2' => 'required|integer|exists:carreras,id',
        ];

        $fields = $request->validate($rules);

        return DB::transaction(function () use ($fields, $request) {
            // Create user
            $user = User::create([
                'nombre' => $fields['nombre'],
                'apellido' => $fields['apellido'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password'] ?? '123456'),
                'rol' => 'POSTULANTE',
                'activo' => true
            ]);

            // Create postulante profile
            $postulante = Postulante::create([
                'id_usuario' => $user->id,
                'id_gestion_academica' => $fields['id_gestion_academica'],
                'ci' => $fields['ci'],
                'fecha_nacimiento' => $fields['fecha_nacimiento'],
                'sexo' => $fields['sexo'],
                'direccion' => $fields['direccion'] ?? null,
                'telefono' => $fields['telefono'] ?? null,
                'colegio_procedencia' => $fields['colegio_procedencia'] ?? null,
                'ciudad' => $fields['ciudad'] ?? null,
                'titulo_bachiller' => $fields['titulo_bachiller'] ?? false,
                'otros_requisitos' => $fields['otros_requisitos'] ?? null,
                'id_carrera_opcion1' => $fields['id_carrera_opcion1'],
                'id_carrera_opcion2' => $fields['id_carrera_opcion2'],
                'id_carrera_asignada' => null,
                'pago_procesado' => false,
                'estado_final' => 'CURSANDO'
            ]);

            // Audit log
            AuditLogger::log(
                $request->user()->id,
                'CREAR',
                'postulantes',
                $postulante->id,
                "Se registró manualmente el postulante CI: {$postulante->ci}."
            );

            return response()->json([
                'success' => true,
                'postulante' => $postulante->load('usuario')
            ], 201);
        });
    }

    /**
     * Update a postulante profile.
     */
    public function update(Request $request, $id)
    {
        $postulante = Postulante::findOrFail($id);
        $user = $request->user();

        // Security check: Postulante can only edit their own profile
        if ($user->rol === 'POSTULANTE' && $user->id !== $postulante->id_usuario) {
            return response()->json([
                'message' => 'No tiene autorización para actualizar este perfil.'
            ], 403);
        }

        $rules = [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:usuarios,email,' . $postulante->id_usuario,
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'colegio_procedencia' => 'nullable|string|max:150',
            'ciudad' => 'nullable|string|max:100',
        ];

        // Only Admin/Coordinator can edit these fields
        if ($user->rol === 'ADMIN' || $user->rol === 'COORDINADOR') {
            $rules += [
                'id_gestion_academica' => 'required|integer|exists:gestiones_academicas,id',
                'ci' => 'required|string|max:20|unique:postulantes,ci,' . $postulante->id,
                'fecha_nacimiento' => 'required|date',
                'sexo' => 'required|string|in:M,F',
                'titulo_bachiller' => 'boolean',
                'otros_requisitos' => 'nullable|string',
                'id_carrera_opcion1' => 'required|integer|exists:carreras,id',
                'id_carrera_opcion2' => 'required|integer|exists:carreras,id',
                'id_carrera_asignada' => 'nullable|integer|exists:carreras,id',
                'estado_final' => 'required|string|in:APROBADO,REPROBADO,CURSANDO'
            ];
        }

        $fields = $request->validate($rules);

        return DB::transaction(function () use ($postulante, $fields, $user) {
            // Update user details
            $postulante->usuario->update([
                'nombre' => $fields['nombre'],
                'apellido' => $fields['apellido'],
                'email' => $fields['email']
            ]);

            // Update profile details
            $profileData = [
                'direccion' => $fields['direccion'] ?? null,
                'telefono' => $fields['telefono'] ?? null,
                'colegio_procedencia' => $fields['colegio_procedencia'] ?? null,
                'ciudad' => $fields['ciudad'] ?? null,
            ];

            if ($user->rol === 'ADMIN' || $user->rol === 'COORDINADOR') {
                $profileData += [
                    'id_gestion_academica' => $fields['id_gestion_academica'],
                    'ci' => $fields['ci'],
                    'fecha_nacimiento' => $fields['fecha_nacimiento'],
                    'sexo' => $fields['sexo'],
                    'titulo_bachiller' => $fields['titulo_bachiller'] ?? false,
                    'otros_requisitos' => $fields['otros_requisitos'] ?? null,
                    'id_carrera_opcion1' => $fields['id_carrera_opcion1'],
                    'id_carrera_opcion2' => $fields['id_carrera_opcion2'],
                    'id_carrera_asignada' => $fields['id_carrera_asignada'] ?? null,
                    'estado_final' => $fields['estado_final']
                ];
            }

            $postulante->update($profileData);

            // Audit log
            AuditLogger::log(
                $user->id,
                'ACTUALIZAR',
                'postulantes',
                $postulante->id,
                "Se actualizó la información del postulante CI: {$postulante->ci}."
            );

            return response()->json([
                'success' => true,
                'postulante' => $postulante->load('usuario')
            ]);
        });
    }

    /**
     * Delete a postulante profile (Admin only).
     */
    public function destroy(Request $request, $id)
    {
        $postulante = Postulante::findOrFail($id);
        $user = $postulante->usuario;

        DB::transaction(function () use ($postulante, $user, $request, $id) {
            $postulante->delete();
            $user->delete(); // This deletes the user and triggers cascade deletes if configured.

            // Audit log
            AuditLogger::log(
                $request->user()->id,
                'ELIMINAR',
                'postulantes',
                $id,
                "Se eliminó el perfil del postulante CI: {$postulante->ci}."
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Postulante eliminado correctamente.'
        ]);
    }
}
