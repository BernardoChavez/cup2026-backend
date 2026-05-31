<?php

namespace App\Modules\Audit\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;

class AuditController extends Controller
{
    /**
     * Retrieve system logs.
     */
    public function index()
    {
        $logs = Bitacora::with(['usuario'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($logs);
    }
}
