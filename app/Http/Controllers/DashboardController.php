<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function administrador()
    {
        $this->authorizeRole('admin');
        
        // Obtenemos una colección vacía para evitar el error de variable indefinida
        // En el futuro, esto se podrá reemplazar con una consulta real a la base de datos
        $productosDestacados = collect();

        // Alerta de inventario: ingredientes con stock bajo (cantidad <= mínimo).
        $stockBajo = \App\Models\Inventario::whereColumn('cantidad_actual', '<=', 'stock_minimo')->count();

        return view('dashboard.administrador.index', compact('productosDestacados', 'stockBajo'));
    }
    
    public function mesero()
    {
        $this->authorizeRole('mesero');

        // Pedidos activos del mesero: pendiente, en preparación y listos para entregar
        $pedidos = \App\Models\Pedido::where('usuario_id', \Illuminate\Support\Facades\Auth::id())
            ->whereIn('estado', [
                \App\Models\Pedido::ESTADO_PENDIENTE,
                \App\Models\Pedido::ESTADO_EN_PREPARACION,
                \App\Models\Pedido::ESTADO_LISTO,
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pedidos que el mesero ya entregó al cajero
        $pedidosFinalizados = \App\Models\Pedido::where('usuario_id', \Illuminate\Support\Facades\Auth::id())
            ->where('estado', \App\Models\Pedido::ESTADO_ENTREGADO)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dashboard.mesero.index', compact('pedidos', 'pedidosFinalizados'));
    }
    
    public function cocinero()
    {
        $this->authorizeRole('cocinero');

        // Mostrar pedidos de hoy: En espera (pendiente) y En preparación
        $query = \App\Models\Pedido::with(['detalles.plato', 'mesa', 'usuario'])
            ->whereDate('created_at', now()->today())
            ->whereIn('estado', [
                \App\Models\Pedido::ESTADO_PENDIENTE,
                \App\Models\Pedido::ESTADO_EN_PREPARACION,
            ]);

        $comandas = $query->orderBy('created_at', 'asc')->paginate(12);

        $tipos = \App\Models\Pedido::getTipos();

        // Estadísticas solo de HOY
        $stats = [
            'total'          => \App\Models\Pedido::whereDate('created_at', now()->today())
                                    ->whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])->count(),
            'pendientes'     => \App\Models\Pedido::whereDate('created_at', now()->today())
                                    ->where('estado', 'pendiente')->count(),
            'en_preparacion' => \App\Models\Pedido::whereDate('created_at', now()->today())
                                    ->where('estado', 'en_preparacion')->count(),
            'listos'         => \App\Models\Pedido::whereDate('created_at', now()->today())
                                    ->where('estado', 'listo')->count(),
        ];

        return view('dashboard.cocinero.index', compact('comandas', 'tipos', 'stats'));
    }
    
    public function cajero()
    {
        $this->authorizeRole('cajero');

        // Todos los pedidos que el mesero marcó como entregados
        $pedidosEntregados = \App\Models\Pedido::with(['usuario', 'mesa', 'detalles.plato'])
            ->where('estado', \App\Models\Pedido::ESTADO_ENTREGADO)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Fase 5/7 (spec): total de INGRESOS DEL DÍA en Bs (facturas pagadas hoy).
        $totalIngresosDia = \App\Models\Factura::where('estado', \App\Models\Factura::ESTADO_PAGADA)
            ->whereDate('updated_at', now()->today())
            ->sum('total');

        // Desglose por método de pago (para el resumen de caja).
        $ingresosPorMetodo = \App\Models\Factura::where('estado', \App\Models\Factura::ESTADO_PAGADA)
            ->whereDate('updated_at', now()->today())
            ->selectRaw("metodo_pago, SUM(total) as total")
            ->groupBy('metodo_pago')
            ->pluck('total', 'metodo_pago');

        $facturasPagadasHoy = \App\Models\Factura::where('estado', \App\Models\Factura::ESTADO_PAGADA)
            ->whereDate('updated_at', now()->today())
            ->count();

        // Cuentas en espera de cobro (mesas con la cuenta ya solicitada).
        $cuentasPorCobrar = \App\Models\Pedido::where('cuenta_solicitada', true)
            ->whereNotIn('estado', [\App\Models\Pedido::ESTADO_CANCELADO, \App\Models\Pedido::ESTADO_FACTURADO])
            ->distinct('mesa_id')->count('mesa_id');

        return view('dashboard.cajero.index', compact(
            'pedidosEntregados',
            'totalIngresosDia',
            'ingresosPorMetodo',
            'facturasPagadasHoy',
            'cuentasPorCobrar'
        ));
    }
    
    public function cliente()
    {
        $this->authorizeRole('cliente');
        return view('dashboard.cliente.index');
    }

    private function authorizeRole($role)
    {
        if (Auth::user()->role !== $role && Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
    }
}