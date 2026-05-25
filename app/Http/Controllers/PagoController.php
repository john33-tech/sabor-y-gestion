<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Módulo de Pagos QR.
 *
 * Lista las facturas con su estado de cobro y muestra el QR de las pendientes
 * para que el cliente escanee y pague. La confirmación llega por webhook a
 * PagoQrController::confirmar (POST /api/pago-qr/confirmar) que dispara un
 * evento Pusher consumido aquí en el front para refrescar en vivo.
 */
class PagoController extends Controller
{
    public function index(Request $request)
    {
        $desde = $request->input('desde', Carbon::today()->toDateString());
        $hasta = $request->input('hasta', Carbon::today()->toDateString());
        $estado = $request->input('estado', 'todos'); // todos|pendiente|pagada|anulada
        $metodo = $request->input('metodo', 'todos'); // todos|qr|efectivo|tarjeta

        $query = Factura::with(['pedido', 'usuario'])
            ->whereBetween('fecha_emision', [
                Carbon::parse($desde)->startOfDay(),
                Carbon::parse($hasta)->endOfDay(),
            ])
            ->orderByDesc('fecha_emision');

        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }
        if ($metodo !== 'todos') {
            $query->where('metodo_pago', $metodo);
        }

        $facturas = $query->paginate(20)->withQueryString();

        // KPIs del rango filtrado (sin paginar).
        $base = Factura::whereBetween('fecha_emision', [
            Carbon::parse($desde)->startOfDay(),
            Carbon::parse($hasta)->endOfDay(),
        ]);

        $kpis = [
            'total_pagado'    => (clone $base)->where('estado', Factura::ESTADO_PAGADA)->sum('total'),
            'total_pendiente' => (clone $base)->where('estado', Factura::ESTADO_PENDIENTE)->sum('total'),
            'count_pagadas'   => (clone $base)->where('estado', Factura::ESTADO_PAGADA)->count(),
            'count_pendientes'=> (clone $base)->where('estado', Factura::ESTADO_PENDIENTE)->count(),
            'pagado_qr'       => (clone $base)->where('estado', Factura::ESTADO_PAGADA)->where('metodo_pago', 'qr')->sum('total'),
            'pagado_efectivo' => (clone $base)->where('estado', Factura::ESTADO_PAGADA)->where('metodo_pago', 'efectivo')->sum('total'),
            'pagado_tarjeta'  => (clone $base)->where('estado', Factura::ESTADO_PAGADA)->where('metodo_pago', 'tarjeta')->sum('total'),
        ];

        return view('pagos.index', compact('facturas', 'kpis', 'desde', 'hasta', 'estado', 'metodo'));
    }

    public function show(Factura $pago)
    {
        // Reusamos la ruta show con la factura como pago.
        $pago->load(['pedido.detalles.plato', 'usuario']);
        return view('pagos.show', ['factura' => $pago]);
    }

    // Métodos resource no usados — redirigen al index.
    public function create() { return redirect()->route('pagos.index'); }
    public function store(Request $r) { return redirect()->route('pagos.index'); }
    public function edit($id) { return redirect()->route('pagos.index'); }
    public function update(Request $r, $id) { return redirect()->route('pagos.index'); }
    public function destroy($id) { return redirect()->route('pagos.index'); }
}
