<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIT/CI</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desc.</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pago</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($facturas as $factura)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                        <a href="{{ route('facturas.show', $factura) }}" class="hover:underline">
                            {{ $factura->numero_factura }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ $factura->cliente_nombre }}
                        @if($factura->cliente_telefono)
                            <div class="text-xs text-gray-400">{{ $factura->cliente_telefono }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $factura->cliente_nit ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        Bs. {{ number_format($factura->subtotal, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500">
                        -{{ number_format($factura->descuento, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                        Bs. {{ number_format($factura->total, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                        <span class="px-2 py-1 rounded text-xs {{ 
                            $factura->metodo_pago == 'efectivo' ? 'bg-gray-100 text-gray-800' : 
                            ($factura->metodo_pago == 'tarjeta' ? 'bg-blue-100 text-blue-800' : 
                            ($factura->metodo_pago == 'qr' ? 'bg-purple-100 text-purple-800' : 'bg-indigo-100 text-indigo-800')) 
                        }}">
                            {{ $factura->metodo_pago }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        @if($tipo === 'pendiente')
                            <button @click="openEdit({{ $factura }})" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="openPay({{ $factura }})" class="text-green-600 hover:text-green-900" title="Pagar">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                            <form action="{{ route('facturas.anular', $factura) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de anular esta factura?')">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Anular">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </form>
                        @elseif($tipo === 'pagada')
                             <a href="{{ route('facturas.show', $factura) }}" class="text-blue-600 hover:text-blue-900" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button @click="openSendMail({{ $factura }})" class="text-indigo-600 hover:text-indigo-900 ml-2" title="Enviar por Correo">
                                <i class="fas fa-envelope"></i>
                            </button>
                            <button class="text-gray-400 cursor-not-allowed ml-2" title="Pagada">
                                <i class="fas fa-check-double"></i>
                            </button>
                        @else
                            <span class="text-gray-400 italic">Anulada</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No hay facturas {{ $tipo }}s registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
