@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-calculator text-primary"></i> Cierre de Caja
        </h1>
        <p class="text-sm text-gray-500">Arqueo del turno: detalle de lo cobrado, cuadre de efectivo e historial de cierres.</p>
    </div>

    {{-- Mensajes --}}
    @if(session('success'))
    <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-2">
        <i class="fas fa-check-circle mt-0.5"></i><span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('info'))
    <div class="mb-4 p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 flex items-start gap-2">
        <i class="fas fa-info-circle mt-0.5"></i><span>{{ session('info') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 flex items-start gap-2">
        <i class="fas fa-exclamation-circle mt-0.5"></i><span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Resumen del turno: totales por método --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        @php
            $tarjetas = [
                ['Efectivo', $totales['efectivo'], 'fa-money-bill-wave', 'emerald'],
                ['Tarjeta', $totales['tarjeta'], 'fa-credit-card', 'indigo'],
                ['QR', $totales['qr'], 'fa-qrcode', 'violet'],
                ['Transferencia', $totales['transferencia'], 'fa-exchange-alt', 'sky'],
            ];
        @endphp
        @foreach($tarjetas as [$label, $monto, $icono, $color])
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-{{ $color }}-500">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</p>
                <i class="fas {{ $icono }} text-{{ $color }}-500"></i>
            </div>
            <p class="text-xl font-bold text-gray-800 mt-1">Bs {{ number_format($monto, 2) }}</p>
        </div>
        @endforeach
        <div class="bg-primary text-white rounded-xl shadow p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase text-white/80">Total turno</p>
                <i class="fas fa-coins text-white/80"></i>
            </div>
            <p class="text-xl font-bold mt-1">Bs {{ number_format($totales['general'], 2) }}</p>
            <p class="text-[11px] text-white/80 mt-1">{{ $facturas->count() }} venta(s)</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Detalle del consumo del turno --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800"><i class="fas fa-list mr-1 text-gray-400"></i> Detalle del turno</h2>
            </div>
            @if($facturas->isEmpty())
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-receipt text-3xl mb-2"></i>
                    <p>No hay ventas pendientes de arqueo en este turno.</p>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Factura</th>
                            <th class="px-4 py-2 text-left">Mesa / Pedido</th>
                            <th class="px-4 py-2 text-left">Hora</th>
                            <th class="px-4 py-2 text-left">Método</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($facturas as $f)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-700">{{ $f->numero_factura }}</td>
                            <td class="px-4 py-2 text-gray-600">
                                @if($f->pedido?->mesa)
                                    Mesa {{ $f->pedido->mesa->numero_mesa }}
                                @else
                                    Pedido #{{ $f->pedido_id }}
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-500">{{ optional($f->fecha_emision)->format('d/m H:i') }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 capitalize">{{ $f->metodo_pago }}</span>
                            </td>
                            <td class="px-4 py-2 text-right font-semibold text-emerald-600">Bs {{ number_format($f->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Arqueo --}}
        <div class="bg-white rounded-lg shadow p-5 h-fit"
             x-data="{ contado: '', registrado: {{ $totales['efectivo'] }},
                       get diferencia() { return (parseFloat(this.contado || 0) - this.registrado).toFixed(2); } }">
            <h2 class="font-semibold text-gray-800 mb-3"><i class="fas fa-cash-register mr-1 text-gray-400"></i> Arqueo</h2>

            @if($facturas->isEmpty())
                <p class="text-sm text-gray-400">No hay nada que arquear todavía.</p>
            @else
            <dl class="space-y-1 text-sm mb-4">
                <div class="flex justify-between"><dt class="text-gray-500">Efectivo registrado</dt><dd class="font-medium">Bs {{ number_format($totales['efectivo'], 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total general</dt><dd class="font-bold">Bs {{ number_format($totales['general'], 2) }}</dd></div>
            </dl>

            <form action="{{ route('caja.cerrar') }}" method="POST" onsubmit="return confirm('¿Cerrar la caja del turno? Esta acción registra el arqueo.');">
                @csrf
                <label class="block text-sm font-medium text-gray-600 mb-1">Efectivo contado (Bs)</label>
                <input type="number" step="0.01" min="0" name="efectivo_contado" x-model="contado" required
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary mb-1" placeholder="0.00">

                <div class="text-sm mb-3 flex justify-between" x-show="contado !== ''">
                    <span class="text-gray-500">Diferencia</span>
                    <span class="font-semibold"
                          :class="parseFloat(diferencia) === 0 ? 'text-gray-700' : (parseFloat(diferencia) > 0 ? 'text-emerald-600' : 'text-red-600')">
                        Bs <span x-text="diferencia"></span>
                    </span>
                </div>

                <label class="block text-sm font-medium text-gray-600 mb-1">Observaciones (opcional)</label>
                <textarea name="observaciones" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary mb-3" placeholder="Notas del cierre..."></textarea>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition">
                    <i class="fas fa-lock mr-1"></i> Cerrar caja
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Historial de cierres --}}
    <div class="bg-white rounded-lg shadow mt-6 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800"><i class="fas fa-history mr-1 text-gray-400"></i> Historial de cierres</h2>
        </div>
        @if($historial->isEmpty())
            <div class="p-6 text-center text-gray-400">Aún no hay cierres registrados.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Cajero</th>
                        <th class="px-4 py-2 text-right">Facturas</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-right">Diferencia</th>
                        <th class="px-4 py-2 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($historial as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-700">{{ $c->fecha_cierre->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $c->cajero?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right text-gray-600">{{ $c->cantidad_facturas }}</td>
                        <td class="px-4 py-2 text-right font-semibold text-gray-800">Bs {{ number_format($c->total_general, 2) }}</td>
                        <td class="px-4 py-2 text-right font-medium {{ (float)$c->diferencia == 0 ? 'text-gray-600' : ((float)$c->diferencia > 0 ? 'text-emerald-600' : 'text-red-600') }}">
                            Bs {{ number_format($c->diferencia, 2) }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('caja.show', $c) }}" class="text-primary hover:underline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $historial->links() }}</div>
        @endif
    </div>
</div>
@endsection
