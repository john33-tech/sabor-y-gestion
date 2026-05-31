@extends('layouts.app')

@section('title', 'Pedidos Entregados')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold" style="color: #1e40af;">
                    <i class="fas fa-cash-register mr-2"></i> Pedidos Entregados - Caja
                </h1>
                <p class="text-muted mt-1">Pedidos servidos y listos para cobrar y finalizar</p>
            </div>
            <div>
                <button onclick="window.location.reload()" class="px-4 py-2 rounded-lg transition" style="background-color: #DBEAFE; color: #1e40af;">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mb-6 p-4 bg-white rounded-lg shadow-md">
        <form method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Pedido</label>
                <select name="tipo_pedido" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $key => $label)
                        <option value="{{ $key }}" {{ request('tipo_pedido') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 rounded-lg text-white transition" style="background-color: #1e40af;">
                    <i class="fas fa-search mr-2"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Mensajes -->
    @if ($errors->any())
        <div class="mb-4 p-4 rounded-lg" style="background-color: #FEE2E2; border-left: 4px solid #EF4444;">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li class="text-red-700">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg" style="background-color: #D1FAE5; border-left: 4px solid #10B981;">
            <p class="text-green-700"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</p>
        </div>
    @endif

    <!-- Cards de Pedidos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($pedidos as $pedido)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all border-l-4" style="border-left-color: #1e40af;">
                <!-- Header del Pedido -->
                <div class="px-6 py-4 border-b-2" style="border-color: #DBEAFE; background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-xl font-bold" style="color: #1e40af;">Pedido #{{ $pedido->numero_pedido }}</h3>
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-clock mr-1"></i> 
                                {{ $pedido->updated_at->format('H:i') }} ({{ $pedido->updated_at->diffForHumans() }})
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold" style="background-color: #BAE6FD; color: #0c4a6e;">
                            <i class="fas fa-check-double mr-1"></i> Entregado
                        </span>
                    </div>
                </div>

                <!-- Información del Cliente -->
                <div class="px-6 py-4 bg-gray-50">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Cliente</p>
                            <p class="text-sm font-medium text-gray-800">{{ $pedido->cliente_nombre }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Tipo</p>
                            <p class="text-sm font-medium text-gray-800">{{ $tipos[$pedido->tipo_pedido] ?? $pedido->tipo_pedido }}</p>
                        </div>
                        @if($pedido->mesa)
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Mesa</p>
                                <p class="text-sm font-medium text-gray-800">Mesa #{{ $pedido->mesa->numero_mesa }}</p>
                            </div>
                        @endif
                        @if($pedido->cliente_telefono)
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Teléfono</p>
                                <p class="text-sm font-medium text-gray-800">{{ $pedido->cliente_telefono }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Detalles Financieros -->
                <div class="px-6 py-4 bg-blue-50 border-t border-blue-100">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">Subtotal:</span>
                            <span class="font-medium">${{ number_format($pedido->subtotal, 2) }}</span>
                        </div>
                        @if($pedido->descuento > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">Descuento:</span>
                                <span class="font-medium text-red-600">-${{ number_format($pedido->descuento, 2) }}</span>
                            </div>
                        @endif
                        @if($pedido->impuesto > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">Impuesto:</span>
                                <span class="font-medium">${{ number_format($pedido->impuesto, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-bold pt-2 border-t border-blue-200">
                            <span style="color: #1e40af;">TOTAL A COBRAR:</span>
                            <span style="color: #1e40af;">${{ number_format($pedido->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Detalles del Pedido -->
                <div class="px-6 py-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Artículos</p>
                    <ul class="space-y-2">
                        @foreach($pedido->detalles as $detalle)
                            <li class="flex justify-between items-center text-sm">
                                <span>
                                    <span class="font-semibold">{{ $detalle->cantidad }}x</span> 
                                    <span class="text-gray-700">{{ $detalle->plato->nombre }}</span>
                                </span>
                                <span class="text-gray-600">${{ number_format($detalle->subtotal, 2) }}</span>
                            </li>
                            @if($detalle->notas)
                                <li class="text-xs text-gray-500 italic ml-4 px-2 py-1 bg-gray-100 rounded">
                                    <i class="fas fa-note-sticky mr-1"></i> {{ $detalle->notas }}
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <!-- Notas del Pedido -->
                @if($pedido->notas)
                    <div class="px-6 py-3 bg-yellow-50 border-t border-yellow-200">
                        <p class="text-xs font-semibold text-yellow-800 mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Notas:</p>
                        <p class="text-sm text-yellow-900">{{ $pedido->notas }}</p>
                    </div>
                @endif

                <!-- Acciones -->
                <div class="px-6 py-4 bg-gray-50 border-t flex gap-2">
                    <form action="{{ route('pedidos.finalizar', $pedido) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 rounded-lg font-semibold text-white transition" 
                            style="background-color: #059669;" 
                            onclick="return confirm('¿Finalizar este pedido? Se liberará la mesa si aplica.')">
                            <i class="fas fa-check-square mr-2"></i> Finalizar y Liberar
                        </button>
                    </form>
                    <a href="{{ route('pedidos.show', $pedido) }}" class="flex-1 px-4 py-2 rounded-lg font-semibold text-gray-700 border-2 border-gray-300 text-center transition hover:bg-gray-100">
                        <i class="fas fa-eye mr-2"></i> Detalles
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 bg-white rounded-lg shadow">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No hay pedidos entregados pendientes de caja</p>
                <p class="text-gray-400 text-sm mt-2">Los pedidos aparecerán aquí cuando el mesero los marque como entregados</p>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    <div class="mt-8">
        {{ $pedidos->links() }}
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh cada 45 segundos
    setInterval(() => {
        // location.reload(); // Descomenta si quieres auto-refresh
    }, 45000);
</script>
@endpush

@endsection
