<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Audit\Controllers\AuditController;

Route::middleware(['auth:sanctum', 'role:ADMIN'])->prefix('admin/bitacora')->group(function () {
    Route::get('/', [AuditController::class, 'index']);
});
