@extends('layouts.app')

@section('content')

{{-- Alerta de stock bajo: el admin la ve al entrar y va directo a reponer --}}
@if(($stockBajo ?? 0) > 0)
<a href="{{ route('inventario.index') }}"
   class="flex items-center justify-between gap-3 p-4 mt-6 bg-amber-50 border border-amber-300 rounded-xl hover:bg-amber-100 transition">
    <div class="flex items-center gap-3 text-amber-800">
        <i class="fas fa-exclamation-triangle text-2xl"></i>
        <div>
            <p class="font-semibold">Inventario: {{ $stockBajo }} ingrediente(s) con stock bajo</p>
            <p class="text-sm">Toca aquí para revisar y reponer el inventario.</p>
        </div>
    </div>
    <i class="fas fa-arrow-right text-amber-600"></i>
</a>
@endif

<!-- Widget de Consumos Recientes -->
<div class="card bg-white rounded-xl shadow-lg overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-primary" style="color: #C2410C;">
                <i class="fas fa-receipt mr-2"></i> Consumos Recientes
            </h2>
            <a href="{{ route('reportes.consumos') }}" class="text-sm font-medium text-primary hover:text-secondary transition-colors duration-200" style="color: #C2410C;">
                Ver todos <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <p class="text-sm text-gray-500 mt-1">Últimos 5 pedidos finalizados</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        N° Pedido
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $recientes = App\Models\Consumo::with('usuario')->latest('fecha_consumo')->limit(5)->get();
                @endphp
                @forelse($recientes as $consumo)
                <tr class="hover:bg-orange-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center">
                                <i class="fas fa-receipt text-xs" style="color: #C2410C;"></i>
                            </div>
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-900">{{ $consumo->numero_pedido }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
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
                            $tipoTextos = [
                                'mesa' => 'Mesa',
                                'delivery' => 'Delivery',
                                'para_llevar' => 'Para Llevar'
                            ];
                            $tipoClase = $tipoClasses[$consumo->tipo_pedido] ?? 'bg-gray-100 text-gray-800';
                            $tipoIcono = $tipoIconos[$consumo->tipo_pedido] ?? 'fa-receipt';
                            $tipoTexto = $tipoTextos[$consumo->tipo_pedido] ?? $consumo->tipo_pedido;
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tipoClase }}">
                            <i class="fas {{ $tipoIcono }} mr-1 text-xs"></i>
                            {{ $tipoTexto }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                            {{ $consumo->fecha_consumo->format('d/m/Y') }}
                            <i class="far fa-clock ml-2 mr-1 text-gray-400"></i>
                            {{ $consumo->fecha_consumo->format('H:i') }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="text-sm font-bold" style="color: #C2410C;">
                            Bs. {{ number_format($consumo->total, 2) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                <i class="fas fa-receipt text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No hay consumos registrados</p>
                            <p class="text-sm text-gray-400 mt-1">Los pedidos finalizados aparecerán aquí</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($recientes->count() > 0)
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
        <div class="flex justify-between items-center">
            <div class="text-xs text-gray-500">
                <i class="fas fa-chart-line mr-1"></i>
                Total recaudado: <span class="font-semibold" style="color: #C2410C;">Bs. {{ number_format($recientes->sum('total'), 2) }}</span>
            </div>
            <div class="text-xs text-gray-500">
                <i class="fas fa-utensils mr-1"></i>
                Total platos: {{ $recientes->sum(fn($c) => collect($c->detalles)->sum('cantidad')) }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

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