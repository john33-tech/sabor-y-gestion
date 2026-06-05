@extends('layouts.app')

@section('title', 'Panel del Mesero')

@push('styles')
<style>
    .status-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .status-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .order-status-pendiente { background-color: #FEF3C7; color: #D97706; border-left: 4px solid #D97706; }
    .order-status-preparacion { background-color: #DBEAFE; color: #2563EB; border-left: 4px solid #2563EB; }
    .order-status-listo { background-color: #D1FAE5; color: #059669; border-left: 4px solid #059669; }
    .order-status-entregado { background-color: #F3F4F6; color: #4B5563; border-left: 4px solid #9CA3AF; }
    .badge-pill { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
</style>
@endpush

@section('content')
<div x-data="serverDashboard()" x-init="init()" class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">

    <!-- Resumen personal (tarjeta cabecera) -->
    <div class="p-5 mb-6 bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center rounded-full w-14 h-14 bg-primary/10">
                    <i class="text-2xl fas fa-user-clock text-primary"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $personal['name'] }}</h2>
                    <div class="flex flex-wrap mt-1 text-sm text-gray-500 gap-x-4 gap-y-1">
                        <span><i class="fas fa-circle text-[8px] mr-1 text-green-500 align-middle"></i> {{ $personal['status'] }}</span>
                        <span><i class="mr-1 far fa-clock"></i> Inicio: {{ $personal['start_time'] }}</span>
                        <span><i class="mr-1 fas fa-hourglass-half"></i> Trabajado: {{ $personal['worked_hours'] }}h {{ $personal['worked_minutes'] }}m</span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 text-sm font-medium text-white transition rounded-lg shadow bg-primary hover:bg-primary/90">
                    <i class="mr-2 fas fa-cash-register"></i>Solicitar Cuenta
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-gray-100 rounded-lg hover:bg-gray-200">
                    <i class="mr-2 fas fa-utensils"></i>Nuevo Pedido
                </button>
            </div>
        </div>
    </div>

    <!-- Indicadores rápidos (3 columnas) -->
    <div class="grid grid-cols-1 gap-5 mb-8 sm:grid-cols-2 lg:grid-cols-3">
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm status-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pedidos Hoy</p>
                    <p class="text-3xl font-bold text-gray-800" x-text="dailyData.orders_served">{{ $daily['orders_served'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="text-xl fas fa-receipt text-primary"></i>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-400">
                <span>Pendientes: <span x-text="dailyData.pending_orders">{{ $daily['pending_orders'] }}</span></span>
                <span class="ml-3">Cancelados: <span x-text="dailyData.cancelled_orders">{{ $daily['cancelled_orders'] }}</span></span>
            </div>
        </div>
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm status-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Mesas Atendidas</p>
                    <p class="text-3xl font-bold text-gray-800" x-text="dailyData.tables_served">{{ $daily['tables_served'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="text-xl text-green-600 fas fa-chair"></i>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-400">
                Tiempo promedio servicio: <span x-text="dailyData.avg_service_time + ' min'">{{ $daily['avg_service_time'] }} min</span>
            </div>
        </div>
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm status-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Ventas Generadas (hoy)</p>
                    <p class="text-3xl font-bold text-primary" x-text="'Bs. ' + performanceData.sales_today">{{ number_format($performance['sales_today'], 2) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="text-xl text-purple-600 fas fa-chart-line"></i>
                </div>
            </div>
            <div class="mt-2 text-xs">
                @php
                    $comparison = $performance['comparison'];
                    $trendClass = $comparison >= 0 ? 'text-green-600' : 'text-red-600';
                    $trendIcon = $comparison >= 0 ? 'arrow-up' : 'arrow-down';
                @endphp
                <span class="{{ $trendClass }}">
                    <i class="fas fa-{{ $trendIcon }} mr-1"></i> {{ abs($comparison) }}% vs ayer
                </span>
                <span class="ml-2 text-gray-400">Productos: {{ $performance['products_sold'] }}</span>
            </div>
        </div>
    </div>

    <!-- Gestión de mesas y pedidos en tiempo real (2 columnas) -->
    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-3">
        <!-- Estado de mesas -->
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
            <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-800">
                <i class="fas fa-table text-primary"></i> Estado de Mesas
            </h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="p-2 text-center rounded-lg bg-red-50">
                    <div class="text-lg font-bold text-red-600" x-text="tablesData.occupied">{{ $tables['occupied'] }}</div>
                    <div class="text-xs text-gray-500">Ocupadas</div>
                </div>
                <div class="p-2 text-center rounded-lg bg-green-50">
                    <div class="text-lg font-bold text-green-600" x-text="tablesData.available">{{ $tables['available'] }}</div>
                    <div class="text-xs text-gray-500">Libres</div>
                </div>
                <div class="p-2 text-center rounded-lg bg-blue-50">
                    <div class="text-lg font-bold text-blue-600" x-text="tablesData.reserved">{{ $tables['reserved'] }}</div>
                    <div class="text-xs text-gray-500">Reservadas</div>
                </div>
                <div class="p-2 text-center rounded-lg bg-amber-50">
                    <div class="text-lg font-bold text-amber-600" x-text="tablesData.pending_bill">{{ $tables['pending_bill'] }}</div>
                    <div class="text-xs text-gray-500">Cta. Pendiente</div>
                </div>
            </div>
            <div class="mt-2 overflow-y-auto text-sm max-h-48">
                <ul class="space-y-1">
                    <template x-for="table in tablesData.all_tables" :key="table.id">
                        <li class="flex items-center justify-between p-2 border-b border-gray-100">
                            <span class="font-medium" x-text="'Mesa ' + table.number"></span>
                            <span>
                                <span x-show="table.status === 'libre'" class="text-green-800 bg-green-100 badge-pill">Libre</span>
                                <span x-show="table.status === 'ocupado'" class="text-red-800 bg-red-100 badge-pill">Ocupada</span>
                                <span x-show="table.status === 'reservado'" class="text-blue-800 bg-blue-100 badge-pill">Reservada</span>
                                <span x-show="table.has_pending_bill" class="ml-2 badge-pill bg-amber-100 text-amber-800">Cuenta</span>
                            </span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <!-- Pedidos en tiempo real -->
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm lg:col-span-2">
            <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-800">
                <i class="fas fa-clock text-primary"></i> Pedidos Activos
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <div class="mb-2 font-medium text-amber-600"><i class="mr-1 fas fa-hourglass-start"></i> Nuevos ({{ count($orders['new']) }})</div>
                    <div class="space-y-2 overflow-y-auto max-h-60">
                        <template x-for="order in ordersData.new" :key="order.id">
                            <div class="p-2 text-sm border-l-4 rounded bg-amber-50 border-amber-500">
                                <div class="font-bold" x-text="order.numero_pedido"></div>
                                <div class="text-gray-600">Mesa: <span x-text="order.mesa_id || 'N/A'"></span></div>
                                <div class="text-xs text-gray-400" x-text="'Bs. ' + parseFloat(order.total).toFixed(2)"></div>
                            </div>
                        </template>
                        <p x-show="ordersData.new.length === 0" class="text-sm text-gray-400">No hay pedidos nuevos</p>
                    </div>
                </div>
                <div>
                    <div class="mb-2 font-medium text-blue-600"><i class="mr-1 fas fa-cogs"></i> En Preparación ({{ count($orders['preparation']) }})</div>
                    <div class="space-y-2 overflow-y-auto max-h-60">
                        <template x-for="order in ordersData.preparation" :key="order.id">
                            <div class="p-2 text-sm border-l-4 border-blue-500 rounded bg-blue-50">
                                <div class="font-bold" x-text="order.numero_pedido"></div>
                                <div class="text-gray-600">Mesa: <span x-text="order.mesa_id || 'N/A'"></span></div>
                            </div>
                        </template>
                        <p x-show="ordersData.preparation.length === 0" class="text-sm text-gray-400">No hay pedidos en preparación</p>
                    </div>
                </div>
                <div>
                    <div class="mb-2 font-medium text-green-600"><i class="mr-1 fas fa-check-circle"></i> Listos ({{ count($orders['ready']) }})</div>
                    <div class="space-y-2 overflow-y-auto max-h-60">
                        <template x-for="order in ordersData.ready" :key="order.id">
                            <div class="p-2 text-sm border-l-4 border-green-500 rounded bg-green-50">
                                <div class="font-bold" x-text="order.numero_pedido"></div>
                                <div class="text-gray-600">Mesa: <span x-text="order.mesa_id || 'N/A'"></span></div>
                                <button class="mt-1 text-xs text-white bg-green-600 px-2 py-0.5 rounded">Servir</button>
                            </div>
                        </template>
                        <p x-show="ordersData.ready.length === 0" class="text-sm text-gray-400">No hay pedidos listos</p>
                    </div>
                </div>
            </div>
            <!-- Últimos entregados -->
            <div class="pt-3 mt-4 border-t border-gray-100">
                <div class="mb-2 text-xs font-medium text-gray-500">Últimos entregados</div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="order in ordersData.recent_delivered" :key="order.id">
                        <div class="px-2 py-1 text-xs bg-gray-100 rounded-full">
                            <span x-text="order.numero_pedido"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Notificaciones y Reservas -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
            <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-800">
                <i class="fas fa-bell text-primary"></i> Notificaciones
                <span class="ml-auto text-xs text-gray-400">En tiempo real</span>
            </h3>
            <div class="space-y-3">
                <template x-for="comment in notificationsData.special_comments" :key="comment.id">
                    <div class="p-3 border-l-4 border-yellow-500 rounded bg-yellow-50">
                        <div class="flex justify-between">
                            <strong class="text-sm">Pedido: <span x-text="comment.numero_pedido"></span></strong>
                            <span class="text-xs text-gray-400" x-text="new Date(comment.created_at).toLocaleTimeString()"></span>
                        </div>
                        <p class="mt-1 text-sm text-gray-700" x-text="comment.notas"></p>
                    </div>
                </template>
                <p x-show="notificationsData.special_comments.length === 0" class="text-sm text-gray-400">Sin comentarios especiales</p>
            </div>
        </div>

        <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
            <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-800">
                <i class="fas fa-calendar-alt text-primary"></i> Próximas Reservas
            </h3>
            <div class="space-y-2 overflow-y-auto max-h-64">
                @forelse($reservations as $res)
                <div class="flex items-center justify-between p-2 border-b border-gray-100">
                    <div>
                        <div class="font-medium">Mesa {{ $res->numero_mesa }}</div>
                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($res->fecha_reserva)->format('d/m/Y') }} - {{ $res->hora_reserva }} hrs</div>
                        <div class="text-xs text-gray-400">{{ $res->personas }} personas</div>
                    </div>
                    <span class="badge-pill {{ $res->estado == 'confirmada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($res->estado) }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-gray-400">No hay reservas próximas</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    function serverDashboard() {
        return {
            dailyData: @json($daily),
            tablesData: @json($tables),
            performanceData: @json($performance),
            ordersData: @json($orders),
            notificationsData: @json($notifications),
            refreshInterval: null,
            init() {
                // Escuchar evento personalizado de WebSocket cuando un pedido está listo
                window.addEventListener('pedido-listo', (event) => {
                    console.log('📦 Dashboard: evento pedido-listo recibido', event.detail);
                    this.refreshData();
                });

                // Escuchar evento de WebSocket cuando una mesa es liberada
                window.addEventListener('mesa-liberada', (event) => {
                    console.log('🪑 Dashboard: evento mesa-liberada recibido', event.detail);
                    this.refreshData();
                });

                // Polling de fallback cada 5 minutos (300 segundos)
                this.refreshInterval = setInterval(() => {
                    this.refreshData();
                }, 300000);
            },
            async refreshData() {
                try {
                    const response = await fetch('{{ route("mesero.refresh-data") }}');
                    const data = await response.json();
                    this.ordersData = data.orders;
                    this.dailyData = data.daily;
                    this.performanceData = data.performance;
                    this.notificationsData = data.notifications;
                    this.tablesData = data.tables;
                    console.log('✅ Dashboard actualizado en tiempo real');
                } catch (error) {
                    console.error('Error refrescando datos:', error);
                }
            },
            destroy() {
                if (this.refreshInterval) clearInterval(this.refreshInterval);
            }
        }
    }
</script>
@endsection
