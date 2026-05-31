@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('content')
<script>
    // Auto-refresh cuando llega un evento de cambio de estado de cualquier pedido
    // del cliente (suscripción global vive en resources/js/app.js).
    window.addEventListener('pedido-estado-cambiado', () => {
        setTimeout(() => window.location.reload(), 1800);
    });
</script>


<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex items-center justify-between">

        <h1 class="text-3xl font-bold text-primary">

            <i class="mr-2 fas fa-shopping-cart"></i>
            Mis Pedidos Cliente

        </h1>

        <a href="{{ route('pedidos.cliente') }}"
           class="px-5 py-3 text-white transition rounded-lg shadow-lg bg-primary hover:bg-secondary">

            <i class="mr-2 fas fa-plus"></i>
            Nuevo Pedido
        </a>

    </div>

    <!-- Mensajes -->
    @if(session('success'))

        <div class="px-4 py-3 text-green-700 bg-green-100 border border-green-400 rounded">

            {{ session('success') }}

        </div>

    @endif

    @if(session('error'))

        <div class="px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded">

            {{ session('error') }}

        </div>

    @endif

    <!-- Tabla -->
    <div class="overflow-hidden bg-white shadow-lg rounded-xl">

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-gray-100">

                    <tr class="text-sm text-left text-gray-600 uppercase">

                        <th class="px-6 py-4">Pedido</th>

                        <th class="px-6 py-4">Tipo</th>

                        <th class="px-6 py-4">Total</th>

                        <th class="px-6 py-4">Estado</th>

                        <th class="px-6 py-4">Ubicación</th>

                        <th class="px-6 py-4">Fecha</th>

                        <th class="px-6 py-4 text-center">Acciones</th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($pedidos as $pedido)

                        <tr class="transition hover:bg-gray-50">

                            <!-- Número Pedido -->
                            <td class="px-6 py-4">

                                <div class="font-semibold text-primary">

                                    #{{ $pedido->numero_pedido }}

                                </div>

                                @if($pedido->mesa)

                                    <div class="text-sm text-gray-500">

                                        Mesa {{ $pedido->mesa->numero_mesa }}

                                    </div>

                                @endif

                            </td>

                            <!-- Tipo -->
                            <td class="px-6 py-4">

                                <span class="capitalize">

                                    {{ str_replace('_', ' ', $pedido->tipo_pedido) }}

                                </span>

                            </td>

                            <!-- Total -->
                            <td class="px-6 py-4 font-bold text-green-600">

                                Bs {{ number_format($pedido->total, 2) }}

                            </td>

                            <!-- Estado: timeline compacto + badge -->
                            <td class="px-6 py-4 min-w-[260px]" data-pedido-estado="{{ $pedido->id }}">
                                <x-pedido-timeline :estado="$pedido->estado" />
                            </td>

                            <!-- Ubicación -->
                            <td class="px-6 py-4">

                                @if($pedido->latitud && $pedido->longitud)

                                    <a href="https://www.google.com/maps?q={{ $pedido->latitud }},{{ $pedido->longitud }}"
                                       target="_blank"
                                       class="text-blue-600 hover:underline">

                                        <i class="mr-1 fas fa-map-marker-alt"></i>
                                        Ver Mapa

                                    </a>

                                @else

                                    <span class="text-gray-400">

                                        No disponible

                                    </span>

                                @endif

                            </td>

                            <!-- Fecha -->
                            <td class="px-6 py-4 text-sm text-gray-500">

                                {{ $pedido->created_at->format('d/m/Y H:i') }}

                            </td>

                            <!-- Acciones -->
                            <td class="px-4 py-2">
                                <div class="flex gap-2">

                                    {{-- VER --}}
                                    <a href="{{ route('pedidos.showCliente', $pedido) }}"
                                    class="px-3 py-1 text-sm text-white rounded"
                                    style="background-color:#3B82F6;">
                                    <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- EDITAR --}}
                                    @if(in_array($pedido->estado, ['pendiente', 'en_preparacion']))
                                    <a href="{{ route('pedidos.edit.cliente', $pedido) }}"
                                    class="px-3 py-1 text-sm text-white rounded"
                                      style="background-color:#F59E0B;">
                                    <i class="fas fa-edit"></i>
                                    </a>
                                    @endif

                                    {{-- CANCELAR --}}
                                    @if($pedido->estado == 'pendiente')
                                    <form action="{{ route('pedidos.destroy.cliente', $pedido) }}"
                                     method="POST"
                                      onsubmit="return confirm('¿Cancelar este pedido?')">

                                         @csrf
                                         @method('DELETE')

                                         <button type="submit"
                                         class="px-3 py-1 text-sm text-white rounded"
                                         style="background-color:#EF4444;">
                                         <i class="fas fa-trash"></i>
                                         </button>

                                    </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="px-6 py-10 text-center">

                                <i class="mb-4 text-5xl text-gray-300 fas fa-shopping-cart"></i>

                                <p class="mb-4 text-gray-500">

                                    No tienes pedidos registrados.

                                </p>

                                <a href="{{ route('pedidos.cliente')  }}"
                                   class="px-5 py-3 text-white rounded-lg shadow bg-primary hover:bg-secondary">

                                    Realizar mi primer pedido

                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- Paginación -->
        @if($pedidos->hasPages())

            <div class="px-6 py-4 border-t">

                {{ $pedidos->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
