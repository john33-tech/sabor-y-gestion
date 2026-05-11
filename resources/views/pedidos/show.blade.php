@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->numero_pedido)

@section('content')
<div class="space-y-6">
    <!-- Header con botones de acción -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold" style="color: #C2410C;">
                <i class="fas fa-receipt mr-2"></i> Pedido #{{ $pedido->numero_pedido }}
            </h1>
            <p class="text-gray-500 mt-1">
                <i class="far fa-calendar-alt mr-1"></i> Creado: {{ $pedido->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('pedidos.imprimir', $pedido) }}" target="_blank" 
               class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 shadow-sm">
                <i class="fas fa-print mr-2"></i> Imprimir Ticket
            </a>
            @if(in_array($pedido->estado, ['pendiente', 'en_preparacion']))
                <a href="{{ route('pedidos.edit', $pedido) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-sm">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            @endif
            <a href="{{ route('pedidos.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información del Pedido -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Detalles del Pedido -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                    <h2 class="text-xl font-semibold" style="color: #C2410C;">
                        <i class="fas fa-list-ul mr-2"></i> Detalle del Pedido
                    </h2>
                </div>
                
                <div class="overflow-x-auto p-6">
                    <table class="w-full">
                        <thead class="bg-gray-50 rounded-lg">
                            <tr>
                                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Plato</th>
                                <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($pedido->detalles as $detalle)
                            <tr class="hover:bg-orange-50 transition-colors duration-150">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-800">{{ $detalle->plato->nombre }}</div>
                                    @if($detalle->notas)
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-sticky-note mr-1"></i> Nota: {{ $detalle->notas }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-orange-100 rounded-full text-sm font-semibold" style="color: #C2410C;">
                                        {{ $detalle->cantidad }}
                                    </span>
                                </td>
                                <td class="text-right py-3 px-4 text-gray-600">
                                    Bs. {{ number_format($detalle->precio_unitario, 2) }}
                                </td>
                                <td class="text-right py-3 px-4 font-semibold" style="color: #C2410C;">
                                    Bs. {{ number_format($detalle->subtotal, 2) }}
                                </td>
                                <td class="text-center py-3 px-4">
                                    <select class="estado-detalle text-xs px-2 py-1 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white"
                                            data-id="{{ $detalle->id }}"
                                            {{ $pedido->estado == 'entregado' ? 'disabled' : '' }}>
                                        @foreach($estadosDetalle as $key => $label)
                                            @php
                                                $estadoColors = [
                                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                                    'en_preparacion' => 'bg-blue-100 text-blue-800',
                                                    'listo' => 'bg-green-100 text-green-800',
                                                    'entregado' => 'bg-gray-100 text-gray-800'
                                                ];
                                            @endphp
                                            <option value="{{ $key }}" {{ $detalle->estado == $key ? 'selected' : '' }} class="{{ $estadoColors[$key] ?? '' }}">
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

            <!-- Dirección para delivery -->
            @if($pedido->tipo_pedido == 'delivery' && $pedido->latitud)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold" style="color: #C2410C;">
                        <i class="fas fa-map-marker-alt mr-2"></i> Ubicación del Cliente
                    </h3>
                </div>
                <div class="p-6">
                    <div id="mapShow" style="height: 350px;" class="rounded-lg border border-gray-200"></div>
                </div>
            </div>
            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
            <script>
                const mapShow = L.map('mapShow').setView([{{ $pedido->latitud }}, {{ $pedido->longitud }}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(mapShow);
                L.marker([{{ $pedido->latitud }}, {{ $pedido->longitud }}])
                    .addTo(mapShow)
                    .bindPopup('Ubicación del cliente')
                    .openPopup();
            </script>
            @endif

            <!-- Notas del Pedido -->
            @if($pedido->notas)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="fas fa-sticky-note mr-2"></i> Notas adicionales
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600">{{ $pedido->notas }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Resumen y Estado -->
        <div class="space-y-6">
            <!-- Estado del Pedido -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="fas fa-tag mr-2"></i> Estado del Pedido
                    </h3>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <select id="estadoPedido" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                                {{ $pedido->estado == 'entregado' ? 'disabled' : '' }}>
                            @foreach($estados as $key => $label)
                                <option value="{{ $key }}" {{ $pedido->estado == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Estado actual:</span>
                            @php
                                $badgeClasses = [
                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                    'en_preparacion' => 'bg-blue-100 text-blue-800',
                                    'listo' => 'bg-green-100 text-green-800',
                                    'entregado' => 'bg-gray-100 text-gray-800'
                                ];
                                $badgeIcons = [
                                    'pendiente' => 'fa-hourglass-half',
                                    'en_preparacion' => 'fa-fire',
                                    'listo' => 'fa-check-circle',
                                    'entregado' => 'fa-check-double'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClasses[$pedido->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                <i class="fas {{ $badgeIcons[$pedido->estado] ?? 'fa-info-circle' }} mr-1"></i>
                                {{ $estados[$pedido->estado] }}
                            </span>
                        </div>
                        
                        @if($pedido->fecha_hora_entrega)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Entregado:</span>
                            <span class="font-medium text-gray-700">{{ $pedido->fecha_hora_entrega->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información del Cliente/Mesa -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="fas fa-info-circle mr-2"></i> Información
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Tipo:</span>
                            @php
                                $tipoClasses = [
                                    'mesa' => 'bg-blue-100 text-blue-800',
                                    'delivery' => 'bg-green-100 text-green-800',
                                    'para_llevar' => 'bg-yellow-100 text-yellow-800'
                                ];
                                $tipoIconos = [
                                    'mesa' => 'fa-chair',
                                    'delivery' => 'fa-motorcycle',
                                    'para_llevar' => 'fa-box'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $tipoClasses[$pedido->tipo_pedido] ?? 'bg-gray-100 text-gray-800' }}">
                                <i class="fas {{ $tipoIconos[$pedido->tipo_pedido] ?? 'fa-receipt' }} mr-1"></i>
                                {{ $tipos[$pedido->tipo_pedido] }}
                            </span>
                        </div>
                        
                        @if($pedido->tipo_pedido == 'mesa')
                            <div class="flex justify-between">
                                <span class="text-gray-500">Mesa:</span>
                                <span class="font-semibold text-gray-800">{{ $pedido->mesa->numero_mesa ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Área:</span>
                                <span class="text-gray-700">{{ $pedido->mesa->area ?? 'N/A' }}</span>
                            </div>
                        @else
                            <div class="flex justify-between">
                                <span class="text-gray-500">Cliente:</span>
                                <span class="font-medium text-gray-800">{{ $pedido->cliente_nombre }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Teléfono:</span>
                                <span class="text-gray-700">{{ $pedido->cliente_telefono }}</span>
                            </div>
                            @if($pedido->direccion)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Dirección:</span>
                                <span class="text-gray-700 text-right">{{ $pedido->direccion }}</span>
                            </div>
                            @endif
                        @endif
                        
                        <div class="flex justify-between pt-2 border-t border-gray-100">
                            <span class="text-gray-500">Atendido por:</span>
                            <span class="font-medium text-gray-800">{{ $pedido->usuario->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Totales -->
            <div class="bg-gradient-to-br from-orange-50 to-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-100 to-orange-50 border-b border-orange-200">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="fas fa-calculator mr-2"></i> Resumen de Pago
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium text-gray-800">Bs. {{ number_format($pedido->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">IVA (13%):</span>
                            <span class="font-medium text-gray-800">Bs. {{ number_format($pedido->impuesto, 2) }}</span>
                        </div>
                        @if($pedido->descuento > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Descuento:</span>
                            <span class="font-medium text-red-600">- Bs. {{ number_format($pedido->descuento, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between pt-3 mt-2 border-t border-orange-200">
                            <span class="text-lg font-bold text-gray-800">TOTAL:</span>
                            <span class="text-2xl font-bold" style="color: #C2410C;">Bs. {{ number_format($pedido->total, 2) }}</span>
                        </div>
                    </div>
                    
                    @if($pedido->estado != 'facturado' && $pedido->estado == 'entregado')
                    <div class="mt-6">
                        <form action="{{ route('facturas.create') }}" method="GET">
                            <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                            <button type="submit" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors duration-200 flex items-center justify-center gap-2">
                                <i class="fas fa-file-invoice-dollar"></i> Generar Factura
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    }
</style>
@endpush

@push('scripts')
<script>
// Función para actualizar el contador del sidebar
function actualizarContadorSidebar() {
    fetch('/api/stock-count')
        .then(response => response.json())
        .then(data => {
            const contadorInventario = document.querySelector('a[href="{{ route("inventario.index") }}"] .rounded-full');
            if (contadorInventario) {
                if (data.count > 0) {
                    contadorInventario.textContent = data.count;
                    contadorInventario.classList.remove('hidden');
                } else {
                    contadorInventario.classList.add('hidden');
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

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
                      actualizarContadorSidebar();
                      location.reload();
                  } else {
                      alert('Error: ' + data.mensaje);
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
                  actualizarContadorSidebar();
                  location.reload();
              } else {
                  alert('Error: ' + data.mensaje);
              }
          });
    });
});
</script>
@endpush
@endsection