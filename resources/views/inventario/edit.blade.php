@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-primary">Editar Inventario</h1>
        <a href="{{ route('inventario.index') }}" class="text-sm font-medium text-primary hover:text-secondary transition-colors duration-200" style="color: #C2410C;">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    <div class="card">
        <form action="{{ route('inventario.update', $inventario) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-carrot mr-1 text-primary"></i> Ingrediente
                    </label>
                    <div class="px-4 py-2 bg-gray-50 rounded-lg border border-border">
                        {{ $inventario->ingrediente->nombre }}
                        <span class="text-xs text-muted ml-2">({{ $inventario->ingrediente->unidad_medida }})</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-chart-line mr-1 text-primary"></i> Cantidad Actual *
                    </label>
                    <input type="number" 
                           name="cantidad_actual" 
                           value="{{ old('cantidad_actual', $inventario->cantidad_actual) }}"
                           step="0.01"
                           required
                           class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                    @error('cantidad_actual')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-flag-checkered mr-1 text-primary"></i> Stock Mínimo (Alerta) *
                    </label>
                    <input type="number" 
                           name="stock_minimo" 
                           value="{{ old('stock_minimo', $inventario->stock_minimo) }}"
                           step="0.01"
                           required
                           class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                    <p class="text-xs text-muted mt-1">Cuando la cantidad llegue a este valor, se mostrará una alerta</p>
                    @error('stock_minimo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-arrow-up mr-1 text-primary"></i> Stock Máximo (Opcional)
                    </label>
                    <input type="number" 
                           name="stock_maximo" 
                           value="{{ old('stock_maximo', $inventario->stock_maximo) }}"
                           step="0.01"
                           class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                    @error('stock_maximo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-map-marker-alt mr-1 text-primary"></i> Ubicación
                    </label>
                    <input type="text" 
                           name="ubicacion" 
                           value="{{ old('ubicacion', $inventario->ubicacion) }}"
                           placeholder="Ej: Estante A, Refrigerador 1"
                           class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                    @error('ubicacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Estado actual: 
                        <span class="font-bold">
                            @if($inventario->isLowStock())
                                ⚠️ Stock bajo ({{ $inventario->cantidad_actual }} / {{ $inventario->stock_minimo }})
                            @else
                                ✅ Stock suficiente
                            @endif
                        </span>
                    </p>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-save mr-2"></i> Actualizar Inventario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection