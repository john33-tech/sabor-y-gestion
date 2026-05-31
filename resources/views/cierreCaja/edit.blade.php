@extends('layouts.app')

@section('title', 'Cerrar Caja')

@section('content')
<div class="py-10">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">

                <h2 class="mb-6 text-2xl font-bold">Cerrar caja #{{ $cierre->id }}</h2>

                {{-- Resumen de apertura --}}
                <div class="p-4 mb-6 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <p class="text-sm">Apertura: <strong>{{ $cierre->opening_date->format('d/m/Y H:i') }}</strong></p>
                    <p class="text-sm">Usuario: <strong>{{ $cierre->user->name }}</strong></p>
                    <p class="text-sm">Monto Inicial: <strong>$ {{ number_format($cierre->initial_amount, 2) }}</strong></p>
                </div>

                {{-- Tabla de ventas calculadas automáticamente --}}
                <div class="mb-6">
                    <h3 class="mb-3 text-lg font-semibold">Resumen de ventas en el período</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left">Concepto</th>
                                    <th class="px-4 py-2 text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="px-4 py-2">Total Ventas</td>
                                    <td class="px-4 py-2 text-right">$ {{ number_format($totals['total_sales'], 2) }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2">Efectivo</td>
                                    <td class="px-4 py-2 text-right">$ {{ number_format($totals['total_cash'], 2) }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2">Tarjeta</td>
                                    <td class="px-4 py-2 text-right">$ {{ number_format($totals['total_card'], 2) }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="px-4 py-2">QR / Transferencia</td>
                                    <td class="px-4 py-2 text-right">$ {{ number_format($totals['total_qr'], 2) }}</td>
                                </tr>
                                <tr class="font-semibold bg-gray-50 dark:bg-gray-700">
                                    <td class="px-4 py-2">Monto esperado (inicial + ventas)</td>
                                    <td class="px-4 py-2 text-right">$ {{ number_format($cierre->initial_amount + $totals['total_sales'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Alerta si hay pedidos pendientes --}}
                @if($hasPendingOrders)
                    <div class="p-4 mb-4 text-red-800 bg-red-100 border-l-4 border-red-600 rounded-md dark:bg-red-900/30 dark:text-red-300">
                        ⚠️ No se puede cerrar la caja porque existen pedidos pendientes (no entregados, cancelados o facturados) dentro del período de esta caja. Por favor, resuelva esos pedidos antes de cerrar.
                    </div>
                @endif

                {{-- Formulario de cierre --}}
                <form method="POST" action="{{ route('caja.update', $cierre) }}" x-data="{
                    initialAmount: {{ $cierre->initial_amount }},
                    totalSales: {{ $totals['total_sales'] }},
                    finalAmount: 0,
                    get expected() { return this.initialAmount + this.totalSales; },
                    get difference() { return this.finalAmount - this.expected; }
                }" @submit.prevent="if({{ $hasPendingOrders ? 'true' : 'false' }}) { alert('No se puede cerrar porque hay pedidos pendientes.'); return false; } $el.submit()">
                    @csrf
                    @method('PUT')

                    <!-- Monto final contado -->
                    <div class="mb-4">
                        <label for="final_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto final en caja *</label>
                        <input type="number" name="final_amount" id="final_amount" step="0.01" min="0"
                               x-model="finalAmount"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('final_amount') border-red-500 @enderror"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Ingrese el monto total contado físicamente en caja.</p>
                        @error('final_amount')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Diferencia calculada en tiempo real -->
                    <div class="p-3 mb-4 rounded-lg" :class="difference >= 0 ? 'bg-green-50 dark:bg-green-900/30' : 'bg-red-50 dark:bg-red-900/30'">
                        <p class="text-sm font-medium">Diferencia calculada:</p>
                        <p class="text-xl font-bold" :class="difference >= 0 ? 'text-green-600' : 'text-red-600'">
                            $ <span x-text="difference.toFixed(2)"></span>
                        </p>
                        <p class="mt-1 text-xs text-gray-500">(Monto final - Monto esperado)</p>
                        <p class="text-xs text-gray-500">Monto esperado = Inicial + Total Ventas = $ <span x-text="expected.toFixed(2)"></span></p>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-4">
                        <label for="observations" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones de cierre</label>
                        <textarea name="observations" id="observations" rows="3"
                                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('observations', $cierre->observations) }}</textarea>
                        @error('observations')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('caja.show', $cierre) }}" class="inline-flex items-center px-4 py-2 font-semibold text-gray-700 transition duration-150 ease-in-out bg-gray-200 border border-transparent rounded-md dark:bg-gray-600 dark:text-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 font-semibold text-white transition duration-150 ease-in-out bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500" :disabled="{{ $hasPendingOrders ? 'true' : 'false' }}">
                            Confirmar Cierre de Caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush
