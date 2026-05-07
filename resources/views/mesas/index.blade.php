@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 bg-background min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-primary">🍽️ Mapa de Mesas</h1>
        <div class="flex gap-3 items-center">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                <span class="text-sm text-muted">Libre</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                <span class="text-sm text-muted">Ocupado</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                <span class="text-sm text-muted">Reservado</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-gray-500 rounded-full"></span>
                <span class="text-sm text-muted">Fuera de Servicio</span>
            </div>
            <a href="{{ route('mesas.create') }}" class="bg-primary hover:bg-secondary text-surface px-5 py-2.5 rounded-lg transition duration-300 ml-4 shadow-md hover:shadow-lg">
                <i class="fas fa-plus mr-2"></i> Nueva Mesa
            </a>
        </div>
    </div>

    @php
        $agrupadas = $mesas->groupBy('area');
    @endphp

    @forelse($agrupadas as $area => $mesasArea)
        <div class="mb-10">
            <h2 class="text-xl font-semibold text-primary mb-4 border-l-4 border-primary pl-3">
                📍 {{ $area ?: 'Sin área asignada' }}
            </h2>
            
            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                @foreach($mesasArea as $mesa)
                    <div class="relative group">
                        <!-- Mesa cuadrada - Click en toda la tarjeta lleva al show -->
                        <a href="{{ route('mesas.show', $mesa) }}" class="block">
                            <div class="
                                relative flex flex-col items-center justify-center
                                transition-all duration-300 hover:shadow-xl hover:scale-105
                                aspect-square rounded-xl cursor-pointer
                                {{ $mesa->estado == 'libre' ? 'bg-green-100 border-2 border-green-400' : '' }}
                                {{ $mesa->estado == 'ocupado' ? 'bg-red-100 border-2 border-red-400' : '' }}
                                {{ $mesa->estado == 'reservado' ? 'bg-yellow-100 border-2 border-yellow-400' : '' }}
                                {{ $mesa->estado == 'fuera_servicio' ? 'bg-gray-100 border-2 border-gray-400' : '' }}
                            ">
                                
                                <!-- Contenido de la mesa -->
                                <div class="text-center p-4 w-full">
                                    <!-- Número de mesa -->
                                    <div class="text-2xl font-bold mb-2 {{ $mesa->estado == 'libre' ? 'text-green-700' : ($mesa->estado == 'ocupado' ? 'text-red-700' : ($mesa->estado == 'reservado' ? 'text-yellow-700' : 'text-gray-700')) }}">
                                        Mesa {{ $mesa->numero_mesa }}
                                    </div>
                                    
                                    <!-- Capacidad con iconos -->
                                    <div class="text-sm text-gray-600 mb-2">
                                        @for($i = 1; $i <= min($mesa->capacidad, 6); $i++)
                                            <i class="fas fa-chair text-xs"></i>
                                        @endfor
                                        @if($mesa->capacidad > 6)
                                            <span class="text-xs">+{{ $mesa->capacidad - 6 }}</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Capacidad en texto -->
                                    <div class="text-xs font-semibold {{ $mesa->estado == 'libre' ? 'text-green-600' : ($mesa->estado == 'ocupado' ? 'text-red-600' : ($mesa->estado == 'reservado' ? 'text-yellow-600' : 'text-gray-600')) }} mb-2">
                                        <i class="fas fa-users mr-1"></i> {{ $mesa->capacidad }} personas
                                    </div>
                                    
                                    <!-- Estado -->
                                    <div class="text-xs font-bold uppercase tracking-wide px-2 py-1 rounded-full inline-block
                                        {{ $mesa->estado == 'libre' ? 'bg-green-500 text-white' : '' }}
                                        {{ $mesa->estado == 'ocupado' ? 'bg-red-500 text-white' : '' }}
                                        {{ $mesa->estado == 'reservado' ? 'bg-yellow-500 text-white' : '' }}
                                        {{ $mesa->estado == 'fuera_servicio' ? 'bg-gray-500 text-white' : '' }}">
                                        @if($mesa->estado == 'libre')
                                            <i class="fas fa-check-circle mr-1"></i> Libre
                                        @elseif($mesa->estado == 'ocupado')
                                            <i class="fas fa-user-clock mr-1"></i> Ocupado
                                        @elseif($mesa->estado == 'reservado')
                                            <i class="fas fa-calendar-check mr-1"></i> Reservado
                                        @else
                                            <i class="fas fa-tools mr-1"></i> Fuera de Servicio
                                        @endif
                                    </div>

                                    <!-- Información de reserva (solo un preview) -->
                                    @if($mesa->estado == 'reservado' && $mesa->hora_reserva)
                                        <div class="mt-3 pt-2 border-t border-gray-300 text-xs">
                                            <div class="font-semibold text-gray-700">
                                                <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($mesa->hora_reserva)->format('d/m H:i') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>

                        <!-- Botones de acción (editar/eliminar) - Evitan propagación del clic -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-20">
                            <div class="flex gap-1">
                                <a href="{{ route('mesas.edit', $mesa) }}" 
                                   class="bg-white text-blue-600 p-2 rounded-full shadow-md hover:bg-blue-600 hover:text-white transition-colors"
                                   title="Editar mesa"
                                   onclick="event.stopPropagation()">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <form action="{{ route('mesas.destroy', $mesa) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('¿Eliminar la mesa {{ $mesa->numero_mesa }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-white text-red-600 p-2 rounded-full shadow-md hover:bg-red-600 hover:text-white transition-colors"
                                            title="Eliminar mesa"
                                            onclick="event.stopPropagation()">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Patas de la mesa -->
                        <div class="flex justify-center gap-3 mt-2">
                            <div class="w-2 h-3 bg-gray-400 rounded-full"></div>
                            <div class="w-2 h-3 bg-gray-400 rounded-full"></div>
                            <div class="w-2 h-3 bg-gray-400 rounded-full"></div>
                            <div class="w-2 h-3 bg-gray-400 rounded-full"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-surface rounded-xl shadow-lg p-10 text-center border border-border">
            <div class="text-6xl mb-4">🍽️</div>
            <p class="text-muted text-lg mb-4">No hay mesas registradas</p>
            <a href="{{ route('mesas.create') }}" class="inline-block bg-primary hover:bg-secondary text-surface px-6 py-3 rounded-lg transition duration-300 shadow-md hover:shadow-lg">
                <i class="fas fa-plus mr-2"></i> Crear primera mesa
            </a>
        </div>
    @endforelse
</div>
@endsection