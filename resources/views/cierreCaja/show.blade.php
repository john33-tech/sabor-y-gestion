@extends('layouts.app') {{-- or your main layout --}}

@section('title', 'Detalle de Cierre de Caja')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Cierre de Caja #{{ $cierre->id }}</h1>
            <div>
                <a href="{{ route('caja.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    ← Volver al listado
                </a>
                @if($cierre->status === 'Open')
                    <a href="{{ route('caja.edit', $cierre) }}" class="ml-2 bg-primary hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                        Cerrar Caja
                    </a>
                @else
                    <button id="generatePdfBtn" class="ml-2 bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-4 rounded">
                        Generar PDF
                    </button>
                @endif
            </div>
        </div>

        {{-- General info --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Información General</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium">ID:</span> {{ $cierre->id }}</div>
                <div><span class="font-medium">Usuario que abrió:</span> {{ $cierre->user->name }}</div>
                <div><span class="font-medium">Fecha apertura:</span> {{ $cierre->opening_date->format('d/m/Y H:i:s') }}</div>
                <div><span class="font-medium">Fecha cierre:</span> {{ $cierre->closing_date ? $cierre->closing_date->format('d/m/Y H:i:s') : '—' }}</div>
                <div><span class="font-medium">Estado:</span>
                    @if($cierre->status == 'Open')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Abierta</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Cerrada</span>
                    @endif
                </div>
                <div><span class="font-medium">Observaciones:</span> {{ $cierre->observations ?? 'Ninguna' }}</div>
            </div>
        </div>

        {{-- Amounts --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Resumen de Movimientos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Ventas por Hora</h2>
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>

            {{-- Orders list --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Pedidos incluidos en este turno</h2>
                    <span class="text-sm text-gray-600">Total pedidos: {{ $orders->total() }} | Monto total: S/ {{ number_format($cierre->total_sales, 2) }}</span>
                </div>
                @if($orders->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Pedido</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mesa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha/Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $order->numero_pedido ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $order->mesa->numero_mesa ?? 'Delivery/LLev' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">S/ {{ number_format($order->total, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst($order->factura->metodo_pago ?? 'N/D') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
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
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
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

<script>
    document.getElementById('generatePdfBtn')?.addEventListener('click', function() {
        alert('Próximamente: generación de PDF del reporte de cierre.');
    });
</script>
@endsection
