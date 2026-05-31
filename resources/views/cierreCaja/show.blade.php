@extends('layouts.app') {{-- or your main layout --}}

@section('title', 'Detalle de Cierre de Caja')

@section('content')
<div class="py-6">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Cierre de Caja #{{ $cierre->id }}</h1>
            <div>
                <a href="{{ route('caja.index') }}" class="px-4 py-2 font-bold text-white bg-gray-500 rounded hover:bg-gray-700">
                    ← Volver al listado
                </a>
                @if($cierre->status === 'Open')
                    <a href="{{ route('caja.edit', $cierre) }}" class="px-4 py-2 ml-2 font-bold text-white rounded bg-primary hover:bg-orange-700">
                        Cerrar Caja
                    </a>
                @else
                    <a href="{{ route('caja.pdf', $cierre) }}" class="px-4 py-2 ml-2 font-bold text-white bg-red-600 rounded hover:bg-red-800">
                        Generar PDF
                    </a>
                @endif
            </div>
        </div>

        {{-- General info --}}
        <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
            <h2 class="mb-4 text-xl font-semibold">Información General</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div><span class="font-medium">ID:</span> {{ $cierre->id }}</div>
                <div><span class="font-medium">Usuario que abrió:</span> {{ $cierre->user->name }}</div>
                <div><span class="font-medium">Fecha apertura:</span> {{ $cierre->opening_date->format('d/m/Y H:i:s') }}</div>
                <div><span class="font-medium">Fecha cierre:</span> {{ $cierre->closing_date ? $cierre->closing_date->format('d/m/Y H:i:s') : '—' }}</div>
                <div><span class="font-medium">Estado:</span>
                    @if($cierre->status == 'Open')
                        <span class="inline-flex px-2 text-xs font-semibold leading-5 text-yellow-800 bg-yellow-100 rounded-full">Abierta</span>
                    @else
                        <span class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Cerrada</span>
                    @endif
                </div>
                <div><span class="font-medium">Observaciones:</span> {{ $cierre->observations ?? 'Ninguna' }}</div>
            </div>
        </div>

        {{-- Amounts --}}
        <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
            <h2 class="mb-4 text-xl font-semibold">Resumen de Movimientos</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div><span class="font-medium">Monto inicial:</span> S/ {{ number_format($cierre->initial_amount, 2) }}</div>
                <div><span class="font-medium">Total ventas:</span> S/ {{ number_format($cierre->total_sales ?? 0, 2) }}</div>
                @if($cierre->status === 'Closed')
                    <div><span class="font-medium">Total efectivo:</span> S/ {{ number_format($cierre->total_cash ?? 0, 2) }}</div>
                    <div><span class="font-medium">Total tarjeta:</span> S/ {{ number_format($cierre->total_card ?? 0, 2) }}</div>
                    <div><span class="font-medium">Total QR:</span> S/ {{ number_format($cierre->total_qr ?? 0, 2) }}</div>
                    <div><span class="font-medium">Monto final declarado:</span> S/ {{ number_format($cierre->final_amount, 2) }}</div>
                    <div><span class="font-medium">Diferencia:</span>
                        <span class="{{ $cierre->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            S/ {{ number_format($cierre->difference, 2) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        @if($cierre->status === 'Closed')
            {{-- Chart --}}
            <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                <h2 class="mb-4 text-xl font-semibold">Ventas por Hora</h2>
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>

            {{-- Orders list --}}
            <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Pedidos incluidos en este turno</h2>
                    <span class="text-sm text-gray-600">Total pedidos: {{ $orders->total() }} | Monto total: S/ {{ number_format($cierre->total_sales, 2) }}</span>
                </div>
                @if($orders->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">N° Pedido</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Mesa</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Método pago</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Fecha/Hora</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $order->numero_pedido ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $order->mesa->numero_mesa ?? 'Delivery/LLev' }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">S/ {{ number_format($order->total, 2) }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ ucfirst($order->factura->metodo_pago ?? 'N/D') }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <a href="{{ route('pedidos.show', $order) }}" class="text-primary hover:text-orange-700">Ver pedido</a>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $orders->appends(['orders_page' => $orders->currentPage()])->links() }}
                    </div>
                @else
                    <p class="text-gray-500">No se encontraron pedidos pagados en este período.</p>
                @endif
            </div>
        @else
            <div class="p-4 mb-6 border-l-4 border-yellow-400 bg-yellow-50">
                <p class="text-yellow-700">Este turno aún no ha sido cerrado. Para ver los pedidos asociados, primero debe cerrar la caja.</p>
            </div>
        @endif
    </div>
</div>

@if($cierre->status === 'Closed' && isset($chartData) && $chartData['labels']->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Ventas (S/)',
                    data: @json($chartData['values']),
                    backgroundColor: 'rgba(194, 65, 12, 0.6)',
                    borderColor: '#C2410C',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
</script>
@endif

@endsection
