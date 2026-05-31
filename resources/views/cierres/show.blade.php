@extends('layouts.app')

@section('title', 'Cuenta Mesa ' . $mesa->numero_mesa)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-3xl font-bold text-primary">
                <i class="fas fa-receipt mr-2"></i>Cuenta de la Mesa {{ $mesa->numero_mesa }}
            </h1>
            <p class="text-sm text-gray-500">
                {{ $mesa->area ?? 'General' }} · Capacidad {{ $mesa->capacidad }}
            </p>
        </div>
        <a href="{{ route('cierres.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-1"></i>Volver a cuentas abiertas
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Detalle de pedidos consolidados --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-800">
                <i class="fas fa-clipboard-list mr-2"></i>Comanda consolidada
                <span class="text-sm text-gray-500 font-normal">({{ $pedidos->count() }} {{ $pedidos->count() === 1 ? 'pedido' : 'pedidos' }})</span>
            </h2>
        </div>

        @foreach($pedidos as $pedido)
            <div class="border-b border-gray-100 last:border-b-0">
                <div class="px-6 py-3 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-gray-700">{{ $pedido->numero_pedido ?? '—' }}</span>
                        <span class="text-gray-500">
                            <i class="far fa-clock mr-1"></i>{{ $pedido->created_at->format('H:i') }}
                        </span>
                        <span class="text-gray-500">
                            <i class="fas fa-user-tie mr-1"></i>{{ $pedido->usuario->name ?? '—' }}
                        </span>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        @if($pedido->estado === 'pendiente') bg-yellow-100 text-yellow-800
                        @elseif($pedido->estado === 'en_preparacion') bg-blue-100 text-blue-800
                        @elseif($pedido->estado === 'listo') bg-purple-100 text-purple-800
                        @elseif($pedido->estado === 'entregado') bg-emerald-100 text-emerald-800
                        @endif">
                        {{ \App\Models\Pedido::getEstados()[$pedido->estado] ?? $pedido->estado }}
                    </span>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wider bg-white border-b">
                            <th class="px-6 py-2">Producto</th>
                            <th class="px-6 py-2 text-center w-20">Cant.</th>
                            <th class="px-6 py-2 text-right w-28">P. Unit.</th>
                            <th class="px-6 py-2 text-right w-28">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->detalles as $detalle)
                            <tr class="border-b border-gray-50 last:border-b-0">
                                <td class="px-6 py-2">
                                    <div class="font-medium text-gray-800">{{ $detalle->plato->nombre ?? '—' }}</div>
                                    @if($detalle->notas)
                                        <div class="text-xs text-gray-500 italic">{{ $detalle->notas }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-2 text-center">{{ $detalle->cantidad }}</td>
                                <td class="px-6 py-2 text-right">Bs {{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="px-6 py-2 text-right font-medium">Bs {{ number_format($detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($pedido->notas)
                    <div class="px-6 py-2 text-xs text-gray-600 bg-amber-50 border-t border-amber-100">
                        <i class="fas fa-sticky-note mr-1 text-amber-600"></i>
                        <span class="font-semibold">Nota del pedido:</span> {{ $pedido->notas }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Resumen y cobro --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6"
             x-data="cierreQrFlow(@js($facturaParaQr?->id), '{{ number_format($resumen['total'], 2) }}')">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-credit-card mr-2"></i>Cobrar y cerrar cuenta
            </h3>

            <form id="formCerrarCuenta" action="{{ route('cierres.cerrar', $mesa->id) }}" method="POST" class="space-y-4"
                  @submit.prevent="onSubmit($event)">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Método de pago <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach([
                            'efectivo' => ['fa-money-bill-wave', 'Efectivo'],
                            'tarjeta' => ['fa-credit-card', 'Tarjeta'],
                            'qr' => ['fa-qrcode', 'QR'],
                            'transferencia' => ['fa-exchange-alt', 'Transferencia'],
                        ] as $valor => $datos)
                            <label class="cursor-pointer">
                                <input type="radio" name="metodo_pago" value="{{ $valor }}" class="peer sr-only" x-model="metodo" {{ $loop->first ? 'checked' : '' }} required>
                                <div class="border-2 border-gray-200 rounded-lg p-3 text-center hover:border-primary peer-checked:border-primary peer-checked:bg-primary/5 transition">
                                    <i class="fas {{ $datos[0] }} text-xl text-gray-600 mb-1"></i>
                                    <div class="text-sm font-medium">{{ $datos[1] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('metodo_pago')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nombre y apellido del cliente para la factura --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre y apellido del cliente <span class="text-red-500">*</span></label>
                    <input type="text" name="cliente_nombre" required maxlength="255"
                           value="{{ old('cliente_nombre') }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary"
                           placeholder="Ej: Juan Pérez">
                    @error('cliente_nombre')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CI/NIT obligatorio: el cajero debe pedirlo al cobrar --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CI / NIT del cliente <span class="text-red-500">*</span></label>
                    <input type="text" name="cliente_nit" required maxlength="20"
                           value="{{ old('cliente_nit') }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary"
                           placeholder="Ej: 1234567 (o S/N si no tiene)">
                    @error('cliente_nit')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Correo opcional: si el cliente quiere su factura por email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Correo del cliente <span class="text-gray-400 font-normal">(opcional — para enviarle la factura)</span>
                    </label>
                    <input type="email" name="cliente_email"
                           value="{{ old('cliente_email') }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary"
                           placeholder="cliente@correo.com">
                    @error('cliente_email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold transition shadow-md">
                    <i :class="metodo === 'qr' ? 'fas fa-qrcode mr-2' : 'fas fa-check-circle mr-2'"></i>
                    <span x-text="metodo === 'qr' ? 'Generar QR y cobrar Bs {{ number_format($resumen['total'], 2) }}' : 'Cerrar cuenta y cobrar Bs {{ number_format($resumen['total'], 2) }}'"></span>
                </button>
            </form>

            {{-- Modal QR para cobro --}}
            <div x-show="qrModalOpen" x-cloak
                 class="fixed inset-0 flex items-center justify-center bg-black/70 p-4"
                 style="z-index: 9999;"
                 @keydown.escape.window="cerrarQr()">
                <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center space-y-3">
                    <h4 class="text-lg font-semibold text-gray-800">Escanea para pagar</h4>
                    <p class="text-xs text-gray-500">
                        Mesa {{ $mesa->numero_mesa }} · Bs {{ number_format($resumen['total'], 2) }}
                    </p>
                    <div x-show="qrLoading" class="py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl"></i>
                        <div class="text-sm mt-2">Generando QR...</div>
                    </div>
                    <div x-show="!qrLoading && !qrPagado" class="flex justify-center" x-html="qrSvg"></div>

                    {{-- Fallback staff: si la app externa de QR no responde, este botón
                         dispara el webhook desde adentro (mismo efecto: factura pagada +
                         correo + Reverb). Solo accesible para admin/cajero/mesero por
                         el middleware de /cierres. --}}
                    <button type="button" @click="simularPago()" x-show="!qrPagado && !qrLoading"
                            class="w-full inline-flex items-center justify-center px-3 py-2 text-xs text-amber-800 bg-amber-50 border border-amber-300 rounded-lg hover:bg-amber-100 transition">
                        <i class="mr-1 fas fa-check-circle"></i>
                        Confirmar pago manualmente (si el cliente ya pagó)
                    </button>

                    <div x-show="qrPagado" class="space-y-2 py-4">
                        <div class="text-emerald-700 font-semibold text-xl">
                            <i class="fas fa-check-circle"></i> ¡Pago confirmado!
                        </div>
                        <div class="text-xs text-gray-400">Cerrando cuenta...</div>
                    </div>

                    <button type="button" @click="cerrarQr()" x-show="!qrPagado"
                            class="w-full px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <script>
            function cierreQrFlow(facturaId, totalLabel) {
                return {
                    metodo: 'efectivo',
                    facturaId: facturaId,
                    qrModalOpen: false,
                    qrLoading: false,
                    qrSvg: '',
                    qrEmisor: '',
                    qrPedidoId: null,
                    qrPagado: false,
                    echoChannel: null,

                    onSubmit(ev) {
                        if (this.metodo !== 'qr') {
                            if (!confirm('¿Confirmas cerrar la cuenta por Bs ' + totalLabel + '?')) return;
                            ev.target.submit();
                            return;
                        }
                        if (!this.facturaId) {
                            alert('No hay factura para generar el QR.');
                            return;
                        }
                        this.abrirQr();
                    },

                    async abrirQr() {
                        this.qrModalOpen = true;
                        this.qrLoading = true;
                        this.qrPagado = false;
                        try {
                            const res = await fetch(`/facturas/${this.facturaId}/generar-qr`, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            if (!res.ok) throw new Error('Error generando QR');
                            const data = await res.json();
                            this.qrSvg = data.qr_svg;
                            this.qrEmisor = data.emisor;
                            this.qrPedidoId = data.factura?.pedido_id || null;
                            this.suscribirCanal(data.emisor);
                        } catch (err) {
                            alert('No se pudo generar el QR: ' + err.message);
                            this.qrModalOpen = false;
                        } finally {
                            this.qrLoading = false;
                        }
                    },

                    suscribirCanal(emisor) {
                        if (!window.Echo) return;
                        if (this.echoChannel) window.Echo.leave('emisor-' + this.qrEmisor);
                        this.echoChannel = window.Echo.channel('emisor-' + emisor)
                            .listen('.pago.confirmado', () => {
                                this.onPagoConfirmado();
                            });
                    },

                    onPagoConfirmado() {
                        this.qrPagado = true;
                        // Disparar el cierre de cuenta (cerrar formalmente: pedidos→facturado, mesa libre, emails).
                        setTimeout(() => document.getElementById('formCerrarCuenta').submit(), 1500);
                    },

                    async simularPago() {
                        try {
                            const res = await fetch('/api/confirmar-pago-qr', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    emisor: this.qrEmisor,
                                    pedido: this.qrPedidoId,
                                    monto: parseFloat(totalLabel.replace(/,/g, '')),
                                }),
                            });
                            if (!res.ok) throw new Error(await res.text());
                            // La confirmación llega por Reverb (suscribirCanal) y dispara onPagoConfirmado().
                        } catch (err) {
                            alert('Error simulando pago: ' + err.message);
                        }
                    },

                    cerrarQr() {
                        if (this.echoChannel && this.qrEmisor) {
                            window.Echo.leave('emisor-' + this.qrEmisor);
                            this.echoChannel = null;
                        }
                        this.qrModalOpen = false;
                    },
                };
            }
        </script>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-calculator mr-2"></i>Resumen
            </h3>

            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Ítems consumidos:</dt>
                    <dd class="font-semibold">{{ $resumen['items'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Subtotal:</dt>
                    <dd>Bs {{ number_format($resumen['subtotal'], 2) }}</dd>
                </div>
                @if($resumen['descuento'] > 0)
                    <div class="flex justify-between text-red-600">
                        <dt>Descuento:</dt>
                        <dd>− Bs {{ number_format($resumen['descuento'], 2) }}</dd>
                    </div>
                @endif
                <div class="border-t border-gray-200 pt-2 mt-2 flex justify-between text-lg font-bold text-gray-900">
                    <dt>Total:</dt>
                    <dd class="text-emerald-600">Bs {{ number_format($resumen['total'], 2) }}</dd>
                </div>
            </dl>
        </div>
    </div>

</div>
@endsection
