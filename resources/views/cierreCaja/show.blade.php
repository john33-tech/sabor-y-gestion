@extends('layouts.app')

@section('title', 'Detalle de Cierre de Caja')

@section('content')
<div class="min-h-screen py-8 bg-background">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-text">Cierre de Caja #{{ $cierre->id }}</h1>
                <p class="text-muted">Detalle administrativo del turno de caja</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('caja.index') }}" class="px-5 py-2.5 font-semibold text-text bg-white border border-border rounded-xl hover:bg-gray-50 transition">
                    ← Volver
                </a>
                @if($cierre->status === 'Open')
                    <a href="{{ route('caja.edit', $cierre) }}" class="px-5 py-2.5 font-bold text-white bg-primary rounded-xl shadow-lg shadow-orange-200 hover:bg-secondary transition">
                        Cerrar Caja
                    </a>
                @else
                    <a href="{{ route('caja.pdf', $cierre) }}" class="px-5 py-2.5 font-bold text-white bg-primary rounded-xl shadow-lg shadow-orange-200 hover:bg-secondary transition">
                        Descargar PDF
                    </a>
                @endif
            </div>
        </div>

        {{-- Bento Grid Layout --}}
        <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-3">
            {{-- General Info Card --}}
            <div class="col-span-1 p-6 border shadow-sm bg-surface rounded-2xl border-border/50">
                <h2 class="pb-2 mb-4 text-sm text-lg font-bold tracking-wider uppercase border-b text-text border-border">Datos del Turno</h2>
                <div class="space-y-4">
                    <div class="flex justify-between"><span class="text-muted">Usuario:</span> <span class="font-semibold">{{ $cierre->user->name }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Apertura:</span> <span class="text-sm font-medium">{{ $cierre->opening_date->format('d/m/y H:i') }}</span></div>
                    <div class="flex justify-between"><span class="text-muted">Cierre:</span> <span class="text-sm font-medium">{{ $cierre->closing_date ? $cierre->closing_date->format('d/m/y H:i') : '—' }}</span></div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted">Estado:</span>
                        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full {{ $cierre->status == 'Open' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                            {{ $cierre->status == 'Open' ? 'Abierta' : 'Cerrada' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Summary Financials --}}
            <div class="col-span-1 p-6 border shadow-sm bg-surface rounded-2xl border-border/50 lg:col-span-2">
                <h2 class="pb-2 mb-4 text-sm text-lg font-bold tracking-wider uppercase border-b text-text border-border">Resumen Financiero</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div class="p-4 border bg-background rounded-xl border-border">
                        <p class="text-xs font-bold uppercase text-muted">Inicial</p>
                        <p class="text-xl font-extrabold text-text">S/ {{ number_format($cierre->initial_amount, 2) }}</p>
                    </div>
                    <div class="p-4 border bg-background rounded-xl border-border">
                        <p class="text-xs font-bold uppercase text-muted">Ventas</p>
                        <p class="text-xl font-extrabold text-primary">S/ {{ number_format($cierre->total_sales ?? 0, 2) }}</p>
                    </div>
                    @if($cierre->status === 'Closed')
                    <div class="col-span-2 p-4 border bg-background rounded-xl border-border sm:col-span-1">
                        <p class="text-xs font-bold uppercase text-muted">Diferencia</p>
                        <p class="text-xl font-extrabold {{ $cierre->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            S/ {{ number_format($cierre->difference, 2) }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($cierre->status === 'Closed')
            <div class="p-6 mb-8 border shadow-sm bg-surface rounded-2xl border-border/50">
                <h2 class="mb-6 text-lg font-bold text-text">Tendencia de Ventas</h2>
                <canvas id="salesChart" class="max-h-[300px]"></canvas>
            </div>

            <div class="overflow-hidden border shadow-sm bg-surface rounded-2xl border-border/50">
                <div class="flex items-center justify-between px-6 py-5 border-b border-border/50">
                    <h2 class="font-bold text-text">Pedidos del Turno</h2>
                    <span class="px-3 py-1 text-xs font-bold rounded-lg bg-primary/10 text-primary">Total: {{ $orders->total() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-muted">Pedido</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-muted">Mesa</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-muted">Total</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-muted">Pago</th>
                                <th class="px-6 py-4 text-xs font-bold text-right uppercase text-muted">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($orders as $order)
                            <tr class="transition hover:bg-orange-50/30">
                                <td class="px-6 py-4 text-sm font-semibold text-text">#{{ $order->numero_pedido ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $order->mesa->numero_mesa ?? 'Delivery' }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-primary">S/ {{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4 text-sm uppercase text-muted">{{ $order->factura->metodo_pago ?? 'N/D' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('pedidos.show', $order) }}" class="font-bold text-primary hover:underline">Ver</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $orders->links() }}
                </div>
            </div>
        @else
            <div class="flex items-center gap-4 p-6 text-yellow-800 border border-yellow-200 rounded-2xl bg-yellow-50">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"/></svg>
                <p class="font-medium">Caja abierta. Los detalles de los pedidos estarán disponibles una vez se proceda con el cierre.</p>
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
                    backgroundColor: '#C2410C',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } }
            }
        });
    });
</script>
@endif
@endsection
