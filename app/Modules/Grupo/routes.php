<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Grupo\Controllers\GrupoController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('grupos', [GrupoController::class, 'index']);
    Route::post('admin/grupos/generar', [GrupoController::class, 'generarGrupos'])->middleware('role:ADMIN,COORDINADOR');
});
