<x-app-layout>
    <x-slot name="title">Detalle de Factura {{ $factura->numero_factura }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    <div class="flex justify-between items-start mb-8 border-b pb-6">
                        <div>
                            <h2 class="text-3xl font-extrabold text-gray-900">FACTURA</h2>
                            <p class="text-blue-600 font-bold text-xl">{{ $factura->numero_factura }}</p>
                            <p class="text-sm text-gray-500 mt-1">Fecha de Emisión: {{ $factura->fecha_emision->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <h3 class="font-bold text-lg">SaborGestion</h3>
                            <p class="text-sm text-gray-600">NIT: 123456789</p>
                            <p class="text-sm text-gray-600">Santa Cruz, Bolivia</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h4 class="text-xs uppercase text-gray-500 font-bold mb-2">Facturado a:</h4>
                            <p class="font-bold text-gray-800">{{ $factura->cliente_nombre }}</p>
                            <p class="text-sm text-gray-600">NIT/CI: {{ $factura->cliente_nit ?? 'S/N' }}</p>
                            <p class="text-sm text-gray-600">Tel: {{ $factura->cliente_telefono ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <h4 class="text-xs uppercase text-gray-500 font-bold mb-2">Estado:</h4>
                            <span class="px-3 py-1 rounded-full text-sm font-bold {{ 
                                $factura->estado == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 
                                ($factura->estado == 'pagada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') 
                            }}">
                                {{ strtoupper($factura->estado) }}
                            </span>
                            <div class="mt-4">
                                <h4 class="text-xs uppercase text-gray-500 font-bold mb-1">Método de Pago:</h4>
                                <p class="text-sm font-medium capitalize">{{ $factura->metodo_pago }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Cant.</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">P. Unit</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($factura->pedido->detalles as $detalle)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800">{{ $detalle->plato->nombre }}</td>
                                        <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $detalle->cantidad }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-600">Bs. {{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium">Bs. {{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end">
                        <div class="w-64">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Subtotal:</span>
                                <span class="text-sm font-medium">Bs. {{ number_format($factura->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Descuento:</span>
                                <span class="text-sm font-medium text-red-600">- Bs. {{ number_format($factura->descuento, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-4">
                                <span class="text-lg font-bold">TOTAL:</span>
                                <span class="text-xl font-black text-blue-700">Bs. {{ number_format($factura->total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-between items-center no-print">
                        <a href="{{ route('facturas.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
                        </a>
                        <div class="space-x-4">
                            <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                                <i class="fas fa-print mr-2"></i> Imprimir Factura
                            </button>
                            @if($factura->estado === 'pendiente')
                                <form action="{{ route('facturas.pagar', $factura) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                                        <i class="fas fa-money-bill-wave mr-2"></i> Pagar Ahora
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .py-12 { padding-top: 0 !important; padding-bottom: 0 !important; }
            .shadow-sm { shadow: none !important; }
            .max-w-4xl { max-width: 100% !important; width: 100% !important; }
        }
    </style>
</x-app-layout>
