<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Account\Controllers\CargaMasivaController;

Route::middleware(['auth:sanctum', 'role:ADMIN'])->prefix('admin/carga-masiva')->group(function () {
    Route::post('postulantes', [CargaMasivaController::class, 'cargarPostulantes']);
    Route::post('docentes', [CargaMasivaController::class, 'cargarDocentes']);
});
