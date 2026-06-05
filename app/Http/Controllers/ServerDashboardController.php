<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\Factura;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $data = $this->getDashboardData();
        
        return view('dashboard.mesero.index', $data);
    }

    public function refreshData()
    {
        return response()->json($this->getDashboardData());
    }

    private function getDashboardData()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Personal
        $personal = [
            'name' => $user->name,
            'status' => 'Trabajando',
            'start_time' => $user->created_at->format('H:i'), // Dummy start time
            'worked_hours' => 8, // Dummy
            'worked_minutes' => 30, // Dummy
        ];

        // Daily stats
        $ordersServed = Pedido::where('usuario_id', $user->id)
            ->whereDate('created_at', $today)
            ->whereIn('estado', [Pedido::ESTADO_ENTREGADO, Pedido::ESTADO_FACTURADO])
            ->count();
            
        $pendingOrders = Pedido::where('usuario_id', $user->id)
            ->whereDate('created_at', $today)
            ->whereIn('estado', [Pedido::ESTADO_PENDIENTE, Pedido::ESTADO_EN_PREPARACION, Pedido::ESTADO_LISTO])
            ->count();
            
        $cancelledOrders = Pedido::where('usuario_id', $user->id)
            ->whereDate('created_at', $today)
            ->where('estado', Pedido::ESTADO_CANCELADO)
            ->count();

        $daily = [
            'orders_served' => $ordersServed,
            'pending_orders' => $pendingOrders,
            'cancelled_orders' => $cancelledOrders,
            'tables_served' => Pedido::where('usuario_id', $user->id)
                ->whereDate('created_at', $today)
                ->distinct('mesa_id')
                ->count(),
            'avg_service_time' => 25, // Dummy
        ];

        // Performance
        $salesToday = Factura::where('usuario_id', $user->id)
            ->whereDate('fecha_emision', $today)
            ->where('estado', Factura::ESTADO_PAGADA)
            ->sum('total');

        $salesYesterday = Factura::where('usuario_id', $user->id)
            ->whereDate('fecha_emision', Carbon::yesterday())
            ->where('estado', Factura::ESTADO_PAGADA)
            ->sum('total');

        $comparison = 0;
        if ($salesYesterday > 0) {
            $comparison = round((($salesToday - $salesYesterday) / $salesYesterday) * 100, 1);
        } elseif ($salesToday > 0) {
            $comparison = 100;
        }

        $performance = [
            'sales_today' => (float)$salesToday,
            'comparison' => $comparison,
            'products_sold' => DetallePedido::whereHas('pedido', function($q) use ($user, $today) {
                    $q->where('usuario_id', $user->id)->whereDate('created_at', $today);
                })->sum('cantidad'),
        ];

        // Tables
        $mesas = Mesa::all();
        
        // Get all tables with pending orders in one query
        $tablesWithPendingBill = Pedido::whereIn('estado', [Pedido::ESTADO_PENDIENTE, Pedido::ESTADO_EN_PREPARACION, Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])
            ->whereHas('factura', function($q) {
                $q->where('estado', Factura::ESTADO_PENDIENTE);
            })
            ->pluck('mesa_id')
            ->unique()
            ->toArray();

        $tables = [
            'occupied' => $mesas->where('estado', 'ocupado')->count(),
            'available' => $mesas->where('estado', 'libre')->count(),
            'reserved' => $mesas->where('estado', 'reservado')->count(),
            'pending_bill' => Pedido::where('estado', Pedido::ESTADO_ENTREGADO)
                ->whereHas('factura', function($q) {
                    $q->where('estado', Factura::ESTADO_PENDIENTE);
                })->distinct('mesa_id')->count(),
            'all_tables' => $mesas->map(fn($m) => [
                'id' => $m->id,
                'number' => $m->numero_mesa,
                'status' => $m->estado,
                'has_pending_bill' => in_array($m->id, $tablesWithPendingBill)
            ]),
        ];

        // Orders
        $orders = [
            'new' => Pedido::where('estado', Pedido::ESTADO_PENDIENTE)->latest()->get(),
            'preparation' => Pedido::where('estado', Pedido::ESTADO_EN_PREPARACION)->latest()->get(),
            'ready' => Pedido::where('estado', Pedido::ESTADO_LISTO)->latest()->get(),
            'recent_delivered' => Pedido::where('estado', Pedido::ESTADO_ENTREGADO)->latest()->limit(10)->get(),
        ];

        // Notifications
        $notifications = [
            'special_comments' => Pedido::whereNotNull('notas')->where('created_at', '>=', now()->subHours(4))->latest()->get(),
        ];

        // Reservations
        $reservations = Mesa::where('estado', 'reservado')
            ->select('numero_mesa', 'hora_reserva as fecha_reserva', 'hora_reserva', 'capacidad as personas', DB::raw("'confirmada' as estado"))
            ->whereNotNull('hora_reserva')
            ->get();
            
        // Also include from Reservas table if any
        $actualReservations = Reserva::whereDate('fecha_reserva', $today)
            ->where('estado', '!=', 'cancelada')
            ->with('mesa')
            ->get()
            ->map(fn($r) => (object)[
                'numero_mesa' => $r->mesa->numero_mesa ?? 'N/A',
                'fecha_reserva' => $r->fecha_reserva,
                'hora_reserva' => $r->hora_reserva,
                'personas' => $r->personas,
                'estado' => $r->estado,
            ]);
            
        $reservations = $reservations->concat($actualReservations);

        $personal['name'] = $user->name;

        return compact('personal', 'daily', 'performance', 'tables', 'orders', 'notifications', 'reservations');
    }
}
