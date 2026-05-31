@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-primary">Control de Inventario</h1>
        <a href="{{ route('inventario.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 shadow-sm">
            <i class="fas fa-plus mr-2"></i> Agregar Stock Inicial
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Alerta de stock bajo (solo aviso) --}}
    @if(($stockBajo ?? 0) > 0 || ($stockAgotado ?? 0) > 0)
    <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 text-amber-800 text-sm">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <strong>Atención:</strong> {{ $stockBajo }} ingrediente(s) con <strong>stock bajo</strong>@if(($stockAgotado ?? 0) > 0) ({{ $stockAgotado }} agotado/s)@endif. Repón abajo lo necesario para producir más platos.
    </div>
    @endif

    {{-- Reposición por PRODUCCIÓN: Calcular (preview) y luego Reponer --}}
    @php
        $platosData = $platos->map(fn($p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'ingredientes' => $p->ingredientes->map(fn($i) => [
                'nombre'   => $i->nombre,
                'unidad'   => $i->unidad_medida,
                'cantidad' => (float) $i->pivot->cantidad,
            ])->values(),
        ])->values();
    @endphp
    <div class="bg-white border border-emerald-200 rounded-lg shadow-sm p-4" x-data="reposicionProduccion()">
        <h2 class="font-semibold text-gray-800 mb-1"><i class="fas fa-calculator text-emerald-600 mr-1"></i> Reponer ingredientes por producción</h2>
        <p class="text-xs text-gray-500 mb-3">Elige un plato y cuántas unidades vas a preparar, presiona <strong>Calcular</strong> para ver lo necesario, y luego <strong>Reponer</strong> para sumarlo al inventario.</p>

        <form action="{{ route('inventario.reponer-producto') }}" method="POST">
            @csrf
            <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Plato</label>
                    <select name="plato_id" x-model="platoId" @change="preview=null" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Selecciona un plato…</option>
                        @foreach($platos as $pl)
                            <option value="{{ $pl->id }}">{{ $pl->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-44">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Unidades a producir</label>
                    <input type="number" name="cantidad" x-model="cantidad" @input="preview=null" min="1" max="1000" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary">
                </div>
                <button type="button" @click="calcular()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                    <i class="fas fa-calculator mr-1"></i> Calcular
                </button>
            </div>

            {{-- Preview del cálculo + botón Reponer (aparece tras Calcular) --}}
            <template x-if="preview && preview.length">
                <div class="mt-4 border-t border-gray-100 pt-3">
                    <p class="text-sm text-gray-600 mb-2">Para <strong x-text="cantidad"></strong> unidad(es) se necesita:</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <template x-for="item in preview" :key="item.nombre">
                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200">
                                +<span x-text="item.total"></span><span x-text="item.unidad"></span> <span x-text="item.nombre"></span>
                            </span>
                        </template>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                        <i class="fas fa-plus mr-1"></i> Reponer al inventario
                    </button>
                </div>
            </template>
        </form>

        <script>
            function reposicionProduccion() {
                return {
                    platos: @json($platosData),
                    platoId: '',
                    cantidad: 10,
                    preview: null,
                    calcular() {
                        const p = this.platos.find(x => String(x.id) === String(this.platoId));
                        const n = parseInt(this.cantidad) || 0;
                        if (!p || n < 1) { this.preview = null; return; }
                        this.preview = p.ingredientes.map(i => ({
                            nombre: i.nombre,
                            unidad: i.unidad,
                            total: Math.round(i.cantidad * n),
                        }));
                    }
                }
            }
        </script>
    </div>

    <!-- Tarjetas de estadísticas -->
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
                    <p class="text-2xl font-bold">{{ $ingredientesConInventario }}</p>
                </div>
                <i class="fas fa-check-circle text-3xl opacity-80"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-600 to-gray-500 rounded-lg shadow-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Sin Inventario</p>
                    <p class="text-2xl font-bold">{{ $ingredientesSinInventario }}</p>
                </div>
                <i class="fas fa-clock text-3xl opacity-80"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-400 rounded-lg shadow-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Stock Bajo</p>
                    <p class="text-2xl font-bold">{{ $stockBajo }}</p>
                </div>
                <i class="fas fa-exclamation-triangle text-3xl opacity-80"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-red-600 to-red-500 rounded-lg shadow-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Agotados</p>
                    <p class="text-2xl font-bold">{{ $stockAgotado }}</p>
                </div>
                <i class="fas fa-times-circle text-3xl opacity-80"></i>
            </div>
        </div>
    </div>

    <!-- Tabla de Inventario -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-border bg-background">
                        <th class="text-left py-3 px-4 font-semibold text-text">Ingrediente</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Unidad</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Cantidad Actual</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Stock Mínimo</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Estado</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Ubicación</th>
                        <th class="text-left py-3 px-4 font-semibold text-text">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ingredientes as $ingrediente)
                    <tr class="border-b border-border hover:bg-background transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                @if($ingrediente->foto)
                                    <img src="{{ Storage::url($ingrediente->foto) }}" 
                                         alt="{{ $ingrediente->nombre }}" 
                                         class="w-10 h-10 object-cover rounded-lg">
                                @else
                                    <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-carrot text-gray-400"></i>
                                    </div>
                                @endif
                                <span class="font-medium text-text">{{ $ingrediente->nombre }}</span>
                            </div>
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
                            <span class="font-bold">
                                {{ number_format($ingrediente->cantidad_actual, 2) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            {{ $ingrediente->inventario?->stock_minimo ?? 'No definido' }}
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $status = $ingrediente->inventario?->stock_status ?? 'sin_inventario';
                                $colors = [
                                    'agotado' => 'bg-red-100 text-red-800',
                                    'bajo' => 'bg-yellow-100 text-yellow-800',
                                    'normal' => 'bg-green-100 text-green-800',
                                    'maximo' => 'bg-blue-100 text-blue-800',
                                    'sin_inventario' => 'bg-gray-100 text-gray-600'
                                ];
                                $icons = [
                                    'agotado' => 'fa-times-circle',
                                    'bajo' => 'fa-exclamation-triangle',
                                    'normal' => 'fa-check-circle',
                                    'maximo' => 'fa-arrow-up',
                                    'sin_inventario' => 'fa-clock'
                                ];
                                $labels = [
                                    'agotado' => 'Agotado',
                                    'bajo' => 'Stock Bajo',
                                    'normal' => 'Normal',
                                    'maximo' => 'Stock Máximo',
                                    'sin_inventario' => 'Sin Inventario'
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $colors[$status] }}">
                                <i class="fas {{ $icons[$status] }} mr-1"></i>
                                {{ $labels[$status] }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            {{ $ingrediente->inventario?->ubicacion ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($ingrediente->inventario)
                                <a href="{{ route('inventario.edit', $ingrediente->inventario) }}"
                                   class="text-primary hover:text-secondary mr-2 transition-colors"
                                   title="Editar inventario">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('inventario.destroy', $ingrediente->inventario) }}" 
                                      method="POST" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            onclick="return confirm('¿Estás seguro de eliminar este registro de inventario?')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('inventario.create') }}?ingrediente={{ $ingrediente->id }}"
                                   class="text-green-600 hover:text-green-800 transition-colors"
                                   title="Crear inventario">
                                    <i class="fas fa-plus-circle"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-muted">
                            <i class="fas fa-boxes text-4xl mb-3 block"></i>
                            <p>No hay ingredientes registrados</p>
                            <a href="{{ route('ingredientes.create') }}" class="text-primary hover:text-secondary mt-2 inline-block">
                                <i class="fas fa-plus mr-1"></i> Crear ingrediente primero
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection