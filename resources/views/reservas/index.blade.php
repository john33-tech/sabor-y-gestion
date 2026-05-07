@extends('layouts.app')

@section('title', 'Mis Reservas')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-primary">
            <i class="fas fa-calendar-alt mr-2"></i>Mis Reservas
        </h1>

        <a href="{{ route('reserva.create') }}"
           class="bg-primary hover:bg-secondary text-surface px-4 py-2 rounded-lg transition duration-300 shadow-md">
            <i class="fas fa-plus mr-2"></i>Nueva Reserva
        </a>
    </div>

    <!-- Mensaje éxito -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
             role="alert">
            <span class="block sm:inline">
                {{ session('success') }}
            </span>
        </div>
    @endif

    <!-- Mensaje error -->
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
             role="alert">
            <span class="block sm:inline">
                {{ session('error') }}
            </span>
        </div>
    @endif

    <!-- Tabla -->
    <div class="bg-surface rounded-lg shadow-lg overflow-hidden border border-border">

        <div class="overflow-x-auto">

            <table class="w-full whitespace-nowrap">

                <!-- Header -->
                <thead class="bg-background">
                    <tr class="text-left text-xs font-semibold text-muted uppercase tracking-wider">
                        <th class="px-6 py-4">Mesa</th>
                        <th class="px-6 py-4">Fecha y Hora</th>
                        <th class="px-6 py-4">Personas</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <!-- Body -->
                <tbody class="divide-y divide-border">

                    @forelse($reservas as $reserva)

                        <tr class="hover:bg-background/50 transition-colors">

                            <!-- Mesa -->
                            <td class="px-6 py-4">
                                <span class="font-medium">
                                    Mesa {{ $reserva->mesa->numero_mesa }}
                                </span>

                                <div class="text-xs text-muted">
                                    {{ $reserva->mesa->area }}
                                </div>
                            </td>

                            <!-- Fecha -->
                            <td class="px-6 py-4">
                                <div>
                                    {{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }}
                                </div>

                                <div class="text-sm text-muted">
                                    {{ $reserva->hora_reserva }}
                                </div>
                            </td>

                            <!-- Personas -->
                            <td class="px-6 py-4">
                                {{ $reserva->personas }}
                            </td>

                            <!-- Estado -->
                            <td class="px-6 py-4">

                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full

                                    {{ $reserva->estado == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : '' }}

                                    {{ $reserva->estado == 'confirmada' ? 'bg-green-100 text-green-800' : '' }}

                                    {{ $reserva->estado == 'cancelada' ? 'bg-red-100 text-red-800' : '' }}

                                    {{ $reserva->estado == 'completada' ? 'bg-blue-100 text-blue-800' : '' }}
                                ">

                                    {{ ucfirst($reserva->estado) }}

                                </span>

                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4">

                                <div class="flex items-center justify-center gap-2">

                                    <!-- Editar -->
                                    <a href="{{ route('reserva.edit', $reserva->id) }}"
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded-lg transition duration-300 shadow-md text-sm">

                                        <i class="fas fa-edit"></i>

                                    </a>

                                    <!-- Eliminar -->
                                    <form action="{{ route('reserva.destroy', $reserva->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar esta reserva?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition duration-300 shadow-md text-sm">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="px-6 py-8 text-center text-muted">

                                <i class="fas fa-calendar-times text-4xl mb-3 text-border"></i>

                                <p>No tienes reservas registradas.</p>

                                <a href="{{ route('reserva.create') }}"
                                   class="text-primary hover:underline mt-2 inline-block">

                                    Realiza tu primera reserva

                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- Paginación -->
        @if($reservas->hasPages())

            <div class="px-6 py-4 border-t border-border">

                {{ $reservas->links() }}

            </div>

        @endif

    </div>

</div>
@endsection