@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto bg-surface rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-text mb-6">➕ Nueva Mesa</h1>
        
        <form action="{{ route('mesas.store') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="block text-text font-medium mb-2">Número de Mesa *</label>
                    <input type="text" 
                           name="numero_mesa" 
                           value="{{ old('numero_mesa') }}" 
                           class="w-full px-4 py-2 border border-border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                           required 
                           placeholder="Ej: 1, 2A, B3">
                    @error('numero_mesa')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-text font-medium mb-2">Capacidad (personas) *</label>
                    <input type="number" 
                           name="capacidad" 
                           value="{{ old('capacidad', 4) }}" 
                           class="w-full px-4 py-2 border border-border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                           required 
                           min="1" 
                           max="20">
                    @error('capacidad')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-text font-medium mb-2">Área</label>
                    <input type="text" 
                           name="area" 
                           value="{{ old('area') }}" 
                           class="w-full px-4 py-2 border border-border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                           placeholder="Ej: Planta Baja, Terraza, Jardín">
                    @error('area')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-text font-medium mb-2">Estado *</label>
                    <select name="estado" 
                            id="estado" 
                            class="w-full px-4 py-2 border border-border rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                        <option value="libre" {{ old('estado') == 'libre' ? 'selected' : '' }}>Libre</option>
                        <option value="ocupado" {{ old('estado') == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                        <option value="reservado" {{ old('estado') == 'reservado' ? 'selected' : '' }}>Reservado</option>
                        <option value="fuera_servicio" {{ old('estado') == 'fuera_servicio' ? 'selected' : '' }}>Fuera de Servicio</option>
                    </select>
                </div>

                <div id="reservaFields" style="{{ old('estado') == 'reservado' ? 'display: block;' : 'display: none;' }}">
                    <div class="border-t border-border pt-4 mt-4">
                        <h3 class="font-semibold text-text mb-3">📋 Datos de Reserva</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-text text-sm mb-1">Nombre del Cliente</label>
                                <input type="text" 
                                       name="cliente_reserva" 
                                       value="{{ old('cliente_reserva') }}" 
                                       class="w-full px-4 py-2 border border-border rounded-lg focus:outline-none focus:border-primary">
                                @error('cliente_reserva')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-text text-sm mb-1">Teléfono de Contacto</label>
                                <input type="number" 
                                       name="telefono_reserva" 
                                       value="{{ old('telefono_reserva') }}" 
                                       class="w-full px-4 py-2 border border-border rounded-lg focus:outline-none focus:border-primary">
                                @error('telefono_reserva')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-text text-sm mb-1">Fecha y Hora de Reserva</label>
                                <input type="datetime-local" 
                                       name="hora_reserva" 
                                       value="{{ old('hora_reserva') }}" 
                                       class="w-full px-4 py-2 border border-border rounded-lg focus:outline-none focus:border-primary">
                                @error('hora_reserva')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="bg-primary hover:bg-secondary text-surface px-6 py-2 rounded-lg transition">
                        Guardar Mesa
                    </button>
                    <a href="{{ route('mesas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-surface px-6 py-2 rounded-lg transition">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const estadoSelect = document.getElementById('estado');
    const reservaFields = document.getElementById('reservaFields');
    
    function toggleReservaFields() {
        reservaFields.style.display = estadoSelect.value === 'reservado' ? 'block' : 'none';
    }
    
    estadoSelect.addEventListener('change', toggleReservaFields);
</script>
@endsection