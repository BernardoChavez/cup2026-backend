<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Authenticate user and return token.
     */
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        // Find user
        $user = User::where('email', $fields['email'])->first();

        // Check password and active status
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.'
            ], 401);
        }

        if (!$user->activo) {
            return response()->json([
                'message' => 'Esta cuenta se encuentra inactiva.'
            ], 403);
        }

        // Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Log login to bitacora if audit system is loaded later
        // We will call a helper method or use observers
        try {
            \App\Modules\Audit\Services\AuditLogger::log(
                $user->id,
                'LOGIN',
                'usuarios',
                $user->id,
                "Usuario autenticado correctamente con rol: {$user->rol}",
                $request->ip()
            );
        } catch (\Exception $e) {
            // Ignore if Audit Logger isn't fully set up yet
        }

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Revoke current user's token.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Log logout
        try {
            \App\Modules\Audit\Services\AuditLogger::log(
                $user->id,
                'LOGOUT',
                'usuarios',
                $user->id,
                "Usuario cerró sesión",
                $request->ip()
            );
        } catch (\Exception $e) {
            // Ignore
        }

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada con éxito.'
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // Load specific profiles if necessary (docente, postulante)
        if ($user->rol === 'POSTULANTE') {
            $user->load('postulante');
        } elseif ($user->rol === 'DOCENTE') {
            $user->load('docente');
        }

        return response()->json($user);
    }
}
