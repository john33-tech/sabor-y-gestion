@extends('layouts.app')

@section('title', 'Reporte de Consumos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold" style="color: #C2410C;">
                <i class="fas fa-chart-line mr-2"></i> Reporte de Consumos
            </h1>
            <p class="text-gray-500 mt-1">
                Historial de pedidos finalizados y estadísticas
            </p>
        </div>
        <a href="{{ route('reportes.consumos.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
           class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 shadow-sm">
            <i class="fas fa-download mr-2"></i> Exportar CSV
        </a>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-orange-600 to-orange-500 rounded-xl shadow-lg p-5 text-white transform transition hover:scale-105 duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Consumos</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['total_consumos']) }}</p>
                </div>
                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-receipt text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-600 to-green-500 rounded-xl shadow-lg p-5 text-white transform transition hover:scale-105 duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Ingresos</p>
                    <p class="text-3xl font-bold">Bs. {{ number_format($stats['total_ingresos'], 2) }}</p>
                </div>
                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-600 to-blue-500 rounded-xl shadow-lg p-5 text-white transform transition hover:scale-105 duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Platos Vendidos</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['total_platos_vendidos']) }}</p>
                </div>
                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-utensils text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-600 to-purple-500 rounded-xl shadow-lg p-5 text-white transform transition hover:scale-105 duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Promedio por Consumo</p>
                    <p class="text-3xl font-bold">Bs. {{ number_format($stats['promedio_por_consumo'], 2) }}</p>
                </div>
                <div class="h-12 w-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos rápidos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ingresos por tipo -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-chart-pie mr-2" style="color: #C2410C;"></i> Ingresos por Tipo de Pedido
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($tipoStats as $tipo => $total)
                        @php
                            $porcentaje = $stats['total_ingresos'] > 0 ? ($total / $stats['total_ingresos']) * 100 : 0;
                            $colors = [
                                'mesa' => 'bg-blue-500',
                                'delivery' => 'bg-green-500',
                                'para_llevar' => 'bg-yellow-500'
                            ];
                            $labels = [
                                'mesa' => 'Mesa',
                                'delivery' => 'Delivery',
                                'para_llevar' => 'Para Llevar'
                            ];
                            $icons = [
                                'mesa' => 'fa-chair',
                                'delivery' => 'fa-motorcycle',
                                'para_llevar' => 'fa-box'
                            ];
                        @endphp
                        <div>
                            <div class="flex justify-between items-center text-sm mb-1">
                                <div class="flex items-center gap-2">
                                    <i class="fas {{ $icons[$tipo] }} text-gray-500"></i>
                                    <span class="font-medium text-gray-700">{{ $labels[$tipo] }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Bs. {{ number_format($total, 2) }}</span>
                                    <span class="text-gray-400 ml-2">({{ number_format($porcentaje, 1) }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="{{ $colors[$tipo] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Platos -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">
                    <i class="fas fa-trophy mr-2" style="color: #C2410C;"></i> Top 5 Platos Más Vendidos
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($topPlatos as $index => $plato)
                        <div class="flex justify-between items-center p-3 rounded-lg hover:bg-orange-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $index == 0 ? 'bg-yellow-100 text-yellow-600' : ($index == 1 ? 'bg-gray-100 text-gray-600' : ($index == 2 ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-500')) }}">
                                    <span class="font-bold text-sm">#{{ $index + 1 }}</span>
                                </div>
                                <span class="font-medium text-gray-800">{{ $plato['nombre'] }}</span>
                            </div>
                            <div class="flex gap-4 items-center">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-box mr-1"></i> {{ number_format($plato['cantidad']) }} und
                                </span>
                                <span class="font-semibold" style="color: #C2410C;">
                                    Bs. {{ number_format($plato['total'], 2) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-700">
                <i class="fas fa-filter mr-2" style="color: #C2410C;"></i> Filtros de Búsqueda
            </h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('reportes.consumos') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="far fa-calendar-alt mr-1 text-gray-400"></i> Fecha Desde
                    </label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="far fa-calendar-alt mr-1 text-gray-400"></i> Fecha Hasta
                    </label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-motorcycle mr-1 text-gray-400"></i> Tipo de Pedido
                    </label>
                    <select name="tipo_pedido" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                        <option value="">Todos</option>
                        @foreach($tiposPedido as $key => $label)
                            <option value="{{ $key }}" {{ request('tipo_pedido') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-1 text-gray-400"></i> Usuario
                    </label>
                    <select name="usuario_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                        <option value="">Todos</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('reportes.consumos') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center justify-center">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Consumos -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-700">
                <i class="fas fa-list mr-2" style="color: #C2410C;"></i> Listado de Consumos
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Pedido</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platos</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($consumos as $consumo)
                    <tr class="hover:bg-orange-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-receipt text-xs" style="color: #C2410C;"></i>
                                </div>
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">{{ $consumo->numero_pedido }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $tipoClasses = [
                                    'mesa' => 'bg-blue-100 text-blue-800',
                                    'delivery' => 'bg-green-100 text-green-800',
                                    'para_llevar' => 'bg-yellow-100 text-yellow-800'
                                ];
                                $tipoIconos = [
                                    'mesa' => 'fa-chair',
                                    'delivery' => 'fa-motorcycle',
                                    'para_llevar' => 'fa-box'
                                ];
                                $tipoTextos = [
                                    'mesa' => 'Mesa',
                                    'delivery' => 'Delivery',
                                    'para_llevar' => 'Para Llevar'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tipoClasses[$consumo->tipo_pedido] ?? 'bg-gray-100 text-gray-800' }}">
                                <i class="fas {{ $tipoIconos[$consumo->tipo_pedido] ?? 'fa-receipt' }} mr-1"></i>
                                {{ $tipoTextos[$consumo->tipo_pedido] ?? $consumo->tipo_pedido }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <i class="fas fa-user mr-1 text-gray-400"></i>
                            {{ $consumo->usuario->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <i class="far fa-calendar-alt mr-1 text-gray-400"></i>
                            {{ $consumo->fecha_consumo->format('d/m/Y') }}
                            <br>
                            <i class="far fa-clock mr-1 text-gray-400"></i>
                            {{ $consumo->fecha_consumo->format('H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @foreach($consumo->detalles as $detalle)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-orange-100" style="color: #C2410C;">
                                        {{ $detalle['plato_nombre'] }} x{{ $detalle['cantidad'] }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                            Bs. {{ number_format($consumo->subtotal, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold" style="color: #C2410C;">
                                Bs. {{ number_format($consumo->total, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button type="button" onclick="verDetalle({{ json_encode($consumo) }})" 
                                    class="text-orange-600 hover:text-orange-800 transition-colors duration-200 p-2 hover:bg-orange-100 rounded-lg"
                                    title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                    <i class="fas fa-chart-line text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 font-medium">No hay consumos registrados</p>
                                <p class="text-sm text-gray-400 mt-1">Los pedidos finalizados aparecerán aquí</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-between items-center flex-wrap gap-2">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-chart-line mr-1"></i>
                    Mostrando {{ $consumos->count() }} de {{ $stats['total_consumos'] }} consumos
                </div>
                <div>
                    {{ $consumos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div id="detalleModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/75 transition-all duration-300">
    <div class="relative max-w-2xl w-full bg-white rounded-xl overflow-hidden shadow-2xl transform transition-all duration-300 scale-95 opacity-0" id="modalContainer">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
            <h3 class="text-xl font-bold" style="color: #C2410C;" id="modalTitle">Detalle del Consumo</h3>
            <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="modalContent" class="p-6 max-h-[70vh] overflow-y-auto">
            <!-- Contenido dinámico -->
        </div>
        <div class="px-6 py-4 flex justify-end border-t border-gray-200 bg-gray-50">
            <button onclick="cerrarModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                <i class="fas fa-times mr-2"></i> Cerrar
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #detalleModal.show {
        display: flex !important;
    }
    
    #detalleModal.show #modalContainer {
        transform: scale(1);
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
function verDetalle(consumo) {
    const modal = document.getElementById('detalleModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    const modalContainer = document.getElementById('modalContainer');
    
    modalTitle.innerHTML = `<i class="fas fa-receipt mr-2"></i> Consumo - Pedido #${consumo.numero_pedido}`;
    
    let detallesHtml = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-gray-500 text-xs uppercase tracking-wide">N° Pedido</p>
                    <p class="font-semibold text-gray-800 mt-1">${consumo.numero_pedido}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Tipo</p>
                    <p class="font-semibold text-gray-800 mt-1">
                        ${consumo.tipo_pedido === 'mesa' ? '<i class="fas fa-chair mr-1"></i> Mesa' : 
                          (consumo.tipo_pedido === 'delivery' ? '<i class="fas fa-motorcycle mr-1"></i> Delivery' : 
                          '<i class="fas fa-box mr-1"></i> Para Llevar')}
                    </p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Usuario</p>
                    <p class="font-semibold text-gray-800 mt-1"><i class="fas fa-user mr-1"></i> ${consumo.usuario?.name || 'N/A'}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Fecha</p>
                    <p class="font-semibold text-gray-800 mt-1"><i class="far fa-calendar-alt mr-1"></i> ${new Date(consumo.fecha_consumo).toLocaleString()}</p>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-utensils mr-2" style="color: #C2410C;"></i> Platos Consumidos
                </h4>
                <div class="space-y-2">
                    ${consumo.detalles.map((detalle, idx) => `
                        <div class="flex justify-between items-center p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-800">${detalle.plato_nombre}</span>
                                    <span class="text-sm text-gray-500">x${detalle.cantidad}</span>
                                </div>
                                ${detalle.notas ? `<p class="text-xs text-gray-500 mt-1"><i class="fas fa-sticky-note mr-1"></i> Nota: ${detalle.notas}</p>` : ''}
                            </div>
                            <div class="text-right">
                                <span class="font-semibold" style="color: #C2410C;">Bs. ${(detalle.subtotal || detalle.precio_unitario * detalle.cantidad).toFixed(2)}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-4 mt-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">Bs. ${consumo.subtotal.toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Descuento</span>
                        <span class="font-medium text-red-600">- Bs. ${consumo.descuento.toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 mt-2">
                        <span class="text-gray-800">TOTAL</span>
                        <span style="color: #C2410C;">Bs. ${consumo.total.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    modalContent.innerHTML = detallesHtml;
    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    const modal = document.getElementById('detalleModal');
    modal.classList.add('hidden');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModal();
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('detalleModal').addEventListener('click', function(event) {
    if (event.target === this) {
        cerrarModal();
    }
});
</script>
@endpush