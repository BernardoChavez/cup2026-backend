<?php

namespace App\Modules\Pago\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Postulante;
use App\Modules\Audit\Services\AuditLogger;

class PagoController extends Controller
{
    /**
     * Register payment details (Postulante).
     */
    public function registrarPago(Request $request)
    {
        $user = $request->user();

        // Get matching postulante
        if ($user->rol === 'POSTULANTE') {
            $postulante = Postulante::where('id_usuario', $user->id)->firstOrFail();
        } else {
            // Admin can register on behalf of a student
            $request->validate(['id_postulante' => 'required|integer|exists:postulantes,id']);
            $postulante = Postulante::findOrFail($request->id_postulante);
        }

        $fields = $request->validate([
            'nro_transaccion_pago' => 'required|string|max:100|unique:postulantes,nro_transaccion_pago,' . $postulante->id,
            'monto_pago' => 'required|numeric|min:0'
        ]);

        $postulante->update([
            'nro_transaccion_pago' => $fields['nro_transaccion_pago'],
            'monto_pago' => $fields['monto_pago'],
            // Remains false until reconciled
        ]);

        // Audit log
        AuditLogger::log(
            $user->id,
            'REGISTRAR_PAGO',
            'postulantes',
            $postulante->id,
            "El postulante CI: {$postulante->ci} registró el comprobante de pago transacción Nro: {$fields['nro_transaccion_pago']}."
        );

        return response()->json([
            'success' => true,
            'message' => 'Comprobante de pago registrado con éxito. Pendiente de conciliación.',
            'postulante' => $postulante
        ]);
    }

    /**
     * List all postulantes with pending payment confirmation (Admin).
     */
    public function listarPendientes()
    {
        $pendientes = Postulante::with('usuario')
            ->where('pago_procesado', false)
            ->whereNotNull('nro_transaccion_pago')
            ->get();

        return response()->json($pendientes);
    }

    /**
     * Reconcile/Approve payment (Admin).
     */
    public function conciliarPago(Request $request, $id)
    {
        $postulante = Postulante::findOrFail($id);

        if ($postulante->pago_procesado) {
            return response()->json([
                'success' => false,
                'message' => 'Este pago ya ha sido conciliado anteriormente.'
            ], 400);
        }

        $postulante->update([
            'pago_procesado' => true
        ]);

        // Audit log
        AuditLogger::log(
            $request->user()->id,
            'CONCILIAR_PAGO',
            'postulantes',
            $postulante->id,
            "Se concilió el pago para el postulante CI: {$postulante->ci}. Monto: {$postulante->monto_pago}."
        );

        return response()->json([
            'success' => true,
            'message' => 'Pago conciliado y aprobado con éxito.',
            'postulante' => $postulante
        ]);
    }
}
