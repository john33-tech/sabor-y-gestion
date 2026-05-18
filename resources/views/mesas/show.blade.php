@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 bg-background min-h-screen">
    <div class="max-w-2xl mx-auto">
        <div class="bg-surface rounded-lg shadow-lg overflow-hidden">
            <div class="bg-primary p-6">
                <h1 class="text-2xl font-bold text-surface">
                    <i class="fas fa-chair mr-2"></i> Detalles de Mesa {{ $mesa->numero_mesa }}
                </h1>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-muted text-sm uppercase tracking-wide">
                            <i class="fas fa-hashtag mr-1"></i> Número de Mesa
                        </label>
                        <p class="text-text font-semibold text-2xl mt-1">{{ $mesa->numero_mesa }}</p>
                    </div>
                    
                    <div>
                        <label class="text-muted text-sm uppercase tracking-wide">
                            <i class="fas fa-users mr-1"></i> Capacidad
                        </label>
                        <p class="text-text font-semibold text-2xl mt-1">{{ $mesa->capacidad }} personas</p>
                    </div>
                    
                    <div>
                        <label class="text-muted text-sm uppercase tracking-wide">
                            <i class="fas fa-map-marker-alt mr-1"></i> Área
                        </label>
                        <p class="text-text font-semibold text-lg mt-1">{{ $mesa->area ?? 'No especificada' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-muted text-sm uppercase tracking-wide">
                            <i class="fas fa-info-circle mr-1"></i> Estado
                        </label>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
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
                            </span>
                        </div>
                    </div>
                </div>

                @php
                    $hasReservaInfo = false;
                    $clienteName = null;
                    $clientePhone = null;
                    $reservaDateTime = null;

                    if (isset($reservaActiva) && $reservaActiva) {
                        $hasReservaInfo = true;
                        $clienteName = $reservaActiva->usuario->name ?? 'Desconocido';
                        $clientePhone = $reservaActiva->usuario->celular ?? 'No especificado';
                        $reservaDateTime = \Carbon\Carbon::parse($reservaActiva->fecha_reserva->format('Y-m-d') . ' ' . $reservaActiva->hora_reserva);
                    } elseif ($mesa->cliente_reserva || $mesa->hora_reserva) {
                        $hasReservaInfo = true;
                        $clienteName = $mesa->cliente_reserva;
                        $clientePhone = $mesa->telefono_reserva;
                        $reservaDateTime = $mesa->hora_reserva ? \Carbon\Carbon::parse($mesa->hora_reserva) : null;
                    }
                @endphp

                @if($mesa->estado == 'reservado' && $hasReservaInfo)
                    <div class="border-t-2 border-border pt-4">
                        <h2 class="font-semibold text-text text-lg mb-4">
                            <i class="fas fa-clipboard-list mr-2 text-primary"></i> Información de Reserva
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($clienteName)
                                <div>
                                    <label class="text-muted text-sm">
                                        <i class="fas fa-user mr-1"></i> Cliente
                                    </label>
                                    <p class="text-text font-medium">{{ $clienteName }}</p>
                                </div>
                            @endif
                            
                            @if($clientePhone)
                                <div>
                                    <label class="text-muted text-sm">
                                        <i class="fas fa-phone mr-1"></i> Teléfono
                                    </label>
                                    <p class="text-text font-medium">{{ $clientePhone }}</p>
                                </div>
                            @endif
                            
                            @if($reservaDateTime)
                                <div class="md:col-span-2">
                                    <label class="text-muted text-sm">
                                        <i class="far fa-calendar-alt mr-1"></i> Fecha y Hora
                                    </label>
                                    <p class="text-text font-medium">
                                        {{ $reservaDateTime->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex gap-3 pt-4 border-t-2 border-border">
                    <a href="{{ route('mesas.edit', $mesa) }}" class="bg-primary hover:bg-secondary text-surface px-6 py-2 rounded-lg transition">
                        <i class="fas fa-edit mr-2"></i> Editar Mesa
                    </a>
                    <a href="{{ route('mesas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-surface px-6 py-2 rounded-lg transition">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al Mapa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection