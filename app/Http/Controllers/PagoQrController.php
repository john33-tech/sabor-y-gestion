<?php

namespace App\Http\Controllers;

use App\Events\PagoConfirmadoEvent;
use App\Mail\FacturaPdfMail;
use App\Models\Factura;
use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PagoQrController extends Controller
{
    /**
     * Página pública del simulador de pago QR. El QR de cada factura apunta
     * a esta URL para que el cliente la abra en su celular y presione "Pagar"
     * sin depender de una app externa.
     */
    public function pagoExterno(Request $request)
    {
        return view('pago-externo.index', [
            'emisor'    => $request->input('emisor', ''),
            'pedido'    => $request->input('pedido', ''),
            'cliente'   => $request->input('cliente', 'Cliente'),
            'monto'     => (float) $request->input('monto', 0),
            'descuento' => (float) $request->input('descuento', 0),
        ]);
    }

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

        // No cobrar un pedido cancelado (su factura puede haber quedado pendiente).
        $factura->loadMissing('pedido');
        if ($factura->pedido && $factura->pedido->estado === Pedido::ESTADO_CANCELADO) {
            return response()->json([
                'success' => false,
                'message' => 'El pedido fue cancelado; no se puede pagar.'
            ], 422);
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

        // Spec #5.1: enviar automáticamente la factura por correo al cliente
        // luego de confirmar el pago QR.
        $correoEnviado = $this->enviarFacturaAlCliente($factura);

        // Disparar evento Pusher para notificar al frontend
        broadcast(new PagoConfirmadoEvent($emisor, [
            'status'           => 'success',
            'factura_id'       => $factura->id,
            'numero_factura'   => $factura->numero_factura,
            'monto'            => $factura->total,
            'cliente'          => $factura->cliente_nombre,
            'correo_enviado'   => $correoEnviado,
            'mensaje'          => '¡Pago QR confirmado para ' . $factura->numero_factura . '!',
        ]));

        // Pedido del CLIENTE: recién al pagar entra a cocina (regla "primero
        // paga, luego se prepara"). Avisamos para que aparezca en el kitchen display.
        $factura->loadMissing('pedido.usuario');
        $pedidoQr = $factura->pedido;
        $esDeCliente = optional($pedidoQr?->usuario)->role === 'cliente';
        if ($pedidoQr
            && $esDeCliente
            && in_array($pedidoQr->estado, [\App\Models\Pedido::ESTADO_PENDIENTE, \App\Models\Pedido::ESTADO_EN_PREPARACION, \App\Models\Pedido::ESTADO_LISTO])) {
            try {
                event(new \App\Events\PedidoCreado($pedidoQr));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo notificar a cocina (pago QR cliente): ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Pago confirmado exitosamente.',
            'factura'        => $factura->numero_factura,
            'correo_enviado' => $correoEnviado,
        ]);
    }

    /**
     * Genera el PDF de la factura y lo envía al correo del usuario asociado
     * al pedido (que es el cliente cuando él mismo creó el pedido).
     * Devuelve true si el correo se despachó, false si falló o no había email.
     */
    private function enviarFacturaAlCliente(Factura $factura): bool
    {
        $factura->loadMissing(['pedido.usuario', 'pedido.detalles.plato']);

        // Preferimos el email capturado en el pedido (cliente_email anotado por
        // el mesero). Fallback al email del usuario que creó el pedido.
        $email = $factura->pedido?->cliente_email
            ?: optional($factura->pedido?->usuario)->email;

        if (!$email) {
            Log::info('Pago QR confirmado pero la factura no tiene email destino', [
                'factura_id' => $factura->id,
            ]);
            return false;
        }

        try {
            $pdfOutput = Pdf::loadView('facturas.pdf', compact('factura'))->output();
            Mail::to($email)->send(new FacturaPdfMail($factura, $pdfOutput));
            return true;
        } catch (\Throwable $e) {
            Log::error('Error enviando factura por correo tras pago QR', [
                'factura_id' => $factura->id,
                'email'      => $email,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
    }
}
