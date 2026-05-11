<?php

namespace App\Http\Controllers;

use App\Events\PagoConfirmadoEvent;
use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PagoQrController extends Controller
{
    /**
     * Webhook para que el sistema externo de QR confirme un pago.
     * POST /api/pago-qr/confirmar
     * No requiere autenticación ni CSRF (es llamado externamente).
     */
    public function confirmar(Request $request)
    {
        $request->validate([
            'emisor' => 'required|string',
            'pedido' => 'required',
            'monto'  => 'required|numeric',
        ]);

        $emisor = $request->input('emisor');
        $pedidoId = $request->input('pedido');

        // Buscar la factura asociada al pedido
        $factura = Factura::where('pedido_id', $pedidoId)
            ->where('estado', 'pendiente')
            ->first();

        if (!$factura) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada o ya fue procesada.'
            ], 404);
        }

        // Marcar factura como pagada con método QR
        $factura->metodo_pago = 'qr';
        $factura->estado = Factura::ESTADO_PAGADA;
        $factura->save();

        // FIX: NO movemos el pedido a 'facturado' acá. Si lo hacemos, cocina
        // pierde de vista el pedido (ComandaController filtra por
        // pendiente/en_preparacion/listo) y nunca lo cocina.
        // El pedido pasa a 'facturado' recién cuando se entrega (flujo
        // normal: pendiente → en_preparacion → listo → entregado → facturado).
        // El estado de cobro ya queda reflejado en la factura.

        // Disparar evento Pusher para notificar al frontend
        broadcast(new PagoConfirmadoEvent($emisor, [
            'status'         => 'success',
            'factura_id'     => $factura->id,
            'numero_factura' => $factura->numero_factura,
            'monto'          => $factura->total,
            'cliente'        => $factura->cliente_nombre,
            'mensaje'        => '¡Pago QR confirmado para ' . $factura->numero_factura . '!',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Pago confirmado exitosamente.',
            'factura' => $factura->numero_factura,
        ]);
    }
}
