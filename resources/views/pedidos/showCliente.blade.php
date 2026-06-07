@extends('layouts.app')

@section('title', 'Detalle del Pedido')

@section('content')

<div class="container mx-auto px-4 py-8">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">

        <div>
            <h1 class="text-3xl font-bold" style="color:#C2410C;">
                <i class="fas fa-receipt mr-2"></i>
                Pedido {{ $pedido->numero_pedido }}
            </h1>

            <p class="text-gray-500 mt-1">
                {{ $pedido->created_at->format('d/m/Y H:i') }}
            </p>
        </div>

        <a href="{{ route('pedidos.misPedidos') }}"
           class="px-4 py-2 rounded-lg text-white"
           style="background-color:#78716C;">
            <i class="fas fa-arrow-left mr-1"></i>
            Volver
        </a>

    </div>

    {{-- INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-xl shadow p-4 border">
            <p class="text-sm text-gray-500 mb-1">Estado</p>

            <span class="px-3 py-1 rounded-full text-white text-sm"
                  style="background-color:
                    {{ $pedido->estado == 'pendiente' ? '#F59E0B' :
                       ($pedido->estado == 'en_preparacion' ? '#3B82F6' :
                       ($pedido->estado == 'listo' ? '#10B981' : '#6B7280')) }};">
                {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
            </span>
        </div>

        <div class="bg-white rounded-xl shadow p-4 border">
            <p class="text-sm text-gray-500 mb-1">Tipo Pedido</p>

            <p class="font-bold text-lg">
                {{ ucfirst(str_replace('_', ' ', $pedido->tipo_pedido)) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-4 border">
            <p class="text-sm text-gray-500 mb-1">Total</p>

            <p class="font-bold text-2xl" style="color:#C2410C;">
                Bs. {{ number_format($pedido->total, 2) }}
            </p>
        </div>

    </div>

    {{-- PAGO CON QR (cliente). Solo si su factura sigue pendiente. El pedido del
         cliente entra a la cocina recién DESPUÉS de pagar (regla "primero paga,
         luego se prepara"). El QR se escanea con otro celular. --}}
    @if(auth()->user()->isCliente() && $pedido->factura && $pedido->factura->estado === 'pendiente')
    <div x-data="pagoCliente({{ $pedido->factura->id }})"
         class="bg-white rounded-xl shadow border overflow-hidden mb-6">
        <div class="px-6 py-4 border-b" style="background-color:#ECFDF5;">
            <h2 class="text-xl font-bold text-emerald-700">
                <i class="fas fa-credit-card mr-2"></i> Pagar pedido
            </h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-gray-500">Total a pagar:</span>
                <span class="text-2xl font-bold text-emerald-600">Bs {{ number_format($pedido->factura->total, 2) }}</span>
            </div>
            <p class="text-sm text-gray-600 bg-amber-50 border border-amber-200 rounded-lg p-3">
                <i class="fas fa-info-circle mr-1 text-amber-600"></i>
                Tu pedido entra a la cocina <strong>después de pagar</strong>. Escanea el QR con la cámara o app bancaria de <strong>otro celular</strong>; la confirmación llega aquí sola.
            </p>

            <button type="button" @click="generarQr()" :disabled="qrLoading"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 text-white transition bg-purple-600 rounded-lg shadow hover:bg-purple-700 disabled:opacity-50">
                <i class="mr-2 fas fa-qrcode"></i>
                <span x-text="qrLoading ? 'Generando...' : 'Pagar con QR'"></span>
            </button>

            {{-- Modal QR. z-index alto porque Leaflet usa hasta ~1000. --}}
            <div x-show="qrModalOpen" x-cloak
                 class="fixed inset-0 flex items-center justify-center bg-black/70 p-4"
                 style="z-index: 9999;"
                 @keydown.escape.window="cerrarQr()">
                <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center space-y-3">
                    <h4 class="text-lg font-semibold text-gray-800">Escanea el QR para pagar</h4>
                    <p class="text-xs text-gray-500">
                        Factura <span class="font-mono" x-text="qrFacturaData.numero_factura"></span>
                        · Bs <span x-text="qrFacturaData.total"></span>
                    </p>
                    <div class="flex justify-center" x-html="qrSvg" x-show="!qrPagado"></div>
                    <p class="text-[11px] text-gray-400" x-show="!qrPagado">
                        Escanea con otro teléfono. La confirmación llegará aquí automáticamente.
                    </p>

                    @if(config('app.env') === 'local' || config('app.debug'))
                        <button type="button" @click="simularPago()" x-show="!qrPagado"
                                class="w-full inline-flex items-center justify-center px-3 py-2 text-xs text-amber-800 bg-amber-50 border border-amber-300 rounded-lg hover:bg-amber-100 transition">
                            <i class="mr-1 fas fa-flask"></i> Simular pago confirmado (solo entorno local)
                        </button>
                    @endif

                    <div x-show="qrPagado" class="space-y-2 py-4">
                        <div class="text-emerald-700 font-semibold text-xl">
                            <i class="fas fa-check-circle"></i> ¡Pago confirmado!
                        </div>
                        <div class="text-sm text-gray-600" x-show="correoEnviado">
                            <i class="fas fa-envelope text-emerald-600"></i> Factura enviada a tu correo.
                        </div>
                        <div class="text-xs text-gray-400">Recargando...</div>
                    </div>
                    <button type="button" @click="cerrarQr()"
                            class="w-full px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cerrar
                    </button>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 text-xs text-gray-600">
                <i class="fas fa-envelope-open-text text-emerald-600 mr-1"></i>
                Al confirmar el pago te enviaremos la factura a
                <span class="font-semibold">{{ auth()->user()->email }}</span>.
            </div>
        </div>
    </div>

    <script>
        function pagoCliente(facturaId) {
            return {
                facturaId,
                qrLoading: false, qrModalOpen: false,
                qrSvg: '', qrUrl: '', qrEmisor: '', qrFacturaData: {},
                qrPagado: false, correoEnviado: false, echoChannel: null,
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
                        if (!res.ok) { const txt = await res.text(); throw new Error(txt); }
                    } catch (err) {
                        alert('Error simulando pago: ' + err.message);
                    }
                },
            };
        }
    </script>
    @endif

    @if($pedido->tipo_pedido == 'delivery' && $pedido->latitud)
    <div class="bg-white rounded-xl shadow border overflow-hidden mb-6">
        <div class="px-6 py-4 border-b" style="background-color:#FFF7ED;">
            <h2 class="text-xl font-bold" style="color:#C2410C;">
                <i class="fas fa-map-marker-alt mr-2"></i>
                Ubicación de Entrega
            </h2>
        </div>
        <div class="p-6">
            <div id="mapShow" style="height: 350px;" class="border border-gray-200 rounded-lg z-0"></div>
        </div>
    </div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapShow = L.map('mapShow').setView([{{ $pedido->latitud }}, {{ $pedido->longitud }}], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(mapShow);
            L.marker([{{ $pedido->latitud }}, {{ $pedido->longitud }}])
                .addTo(mapShow)
                .bindPopup('Ubicación de entrega')
                .openPopup();
        });
    </script>
    @endif

    {{-- DETALLES --}}
    <div class="bg-white rounded-xl shadow border overflow-hidden">

        <div class="px-6 py-4 border-b"
             style="background-color:#FFF7ED;">

            <h2 class="text-xl font-bold"
                style="color:#C2410C;">

                <i class="fas fa-utensils mr-2"></i>
                Detalle del Pedido

            </h2>
        </div>

        <div class="p-6">

            <div class="space-y-4">

                @foreach($pedido->detalles as $detalle)

                    <div class="flex justify-between items-center border rounded-lg p-4">

                        <div>

                            <h3 class="font-bold text-lg">
                                {{ $detalle->plato->nombre }}
                            </h3>

                            <p class="text-sm text-gray-500">
                                Cantidad: {{ $detalle->cantidad }}
                            </p>

                            @if($detalle->notas)
                                <p class="text-sm mt-1">
                                    📝 {{ $detalle->notas }}
                                </p>
                            @endif

                        </div>

                        <div class="text-right">

                            <p class="font-bold text-lg"
                               style="color:#C2410C;">

                                Bs. {{ number_format($detalle->subtotal, 2) }}

                            </p>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    </div>

</div>

@endsection