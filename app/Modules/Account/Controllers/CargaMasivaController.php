<?php

namespace App\Modules\Account\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Account\Services\CargaMasivaService;

class CargaMasivaController extends Controller
{
    protected $cargaMasivaService;

    public function __construct(CargaMasivaService $cargaMasivaService)
    {
        $this->cargaMasivaService = $cargaMasivaService;
    }

    /**
     * Bulk upload postulantes via CSV.
     */
    public function cargarPostulantes(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('archivo');
        $adminUserId = $request->user()->id;

        try {
            $importedCount = $this->cargaMasivaService->importPostulantes($file->getRealPath(), $adminUserId);
            return response()->json([
                'success' => true,
                'message' => "Se importaron correctamente {$importedCount} postulantes."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la carga masiva: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Bulk upload docentes via CSV.
     */
    public function cargarDocentes(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('archivo');
        $adminUserId = $request->user()->id;

        try {
            $importedCount = $this->cargaMasivaService->importDocentes($file->getRealPath(), $adminUserId);
            return response()->json([
                'success' => true,
                'message' => "Se importaron correctamente {$importedCount} docentes."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la carga masiva: ' . $e->getMessage()
            ], 400);
        }
    }
}
