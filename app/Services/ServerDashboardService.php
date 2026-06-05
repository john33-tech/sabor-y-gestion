<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ServerDashboardService
{
    protected $userId;
    protected $cacheTtl = 120; // 2 minutos

    public function __construct()
    {
        $this->userId = Auth::id();
    }

    // Resumen personal (turno, tiempo trabajado)
    public function getPersonalSummary()
    {
        return Cache::remember("server_personal_summary_{$this->userId}", $this->cacheTtl, function () {
            $user = Auth::user();
            $session = DB::table('sessions')
                ->where('user_id', $this->userId)
                ->orderBy('last_activity', 'desc')
                ->first();

            $startTime = $session ? \Carbon\Carbon::createFromTimestamp($session->last_activity) : now();
            $now = now();
            $worked = $startTime->diff($now);
            $active = $session && ($now->timestamp - $session->last_activity) < 300; // 5 min

            return [
                'name' => $user->name,
                'status' => $active ? 'Activo' : 'Inactivo',
                'start_time' => $startTime->format('H:i'),
                'worked_hours' => $worked->h,
                'worked_minutes' => $worked->i,
            ];
        });
    }

    // Actividad diaria
    public function getDailyActivity()
    {
        return Cache::remember("server_daily_activity_{$this->userId}", $this->cacheTtl, function () {
            $today = now()->toDateString();

            $ordersServed = DB::table('pedidos')
                ->where('usuario_id', $this->userId)
                ->where('estado', 'entregado')
                ->whereDate('created_at', $today)
                ->count();

            $tablesServed = DB::table('pedidos')
                ->where('usuario_id', $this->userId)
                ->where('estado', 'entregado')
                ->whereDate('created_at', $today)
                ->whereNotNull('mesa_id')
                ->distinct('mesa_id')
                ->count('mesa_id');

            $pendingOrders = DB::table('pedidos')
                ->where('usuario_id', $this->userId)
                ->whereNotIn('estado', ['entregado', 'cancelado', 'facturado'])
                ->count();

            $deliveredOrders = $ordersServed; // mismo valor

            $cancelledOrders = DB::table('pedidos')
                ->where('usuario_id', $this->userId)
                ->where('estado', 'cancelado')
                ->whereDate('created_at', $today)
                ->count();

            $avgServiceTime = DB::table('pedidos')
                ->where('usuario_id', $this->userId)
                ->where('estado', 'entregado')
                ->whereNotNull('updated_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
                ->value('avg_minutes');

            return [
                'orders_served' => $ordersServed,
                'tables_served' => $tablesServed,
                'pending_orders' => $pendingOrders,
                'delivered_orders' => $deliveredOrders,
                'cancelled_orders' => $cancelledOrders,
                'avg_service_time' => round($avgServiceTime ?? 0),
            ];
        });
    }

    // Gestión de mesas (ocupadas, libres, con cuenta pendiente)
    public function getTablesOverview()
    {
        return Cache::remember('server_tables_overview', $this->cacheTtl, function () {
            // Todas las mesas con su estado
            $tables = DB::table('mesas')->get(['id', 'numero_mesa', 'estado']);

            // Pedidos con facturas pendientes (para identificar mesas con cuenta pendiente)
            $pendingInvoices = DB::table('facturas')
                ->where('estado', 'pendiente')
                ->pluck('pedido_id')
                ->toArray();

            $tablesWithPendingBill = [];
            if (!empty($pendingInvoices)) {
                $tablesWithPendingBill = DB::table('pedidos')
                    ->whereIn('id', $pendingInvoices)
                    ->whereNotNull('mesa_id')
                    ->pluck('mesa_id')
                    ->toArray();
            }

            $occupied = $tables->where('estado', 'ocupado')->count();
            $available = $tables->where('estado', 'libre')->count();
            $reserved = $tables->where('estado', 'reservado')->count();

            $pendingBillCount = count($tablesWithPendingBill);

            return [
                'occupied' => $occupied,
                'available' => $available,
                'reserved' => $reserved,
                'pending_bill' => $pendingBillCount,
                'all_tables' => $tables->map(function ($table) use ($tablesWithPendingBill) {
                    return [
                        'id' => $table->id,
                        'number' => $table->numero_mesa,
                        'status' => $table->estado,
                        'has_pending_bill' => in_array($table->id, $tablesWithPendingBill),
                    ];
                }),
            ];
        });
    }

    // Rendimiento personal (ventas, productos, comparativa)
    public function getPersonalPerformance()
    {
        return Cache::remember("server_performance_{$this->userId}", $this->cacheTtl, function () {
            $today = now()->toDateString();
            $yesterday = now()->subDay()->toDateString();

            $salesToday = DB::table('pedidos')
                ->where('usuario_id', $this->userId)
                ->where('estado', 'entregado')
                ->whereDate('created_at', $today)
                ->sum('total') ?? 0;

            $salesYesterday = DB::table('pedidos')
                ->where('usuario_id', $this->userId)
                ->where('estado', 'entregado')
                ->whereDate('created_at', $yesterday)
                ->sum('total') ?? 0;

            $productsSold = DB::table('detalle_pedidos')
                ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
                ->where('pedidos.usuario_id', $this->userId)
                ->where('pedidos.estado', 'entregado')
                ->whereDate('pedidos.created_at', $today)
                ->sum('detalle_pedidos.cantidad');

            $comparison = $salesYesterday > 0
                ? round((($salesToday - $salesYesterday) / $salesYesterday) * 100, 1)
                : ($salesToday > 0 ? 100 : 0);

            return [
                'sales_today' => $salesToday,
                'products_sold' => $productsSold,
                'comparison' => $comparison,
            ];
        });
    }

    // Pedidos en tiempo real (agrupados por estado)
    public function getRealTimeOrders()
    {
        // Sin caché para mantener actualización
        $orders = DB::table('pedidos')
            ->where('usuario_id', $this->userId)
            ->whereNotIn('estado', ['entregado', 'cancelado', 'facturado'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'numero_pedido', 'mesa_id', 'estado', 'created_at', 'total']);

        $new = $orders->where('estado', 'pendiente')->values();
        $preparation = $orders->where('estado', 'en_preparacion')->values();
        $ready = $orders->where('estado', 'listo')->values();

        // Últimos entregados (para historial rápido)
        $recentDelivered = DB::table('pedidos')
            ->where('usuario_id', $this->userId)
            ->where('estado', 'entregado')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['id', 'numero_pedido', 'mesa_id', 'total', 'updated_at']);

        return [
            'new' => $new,
            'preparation' => $preparation,
            'ready' => $ready,
            'recent_delivered' => $recentDelivered,
        ];
    }

    // Notificaciones (comentarios especiales en pedidos pendientes)
    public function getNotifications()
    {
        return Cache::remember("server_notifications_{$this->userId}", 1, function () {
            // Comentarios en detalles de pedidos pendientes o en preparación
            $specialComments = DB::table('detalle_pedidos')
                ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
                ->where('pedidos.usuario_id', $this->userId)
                ->whereIn('pedidos.estado', ['pendiente', 'en_preparacion'])
                ->whereNotNull('detalle_pedidos.notas')
                ->where('detalle_pedidos.notas', '!=', '')
                ->select('pedidos.numero_pedido', 'detalle_pedidos.notas', 'detalle_pedidos.created_at')
                ->orderBy('detalle_pedidos.created_at', 'desc')
                ->limit(5)
                ->get();

            // Llamados de mesa (simulado: si no existe, se omite)
            $tableCalls = collect(); // se podría implementar con evento

            return [
                'special_comments' => $specialComments,
                'table_calls' => $tableCalls,
            ];
        });
    }

    // Próximas reservas
    public function getUpcomingReservations()
    {
        return Cache::remember('server_upcoming_reservations', 60, function () {
            return DB::table('reservas')
                ->join('mesas', 'reservas.mesa_id', '=', 'mesas.id')
                ->where('reservas.fecha_reserva', '>=', now()->toDateString())
                ->whereIn('reservas.estado', ['pendiente', 'confirmada'])
                ->orderBy('reservas.fecha_reserva')
                ->orderBy('reservas.hora_reserva')
                ->limit(10)
                ->get([
                    'reservas.id',
                    'mesas.numero_mesa',
                    'reservas.fecha_reserva',
                    'reservas.hora_reserva',
                    'reservas.personas',
                    'reservas.estado',
                    'reservas.notas',
                ]);
        });
    }
}
