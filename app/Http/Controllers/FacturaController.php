<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Mail\FacturaPdfMail;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaController extends Controller
{
    /**
     * Mostrar lista de facturas organizadas por estado.
     */
    public function index()
    {
        $pendientes = Factura::with(['pedido', 'usuario'])
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        $pagadas = Factura::with(['pedido', 'usuario'])
            ->where('estado', 'pagada')
            ->orderBy('created_at', 'desc')
            ->get();

        $anuladas = Factura::with(['pedido', 'usuario'])
            ->where('estado', 'anulada')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('facturas.index', compact('pendientes', 'pagadas', 'anuladas'));
    }

    /**
     * Crear una factura a partir de un pedido.
     */
    public function create(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id'
        ]);

        $pedido = Pedido::findOrFail($request->pedido_id);

        // Usar la lógica del modelo Pedido para generar o actualizar la factura
        $factura = $pedido->generarOrUpdateFactura();

        return redirect()->route('facturas.index')
            ->with('success', 'Factura ' . $factura->numero_factura . ' generada correctamente para el pedido #' . $pedido->numero_pedido);
    }

    /**
     * Mostrar detalle de una factura.
     */
    public function show(Factura $factura)
    {
        $factura->load(['pedido.detalles.plato', 'usuario']);
        return view('facturas.show', compact('factura'));
    }

    /**
     * Actualizar campos específicos de la factura y recalcular total.
     */
    public function update(Request $request, Factura $factura)
    {
        $request->validate([
            'cliente_nombre' => 'required|string|max:255',
            'cliente_nit' => 'nullable|string|max:20',
            'cliente_telefono' => 'nullable|string|max:20',
            'descuento' => 'required|numeric|min:0|max:' . $factura->subtotal,
            'metodo_pago' => 'required|in:efectivo,tarjeta,qr,transferencia',
        ]);

        $factura->fill($request->only([
            'cliente_nombre',
            'cliente_nit',
            'cliente_telefono',
            'descuento',
            'metodo_pago'
        ]));

        $factura->recalculateTotal();
        $factura->save();

        return redirect()->back()->with('success', 'Factura actualizada correctamente');
    }

    /**
     * Marcar factura como pagada.
     */
    public function pagar(Request $request, Factura $factura)
    {
        // Seguridad: Si es cliente, solo puede pagar sus propias facturas
        if (Auth::user()->role === 'cliente' && $factura->pedido->usuario_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'metodo_pago' => 'sometimes|required|in:efectivo,tarjeta,qr,transferencia',
        ]);

        if ($request->has('metodo_pago')) {
            $factura->metodo_pago = $request->metodo_pago;
        }

        $factura->estado = Factura::ESTADO_PAGADA;
        $factura->save();

        if ($factura->pedido) {
            $pedido = $factura->pedido;
            $pedido->loadMissing('usuario');

            $esClienteEnCocina = optional($pedido->usuario)->role === 'cliente'
                && in_array($pedido->estado, [
                    Pedido::ESTADO_PENDIENTE,
                    Pedido::ESTADO_EN_PREPARACION,
                    Pedido::ESTADO_LISTO,
                ]);

            if ($esClienteEnCocina) {
                // Pedido de autoservicio del cliente recién pagado: NO cerrarlo a
                // facturado. Debe ENTRAR a la cocina (regla "primero paga, luego se
                // prepara"), igual que el webhook del QR. Se avisa al cocinero.
                try {
                    event(new \App\Events\PedidoCreado($pedido));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('No se pudo notificar a cocina (factura pagar cliente): ' . $e->getMessage());
                }
            } else {
                // Mesa/cajero o pedido ya entregado: cerrar la cuenta a facturado.
                $pedido->update(['estado' => Pedido::ESTADO_FACTURADO]);
            }
        }

        // Avisar en vivo (mesero/cajero/admin) que esta cuenta se pagó (tu mejora).
        // El pago ya quedó guardado; si Reverb falla, no rompemos la respuesta.
        try {
            $factura->loadMissing('pedido.mesa');
            broadcast(new \App\Events\CuentaPagada([
                'mesa'           => $factura->pedido?->mesa?->numero_mesa,
                'numero_factura' => $factura->numero_factura,
                'total'          => number_format($factura->total, 2),
                'metodo'         => $factura->metodo_pago,
                'origen'         => 'factura',
            ]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo emitir CuentaPagada (factura): ' . $e->getMessage());
        }

        // Respuesta AJAX/JSON del grupo (p. ej. cobro desde modal).
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Factura pagada']);
        }

        return redirect()->back()->with('success', 'Factura ' . $factura->numero_factura . ' marcada como PAGADA');
    }

    /**
     * Marcar factura como anulada.
     */
    public function anular(Factura $factura)
    {
        $factura->estado = Factura::ESTADO_ANULADA;
        $factura->save();

        return redirect()->back()->with('success', 'Factura ' . $factura->numero_factura . ' marcada como ANULADA');
    }

    /**
     * Eliminar factura físicamente (opcional).
     */
    public function destroy(Factura $factura)
    {
        $factura->delete();
        return redirect()->route('facturas.index')->with('success', 'Factura eliminada definitivamente');
    }

    /**
     * Generar código QR para pago de una factura.
     * GET /facturas/{factura}/generar-qr
     */
    public function generarQr(Factura $factura)
    {
        // Seguridad: Si es cliente, solo puede ver sus propias facturas
        if (Auth::user()->role === 'cliente' && $factura->pedido->usuario_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $factura->load('pedido');

        // Cliente solo puede generar QR de su propia factura
        $user = Auth::user();
        if ($user && $user->isCliente()) {
            if (!$factura->pedido || $factura->pedido->usuario_id !== $user->id) {
                abort(403, 'No puedes generar el QR de una factura ajena.');
            }
        }

        // Identificador único del canal Pusher para este pago
        $emisor = 'fact' . $factura->id;

        // Construir URL del QR con parámetros dinámicos
        $params = [
            'i'         => 1,
            'cliente'   => $factura->cliente_nombre,
            'monto'     => $factura->total,
            'descuento' => $factura->descuento,
            'emisor'    => $emisor,
            'pedido'    => $factura->pedido_id,
        ];


        // El QR apunta a la página de pago DENTRO de esta misma app (/pago-externo),
        // así el cliente escanea, paga y la confirmación llega directo a este
        // servidor (sin depender de un sitio externo). Se puede sobreescribir con
        // APP_URL_QR si se quiere usar otro simulador.
        $baseUrl = rtrim(env('APP_URL_QR', config('app.url')), '/');
        $url = $baseUrl . '/pago-externo?' . http_build_query($params);

        // Generar QR 300x300 en formato SVG
        $qrSvg = (string) QrCode::size(300)
            ->style('round')
            ->eye('circle')
            ->margin(1)
            ->generate($url);

        return response()->json([
            'qr_svg' => $qrSvg,
            'emisor' => $emisor,
            'url'    => $url,
            'factura' => [
                'id'              => $factura->id,
                'numero_factura'  => $factura->numero_factura,
                'cliente_nombre'  => $factura->cliente_nombre,
                'total'           => number_format($factura->total, 2),
                'descuento'       => number_format($factura->descuento, 2),
                'subtotal'        => number_format($factura->subtotal, 2),
                'impuesto'        => '0.00', // IVA desactivado
                'pedido_id'       => $factura->pedido_id,
            ],
        ]);
    }

    /**
     * Previsualizar o descargar el PDF de una factura.
     * Útil para verificar el formato y para que admin/cajero ofrezcan
     * una descarga al cliente sin pasar por email.
     */
    public function descargarPdf(Factura $factura)
    {
        $factura->load(['pedido.detalles.plato.categoria', 'pedido.mesa', 'pedido.usuario', 'usuario']);

        // Cliente solo puede descargar su propia factura
        $user = Auth::user();
        if ($user && $user->isCliente()) {
            if (!$factura->pedido || $factura->pedido->usuario_id !== $user->id) {
                abort(403, 'No puedes descargar facturas ajenas.');
            }
        }

        $pdf = Pdf::loadView('facturas.pdf', compact('factura'));
        return $pdf->stream('factura-' . $factura->numero_factura . '.pdf');
    }

    /**
     * Generar PDF y enviar factura por correo electrónico.
     */
    public function enviarPorCorreo(Request $request, Factura $factura)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Cargar las relaciones necesarias para el PDF
        $factura->load(['pedido.detalles.plato', 'usuario']);

        // Cliente solo puede enviar la factura de su propio pedido
        $user = Auth::user();
        if ($user && $user->isCliente()) {
            if (!$factura->pedido || $factura->pedido->usuario_id !== $user->id) {
                abort(403, 'No puedes enviar la factura de un pedido ajeno.');
            }
        }

        $email = $request->input('email');

        // Generar el PDF
        $pdf = Pdf::loadView('facturas.pdf', compact('factura'));
        $pdfOutput = $pdf->output();

        // Enviar el correo
        try {
            Mail::to($email)->send(new FacturaPdfMail($factura, $pdfOutput));
            return redirect()->back()->with('success', 'Factura enviada correctamente a ' . $email);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }
    }
}
