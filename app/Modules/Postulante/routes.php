<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Postulante\Controllers\PostulanteController;

Route::middleware(['auth:sanctum'])->prefix('postulantes')->group(function () {
    // Admin/Coordinator CRUD
    Route::middleware('role:ADMIN,COORDINADOR')->group(function () {
        Route::get('/', [PostulanteController::class, 'index']);
        Route::post('/', [PostulanteController::class, 'store']);
    });

    // Custom access (Postulante can view/edit their own, Admin/Coord can do everything)
    Route::get('{id}', [PostulanteController::class, 'show']);
    Route::put('{id}', [PostulanteController::class, 'update']);
    
    // Only Admin can delete a profile
    Route::delete('{id}', [PostulanteController::class, 'destroy'])->middleware('role:ADMIN');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('carreras', function () {
        return response()->json(\App\Models\Carrera::all());
    });
    Route::get('gestiones-academicas', function () {
        return response()->json(\App\Models\GestionAcademica::all());
    });
});

