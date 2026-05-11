@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showImageModal: false, modalImageUrl: '', modalImageTitle: '' }">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-primary">Ingredientes</h1>
        <a href="{{ route('ingredientes.create') }}" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> Nuevo Ingrediente
        </a>
    </div>

    <!-- Tarjetas de estadísticas actualizadas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-gradient-to-br from-primary to-secondary rounded-lg shadow-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Ingredientes</p>
                    <p class="text-2xl font-bold">{{ $totalIngredientes }}</p>
                </div>
                <i class="fas fa-carrot text-3xl opacity-80"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-600 to-blue-500 rounded-lg shadow-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Con Inventario</p>
                    <p class="text-2xl font-bold">{{ $stats['con_inventario'] }}</p>
                </div>
                <i class="fas fa-boxes text-3xl opacity-80"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-400 rounded-lg shadow-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Stock Bajo</p>
                    <p class="text-2xl font-bold">{{ $stats['stock_bajo'] }}</p>
                </div>
                <i class="fas fa-exclamation-triangle text-3xl opacity-80"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-red-600 to-red-500 rounded-lg shadow-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Agotados</p>
                    <p class="text-2xl font-bold">{{ $stats['stock_agotado'] }}</p>
                </div>
                <i class="fas fa-times-circle text-3xl opacity-80"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-600 to-green-500 rounded-lg shadow-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">En uso en platos</p>
                    <p class="text-2xl font-bold">{{ $ingredientes->where('platos_count', '>', 0)->count() }}</p>
                </div>
                <i class="fas fa-utensils text-3xl opacity-80"></i>
            </div>
        </div>
    </div>

    <!-- Panel de búsqueda y filtros actualizado -->
    <div class="card p-4">
        <form method="GET" action="{{ route('ingredientes.index') }}" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-search mr-1 text-primary"></i> Buscar
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Nombre del ingrediente..."
                               class="w-full px-4 py-2 pl-10 rounded-lg border border-border focus:outline-none focus:border-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-muted"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-ruler mr-1 text-primary"></i> Unidad de Medida
                    </label>
                    <div class="relative">
                        <select name="unidad" class="w-full px-4 py-2 appearance-none rounded-lg border border-border focus:outline-none focus:border-primary">
                            <option value="">Todas</option>
                            <option value="gr" {{ request('unidad') == 'gr' ? 'selected' : '' }}>Gramos (gr)</option>
                            <option value="ml" {{ request('unidad') == 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                            <option value="unidad" {{ request('unidad') == 'unidad' ? 'selected' : '' }}>Unidad</option>
                            <option value="cda" {{ request('unidad') == 'cda' ? 'selected' : '' }}>Cucharada (cda)</option>
                            <option value="cdta" {{ request('unidad') == 'cdta' ? 'selected' : '' }}>Cucharadita (cdta)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-3 text-muted pointer-events-none"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-chart-line mr-1 text-primary"></i> Estado de Stock
                    </label>
                    <div class="relative">
                        <select name="estado_stock" class="w-full px-4 py-2 appearance-none rounded-lg border border-border focus:outline-none focus:border-primary">
                            <option value="">Todos</option>
                            <option value="bajo" {{ request('estado_stock') == 'bajo' ? 'selected' : '' }}>⚠️ Stock Bajo</option>
                            <option value="agotado" {{ request('estado_stock') == 'agotado' ? 'selected' : '' }}>❌ Agotados</option>
                            <option value="normal" {{ request('estado_stock') == 'normal' ? 'selected' : '' }}>✅ Normal</option>
                            <option value="sin_inventario" {{ request('estado_stock') == 'sin_inventario' ? 'selected' : '' }}>📦 Sin Inventario</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-3 text-muted pointer-events-none"></i>
                    </div>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                </div>
            </div>

            @if(request()->anyFilled(['search', 'unidad', 'estado_stock']))
                <div class="flex justify-end mt-4">
                    <a href="{{ route('ingredientes.index') }}" class="btn-secondary px-6">
                        <i class="fas fa-undo-alt mr-2"></i> Limpiar filtros
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Resultados de búsqueda -->
    <div class="flex justify-between items-center mb-2">
        <p class="text-sm text-muted">
            <i class="fas fa-chart-line mr-1"></i> 
            Mostrando {{ $ingredientes->count() }} de {{ $totalIngredientes }} ingredientes
            @if(request('search'))
                <span class="ml-2 px-2 py-1 bg-primary/10 rounded-full text-xs">
                    <i class="fas fa-search mr-1"></i> Búsqueda: "{{ request('search') }}"
                </span>
            @endif
            @if(request('unidad'))
                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                    <i class="fas fa-ruler mr-1"></i> Unidad: {{ request('unidad') }}
                </span>
            @endif
            @if(request('estado_stock'))
                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                    <i class="fas fa-chart-line mr-1"></i> Stock: {{ request('estado_stock') }}
                </span>
            @endif
        </p>
    </div>

    <!-- Tabla de Ingredientes con columna de stock -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-border bg-background">
                        <th class="text-left py-3 px-4 font-semibold text-text">Foto</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Nombre</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Unidad</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Stock Actual</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Stock Mínimo</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Estado</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Usado en</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ingredientes as $ingrediente)
                    @php
                        $inventario = $ingrediente->inventario;
                        $cantidadActual = $inventario?->cantidad_actual ?? 0;
                        $stockMinimo = $inventario?->stock_minimo ?? 0;
                        $isLowStock = $inventario && $cantidadActual <= $stockMinimo;
                        $isOutOfStock = $cantidadActual <= 0;
                        
                        $stockStatus = !$inventario ? 'sin_inventario' : ($isOutOfStock ? 'agotado' : ($isLowStock ? 'bajo' : 'normal'));
                        $statusColors = [
                            'agotado' => 'bg-red-100 text-red-800',
                            'bajo' => 'bg-yellow-100 text-yellow-800',
                            'normal' => 'bg-green-100 text-green-800',
                            'sin_inventario' => 'bg-gray-100 text-gray-600'
                        ];
                        $statusIcons = [
                            'agotado' => 'fa-times-circle',
                            'bajo' => 'fa-exclamation-triangle',
                            'normal' => 'fa-check-circle',
                            'sin_inventario' => 'fa-clock'
                        ];
                        $statusLabels = [
                            'agotado' => 'Agotado',
                            'bajo' => 'Stock Bajo',
                            'normal' => 'Normal',
                            'sin_inventario' => 'Sin Inventario'
                        ];
                    @endphp
                    <tr class="border-b border-border hover:bg-background transition-colors">
                        <td class="py-3 px-4">
                            @if($ingrediente->foto)
                                <img src="{{ Storage::url($ingrediente->foto) }}" 
                                     alt="{{ $ingrediente->nombre }}" 
                                     class="w-12 h-12 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                                     @click="modalImageUrl = '{{ Storage::url($ingrediente->foto) }}'; modalImageTitle = '{{ $ingrediente->nombre }}'; showImageModal = true">
                            @else
                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-carrot text-gray-400 text-xl"></i>
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-medium text-text">{{ $ingrediente->nombre }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                @switch($ingrediente->unidad_medida)
                                    @case('gr') Gramos @break
                                    @case('ml') Mililitros @break
                                    @case('unidad') Unidad(es) @break
                                    @case('cda') Cucharada(s) @break
                                    @case('cdta') Cucharadita(s) @break
                                @endswitch
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($inventario)
                                <span class="font-bold {{ $isLowStock ? 'text-red-600' : ($isOutOfStock ? 'text-red-600' : 'text-green-600') }}">
                                    {{ number_format($cantidadActual, 2) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($inventario)
                                {{ number_format($stockMinimo, 2) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$stockStatus] }}">
                                <i class="fas {{ $statusIcons[$stockStatus] }} mr-1"></i>
                                {{ $statusLabels[$stockStatus] }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $ingrediente->platos_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                <i class="fas fa-utensils mr-1"></i>
                                {{ $ingrediente->platos_count }} plato(s)
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('ingredientes.show', $ingrediente) }}" class="text-info hover:text-info-dark transition-colors" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('ingredientes.edit', $ingrediente) }}" class="text-primary hover:text-secondary transition-colors" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($inventario)
                                    <a href="{{ route('inventario.edit', $inventario) }}" class="text-blue-600 hover:text-blue-800 transition-colors" title="Editar inventario">
                                        <i class="fas fa-boxes"></i>
                                    </a>
                                @else
                                    <a href="{{ route('inventario.create') }}?ingrediente={{ $ingrediente->id }}" class="text-green-600 hover:text-green-800 transition-colors" title="Agregar inventario">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                @endif
                                <form action="{{ route('ingredientes.destroy', $ingrediente) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" 
                                            onclick="return confirm('¿Estás seguro de eliminar este ingrediente?')"
                                            {{ $ingrediente->platos_count > 0 ? 'disabled title="No se puede eliminar porque está siendo usado en platos"' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-muted">
                            <i class="fas fa-carrot text-4xl mb-3 block"></i>
                            <p>No hay ingredientes registrados</p>
                            <a href="{{ route('ingredientes.create') }}" class="text-primary hover:text-secondary mt-2 inline-block">
                                <i class="fas fa-plus mr-1"></i> Crear primer ingrediente
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $ingredientes->links() }}
        </div>
    </div>

    <!-- Modal de Imagen (igual que antes) -->
    <div x-show="showImageModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75"
         x-cloak
         @keydown.escape.window="showImageModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="relative max-w-4xl w-full bg-white rounded-xl overflow-hidden shadow-2xl"
             @click.away="showImageModal = false"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-xl font-bold text-text" x-text="modalImageTitle"></h3>
                <button @click="showImageModal = false" class="text-muted hover:text-text transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div class="p-2 bg-gray-50 flex justify-center items-center">
                <img :src="modalImageUrl" :alt="modalImageTitle" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-inner">
            </div>
            
            <div class="p-4 flex justify-end">
                <button @click="showImageModal = false" class="btn-secondary">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    .text-info {
        color: #17a2b8;
    }
    .text-info-dark {
        color: #0f6674;
    }
    button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

@push('scripts')
<script>
    // Auto-submit para filtros
    const unidadFilter = document.querySelector('select[name="unidad"]');
    const estadoStockFilter = document.querySelector('select[name="estado_stock"]');
    const filterForm = document.getElementById('filterForm');
    
    if (unidadFilter) {
        unidadFilter.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    if (estadoStockFilter) {
        estadoStockFilter.addEventListener('change', function() {
            filterForm.submit();
        });
    }
</script>
@endpush
@endsection