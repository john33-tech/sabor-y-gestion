@extends('layouts.app')

@section('title', 'Dashboard Ejecutivo')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
    .trend-up { color: #10b981; }
    .trend-down { color: #ef4444; }
</style>
@endpush

@section('content')
<div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
    @if($alerts['pending_orders'] > 0 || $alerts['pending_invoices'] > 0)
    <div class="p-4 mb-6 border-l-4 border-red-500 rounded-lg bg-red-50">
        <div class="flex items-center">
            <i class="mr-3 text-xl text-red-500 fas fa-exclamation-triangle"></i>
            <div>
                <p class="font-semibold text-red-800">Atención requerida</p>
                <p class="text-sm text-red-700">
                    {{ $alerts['pending_orders'] }} pedido(s) pendiente(s) |
                    {{ $alerts['pending_invoices'] }} factura(s) pendiente(s) |
                    {{ $alerts['open_registers'] }} caja(s) abierta(s)
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-5 mb-8 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden bg-white border border-gray-100 rounded-lg shadow-md stat-card">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-orange-100 rounded-full">
                        <i class="text-xl fas fa-chart-line text-primary"></i>
                    </div>
                    <div class="flex-1 ml-4">
                        <p class="text-sm font-medium text-gray-500 truncate">Ventas Totales</p>
                        <p class="text-2xl font-semibold text-gray-900">Bs. {{ number_format($summary['total_sales'], 2) }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center text-sm {{ $summary['sales_trend'] >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="fas fa-arrow-{{ $summary['sales_trend'] >= 0 ? 'up' : 'down' }} mr-1"></i>
                        {{ abs($summary['sales_trend']) }}% vs mes anterior
                    </span>
                    <span class="ml-2 text-sm text-gray-500">Mes actual: Bs. {{ number_format($summary['monthly_sales'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-100 rounded-lg shadow-md stat-card">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-100 rounded-full">
                        <i class="text-xl text-blue-600 fas fa-users"></i>
                    </div>
                    <div class="flex-1 ml-4">
                        <p class="text-sm font-medium text-gray-500 truncate">Usuarios</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $summary['total_users'] }}</p>
                    </div>
                </div>
                <div class="mt-3 text-sm text-gray-500">
                    <span class="text-green-600">{{ $summary['active_users'] }} activos</span> ·
                    <span>{{ $summary['total_customers'] }} clientes</span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-100 rounded-lg shadow-md stat-card">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 rounded-full bg-amber-100">
                        <i class="text-xl fas fa-utensils text-amber-600"></i>
                    </div>
                    <div class="flex-1 ml-4">
                        <p class="text-sm font-medium text-gray-500 truncate">Productos</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $summary['total_products'] }}</p>
                    </div>
                </div>
                <div class="mt-3 text-sm">
                    @if($summary['low_stock_products'] > 0)
                        <span class="text-red-600"><i class="fas fa-exclamation-triangle"></i> {{ $summary['low_stock_products'] }} con stock bajo</span>
                    @else
                        <span class="text-green-600"><i class="fas fa-check-circle"></i> Stock suficiente</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-100 rounded-lg shadow-md stat-card">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-purple-100 rounded-full">
                        <i class="text-xl text-purple-600 fas fa-cash-register"></i>
                    </div>
                    <div class="flex-1 ml-4">
                        <p class="text-sm font-medium text-gray-500 truncate">Cajas Abiertas</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $summary['open_registers'] }}</p>
                    </div>
                </div>
                <div class="mt-3 text-sm text-gray-500">
                    Ventas hoy: <span class="font-semibold text-primary">Bs. {{ number_format($summary['today_sales'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-md">
            <h3 class="flex items-center mb-3 text-lg font-semibold text-gray-800">
                <i class="mr-2 fas fa-calendar-day text-primary"></i> Ventas diarias (últimos 30 días)
            </h3>
            <canvas id="salesDayChart" height="200"></canvas>
        </div>
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-md">
            <h3 class="flex items-center mb-3 text-lg font-semibold text-gray-800">
                <i class="mr-2 fas fa-chart-bar text-primary"></i> Ventas mensuales
            </h3>
            <canvas id="salesMonthChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-md">
            <h3 class="flex items-center mb-3 text-lg font-semibold text-gray-800">
                <i class="mr-2 fas fa-trophy text-primary"></i> Productos más vendidos
            </h3>
            @if(count($topProducts))
                <div class="space-y-3">
                    @foreach($topProducts as $product)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700">{{ $product['name'] }}</span>
                            <div class="flex items-center">
                                <div class="w-48 h-2 mr-2 bg-gray-200 rounded-full">
                                    <div class="h-2 rounded-full bg-primary" style="width: {{ min(100, ($product['quantity'] / max($topProducts[0]['quantity'], 1)) * 100) }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-600">{{ $product['quantity'] }} vendidos</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-4 text-center text-gray-500">Sin datos de ventas aún</p>
            @endif
        </div>

        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-md">
            <h3 class="flex items-center mb-3 text-lg font-semibold text-gray-800">
                <i class="mr-2 fas fa-credit-card text-primary"></i> Métodos de pago
            </h3>
            <canvas id="paymentChart" height="180"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
        <div class="overflow-hidden bg-white border border-gray-100 rounded-lg shadow-md">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="font-semibold text-gray-800"><i class="mr-2 fas fa-file-invoice text-primary"></i> Últimas facturas</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivity['invoices'] as $invoice)
                <div class="px-4 py-3 transition hover:bg-orange-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800">#{{ $invoice->numero_factura }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($invoice->created_at)->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-primary">Bs. {{ number_format($invoice->total, 2) }}</p>
                            <span class="inline-block px-2 py-0.5 text-xs rounded-full
                                @if($invoice->estado == 'pagada') bg-green-100 text-green-800
                                @elseif($invoice->estado == 'pendiente') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($invoice->estado) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-gray-500">Sin facturas recientes</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-100 rounded-lg shadow-md">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="font-semibold text-gray-800"><i class="mr-2 fas fa-receipt text-primary"></i> Últimos pedidos</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivity['orders'] as $order)
                <div class="px-4 py-3 transition hover:bg-orange-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800">#{{ $order->numero_pedido ?? $order->id }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst($order->tipo_pedido) }} · {{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-primary">Bs. {{ number_format($order->total, 2) }}</p>
                            <span class="inline-block px-2 py-0.5 text-xs rounded-full
                                @if($order->estado == 'entregado') bg-green-100 text-green-800
                                @elseif($order->estado == 'pendiente') bg-yellow-100 text-yellow-800
                                @elseif($order->estado == 'en_preparacion') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ str_replace('_', ' ', $order->estado) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-gray-500">Sin pedidos recientes</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-100 rounded-lg shadow-md">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="font-semibold text-gray-800"><i class="mr-2 fas fa-user-plus text-primary"></i> Nuevos clientes</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivity['customers'] as $customer)
                <div class="flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="font-medium text-gray-800">{{ $customer->name }}</p>
                        <p class="text-xs text-gray-500">{{ $customer->email }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($customer->created_at)->diffForHumans() }}</span>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-gray-500">Sin clientes registrados recientemente</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-100 rounded-lg shadow-md">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="font-semibold text-gray-800"><i class="mr-2 fas fa-clock text-primary"></i> Últimos accesos</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivity['logins'] as $login)
                <div class="flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="font-medium text-gray-800">{{ $login->name }}</p>
                        <p class="text-xs text-gray-500">{{ $login->email }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::createFromTimestamp($login->last_activity)->diffForHumans() }}</span>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-gray-500">Sin accesos recientes</div>
                @endforelse
            </div>
        </div>
    </div>

    @if($alerts['out_of_stock']->count() > 0 || $alerts['min_stock']->count() > 0)
    <div class="overflow-hidden bg-white border border-red-100 rounded-lg shadow-md">
        <div class="px-4 py-3 border-b border-red-200 bg-red-50">
            <h3 class="font-semibold text-red-800"><i class="mr-2 fas fa-bell"></i> Alertas de inventario</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @if($alerts['out_of_stock']->count())
                <div class="px-4 py-3">
                    <p class="mb-2 font-medium text-red-700">Ingredientes agotados:</p>
                    <ul class="text-sm text-gray-700 list-disc list-inside">
                        @foreach($alerts['out_of_stock'] as $item)
                            <li>{{ $item->nombre }} (stock: {{ $item->cantidad_actual }} {{ $item->unidad_medida ?? 'unidades' }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($alerts['min_stock']->count())
                <div class="px-4 py-3">
                    <p class="mb-2 font-medium text-amber-700">Ingredientes con stock mínimo:</p>
                    <ul class="text-sm text-gray-700 list-disc list-inside">
                        @foreach($alerts['min_stock'] as $item)
                            <li>{{ $item->nombre }} (actual: {{ $item->cantidad_actual }}, mínimo: {{ $item->stock_minimo }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('salesDayChart'), {
        type: 'line',
        data: {
            labels: @json($salesPerDay['labels']),
            datasets: [{
                label: 'Ventas (Bs.)',
                data: @json($salesPerDay['values']),
                borderColor: '#C2410C',
                backgroundColor: 'rgba(194, 65, 12, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#C2410C',
                pointBorderColor: '#fff',
                pointRadius: 3
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: (val) => 'Bs. ' + val } } } }
    });

    new Chart(document.getElementById('salesMonthChart'), {
        type: 'bar',
        data: {
            labels: @json($salesPerMonth['labels']),
            datasets: [{
                label: 'Ventas (Bs.)',
                data: @json($salesPerMonth['values']),
                backgroundColor: '#F97316',
                borderRadius: 6
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: (val) => 'Bs. ' + val } } } }
    });

    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: @json($paymentMethods['labels']),
            datasets: [{
                data: @json($paymentMethods['counts']),
                backgroundColor: ['#C2410C', '#F97316', '#FDBA74', '#FFEDD5'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
@endpush
