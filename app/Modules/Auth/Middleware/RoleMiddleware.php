<?php

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->rol, $roles)) {
            return response()->json([
                'message' => 'No tiene autorización para realizar esta acción.'
            ], 403);
        }

        return $next($request);
    }
}
