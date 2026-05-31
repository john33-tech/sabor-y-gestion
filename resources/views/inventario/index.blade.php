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

    {{-- Alerta de stock bajo: aviso + reposición masiva con 1 clic --}}
    @if(($stockBajo ?? 0) > 0 || ($stockAgotado ?? 0) > 0)
    <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-amber-800 text-sm">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <strong>Atención:</strong> {{ $stockBajo }} ingrediente(s) con <strong>stock bajo</strong>@if(($stockAgotado ?? 0) > 0) ({{ $stockAgotado }} agotado/s)@endif. Usa "Reponer" en cada fila, o repón todos de una vez:
        </div>
        <form action="{{ route('inventario.reponer-todos') }}" method="POST"
              onsubmit="return confirm('¿Reponer al máximo TODOS los ingredientes con stock bajo?');">
            @csrf
            <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition whitespace-nowrap">
                <i class="fas fa-truck-loading mr-1"></i> Reponer todos los bajos
            </button>
        </form>
    </div>
    @endif

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
                                {{-- Reposición rápida: rellena al stock máximo con 1 clic --}}
                                <form action="{{ route('inventario.reponer', $ingrediente->inventario) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-2 py-1 mr-2 text-xs font-semibold text-white bg-emerald-600 rounded hover:bg-emerald-700 transition-colors"
                                            onclick="return confirm('¿Reponer el stock de {{ $ingrediente->nombre }} al máximo?')"
                                            title="Reponer stock al máximo">
                                        <i class="fas fa-truck-loading mr-1"></i> Reponer
                                    </button>
                                </form>
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