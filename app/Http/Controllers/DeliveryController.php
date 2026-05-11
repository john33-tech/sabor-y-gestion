<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * GPS Delivery: gestiona los pedidos tipo "delivery" en un mapa.
 *
 * Usa los campos latitud/longitud/direccion del modelo Pedido. Cada pedido
 * delivery aparece como un pin en el mapa Leaflet (OpenStreetMap, sin API key)
 * con su estado, total y dirección.
 */
class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->input('estado', 'activos'); // activos|pendiente|en_preparacion|listo|entregado|todos

        $query = Pedido::with(['detalles.plato', 'usuario'])
            ->where('tipo_pedido', Pedido::TIPO_DELIVERY)
            ->orderByDesc('created_at');

        if ($estado === 'activos') {
            $query->whereIn('estado', [
                Pedido::ESTADO_PENDIENTE,
                Pedido::ESTADO_EN_PREPARACION,
                Pedido::ESTADO_LISTO,
            ]);
        } elseif ($estado !== 'todos') {
            $query->where('estado', $estado);
        }

        $pedidos = $query->limit(100)->get();

        $kpis = [
            'total_hoy'    => Pedido::where('tipo_pedido', Pedido::TIPO_DELIVERY)
                                ->whereDate('created_at', Carbon::today())
                                ->count(),
            'pendientes'   => Pedido::where('tipo_pedido', Pedido::TIPO_DELIVERY)
                                ->where('estado', Pedido::ESTADO_PENDIENTE)
                                ->count(),
            'en_camino'    => Pedido::where('tipo_pedido', Pedido::TIPO_DELIVERY)
                                ->where('estado', Pedido::ESTADO_LISTO)
                                ->count(),
            'entregados_hoy' => Pedido::where('tipo_pedido', Pedido::TIPO_DELIVERY)
                                ->where('estado', Pedido::ESTADO_ENTREGADO)
                                ->whereDate('fecha_hora_entrega', Carbon::today())
                                ->count(),
        ];

        // Pins para el mapa: solo los que tienen coordenadas válidas.
        $puntos = $pedidos->filter(fn($p) => $p->latitud && $p->longitud)
            ->map(fn($p) => [
                'id'             => $p->id,
                'numero'         => $p->numero_pedido,
                'cliente'        => $p->cliente_nombre,
                'telefono'       => $p->cliente_telefono,
                'direccion'      => $p->direccion,
                'lat'            => (float) $p->latitud,
                'lng'            => (float) $p->longitud,
                'estado'         => $p->estado,
                'total'          => (float) $p->total,
                'creado'         => optional($p->created_at)->format('H:i'),
            ])->values();

        return view('delivery.index', compact('pedidos', 'kpis', 'estado', 'puntos'));
    }

    public function show(Pedido $delivery)
    {
        $delivery->load(['detalles.plato', 'usuario', 'factura']);
        return view('delivery.show', ['pedido' => $delivery]);
    }

    public function update(Request $request, Pedido $delivery)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_preparacion,listo,entregado,cancelado',
        ]);
        $delivery->actualizarEstado($request->estado);
        return back()->with('success', 'Estado del pedido actualizado.');
    }

    // Métodos resource no usados.
    public function create()  { return redirect()->route('delivery.index'); }
    public function store(Request $r) { return redirect()->route('delivery.index'); }
    public function edit($id) { return redirect()->route('delivery.index'); }
    public function destroy($id) { return redirect()->route('delivery.index'); }
}
