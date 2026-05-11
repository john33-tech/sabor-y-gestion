@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->numero_pedido)

@section('content')
<div class="space-y-6">
    <!-- Header con botones de acción -->
    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold" style="color: #C2410C;">
                <i class="mr-2 fas fa-receipt"></i> Pedido #{{ $pedido->numero_pedido }}
            </h1>
            <p class="mt-1 text-gray-500">
                <i class="mr-1 far fa-calendar-alt"></i> Creado: {{ $pedido->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('pedidos.imprimir', $pedido) }}" target="_blank"
               class="inline-flex items-center px-4 py-2 text-white transition-colors duration-200 bg-orange-600 rounded-lg shadow-sm hover:bg-orange-700">
                <i class="mr-2 fas fa-print"></i> Imprimir Ticket
            </a>
            @if(in_array($pedido->estado, ['pendiente', 'en_preparacion']))
                <a href="{{ route('pedidos.edit', $pedido) }}"
                   class="inline-flex items-center px-4 py-2 text-white transition-colors duration-200 bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
                    <i class="mr-2 fas fa-edit"></i> Editar
                </a>
            @endif
            <a href="{{ route('pedidos.index') }}"
               class="inline-flex items-center px-4 py-2 text-white transition-colors duration-200 bg-gray-500 rounded-lg shadow-sm hover:bg-gray-600">
                <i class="mr-2 fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Información del Pedido -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Detalles del Pedido -->
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h2 class="text-xl font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-list-ul"></i> Detalle del Pedido
                    </h2>
                </div>

                <div class="p-6 overflow-x-auto">
                    <table class="w-full">
                        <thead class="rounded-lg bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Plato</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Precio</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($pedido->detalles as $detalle)
                            <tr class="transition-colors duration-150 hover:bg-orange-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $detalle->plato->nombre }}</div>
                                    @if($detalle->notas)
                                        <div class="mt-1 text-xs text-gray-500">
                                            <i class="mr-1 fas fa-sticky-note"></i> Nota: {{ $detalle->notas }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 text-sm font-semibold bg-orange-100 rounded-full" style="color: #C2410C;">
                                        {{ $detalle->cantidad }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">
                                    Bs. {{ number_format($detalle->precio_unitario, 2) }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-right" style="color: #C2410C;">
                                    Bs. {{ number_format($detalle->subtotal, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <select class="px-2 py-1 text-xs bg-white border border-gray-300 rounded-lg estado-detalle focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
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
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="text-lg font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-map-marker-alt"></i> Ubicación del Cliente
                    </h3>
                </div>
                <div class="p-6">
                    <div id="mapShow" style="height: 350px;" class="border border-gray-200 rounded-lg"></div>
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
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-sticky-note"></i> Notas adicionales
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
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-tag"></i> Estado del Pedido
                    </h3>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <select id="estadoPedido" class="w-full px-4 py-2 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                {{ $pedido->estado == 'entregado' ? 'disabled' : '' }}>
                            @foreach($estados as $key => $label)
                                <option value="{{ $key }}" {{ $pedido->estado == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
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
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-info-circle"></i> Información
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
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
                                <span class="text-right text-gray-700">{{ $pedido->direccion }}</span>
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
            <div class="card bg-gradient-to-r from-primary/5 to-primary/10">
                <h3 class="mb-3 font-semibold">Resumen de Pago</h3>

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
                    <div class="flex justify-between pt-2 text-lg font-bold border-t">
                        <span>TOTAL:</span>
                        <span class="text-xl text-primary">${{ number_format($pedido->total, 2) }}</span>
                    </div>
                </div>

                @if($pedido->factura)
                <div class="mt-4">
                    <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-blue-800">Factura #{{ $pedido->factura->numero_factura }}</span>
                            <span class="px-2 py-1 rounded text-xs {{ $pedido->factura->estado == 'pagada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ strtoupper($pedido->factura->estado) }}
                            </span>
                        </div>
                        <div class="mb-3 text-sm text-blue-700">
                            Generada automáticamente para el cobro.
                        </div>
                        {{-- Opcional: Link a la factura si existe la vista --}}
                        {{-- <a href="{{ route('facturas.show', $pedido->factura) }}" class="justify-center w-full btn-primary">
                            <i class="mr-2 fas fa-eye"></i> Ver Factura
                        </a> --}}
                    </div>
                </div>
                @elseif($pedido->estado != 'facturado' && $pedido->estado == 'entregado')
                <div class="mt-4">
                    <form action="{{ route('facturas.create') }}" method="GET">
                        <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                        <button type="submit" class="w-full btn-primary">
                            <i class="mr-2 fas fa-file-invoice-dollar"></i> Generar Factura
                        </button>
                    </form>
                </div>
                @endif
            <div class="overflow-hidden shadow-lg bg-gradient-to-br from-orange-50 to-white rounded-xl">
                <div class="px-6 py-4 border-b border-orange-200 bg-gradient-to-r from-orange-100 to-orange-50">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-calculator"></i> Resumen de Pago
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
                            <button type="submit" class="flex items-center justify-center w-full gap-2 px-4 py-2 text-white transition-colors duration-200 bg-orange-600 rounded-lg hover:bg-orange-700">
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
