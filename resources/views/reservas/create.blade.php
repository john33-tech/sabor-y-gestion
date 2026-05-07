@extends('layouts.app')

@section('title', 'Nueva Reserva')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-primary">
            <i class="fas fa-calendar-plus mr-2"></i>Nueva Reserva
        </h1>
        <a href="{{ route('reserva.index') }}" class="text-muted hover:text-text transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">¡Error!</strong>
            <span class="block sm:inline">Por favor corrige los siguientes errores:</span>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-surface rounded-lg shadow-lg overflow-hidden border border-border">
        <form action="{{ route('reserva.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <!-- Selección de Mesa -->
                <div class="col-span-1 md:col-span-2">
                    <label for="mesa_id" class="block text-sm font-medium text-text mb-2">Seleccionar Mesa <span class="text-red-500">*</span></label>
                    <select name="mesa_id" id="mesa_id" class="w-full bg-background border border-border rounded-lg px-4 py-2 text-text focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-300" required>
                        <option value="">-- Selecciona una mesa --</option>
                        @foreach($mesas as $mesa)
                            <option value="{{ $mesa->id }}" {{ old('mesa_id') == $mesa->id ? 'selected' : '' }}>
                                Mesa {{ $mesa->numero_mesa }} - Capacidad: {{ $mesa->capacidad }} personas ({{ $mesa->area ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha -->
                <div>
                    <label for="fecha_reserva" class="block text-sm font-medium text-text mb-2">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_reserva" id="fecha_reserva" value="{{ old('fecha_reserva', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" class="w-full bg-background border border-border rounded-lg px-4 py-2 text-text focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-300" required>
                </div>

                <!-- Hora -->
                <div>
                    <label for="hora_reserva" class="block text-sm font-medium text-text mb-2">Hora <span class="text-red-500">*</span></label>
                    <input type="time" name="hora_reserva" id="hora_reserva" value="{{ old('hora_reserva') }}" class="w-full bg-background border border-border rounded-lg px-4 py-2 text-text focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-300" required>
                </div>

                <!-- Personas -->
                <div>
                    <label for="personas" class="block text-sm font-medium text-text mb-2">Cantidad de Personas <span class="text-red-500">*</span></label>
                    <input type="number" name="personas" id="personas" value="{{ old('personas', 2) }}" min="1" max="20" class="w-full bg-background border border-border rounded-lg px-4 py-2 text-text focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-300" required>
                </div>

            </div>

            <!-- Notas -->
            <div class="mb-6">
                <label for="notas" class="block text-sm font-medium text-text mb-2">Notas Especiales (Opcional)</label>
                <textarea name="notas" id="notas" rows="3" class="w-full bg-background border border-border rounded-lg px-4 py-2 text-text focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-300 placeholder-muted" placeholder="Ej: Celebración de cumpleaños, alergias, etc.">{{ old('notas') }}</textarea>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-4 border-t border-border">
                <a href="{{ route('reserva.index') }}" class="px-6 py-2 border border-border rounded-lg text-text hover:bg-background transition duration-300">
                    Cancelar
                </a>
                <button type="submit" class="bg-primary hover:bg-secondary text-surface px-6 py-2 rounded-lg font-semibold transition duration-300 shadow-md">
                    <i class="fas fa-save mr-2"></i>Confirmar Reserva
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
