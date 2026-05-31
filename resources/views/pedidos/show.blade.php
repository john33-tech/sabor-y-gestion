@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->numero_pedido)

@section('content')
<div class="space-y-6">
    <!-- Header con botones de acción -->
    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold" style="color: #C2410C;">
                <i class="mr-2 fas fa-receipt"></i> Pedido #{{ $pedido->numero_pedido }}
            </h1>
            <p class="mt-1 text-gray-500">
                <i class="mr-1 far fa-calendar-alt"></i> Creado: {{ $pedido->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('pedidos.imprimir', $pedido) }}" target="_blank"
               class="inline-flex items-center px-4 py-2 text-white transition-colors duration-200 bg-orange-600 rounded-lg shadow-sm hover:bg-orange-700">
                <i class="mr-2 fas fa-print"></i> Imprimir Ticket
            </a>
            @if(in_array($pedido->estado, ['pendiente', 'en_preparacion']))
                <a href="{{ route('pedidos.edit', $pedido) }}"
                   class="inline-flex items-center px-4 py-2 text-white transition-colors duration-200 bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
                    <i class="mr-2 fas fa-edit"></i> Editar
                </a>
            @endif
            <a href="{{ route('pedidos.index') }}"
               class="inline-flex items-center px-4 py-2 text-white transition-colors duration-200 bg-gray-500 rounded-lg shadow-sm hover:bg-gray-600">
                <i class="mr-2 fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Información del Pedido -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Detalles del Pedido -->
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h2 class="text-xl font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-list-ul"></i> Detalle del Pedido
                    </h2>
                </div>

                <div class="p-6 overflow-x-auto">
                    @if(session('success'))
                        <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 border border-green-300 rounded">
                            <i class="mr-1 fas fa-check-circle"></i>{{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 border border-red-300 rounded">
                            <i class="mr-1 fas fa-exclamation-circle"></i>{{ session('error') }}
                        </div>
                    @endif
                    <table class="w-full">
                        <thead class="rounded-lg bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Plato</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Precio</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($pedido->detalles as $detalle)
                            <tr class="transition-colors duration-150 hover:bg-orange-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $detalle->plato->nombre }}</div>
                                    @if($detalle->notas)
                                        <div class="mt-1 text-xs text-gray-500">
                                            <i class="mr-1 fas fa-sticky-note"></i> Nota: {{ $detalle->notas }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 text-sm font-semibold bg-orange-100 rounded-full" style="color: #C2410C;">
                                        {{ $detalle->cantidad }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">
                                    Bs. {{ number_format($detalle->precio_unitario, 2) }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-right" style="color: #C2410C;">
                                    Bs. {{ number_format($detalle->subtotal, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <select class="px-2 py-1 text-xs bg-white border border-gray-300 rounded-lg estado-detalle focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                            data-id="{{ $detalle->id }}"
                                            {{ $pedido->estado == 'entregado' ? 'disabled' : '' }}>
                                        @foreach($estadosDetalle as $key => $label)
                                            @php
                                                $estadoColors = [
                                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                                    'en_preparacion' => 'bg-blue-100 text-blue-800',
                                                    'listo' => 'bg-green-100 text-green-800',
                                                    'entregado' => 'bg-gray-100 text-gray-800'
                                                ];
                                            @endphp
                                            <option value="{{ $key }}" {{ $detalle->estado == $key ? 'selected' : '' }} class="{{ $estadoColors[$key] ?? '' }}">
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- D4: Agregar productos solo mientras la cuenta NO esté pagada/cerrada -->
            @if($pedido->puedeAgregarProductos() && $platosDisponibles->isNotEmpty())
            <div x-data="agregarItems()" class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-white">
                    <h2 class="text-xl font-semibold text-emerald-700">
                        <i class="mr-2 fas fa-plus-circle"></i> Agregar productos
                    </h2>
                    <p class="mt-1 text-xs text-gray-500">
                        Suma productos a este pedido sin tocar lo que ya pidió el cliente. Si el plato ya estaba, se acumula la cantidad.
                    </p>
                </div>

                <form action="{{ route('pedidos.agregar-items', $pedido) }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="grid grid-cols-12 gap-2 p-3 border border-gray-200 rounded-lg bg-gray-50/50">
                            <div class="col-span-12 sm:col-span-6">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Producto</label>
                                <select :name="`items[${idx}][plato_id]`" x-model="item.plato_id" required
                                        class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">-- Selecciona --</option>
                                    @foreach($platosDisponibles as $categoria => $platos)
                                        <optgroup label="{{ $categoria ?? 'Sin categoría' }}">
                                            @foreach($platos as $plato)
                                                <option value="{{ $plato->id }}" data-precio="{{ $plato->precio }}">
                                                    {{ $plato->nombre }} — Bs {{ number_format($plato->precio, 2) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-6 sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Cantidad</label>
                                <input type="number" :name="`items[${idx}][cantidad]`" x-model.number="item.cantidad"
                                       min="1" required
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nota (opcional)</label>
                                <input type="text" :name="`items[${idx}][notas]`" x-model="item.notas"
                                       maxlength="500" placeholder="Ej: sin cebolla"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div class="flex items-end col-span-12 sm:col-span-1">
                                <button type="button" @click="removeRow(idx)" x-show="items.length > 1"
                                        class="w-full px-2 py-2 text-red-600 transition rounded-lg hover:bg-red-50">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <button type="button" @click="addRow()"
                                class="inline-flex items-center px-3 py-2 text-sm text-emerald-700 transition rounded-lg hover:bg-emerald-50">
                            <i class="mr-1 fas fa-plus"></i> Agregar otro producto
                        </button>
                        <button type="submit"
                                class="inline-flex items-center px-5 py-2 text-white transition bg-emerald-600 rounded-lg shadow hover:bg-emerald-700">
                            <i class="mr-2 fas fa-save"></i> Agregar al pedido
                        </button>
                    </div>
                </form>
            </div>
            <script>
                function agregarItems() {
                    return {
                        items: [{ plato_id: '', cantidad: 1, notas: '' }],
                        addRow() { this.items.push({ plato_id: '', cantidad: 1, notas: '' }); },
                        removeRow(idx) { this.items.splice(idx, 1); },
                    };
                }
            </script>
            @endif

            <!-- Dirección para delivery -->
            @if($pedido->tipo_pedido == 'delivery' && $pedido->latitud)
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="text-lg font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-map-marker-alt"></i> Ubicación del Cliente
                    </h3>
                </div>
                <div class="p-6">
                    <div id="mapShow" style="height: 350px;" class="border border-gray-200 rounded-lg"></div>
                </div>
            </div>
            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
            <script>
                const mapShow = L.map('mapShow').setView([{{ $pedido->latitud }}, {{ $pedido->longitud }}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(mapShow);
                L.marker([{{ $pedido->latitud }}, {{ $pedido->longitud }}])
                    .addTo(mapShow)
                    .bindPopup('Ubicación del cliente')
                    .openPopup();
            </script>
            @endif

            <!-- Notas del Pedido -->
            @if($pedido->notas)
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-sticky-note"></i> Notas adicionales
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600">{{ $pedido->notas }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Resumen y Estado -->
        <div class="space-y-6">
            <!-- Estado del Pedido -->
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-tag"></i> Estado del Pedido
                    </h3>
                </div>
                <div class="p-6">
                    {{-- Timeline visual del estado (#2): visible para todos los roles --}}
                    <div id="pedidoTimelineWrap" class="mb-5 pb-5 border-b border-gray-100">
                        <x-pedido-timeline :estado="$pedido->estado" />
                    </div>

                    {{-- Auto-refresh al recibir evento Reverb del cambio de estado --}}
                    <script>
                        window.addEventListener('pedido-estado-cambiado', (ev) => {
                            if (ev.detail && Number(ev.detail.pedido_id) === {{ (int) $pedido->id }}) {
                                setTimeout(() => window.location.reload(), 1800);
                            }
                        });
                    </script>

                    @auth
                        @if(auth()->user()->isCliente())
                            {{-- Cliente: cancelar (si pendiente) o confirmar recepción (si listo en delivery/para_llevar) --}}
                            @if($pedido->estado === 'pendiente')
                                <form action="{{ route('pedidos.cambiar-estado', $pedido) }}" method="POST"
                                      class="mb-4"
                                      onsubmit="return confirm('¿Seguro que deseas cancelar este pedido? Esta acción no se puede deshacer.');">
                                    @csrf
                                    <input type="hidden" name="estado" value="cancelado">
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-full px-4 py-2 text-white transition bg-red-600 rounded-lg shadow hover:bg-red-700">
                                        <i class="mr-2 fas fa-ban"></i> Cancelar pedido
                                    </button>
                                </form>
                            @elseif($pedido->estado === 'listo' && in_array($pedido->tipo_pedido, ['delivery', 'para_llevar']))
                                <form action="{{ route('pedidos.cambiar-estado', $pedido) }}" method="POST"
                                      class="mb-4"
                                      onsubmit="return confirm('¿Confirmas que recibiste tu pedido?');">
                                    @csrf
                                    <input type="hidden" name="estado" value="entregado">
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-full px-4 py-3 text-white transition bg-emerald-600 rounded-lg shadow hover:bg-emerald-700">
                                        <i class="mr-2 fas fa-hand-holding-heart"></i>
                                        He recibido mi pedido
                                    </button>
                                </form>
                            @else
                                <div class="px-4 py-3 mb-4 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-lg">
                                    <i class="mr-1 fas fa-info-circle"></i>
                                    @if($pedido->estado === 'en_preparacion')
                                        Tu pedido ya está en cocina, no se puede cancelar.
                                    @elseif($pedido->estado === 'entregado')
                                        Pedido entregado. ¡Gracias!
                                    @else
                                        El pedido se encuentra en estado "{{ str_replace('_', ' ', $pedido->estado) }}".
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="mb-4">
                                <select id="estadoPedido" class="w-full px-4 py-2 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        {{ $pedido->estado == 'entregado' ? 'disabled' : '' }}>
                                    @foreach($estados as $key => $label)
                                        <option value="{{ $key }}" {{ $pedido->estado == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endauth

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Estado actual:</span>
                            @php
                                $badgeClasses = [
                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                    'en_preparacion' => 'bg-blue-100 text-blue-800',
                                    'listo' => 'bg-green-100 text-green-800',
                                    'entregado' => 'bg-gray-100 text-gray-800'
                                ];
                                $badgeIcons = [
                                    'pendiente' => 'fa-hourglass-half',
                                    'en_preparacion' => 'fa-fire',
                                    'listo' => 'fa-check-circle',
                                    'entregado' => 'fa-check-double'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClasses[$pedido->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                <i class="fas {{ $badgeIcons[$pedido->estado] ?? 'fa-info-circle' }} mr-1"></i>
                                {{ $estados[$pedido->estado] }}
                            </span>
                        </div>

                        @if($pedido->fecha_hora_entrega)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Entregado:</span>
                            <span class="font-medium text-gray-700">{{ $pedido->fecha_hora_entrega->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pago de cuenta para el cliente (puntos #5 y #5.1 del spec) --}}
            @auth
                @if(auth()->user()->isCliente() && $pedido->factura && $pedido->factura->estado === 'pendiente')
                <div x-data="pagoCliente({{ $pedido->factura->id }}, '{{ addslashes(auth()->user()->email) }}')"
                     class="overflow-hidden bg-white shadow-lg rounded-xl">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-white">
                        <h3 class="font-semibold text-emerald-700">
                            <i class="mr-2 fas fa-credit-card"></i> Pagar pedido
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Total a pagar:</span>
                            <span class="text-2xl font-bold text-emerald-600">
                                Bs {{ number_format($pedido->factura->total, 2) }}
                            </span>
                        </div>

                        {{-- Botón pago QR --}}
                        <button type="button" @click="generarQr()" :disabled="qrLoading"
                                class="w-full inline-flex items-center justify-center px-4 py-2 text-white transition bg-purple-600 rounded-lg shadow hover:bg-purple-700 disabled:opacity-50">
                            <i class="mr-2 fas fa-qrcode"></i>
                            <span x-text="qrLoading ? 'Generando...' : 'Pagar con QR (simulación)'"></span>
                        </button>

                        {{-- Modal QR inline. z-index alto porque Leaflet usa z-index hasta 1000. --}}
                        <div x-show="qrModalOpen" x-cloak
                             class="fixed inset-0 flex items-center justify-center bg-black/70 p-4"
                             style="z-index: 9999;"
                             @keydown.escape.window="cerrarQr()">
                            <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center space-y-3">
                                <h4 class="text-lg font-semibold text-gray-800">Escanea o toca para pagar</h4>
                                <p class="text-xs text-gray-500">
                                    Factura <span class="font-mono" x-text="qrFacturaData.numero_factura"></span>
                                    · Bs <span x-text="qrFacturaData.total"></span>
                                </p>
                                <div class="flex justify-center" x-html="qrSvg" x-show="!qrPagado"></div>

                                {{-- Pagar en el MISMO dispositivo: si el QR está en tu teléfono no
                                     puedes escanearlo, así que abrimos la página de pago directo. --}}
                                <a :href="qrUrl" target="_blank" x-show="!qrPagado"
                                   class="w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                                    <i class="mr-2 fas fa-mobile-alt"></i> Pagar aquí (en este dispositivo)
                                </a>
                                <p class="text-[11px] text-gray-400" x-show="!qrPagado">
                                    ¿El QR está en tu teléfono? Toca el botón para pagar sin escanear.
                                </p>

                                @if(config('app.env') === 'local' || config('app.debug'))
                                    {{-- Solo en local/debug: el sistema externo de QR no puede llegar a
                                         localhost, así que ofrecemos un botón para disparar el webhook
                                         desde adentro y probar el flujo completo (pago + correo + Reverb). --}}
                                    <button type="button" @click="simularPago()" x-show="!qrPagado"
                                            class="w-full inline-flex items-center justify-center px-3 py-2 text-xs text-amber-800 bg-amber-50 border border-amber-300 rounded-lg hover:bg-amber-100 transition">
                                        <i class="mr-1 fas fa-flask"></i>
                                        Simular pago confirmado (solo entorno local)
                                    </button>
                                @endif

                                <div x-show="qrPagado" class="space-y-2 py-4">
                                    <div class="text-emerald-700 font-semibold text-xl">
                                        <i class="fas fa-check-circle"></i> ¡Pago confirmado!
                                    </div>
                                    <div class="text-sm text-gray-600" x-show="correoEnviado">
                                        <i class="fas fa-envelope text-emerald-600"></i>
                                        Factura enviada a tu correo.
                                    </div>
                                    <div class="text-sm text-red-600" x-show="qrPagado && !correoEnviado">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        El pago se registró pero el correo falló (revisa los logs).
                                    </div>
                                    <div class="text-xs text-gray-400">Recargando...</div>
                                </div>
                                <button type="button" @click="cerrarQr()"
                                        class="w-full px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                                    Cerrar
                                </button>
                            </div>
                        </div>

                        {{-- Nota: el envío de factura por correo es automático al confirmar el pago QR (spec #5.1) --}}
                        <div class="pt-3 border-t border-gray-100 text-xs text-gray-600">
                            <i class="fas fa-envelope-open-text text-emerald-600 mr-1"></i>
                            Al confirmar el pago te enviaremos la factura a
                            <span class="font-semibold">{{ auth()->user()->email }}</span>.
                        </div>
                    </div>
                </div>
                <script>
                    function pagoCliente(facturaId, defaultEmail) {
                        return {
                            facturaId,
                            qrLoading: false,
                            qrModalOpen: false,
                            qrSvg: '',
                            qrUrl: '',
                            qrEmisor: '',
                            qrFacturaData: {},
                            qrPagado: false,
                            correoEnviado: false,
                            echoChannel: null,
                            async generarQr() {
                                this.qrLoading = true;
                                try {
                                    const res = await fetch(`/facturas/${this.facturaId}/generar-qr`, {
                                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                    });
                                    if (!res.ok) throw new Error('Error generando QR');
                                    const data = await res.json();
                                    this.qrSvg = data.qr_svg;
                                    this.qrUrl = data.url;
                                    this.qrEmisor = data.emisor;
                                    this.qrFacturaData = data.factura;
                                    this.qrPagado = false;
                                    this.qrModalOpen = true;
                                    this.suscribirCanal(data.emisor);
                                } catch (err) {
                                    alert('No se pudo generar el QR. Intenta de nuevo.');
                                } finally {
                                    this.qrLoading = false;
                                }
                            },
                            suscribirCanal(emisor) {
                                if (!window.Echo) return;
                                if (this.echoChannel) window.Echo.leave('emisor-' + this.qrEmisor);
                                this.echoChannel = window.Echo.channel('emisor-' + emisor)
                                    .listen('.pago.confirmado', (e) => {
                                        this.qrPagado = true;
                                        this.correoEnviado = !!(e && e.correo_enviado);
                                        setTimeout(() => window.location.reload(), 3000);
                                    });
                            },
                            cerrarQr() {
                                if (this.echoChannel && this.qrEmisor) {
                                    window.Echo.leave('emisor-' + this.qrEmisor);
                                    this.echoChannel = null;
                                }
                                this.qrModalOpen = false;
                            },
                            async simularPago() {
                                try {
                                    const monto = parseFloat(String(this.qrFacturaData.total).replace(/,/g, ''));
                                    const res = await fetch('/api/confirmar-pago-qr', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                        },
                                        body: JSON.stringify({
                                            emisor: this.qrEmisor,
                                            pedido: this.qrFacturaData.pedido_id,
                                            monto: monto,
                                        }),
                                    });
                                    if (!res.ok) {
                                        const txt = await res.text();
                                        throw new Error(txt);
                                    }
                                    // La confirmación llega también vía Reverb; el listener
                                    // actualiza qrPagado y correoEnviado solo.
                                } catch (err) {
                                    alert('Error simulando pago: ' + err.message);
                                }
                            },
                        };
                    }
                </script>
                @endif
            @endauth

            <!-- Información del Cliente/Mesa -->
            <div class="overflow-hidden bg-white shadow-lg rounded-xl">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-white">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-info-circle"></i> Información
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Tipo:</span>
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
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $tipoClasses[$pedido->tipo_pedido] ?? 'bg-gray-100 text-gray-800' }}">
                                <i class="fas {{ $tipoIconos[$pedido->tipo_pedido] ?? 'fa-receipt' }} mr-1"></i>
                                {{ $tipos[$pedido->tipo_pedido] }}
                            </span>
                        </div>

                        @if($pedido->tipo_pedido == 'mesa')
                            <div class="flex justify-between">
                                <span class="text-gray-500">Mesa:</span>
                                <span class="font-semibold text-gray-800">{{ $pedido->mesa->numero_mesa ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Ubicación:</span>
                                <span class="text-gray-700">{{ $pedido->mesa->area ?? 'N/A' }}</span>
                            </div>
                        @else
                            <div class="flex justify-between">
                                <span class="text-gray-500">Cliente:</span>
                                <span class="font-medium text-gray-800">{{ $pedido->cliente_nombre }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Teléfono:</span>
                                <span class="text-gray-700">{{ $pedido->cliente_telefono }}</span>
                            </div>
                            @if($pedido->direccion)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Dirección:</span>
                                <span class="text-right text-gray-700">{{ $pedido->direccion }}</span>
                            </div>
                            @endif
                        @endif

                        @php
                            $creadorPedido = $pedido->usuario;
                            $pedidoEsAutopedido = $creadorPedido && method_exists($creadorPedido, 'isCliente') && $creadorPedido->isCliente();
                        @endphp
                        <div class="flex justify-between pt-2 border-t border-gray-100">
                            <span class="text-gray-500">{{ $pedidoEsAutopedido ? 'Pedido por:' : 'Atendido por:' }}</span>
                            <span class="font-medium text-gray-800">{{ $creadorPedido->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Badge de factura (cuando aplica) --}}
            @if($pedido->factura)
            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-blue-800">Factura #{{ $pedido->factura->numero_factura }}</span>
                    <span class="px-2 py-1 rounded text-xs {{ $pedido->factura->estado == 'pagada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ strtoupper($pedido->factura->estado) }}
                    </span>
                </div>
                <div class="text-sm text-blue-700">
                    Generada automáticamente para el cobro.
                </div>
                {{-- El PDF abre en el navegador (stream inline). El mesero puede
                     VERLO ("Ver PDF"); admin/cajero/cliente además lo descargan. --}}
                @if(in_array(auth()->user()->role, ['admin', 'cajero', 'cliente', 'mesero']))
                <a href="{{ route('facturas.pdf', $pedido->factura) }}" target="_blank"
                   class="inline-flex items-center mt-3 text-sm font-medium text-blue-700 hover:text-blue-900">
                    <i class="mr-1 fas fa-file-pdf"></i>
                    {{ auth()->user()->role === 'mesero' ? 'Ver PDF' : 'Ver / Descargar PDF' }}
                </a>
                @endif
            </div>
            @endif

            <div class="overflow-hidden shadow-lg bg-gradient-to-br from-orange-50 to-white rounded-xl">
                <div class="px-6 py-4 border-b border-orange-200 bg-gradient-to-r from-orange-100 to-orange-50">
                    <h3 class="font-semibold" style="color: #C2410C;">
                        <i class="mr-2 fas fa-calculator"></i> Resumen de Pago
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium text-gray-800">Bs. {{ number_format($pedido->subtotal, 2) }}</span>
                        </div>
                        @if($pedido->descuento > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Descuento:</span>
                            <span class="font-medium text-red-600">- Bs. {{ number_format($pedido->descuento, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between pt-3 mt-2 border-t border-orange-200">
                            <span class="text-lg font-bold text-gray-800">TOTAL:</span>
                            <span class="text-2xl font-bold" style="color: #C2410C;">Bs. {{ number_format($pedido->total, 2) }}</span>
                        </div>
                    </div>

                    {{-- La factura se genera automáticamente al crear el pedido.
                         El acceso al PDF está en el badge "Factura #..." de arriba. --}}
                </div>
            </div>
        </div>
    </div>
</div>

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

@push('scripts')
<script>
// Función para actualizar el contador del sidebar
function actualizarContadorSidebar() {
    fetch('/api/stock-count')
        .then(response => response.json())
        .then(data => {
            const contadorInventario = document.querySelector('a[href="{{ route("inventario.index") }}"] .rounded-full');
            if (contadorInventario) {
                if (data.count > 0) {
                    contadorInventario.textContent = data.count;
                    contadorInventario.classList.remove('hidden');
                } else {
                    contadorInventario.classList.add('hidden');
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Cambiar estado del pedido
const estadoSelect = document.getElementById('estadoPedido');
if(estadoSelect) {
    estadoSelect.addEventListener('change', function() {
        if(confirm('¿Cambiar estado del pedido a ' + this.options[this.selectedIndex].text + '?')) {
            fetch('{{ route("pedidos.cambiar-estado", $pedido) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ estado: this.value })
            }).then(response => response.json())
              .then(data => {
                  if(data.success) {
                      actualizarContadorSidebar();
                      location.reload();
                  } else {
                      alert('Error: ' + data.mensaje);
                  }
              });
        }
    });
}

// Cambiar estado de los detalles
document.querySelectorAll('.estado-detalle').forEach(select => {
    select.addEventListener('change', function() {
        const detalleId = this.dataset.id;
        const nuevoEstado = this.value;

        fetch(`/detalle-pedido/${detalleId}/cambiar-estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ estado: nuevoEstado })
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  actualizarContadorSidebar();
                  location.reload();
              } else {
                  alert('Error: ' + data.mensaje);
              }
          });
    });
});
</script>
@endpush
@endsection
