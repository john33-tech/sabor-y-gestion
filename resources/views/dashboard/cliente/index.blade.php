@extends('layouts.app')

@section('title', 'Dashboard Cliente')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold" style="color: #C2410C;">
                <i class="fas fa-user-circle mr-2"></i> Dashboard Cliente
            </h1>
            <p class="text-gray-500 mt-1">
                Bienvenido, <span class="font-semibold text-gray-700">{{ Auth::user()->name ?? 'Cliente' }}</span>
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('pedidos.cliente') }}" 
               class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 shadow-sm">
                <i class="fas fa-plus-circle mr-2"></i> Nuevo Pedido
            </a>
            <a href="{{ route('reserva.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-sm">
                <i class="fas fa-calendar-check mr-2"></i> Reservar Mesa
            </a>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $totalPedidos = App\Models\Pedido::where('cliente_telefono', Auth::user()->email ?? '')->orWhere('usuario_id', Auth::id())->count();
            $pedidosPendientes = App\Models\Pedido::where(function($q) {
                $q->where('cliente_telefono', Auth::user()->email ?? '')->orWhere('usuario_id', Auth::id());
            })->whereIn('estado', ['pendiente', 'en_preparacion'])->count();
            $totalGastado = App\Models\Pedido::where(function($q) {
                $q->where('cliente_telefono', Auth::user()->email ?? '')->orWhere('usuario_id', Auth::id());
            })->where('estado', 'entregado')->sum('total');
            $pedidosEntregados = App\Models\Pedido::where(function($q) {
                $q->where('cliente_telefono', Auth::user()->email ?? '')->orWhere('usuario_id', Auth::id());
            })->where('estado', 'entregado')->count();
        @endphp

        <div class="bg-gradient-to-br from-orange-600 to-orange-500 rounded-xl shadow-lg p-5 text-white transform transition hover:scale-105 duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Pedidos</p>
                    <p class="text-3xl font-bold">{{ number_format($totalPedidos) }}</p>
                </div>
                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-600 to-yellow-500 rounded-xl shadow-lg p-5 text-white transform transition hover:scale-105 duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pedidos Activos</p>
                    <p class="text-3xl font-bold">{{ number_format($pedidosPendientes) }}</p>
                </div>
                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-600 to-green-500 rounded-xl shadow-lg p-5 text-white transform transition hover:scale-105 duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Gastado</p>
                    <p class="text-3xl font-bold">Bs. {{ number_format($totalGastado, 2) }}</p>
                </div>
                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-600 to-blue-500 rounded-xl shadow-lg p-5 text-white transform transition hover:scale-105 duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pedidos Completados</p>
                    <p class="text-3xl font-bold">{{ number_format($pedidosEntregados) }}</p>
                </div>
                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Secciones principales -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Últimos Pedidos -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold" style="color: #C2410C;">
                        <i class="fas fa-history mr-2"></i> Mis Últimos Pedidos
                    </h2>
                    <a href="{{ route('pedidos.misPedidos') }}" class="text-sm text-orange-600 hover:text-orange-700">
                        Ver todos <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-6">
                @php
                    $ultimosPedidos = App\Models\Pedido::where(function($q) {
                        $q->where('cliente_telefono', Auth::user()->email ?? '')->orWhere('usuario_id', Auth::id());
                    })->latest()->limit(5)->get();
                @endphp
                
                @if($ultimosPedidos->count() > 0)
                    <div class="space-y-3">
                        @foreach($ultimosPedidos as $pedido)
                            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-orange-50 transition-colors border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                                        <i class="fas fa-receipt text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $pedido->numero_pedido }}</p>
                                        <p class="text-xs text-gray-500">{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold" style="color: #C2410C;">Bs. {{ number_format($pedido->total, 2) }}</p>
                                    @php
                                        $estadoClasses = [
                                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                                            'en_preparacion' => 'bg-blue-100 text-blue-800',
                                            'listo' => 'bg-green-100 text-green-800',
                                            'entregado' => 'bg-gray-100 text-gray-800'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs {{ $estadoClasses[$pedido->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($pedido->estado) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-shopping-bag text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No tienes pedidos aún</p>
                        <a href="{{ route('pedidos.create') }}" class="text-orange-600 text-sm mt-2 inline-block">
                            Realizar primer pedido
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Platos Recomendados / Populares -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold" style="color: #C2410C;">
                        <i class="fas fa-star mr-2"></i> Platos Populares
                    </h2>
                    <a href="{{ route('pedidos.create') }}" class="text-sm text-orange-600 hover:text-orange-700">
                        Ver menú <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <p class="text-sm text-gray-500 mt-1">Los más pedidos por nuestros clientes</p>
            </div>
            <div class="p-6">
                @php
                    $platosPopulares = App\Models\DetallePedido::with('plato')
                        ->select('plato_id', DB::raw('SUM(cantidad) as total_vendido'))
                        ->groupBy('plato_id')
                        ->orderBy('total_vendido', 'desc')
                        ->limit(4)
                        ->get();
                @endphp
                
                @if($platosPopulares->count() > 0)
                    <div class="space-y-3">
                        @foreach($platosPopulares as $item)
                            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-orange-50 transition-colors border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                                        <i class="fas fa-utensils text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $item->plato->nombre ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-chart-line mr-1"></i> {{ number_format($item->total_vendido) }} pedidos
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold" style="color: #C2410C;">Bs. {{ number_format($item->plato->precio ?? 0, 2) }}</p>
                                    <a href="{{ route('pedidos.create') }}?plato={{ $item->plato_id }}" class="text-xs text-green-600 hover:text-green-700">
                                        Pedir <i class="fas fa-shopping-cart ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-utensils text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No hay platos populares aún</p>
                        <p class="text-sm text-gray-400 mt-1">Sé el primero en pedir</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Segunda fila -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mis Reservas -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold" style="color: #C2410C;">
                        <i class="fas fa-calendar-alt mr-2"></i> Mis Reservas
                    </h2>
                    <a href="{{ route('reserva.index') }}" class="text-sm text-orange-600 hover:text-orange-700">
                        Reservar <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-6">
                @php
                    $misReservas = App\Models\Reserva::where('usuario_id', Auth::id())->where('fecha_reserva', '>=', now())->latest()->limit(3)->get();
                @endphp
                
                @if($misReservas->count() > 0)
                    <div class="space-y-3">
                        @foreach($misReservas as $reserva)
                            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-orange-50 transition-colors border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-chair text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">Mesa {{ $reserva->mesa->numero_mesa ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">
                                            <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}
                                            <i class="far fa-clock ml-2 mr-1"></i> {{ $reserva->hora }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Confirmada
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-calendar-check text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No tienes reservas activas</p>
                        <a href="{{ route('reserva.index') }}" class="text-orange-600 text-sm mt-2 inline-block">
                            Realizar una reserva
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Datos de Perfil -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                <h2 class="text-xl font-semibold" style="color: #C2410C;">
                    <i class="fas fa-user mr-2"></i> Mi Perfil
                </h2>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex-shrink-0 w-20 h-20 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                        {{ strtoupper(substr(Auth::user()->name ?? 'C', 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ Auth::user()->name ?? 'Cliente' }}</h3>
                        <p class="text-gray-500">
                            <i class="fas fa-envelope mr-1"></i> {{ Auth::user()->email ?? 'cliente@ejemplo.com' }}
                        </p>
                        <p class="text-gray-500">
                            <i class="fas fa-calendar-alt mr-1"></i> Miembro desde: {{ Auth::user()->created_at->format('d/m/Y') ?? '2024' }}
                        </p>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-gray-500 text-sm">Total Pedidos</p>
                            <p class="text-2xl font-bold" style="color: #C2410C;">{{ number_format($totalPedidos) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Pedidos Completados</p>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($pedidosEntregados) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Tasa de Éxito</p>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ $totalPedidos > 0 ? number_format(($pedidosEntregados / $totalPedidos) * 100, 0) : 0 }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    }
</style>
@endpush

@push('scripts')
<script>
    // Función para actualizar datos en tiempo real (opcional)
    function actualizarDashboard() {
        location.reload();
    }
    
    // Auto-refresh cada 30 segundos (opcional)
    // setInterval(actualizarDashboard, 30000);
</script>
@endpush
@endsection