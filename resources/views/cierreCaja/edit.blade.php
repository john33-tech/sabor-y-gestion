@extends('layouts.app')

@section('title', 'Cerrar Caja')

@section('content')
<div class="min-h-screen py-8 bg-background">
    <div class="max-w-4xl px-4 mx-auto sm:px-6">

        {{-- Header Section --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-text">Cierre de Caja #{{ $cierre->id }}</h1>
            <p class="text-muted">Proceso de arqueo y conciliación final</p>
        </div>

        {{-- Main Card --}}
        <div class="overflow-hidden border shadow-sm bg-surface border-border rounded-2xl">

            {{-- Resumen de datos clave --}}
            <div class="grid grid-cols-1 divide-y md:grid-cols-3 md:divide-y-0 md:divide-x divide-border bg-orange-50/30">
                <div class="p-5">
                    <p class="text-[10px] font-bold tracking-widest uppercase text-muted">Fecha Apertura</p>
                    <p class="mt-1 text-sm font-semibold text-text">{{ $cierre->opening_date->format('d/m/Y H:i') }}</p>
                </div>
                <div class="p-5">
                    <p class="text-[10px] font-bold tracking-widest uppercase text-muted">Usuario</p>
                    <p class="mt-1 text-sm font-semibold text-text">{{ $cierre->user->name }}</p>
                </div>
                <div class="p-5">
                    <p class="text-[10px] font-bold tracking-widest uppercase text-muted">Monto Inicial</p>
                    <p class="mt-1 text-sm font-bold text-primary">$ {{ number_format($cierre->initial_amount, 2) }}</p>
                </div>
            </div>

            <div class="p-6 md:p-8">
                {{-- Tabla de Ventas (Diseño simplificado) --}}
                <div class="mb-8">
                    <h3 class="mb-4 text-sm font-bold tracking-wider uppercase text-text">Desglose de Ventas</h3>
                    <div class="overflow-hidden border border-border rounded-xl">
                        <table class="w-full text-sm">
                            <thead class="bg-background text-muted uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="px-4 py-3 text-left">Concepto</th>
                                    <th class="px-4 py-3 text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach(['Total Ventas' => $totals['total_sales'], 'Efectivo' => $totals['total_cash'], 'Tarjeta' => $totals['total_card'], 'QR/Transferencia' => $totals['total_qr']] as $label => $val)
                                <tr class="transition-colors hover:bg-orange-50/50">
                                    <td class="px-4 py-3 font-medium text-text">{{ $label }}</td>
                                    <td class="px-4 py-3 font-mono text-right text-text">$ {{ number_format($val, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr class="bg-primary/5">
                                    <td class="px-4 py-3 font-bold text-primary">Total Esperado</td>
                                    <td class="px-4 py-3 font-bold text-right text-primary">$ {{ number_format($cierre->initial_amount + $totals['total_sales'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($hasPendingOrders)
                    <div class="flex gap-3 p-4 mb-6 text-sm text-red-800 border border-red-100 rounded-lg bg-red-50">
                        <svg class="flex-shrink-0 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-4a1 1 0 100 2 1 1 0 000-2z"/></svg>
                        <p>Existen pedidos pendientes. Por favor, resuélvalos antes de continuar.</p>
                    </div>
                @endif

                {{-- Formulario --}}
                <form method="POST" action="{{ route('caja.update', $cierre) }}"
                      x-data="{
                        finalAmount: 0,
                        submitting: false,
                        expected: {{ $cierre->initial_amount + $totals['total_sales'] }}
                      }"
                      @submit.prevent="submitting = true; $el.submit()">
                    @csrf @method('PUT')

                    <div class="mb-6">
                        <label class="block mb-2 text-xs font-bold uppercase text-text">Monto Final Contado</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-muted">$</span>
                            <input type="number" name="final_amount" step="0.01" required
                                   x-model="finalAmount"
                                   class="w-full pl-8 pr-4 py-2.5 bg-background border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        </div>
                    </div>

                    {{-- Feedback de diferencia --}}
                    <div class="flex items-center justify-between p-4 mb-6 border rounded-xl"
                         :class="(finalAmount - expected) === 0 ? 'bg-gray-100 border-gray-200' : 'bg-primary/5 border-primary/20'">
                        <span class="text-sm font-bold text-text">Diferencia:</span>
                        <span class="font-mono text-xl font-black"
                              :class="(finalAmount - expected) >= 0 ? 'text-emerald-600' : 'text-red-600'"
                              x-text="(finalAmount - expected).toFixed(2)"></span>
                    </div>

                    <div class="mb-6">
                        <label class="block mb-2 text-xs font-bold uppercase text-text">Observaciones</label>
                        <textarea name="observations" rows="2" class="w-full p-3 rounded-lg outline-none bg-background border-border focus:ring-2 focus:ring-primary">{{ old('observations', $cierre->observations) }}</textarea>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('caja.show', $cierre) }}" class="flex-1 py-2.5 text-center font-bold text-muted bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</a>
                        <button type="submit"
                                :disabled="submitting || {{ $hasPendingOrders ? 'true' : 'false' }}"
                                class="flex-[2] py-2.5 font-bold text-white bg-primary rounded-lg hover:bg-primary/90 disabled:opacity-50 transition-all">
                            <span x-text="submitting ? 'Procesando...' : 'Confirmar Cierre de Caja'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
