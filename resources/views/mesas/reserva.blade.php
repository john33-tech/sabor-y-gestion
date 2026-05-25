@extends('layouts.app')

@section('title', 'Reserva de Mesa')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-text">📅 Reservas Disponibles</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($mesas as $mesa)
            <div class="bg-surface rounded-lg shadow-lg overflow-hidden border border-border hover:shadow-xl transition duration-300 relative group">
                
                <div class="p-6">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="text-2xl font-bold {{ $mesa->estado == 'libre' ? 'text-green-600' : ($mesa->estado == 'ocupado' ? 'text-red-600' : 'text-yellow-600') }}">
                            Mesa {{ $mesa->numero_mesa }}
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                            {{ $mesa->estado == 'libre' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $mesa->estado == 'ocupado' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $mesa->estado == 'reservado' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $mesa->estado == 'fuera_servicio' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst($mesa->estado) }}
                        </span>
                    </div>

                    <div class="space-y-2 mb-4">
                        <p class="text-sm text-muted">
                            <i class="fas fa-users mr-2"></i> Capacidad: <span class="font-semibold">{{ $mesa->capacidad }} personas</span>
                        </p>
                        <p class="text-sm text-muted">
                            <i class="fas fa-map-marker-alt mr-2"></i> Ubicación: <span class="font-semibold">{{ $mesa->area ?? 'General' }}</span>
                        </p>
                    </div>

                    @if($mesa->estado == 'reservado' && $mesa->hora_reserva)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/30 rounded-lg p-3 mb-4">
                            <p class="text-xs font-semibold text-yellow-700 dark:text-yellow-300 mb-1">
                                ⏰ Próxima Reserva
                            </p>
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <i class="fas fa-user mr-1"></i> {{ $mesa->cliente_reserva }}
                            </p>
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <i class="fas fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($mesa->hora_reserva)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @elseif($mesa->estado == 'ocupado')
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/30 rounded-lg p-3 mb-4">
                            <p class="text-xs font-semibold text-red-700 dark:text-red-300 mb-1">
                                🔴 Mesa Ocupada
                            </p>
                            <p class="text-sm text-red-800 dark:text-red-200">
                                <i class="fas fa-utensils mr-1"></i> Los clientes están usando esta mesa
                            </p>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <a href="{{ route('mesas.edit', $mesa->id) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-semibold transition duration-300 flex items-center justify-center">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                        <button onclick="confirmarEliminacion({{ $mesa->id }}, '{{ $mesa->numero_mesa }}')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-semibold transition duration-300 flex items-center justify-center">
                            <i class="fas fa-trash mr-1"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-surface rounded-lg border-2 border-dashed border-border">
                <div class="text-5xl mb-4">🪑</div>
                <h3 class="text-lg font-semibold text-text mb-2">No hay mesas registradas</h3>
                <p class="text-muted mb-4">Empieza creando tu primera mesa</p>
                <a href="{{ route('mesas.create') }}" class="bg-primary hover:bg-secondary text-surface px-6 py-3 rounded-lg font-semibold transition duration-300">
                    <i class="fas fa-plus mr-2"></i> Crear Primera Mesa
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection