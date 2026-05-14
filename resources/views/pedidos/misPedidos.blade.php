@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('content')

<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex justify-between items-center">

        <h1 class="text-3xl font-bold text-primary">

            <i class="fas fa-shopping-cart mr-2"></i>
            Mis Pedidos

        </h1>

        <a href="{{ route('pedidos.cliente') }}"
           class="bg-primary hover:bg-secondary text-white px-5 py-3 rounded-lg shadow-lg transition">

            <i class="fas fa-plus mr-2"></i>
            Nuevo Pedido

        </a>

    </div>

    <!-- Mensajes -->
    @if(session('success'))

        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">

            {{ session('success') }}

        </div>

    @endif

    @if(session('error'))

        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">

            {{ session('error') }}

        </div>

    @endif

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-gray-100">

                    <tr class="text-left text-sm uppercase text-gray-600">

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

                        <tr class="hover:bg-gray-50 transition">

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

                            <!-- Estado -->
                            <td class="px-6 py-4">

                                <span class="px-3 py-1 rounded-full text-xs font-bold

                                    {{ $pedido->estado == 'pendiente' ? 'bg-yellow-100 text-yellow-700' : '' }}

                                    {{ $pedido->estado == 'en_preparacion' ? 'bg-blue-100 text-blue-700' : '' }}

                                    {{ $pedido->estado == 'entregado' ? 'bg-green-100 text-green-700' : '' }}

                                    {{ $pedido->estado == 'cancelado' ? 'bg-red-100 text-red-700' : '' }}

                                ">

                                    {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}

                                </span>

                            </td>

                            <!-- Ubicación -->
                            <td class="px-6 py-4">

                                @if($pedido->latitud && $pedido->longitud)

                                    <a href="https://www.google.com/maps?q={{ $pedido->latitud }},{{ $pedido->longitud }}"
                                       target="_blank"
                                       class="text-blue-600 hover:underline">

                                        <i class="fas fa-map-marker-alt mr-1"></i>
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
                                    <a href="{{ route('pedidos.show.cliente', $pedido) }}"
                                    class="px-3 py-1 rounded text-white text-sm"
                                    style="background-color:#3B82F6;">
                                    <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- EDITAR --}}
                                    @if(in_array($pedido->estado, ['pendiente', 'en_preparacion']))
                                    <a href="{{ route('pedidos.edit.cliente', $pedido) }}"
                                    class="px-3 py-1 rounded text-white text-sm"
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
                                         class="px-3 py-1 rounded text-white text-sm"
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

                                <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>

                                <p class="text-gray-500 mb-4">

                                    No tienes pedidos registrados.

                                </p>

                                <a href="{{ route('pedidos.cliente')  }}"
                                   class="bg-primary hover:bg-secondary text-white px-5 py-3 rounded-lg shadow">

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