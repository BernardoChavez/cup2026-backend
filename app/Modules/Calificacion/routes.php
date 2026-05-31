<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Calificacion\Controllers\CalificacionController;

Route::middleware(['auth:sanctum'])->prefix('calificaciones')->group(function () {
    Route::get('/', [CalificacionController::class, 'index'])->middleware('role:ADMIN,COORDINADOR,DOCENTE');
    Route::get('{id_postulante}', [CalificacionController::class, 'show']);
    Route::post('registro', [CalificacionController::class, 'registrarCalificaciones'])->middleware('role:ADMIN,DOCENTE');
});
