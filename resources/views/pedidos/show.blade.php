@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->numero_pedido)

@section('content')
<div class="space-y-6">
    <!-- Header con botones de acción -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-primary">
                <i class="fas fa-receipt mr-2"></i> Pedido #{{ $pedido->numero_pedido }}
            </h1>
            <p class="text-muted mt-1">
                Creado: {{ $pedido->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('pedidos.imprimir', $pedido) }}" target="_blank" class="btn-secondary">
                <i class="fas fa-print mr-2"></i> Imprimir Ticket
            </a>
            @if(in_array($pedido->estado, ['pendiente', 'en_preparacion']))
                <a href="{{ route('pedidos.edit', $pedido) }}" class="btn-primary">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            @endif
            <a href="{{ route('pedidos.index') }}" class="btn-gray">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información del Pedido -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Detalles del Pedido -->
            <div class="card">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="fas fa-list-ul mr-2 text-primary"></i> Detalle del Pedido
                </h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr>
                                <th class="text-left py-2">Plato</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Precio</th>
                                <th class="text-right">Subtotal</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedido->detalles as $detalle)
                            <tr class="border-b">
                                <td class="py-3">
                                    <div class="font-semibold">{{ $detalle->plato->nombre }}</div>
                                    @if($detalle->notas)
                                        <div class="text-xs text-muted">Nota: {{ $detalle->notas }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $detalle->cantidad }}</td>
                                <td class="text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="text-right font-semibold">${{ number_format($detalle->subtotal, 2) }}</td>
                                <td class="text-center">
                                    <select class="estado-detalle text-xs px-2 py-1 rounded border"
                                            data-id="{{ $detalle->id }}"
                                            {{ $pedido->estado == 'entregado' ? 'disabled' : '' }}>
                                        @foreach($estadosDetalle as $key => $label)
                                            <option value="{{ $key }}" {{ $detalle->estado == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notas del Pedido -->
            @if($pedido->notas)
            <div class="card">
                <h3 class="font-semibold mb-2">Notas adicionales</h3>
                <p class="text-muted">{{ $pedido->notas }}</p>
            </div>
            @endif
        </div>

        <!-- Resumen y Estado -->
        <div class="space-y-6">
            <!-- Estado del Pedido -->
            <div class="card">
                <h3 class="font-semibold mb-3">Estado del Pedido</h3>
                
                <div class="mb-4">
                    <select id="estadoPedido" class="w-full px-4 py-2 rounded-lg border" 
                            {{ $pedido->estado == 'entregado' ? 'disabled' : '' }}>
                        @foreach($estados as $key => $label)
                            <option value="{{ $key }}" {{ $pedido->estado == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted">Estado actual:</span>
                        <span class="font-semibold px-2 py-1 rounded-full text-xs
                            {{ $pedido->estado == 'pendiente' ? 'bg-yellow-100 text-yellow-800' :
                               ($pedido->estado == 'en_preparacion' ? 'bg-blue-100 text-blue-800' :
                               ($pedido->estado == 'listo' ? 'bg-green-100 text-green-800' :
                               ($pedido->estado == 'entregado' ? 'bg-gray-100 text-gray-800' :
                               'bg-purple-100 text-purple-800'))) }}">
                            {{ $estados[$pedido->estado] }}
                        </span>
                    </div>
                    
                    @if($pedido->fecha_hora_entrega)
                    <div class="flex justify-between">
                        <span class="text-muted">Entregado:</span>
                        <span>{{ $pedido->fecha_hora_entrega->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información del Cliente/Mesa -->
            <div class="card">
                <h3 class="font-semibold mb-3">Información</h3>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-muted">Tipo:</span>
                        <span>{{ $tipos[$pedido->tipo_pedido] }}</span>
                    </div>
                    
                    @if($pedido->tipo_pedido == 'mesa')
                        <div class="flex justify-between">
                            <span class="text-muted">Mesa:</span>
                            <span class="font-semibold">{{ $pedido->mesa->numero_mesa ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Área:</span>
                            <span>{{ $pedido->mesa->area ?? 'N/A' }}</span>
                        </div>
                    @else
                        <div class="flex justify-between">
                            <span class="text-muted">Cliente:</span>
                            <span>{{ $pedido->cliente_nombre }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Teléfono:</span>
                            <span>{{ $pedido->cliente_telefono }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-muted">Atendido por:</span>
                        <span>{{ $pedido->usuario->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Totales -->
            <div class="card bg-gradient-to-r from-primary/5 to-primary/10">
                <h3 class="font-semibold mb-3">Resumen de Pago</h3>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>${{ number_format($pedido->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>IVA (13%):</span>
                        <span>${{ number_format($pedido->impuesto, 2) }}</span>
                    </div>
                    @if($pedido->descuento > 0)
                    <div class="flex justify-between text-red-600">
                        <span>Descuento:</span>
                        <span>-${{ number_format($pedido->descuento, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-lg pt-2 border-t">
                        <span>TOTAL:</span>
                        <span class="text-primary text-xl">${{ number_format($pedido->total, 2) }}</span>
                    </div>
                </div>
                
                @if($pedido->factura)
                <div class="mt-4">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-blue-800">Factura #{{ $pedido->factura->numero_factura }}</span>
                            <span class="px-2 py-1 rounded text-xs {{ $pedido->factura->estado == 'pagada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ strtoupper($pedido->factura->estado) }}
                            </span>
                        </div>
                        <div class="text-sm text-blue-700 mb-3">
                            Generada automáticamente para el cobro.
                        </div>
                        {{-- Opcional: Link a la factura si existe la vista --}}
                        {{-- <a href="{{ route('facturas.show', $pedido->factura) }}" class="btn-primary w-full justify-center">
                            <i class="fas fa-eye mr-2"></i> Ver Factura
                        </a> --}}
                    </div>
                </div>
                @elseif($pedido->estado != 'facturado' && $pedido->estado == 'entregado')
                <div class="mt-4">
                    <form action="{{ route('facturas.create') }}" method="GET">
                        <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                        <button type="submit" class="btn-primary w-full">
                            <i class="fas fa-file-invoice-dollar mr-2"></i> Generar Factura
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.card {
    @apply bg-white rounded-xl shadow-lg p-6;
}
.btn-primary {
    @apply bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition inline-flex items-center;
}
.btn-secondary {
    @apply bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition inline-flex items-center;
}
.btn-gray {
    @apply bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center;
}
.text-muted {
    @apply text-gray-500;
}
</style>

@push('scripts')
<script>
// Cambiar estado del pedido
const estadoSelect = document.getElementById('estadoPedido');
if(estadoSelect) {
    estadoSelect.addEventListener('change', function() {
        if(confirm('¿Cambiar estado del pedido a ' + this.options[this.selectedIndex].text + '?')) {
            fetch('{{ route("pedidos.cambiar-estado", $pedido) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ estado: this.value })
            }).then(response => response.json())
              .then(data => {
                  if(data.success) {
                      location.reload();
                  }
              });
        }
    });
}

// Cambiar estado de los detalles
document.querySelectorAll('.estado-detalle').forEach(select => {
    select.addEventListener('change', function() {
        const detalleId = this.dataset.id;
        const nuevoEstado = this.value;
        
        fetch(`/detalle-pedido/${detalleId}/cambiar-estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ estado: nuevoEstado })
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  // Recargar para actualizar estados
                  location.reload();
              }
          });
    });
});
</script>
@endpush
@endsection