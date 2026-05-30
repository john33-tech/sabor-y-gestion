@extends('layouts.app')

@section('title', 'Cierre de Cuenta')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-3xl font-bold text-primary">
                <i class="fas fa-cash-register mr-2"></i>Cierre de Cuenta
            </h1>
            <p class="text-sm text-gray-500">
                Mesas con cuenta abierta. Selecciona una para ver el detalle consumido y cobrar.
            </p>
        </div>
    </div>

    {{-- Mensajes flash --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
            <div><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
            @if(session('facturas_cerradas') && count(session('facturas_cerradas')))
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach(session('facturas_cerradas') as $facturaId)
                        <a href="{{ route('facturas.pdf', $facturaId) }}" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 text-sm bg-white border border-green-300 text-green-800 rounded hover:bg-green-50 transition">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir factura {{ $loop->iteration }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded">
            <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($cuentas->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-mug-hot text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No hay cuentas abiertas en este momento.</p>
            <p class="text-gray-400 text-sm mt-1">Cuando una mesa tenga pedidos sin cobrar aparecerá aquí.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($cuentas as $cuenta)
                <a href="{{ route('cierres.show', $cuenta['mesa']->id) }}"
                   class="block bg-white rounded-lg shadow hover:shadow-lg transition border-l-4 border-amber-500 overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Mesa</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $cuenta['mesa']->numero_mesa }}</p>
                                <p class="text-xs text-gray-500">{{ $cuenta['mesa']->area ?? 'General' }}</p>
                            </div>
                            @if($cuenta['tiene_pendiente'])
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                <i class="fas fa-clock mr-1"></i>Por cobrar
                            </span>
                            @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                <i class="fas fa-check mr-1"></i>Pagado · cerrar
                            </span>
                            @endif
                        </div>

                        <div class="border-t border-gray-100 pt-3 space-y-1 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Pedidos:</span>
                                <span class="font-semibold">{{ $cuenta['cantidad_pedidos'] }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Abierta desde:</span>
                                <span>{{ \Carbon\Carbon::parse($cuenta['abierta_desde'])->diffForHumans(null, true) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500">Total a cobrar</p>
                            <p class="text-2xl font-bold text-emerald-600">Bs {{ number_format($cuenta['total'], 2) }}</p>
                        </div>

                        <div class="mt-3 text-right">
                            <span class="text-primary text-sm font-medium">
                                Ver detalle <i class="fas fa-arrow-right ml-1"></i>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

</div>
@endsection
