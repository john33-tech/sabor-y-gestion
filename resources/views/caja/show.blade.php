@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-calculator text-primary"></i> Cierre de Caja #{{ $cierre->id }}
            </h1>
            <p class="text-sm text-gray-500">
                {{ $cierre->fecha_cierre->format('d/m/Y H:i') }} · Cajero: {{ $cierre->cajero?->name ?? '—' }}
            </p>
        </div>
        <a href="{{ route('caja.index') }}" class="text-sm text-gray-500 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-2">
        <i class="fas fa-check-circle mt-0.5"></i><span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Resumen del arqueo --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="md:col-span-2 bg-white rounded-lg shadow p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Totales por método de pago</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Efectivo</dt><dd class="font-medium">Bs {{ number_format($cierre->total_efectivo, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tarjeta</dt><dd class="font-medium">Bs {{ number_format($cierre->total_tarjeta, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">QR</dt><dd class="font-medium">Bs {{ number_format($cierre->total_qr, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Transferencia</dt><dd class="font-medium">Bs {{ number_format($cierre->total_transferencia, 2) }}</dd></div>
                <div class="flex justify-between pt-2 border-t border-gray-100"><dt class="font-semibold text-gray-700">Total general</dt><dd class="font-bold text-gray-900">Bs {{ number_format($cierre->total_general, 2) }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Arqueo de efectivo</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Registrado</dt><dd>Bs {{ number_format($cierre->total_efectivo, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Contado</dt><dd>Bs {{ number_format($cierre->efectivo_contado, 2) }}</dd></div>
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <dt class="font-semibold text-gray-700">Diferencia</dt>
                    <dd class="font-bold {{ (float)$cierre->diferencia == 0 ? 'text-gray-700' : ((float)$cierre->diferencia > 0 ? 'text-emerald-600' : 'text-red-600') }}">
                        Bs {{ number_format($cierre->diferencia, 2) }}
                    </dd>
                </div>
            </dl>
            @if($cierre->observaciones)
            <p class="text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100"><strong>Obs:</strong> {{ $cierre->observaciones }}</p>
            @endif
        </div>
    </div>

    {{-- Facturas incluidas --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Facturas contabilizadas ({{ $cierre->facturas->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Factura</th>
                        <th class="px-4 py-2 text-left">Mesa / Pedido</th>
                        <th class="px-4 py-2 text-left">Método</th>
                        <th class="px-4 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($cierre->facturas as $f)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium text-gray-700">{{ $f->numero_factura }}</td>
                        <td class="px-4 py-2 text-gray-600">
                            @if($f->pedido?->mesa) Mesa {{ $f->pedido->mesa->numero_mesa }} @else Pedido #{{ $f->pedido_id }} @endif
                        </td>
                        <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 capitalize">{{ $f->metodo_pago }}</span></td>
                        <td class="px-4 py-2 text-right font-semibold text-emerald-600">Bs {{ number_format($f->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
