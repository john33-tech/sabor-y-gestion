@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-primary">Configurar Inventario</h1>
        <a href="{{ route('inventario.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    <div class="card">
        <form action="{{ route('inventario.store') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-carrot mr-1 text-primary"></i> Ingrediente *
                    </label>
                    <select name="ingrediente_id" required class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                        <option value="">Selecciona un ingrediente</option>
                        @foreach($ingredientes as $ingrediente)
                            <option value="{{ $ingrediente->id }}" {{ request('ingrediente') == $ingrediente->id ? 'selected' : '' }}>
                                {{ $ingrediente->nombre }} ({{ $ingrediente->unidad_medida }})
                            </option>
                        @endforeach
                    </select>
                    @error('ingrediente_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-chart-line mr-1 text-primary"></i> Cantidad Actual *
                    </label>
                    <input type="number" 
                           name="cantidad_actual" 
                           value="{{ old('cantidad_actual') }}"
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
                           value="{{ old('stock_minimo', 100) }}"
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
                           value="{{ old('stock_maximo') }}"
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
                           value="{{ old('ubicacion') }}"
                           placeholder="Ej: Estante A, Refrigerador 1"
                           class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                    @error('ubicacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> Guardar Inventario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection