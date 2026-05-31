<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Pago\Controllers\PagoController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Postulante registers their payment details
    Route::post('postulantes/pago', [PagoController::class, 'registrarPago'])->middleware('role:POSTULANTE,ADMIN');

    // Admin panel endpoints for reconciliation
    Route::middleware('role:ADMIN,COORDINADOR')->prefix('admin/pago')->group(function () {
        Route::get('pendientes', [PagoController::class, 'listarPendientes']);
        Route::post('conciliar/{id}', [PagoController::class, 'conciliarPago']);
    });
});
