<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admision\Controllers\AdmisionController;

Route::middleware(['auth:sanctum', 'role:ADMIN,COORDINADOR'])->prefix('admin/admision')->group(function () {
    Route::post('procesar', [AdmisionController::class, 'procesarAdmision']);
});
