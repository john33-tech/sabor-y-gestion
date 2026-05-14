@extends('layouts.app')

@section('title', 'Detalle del Pedido')

@section('content')

<div class="container mx-auto px-4 py-8">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">

        <div>
            <h1 class="text-3xl font-bold" style="color:#C2410C;">
                <i class="fas fa-receipt mr-2"></i>
                Pedido {{ $pedido->numero_pedido }}
            </h1>

            <p class="text-gray-500 mt-1">
                {{ $pedido->created_at->format('d/m/Y H:i') }}
            </p>
        </div>

        <a href="{{ route('pedidos.misPedidos') }}"
           class="px-4 py-2 rounded-lg text-white"
           style="background-color:#78716C;">
            <i class="fas fa-arrow-left mr-1"></i>
            Volver
        </a>

    </div>

    {{-- INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-xl shadow p-4 border">
            <p class="text-sm text-gray-500 mb-1">Estado</p>

            <span class="px-3 py-1 rounded-full text-white text-sm"
                  style="background-color:
                    {{ $pedido->estado == 'pendiente' ? '#F59E0B' :
                       ($pedido->estado == 'en_preparacion' ? '#3B82F6' :
                       ($pedido->estado == 'listo' ? '#10B981' : '#6B7280')) }};">
                {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
            </span>
        </div>

        <div class="bg-white rounded-xl shadow p-4 border">
            <p class="text-sm text-gray-500 mb-1">Tipo Pedido</p>

            <p class="font-bold text-lg">
                {{ ucfirst(str_replace('_', ' ', $pedido->tipo_pedido)) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-4 border">
            <p class="text-sm text-gray-500 mb-1">Total</p>

            <p class="font-bold text-2xl" style="color:#C2410C;">
                Bs. {{ number_format($pedido->total, 2) }}
            </p>
        </div>

    </div>

    {{-- DETALLES --}}
    <div class="bg-white rounded-xl shadow border overflow-hidden">

        <div class="px-6 py-4 border-b"
             style="background-color:#FFF7ED;">

            <h2 class="text-xl font-bold"
                style="color:#C2410C;">

                <i class="fas fa-utensils mr-2"></i>
                Detalle del Pedido

            </h2>
        </div>

        <div class="p-6">

            <div class="space-y-4">

                @foreach($pedido->detalles as $detalle)

                    <div class="flex justify-between items-center border rounded-lg p-4">

                        <div>

                            <h3 class="font-bold text-lg">
                                {{ $detalle->plato->nombre }}
                            </h3>

                            <p class="text-sm text-gray-500">
                                Cantidad: {{ $detalle->cantidad }}
                            </p>

                            @if($detalle->notas)
                                <p class="text-sm mt-1">
                                    📝 {{ $detalle->notas }}
                                </p>
                            @endif

                        </div>

                        <div class="text-right">

                            <p class="font-bold text-lg"
                               style="color:#C2410C;">

                                Bs. {{ number_format($detalle->subtotal, 2) }}

                            </p>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    </div>

</div>

@endsection