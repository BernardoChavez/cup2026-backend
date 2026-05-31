<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Docente\Controllers\DocenteController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Admin endpoints for Docente management
    Route::middleware('role:ADMIN,COORDINADOR')->prefix('admin/docentes')->group(function () {
        Route::get('/', [DocenteController::class, 'index']);
        Route::post('/', [DocenteController::class, 'store']);
        Route::put('{id}', [DocenteController::class, 'update']);
    });

    // Docente specific endpoints
    Route::prefix('docente')->group(function () {
        Route::get('grupos', [DocenteController::class, 'listarMisGrupos'])->middleware('role:DOCENTE');
        Route::get('grupos/{id_grupo}/estudiantes', [DocenteController::class, 'listarEstudiantesGrupo'])->middleware('role:DOCENTE,ADMIN,COORDINADOR');
        Route::post('asistencias', [DocenteController::class, 'registrarAsistencia'])->middleware('role:DOCENTE,ADMIN');
    });
});
