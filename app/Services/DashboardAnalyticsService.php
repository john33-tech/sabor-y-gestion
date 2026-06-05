<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    protected $cacheTtl = 300; // 5 minutos

    public function getGeneralSummary(): array
    {
        return Cache::remember('admin_dashboard_summary', $this->cacheTtl, function () {
            $totalUsers = DB::table('users')->count();
            // Adaptado: este proyecto usa SESSION_DRIVER=file (sin tabla 'sessions'),
            // por lo que no se puede medir usuarios activos por sesión.
            $activeUsers = 0;
            $totalCustomers = DB::table('users')->where('role', 'cliente')->count();
            $totalProducts = DB::table('platos')->count();
            $lowStockProducts = DB::table('inventario')
                ->whereRaw('cantidad_actual <= stock_minimo')
                ->count();
            $totalSales = DB::table('facturas')->where('estado', 'pagada')->sum('total') ?? 0;
            $todaySales = DB::table('facturas')
                ->where('estado', 'pagada')
                ->whereDate('fecha_emision', today())
                ->sum('total') ?? 0;
            $monthlySales = DB::table('facturas')
                ->where('estado', 'pagada')
                ->whereYear('fecha_emision', now()->year)
                ->whereMonth('fecha_emision', now()->month)
                ->sum('total') ?? 0;
            $prevMonthSales = DB::table('facturas')
                ->where('estado', 'pagada')
                ->whereYear('fecha_emision', now()->subMonth()->year)
                ->whereMonth('fecha_emision', now()->subMonth()->month)
                ->sum('total') ?? 0;
            $salesTrend = $prevMonthSales > 0
                ? round((($monthlySales - $prevMonthSales) / $prevMonthSales) * 100, 1)
                : ($monthlySales > 0 ? 100 : 0);
            // Adaptado: 'cierres_caja' no maneja estado abierto/cerrado (es un arqueo con totales),
            // así que el concepto "cajas abiertas" no aplica en este esquema.
            $openRegisters = 0;

            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_customers' => $totalCustomers,
                'total_products' => $totalProducts,
                'low_stock_products' => $lowStockProducts,
                'total_sales' => $totalSales,
                'today_sales' => $todaySales,
                'monthly_sales' => $monthlySales,
                'sales_trend' => $salesTrend,
                'open_registers' => $openRegisters,
            ];
        });
    }

    public function getSalesPerDay(): array
    {
        return Cache::remember('admin_dashboard_sales_per_day', $this->cacheTtl, function () {
            $start = now()->subDays(29)->startOfDay();
            $end = now()->endOfDay();
            $sales = DB::table('facturas')
                ->select(DB::raw('DATE(fecha_emision) as date'), DB::raw('SUM(total) as total'))
                ->where('estado', 'pagada')
                ->whereBetween('fecha_emision', [$start, $end])
                ->groupBy(DB::raw('DATE(fecha_emision)'))
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $labels = [];
            $values = [];
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays(29 - $i)->toDateString();
                $labels[] = now()->subDays(29 - $i)->format('d/m');
                $values[] = isset($sales[$date]) ? (float) $sales[$date]->total : 0;
            }
            return ['labels' => $labels, 'values' => $values];
        });
    }

    public function getSalesPerMonth(): array
    {
        return Cache::remember('admin_dashboard_sales_per_month', $this->cacheTtl, function () {
            $start = now()->subMonths(11)->startOfMonth();
            $end = now()->endOfMonth();
            $sales = DB::table('facturas')
                ->select(DB::raw('DATE_FORMAT(fecha_emision, "%Y-%m") as month'), DB::raw('SUM(total) as total'))
                ->where('estado', 'pagada')
                ->whereBetween('fecha_emision', [$start, $end])
                ->groupBy(DB::raw('DATE_FORMAT(fecha_emision, "%Y-%m")'))
                ->orderBy('month')
                ->get()
                ->keyBy('month');

            $labels = [];
            $values = [];
            $current = $start->copy();
            while ($current <= $end) {
                $monthKey = $current->format('Y-m');
                $labels[] = $current->locale('es')->isoFormat('MMM YYYY');
                $values[] = isset($sales[$monthKey]) ? (float) $sales[$monthKey]->total : 0;
                $current->addMonth();
            }
            return ['labels' => $labels, 'values' => $values];
        });
    }

    public function getTopProducts(): array
    {
        return Cache::remember('admin_dashboard_top_products', $this->cacheTtl, function () {
            return DB::table('detalle_pedidos')
                ->join('platos', 'detalle_pedidos.plato_id', '=', 'platos.id')
                ->select('platos.nombre', DB::raw('SUM(detalle_pedidos.cantidad) as total_quantity'))
                ->groupBy('platos.id', 'platos.nombre')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get()
                ->map(fn($item) => ['name' => $item->nombre, 'quantity' => (int) $item->total_quantity])
                ->toArray();
        });
    }

    public function getTopCategories(): array
    {
        return Cache::remember('admin_dashboard_top_categories', $this->cacheTtl, function () {
            return DB::table('detalle_pedidos')
                ->join('platos', 'detalle_pedidos.plato_id', '=', 'platos.id')
                ->join('categorias', 'platos.categoria_id', '=', 'categorias.id')
                ->select('categorias.nombre', DB::raw('SUM(detalle_pedidos.cantidad) as total_quantity'))
                ->groupBy('categorias.id', 'categorias.nombre')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get()
                ->map(fn($item) => ['name' => $item->nombre, 'quantity' => (int) $item->total_quantity])
                ->toArray();
        });
    }

    public function getPaymentMethods(): array
    {
        return Cache::remember('admin_dashboard_payment_methods', $this->cacheTtl, function () {
            $methods = DB::table('facturas')
                ->select('metodo_pago', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
                ->where('estado', 'pagada')
                ->groupBy('metodo_pago')
                ->get();

            $labels = ['Efectivo', 'Tarjeta', 'QR', 'Transferencia'];
            $counts = [0, 0, 0, 0];
            $totals = [0, 0, 0, 0];

            foreach ($methods as $m) {
                $index = match ($m->metodo_pago) {
                    'efectivo' => 0,
                    'tarjeta' => 1,
                    'qr' => 2,
                    'transferencia' => 3,
                    default => 0,
                };
                $counts[$index] = $m->count;
                $totals[$index] = (float) $m->total;
            }
            return ['labels' => $labels, 'counts' => $counts, 'totals' => $totals];
        });
    }

    public function getAlerts(): array
    {
        return Cache::remember('admin_dashboard_alerts', $this->cacheTtl, function () {
            $outOfStock = DB::table('inventario')
                ->join('ingredientes', 'inventario.ingrediente_id', '=', 'ingredientes.id')
                ->where('inventario.cantidad_actual', '<=', 0)
                ->select('ingredientes.nombre', 'inventario.cantidad_actual', 'ingredientes.unidad_medida')
                ->get();

            $minStock = DB::table('inventario')
                ->join('ingredientes', 'inventario.ingrediente_id', '=', 'ingredientes.id')
                ->whereRaw('inventario.cantidad_actual <= inventario.stock_minimo')
                ->where('inventario.cantidad_actual', '>', 0)
                ->select('ingredientes.nombre', 'inventario.cantidad_actual', 'inventario.stock_minimo', 'ingredientes.unidad_medida')
                ->get();

            $pendingInvoices = DB::table('facturas')->where('estado', 'pendiente')->count();
            $pendingOrders = DB::table('pedidos')->whereNotIn('estado', ['entregado', 'cancelado', 'facturado'])->count();
            // Adaptado: ver nota en getGeneralSummary() — 'cierres_caja' no tiene estado abierto/cerrado.
            $openRegisters = 0;

            return [
                'out_of_stock' => $outOfStock,
                'min_stock' => $minStock,
                'pending_invoices' => $pendingInvoices,
                'pending_orders' => $pendingOrders,
                'open_registers' => $openRegisters,
            ];
        });
    }

    public function getRecentActivity(): array
    {
        $recentInvoices = DB::table('facturas')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'numero_factura', 'total', 'estado', 'created_at']);

        $recentOrders = DB::table('pedidos')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'numero_pedido', 'tipo_pedido', 'estado', 'total', 'created_at']);

        $recentCustomers = DB::table('users')
            ->where('role', 'cliente')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        // Adaptado: sin tabla 'sessions' (SESSION_DRIVER=file) no hay historial de logins.
        $recentLogins = collect();

        return [
            'invoices' => $recentInvoices,
            'orders' => $recentOrders,
            'customers' => $recentCustomers,
            'logins' => $recentLogins,
        ];
    }
}
