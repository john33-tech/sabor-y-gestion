<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            'descuento' => 'required|numeric|min:0|max:' . ($factura->subtotal + $factura->impuesto),
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
        $request->validate([
            'metodo_pago' => 'sometimes|required|in:efectivo,tarjeta,qr,transferencia',
        ]);

        if ($request->has('metodo_pago')) {
            $factura->metodo_pago = $request->metodo_pago;
        }

        $factura->estado = Factura::ESTADO_PAGADA;
        $factura->save();

        if ($factura->pedido) {
            $factura->pedido->update(['estado' => Pedido::ESTADO_FACTURADO]);
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
        $factura->load('pedido');

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

        $url = 'https://proyecto-tis-umss.infinityfreeapp.com/?' . http_build_query($params);

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
                'impuesto'        => number_format($factura->impuesto, 2),
                'pedido_id'       => $factura->pedido_id,
            ],
        ]);
    }
}
