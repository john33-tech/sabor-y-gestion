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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

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

    {{-- SEGUIMIENTO EN VIVO del pedido (estilo app de delivery). Se actualiza
         solo en tiempo real al cambiar el estado (evento global de app.js). --}}
    <div x-data="seguimientoPedido({ estadoInicial: '{{ $pedido->estado }}', pedidoId: {{ $pedido->id }}, tipo: '{{ $pedido->tipo_pedido }}', lat: {{ $pedido->latitud ?? 'null' }}, lng: {{ $pedido->longitud ?? 'null' }} })"
         class="bg-white rounded-xl shadow border overflow-hidden mb-6">
        <div class="px-6 py-4 border-b" style="background-color:#FFF7ED;">
            <h2 class="text-xl font-bold" style="color:#C2410C;">
                <i class="fas fa-location-arrow mr-2"></i> Seguimiento de tu pedido
            </h2>
        </div>
        <div class="p-6">
            {{-- Estados terminales (cancelado / facturado) --}}
            <template x-if="esTerminalRaro">
                <div class="flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium"
                     :class="estado === 'cancelado' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-indigo-50 border border-indigo-200 text-indigo-700'">
                    <i class="fas" :class="estado === 'cancelado' ? 'fa-ban' : 'fa-file-invoice-dollar'"></i>
                    <span x-text="labelActual()"></span>
                </div>
            </template>

            {{-- Flujo normal: pill de estado + stepper --}}
            <template x-if="!esTerminalRaro">
                <div>
                    <div class="flex justify-center mb-3">
                        <span class="px-4 py-1.5 rounded-full text-white text-sm font-semibold inline-flex items-center gap-2"
                              :style="`background-color:${colorActual()}`">
                            <span class="w-2 h-2 rounded-full bg-white/80 animate-pulse"></span>
                            <span x-text="labelActual()"></span>
                        </span>
                    </div>

                    {{-- Entrega estimada (solo delivery con coordenadas, hasta que se entrega) --}}
                    <template x-if="tieneCoords && estado !== 'entregado'">
                        <p class="text-center text-sm text-gray-600 mb-5">
                            <i class="fas fa-motorcycle mr-1" style="color:#C2410C"></i>
                            Entrega estimada: <strong x-text="'~' + etaMin() + ' min'"></strong>
                            <span class="text-gray-400" x-text="'· ' + distKm().toFixed(1) + ' km'"></span>
                        </p>
                    </template>

                    <div class="flex items-start">
                        <template x-for="(paso, i) in pasos" :key="paso.clave">
                            <div class="flex-1 flex flex-col items-center relative">
                                <template x-if="i < pasos.length - 1">
                                    <div class="absolute left-1/2 w-full h-1 transition-colors" style="top:18px;"
                                         :class="hecho(i) ? 'bg-emerald-500' : 'bg-gray-200'"></div>
                                </template>
                                <span class="relative z-10 flex items-center justify-center w-10 h-10 rounded-full ring-4 ring-white transition-colors"
                                      :class="claseCirculo(i)">
                                    <i class="fas text-sm" :class="hecho(i) ? 'fa-check' : paso.icon"></i>
                                </span>
                                <span class="mt-2 text-[11px] sm:text-xs text-center font-medium transition-colors"
                                      :class="claseLabel(i)" x-text="paso.label"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Botón para que el cliente confirme la recepción (solo cuando está "En camino"/Listo) --}}
                    <template x-if="puedeConfirmar">
                        <button type="button" @click="confirmarRecepcion()" :disabled="confirmando"
                                class="mt-6 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-white font-semibold rounded-lg transition hover:opacity-90 disabled:opacity-50"
                                style="background-color:#10B981;">
                            <i class="fas fa-circle-check"></i>
                            <span x-text="confirmando ? 'Confirmando...' : 'Confirmar que recibí mi pedido'"></span>
                        </button>
                    </template>

                    <p class="mt-6 text-center text-xs text-gray-400">
                        <i class="fas fa-circle-notch fa-spin mr-1"></i>
                        Esta pantalla se actualiza sola cuando tu pedido avanza.
                    </p>
                </div>
            </template>
        </div>
    </div>

    <script>
        function seguimientoPedido(cfg) {
            const ESTADOS = ['pendiente', 'en_preparacion', 'listo', 'entregado'];
            return {
                estado: cfg.estadoInicial,
                pedidoId: cfg.pedidoId,
                tipo: cfg.tipo,
                lat: cfg.lat,
                lng: cfg.lng,
                kmRuta: null,   // distancia por calle (OSRM); llega por evento del mapa
                minRuta: null,  // tiempo por calle (OSRM)
                pasos: [
                    { clave: 'pendiente',      icon: 'fa-receipt',      label: 'Recibido' },
                    { clave: 'en_preparacion', icon: 'fa-fire',         label: 'En cocina' },
                    { clave: 'listo',          icon: cfg.tipo === 'delivery' ? 'fa-motorcycle' : 'fa-bell',
                                               label: cfg.tipo === 'delivery' ? 'En camino' : (cfg.tipo === 'para_llevar' ? 'Para retirar' : 'Listo') },
                    { clave: 'entregado',      icon: 'fa-check-double', label: 'Entregado' },
                ],
                init() {
                    // app.js (canal cliente.{id}.pedidos) re-emite este evento global.
                    window.addEventListener('pedido-estado-cambiado', (e) => {
                        if (e.detail && String(e.detail.pedido_id) === String(this.pedidoId) && e.detail.estado) {
                            this.estado = e.detail.estado;
                        }
                    });
                    // El mapa comparte su distancia/tiempo POR CALLE para que la barra
                    // muestre lo mismo (no la línea recta).
                    window.addEventListener('ruta-delivery-calculada', (e) => {
                        if (e.detail) { this.kmRuta = e.detail.km; this.minRuta = e.detail.min; }
                    });
                },
                get idx() { return ESTADOS.indexOf(this.estado); },
                get esFinal() { return this.estado === 'entregado'; },
                get esTerminalRaro() { return this.estado === 'cancelado' || this.estado === 'facturado'; },
                hecho(i) { return this.idx >= 0 && (i < this.idx || this.esFinal); },
                actual(i) { return i === this.idx && !this.esFinal; },
                claseCirculo(i) {
                    if (this.hecho(i)) return 'bg-emerald-500 text-white';
                    if (this.actual(i)) return 'bg-amber-500 text-white animate-pulse';
                    return 'bg-gray-200 text-gray-400';
                },
                claseLabel(i) {
                    if (this.hecho(i)) return 'text-emerald-700';
                    if (this.actual(i)) return 'text-amber-700';
                    return 'text-gray-400';
                },
                labelActual() {
                    const m = {
                        pendiente: 'Pedido recibido',
                        en_preparacion: 'En preparación',
                        listo: this.tipo === 'delivery' ? 'En camino' : 'Listo para retirar',
                        entregado: '¡Entregado!',
                        cancelado: 'Pedido cancelado',
                        facturado: 'Pedido facturado',
                    };
                    return m[this.estado] || this.estado;
                },
                colorActual() {
                    const m = {
                        pendiente: '#F59E0B', en_preparacion: '#3B82F6', listo: '#10B981',
                        entregado: '#0EA5E9', cancelado: '#EF4444', facturado: '#6366F1',
                    };
                    return m[this.estado] || '#6B7280';
                },
                // Envío a domicilio: distancia y tiempo estimado desde el restaurante.
                get tieneCoords() {
                    return this.tipo === 'delivery' && this.lat != null && this.lng != null && window.RESTAURANTE;
                },
                distKm() {
                    // Preferir la distancia por calle (OSRM); si aún no llega, la recta.
                    return this.kmRuta != null
                        ? this.kmRuta
                        : window.distanciaKm(window.RESTAURANTE.lat, window.RESTAURANTE.lng, this.lat, this.lng);
                },
                etaMin() {
                    return this.minRuta != null ? this.minRuta : window.tiempoEntregaMin(this.distKm());
                },
                // El cliente confirma que recibió su pedido (listo -> entregado).
                get puedeConfirmar() {
                    return this.estado === 'listo' && (this.tipo === 'delivery' || this.tipo === 'para_llevar');
                },
                confirmando: false,
                async confirmarRecepcion() {
                    if (this.confirmando) return;
                    if (!confirm('¿Confirmas que recibiste tu pedido?')) return;
                    this.confirmando = true;
                    try {
                        const res = await fetch(`/pedidos/${this.pedidoId}/cambiar-estado`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            },
                            body: JSON.stringify({ estado: 'entregado' }),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.success) {
                            this.estado = 'entregado';
                        } else {
                            alert(data.mensaje || 'No se pudo confirmar la recepción.');
                        }
                    } catch (e) {
                        alert('Error al confirmar la recepción.');
                    } finally {
                        this.confirmando = false;
                    }
                },
            };
        }
    </script>

    {{-- PAGO CON QR (cliente). Solo si su factura sigue pendiente. El pedido del
         cliente entra a la cocina recién DESPUÉS de pagar (regla "primero paga,
         luego se prepara"). El QR se escanea con otro celular. --}}
    @if(auth()->user()->isCliente() && $pedido->estado === 'pendiente' && $pedido->factura && $pedido->factura->estado === 'pendiente')
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
            <div id="delivery-info" class="mb-3 text-sm text-gray-700 rounded-lg px-3 py-2"
                 style="background-color:#FFF7ED; border:1px solid #FED7AA;"></div>
            <div id="mapShow" style="height: 350px;" class="border border-gray-200 rounded-lg z-0"></div>
        </div>
    </div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cliente = [{{ $pedido->latitud }}, {{ $pedido->longitud }}];
            const resto = [window.RESTAURANTE.lat, window.RESTAURANTE.lng];

            const mapShow = L.map('mapShow');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(mapShow);

            // Pin del restaurante (origen) y del cliente (destino).
            const iconResto = L.divIcon({ html: '<div style="font-size:26px;line-height:1">🍴</div>', className: '', iconSize: [26, 26], iconAnchor: [13, 13] });
            L.marker(resto, { icon: iconResto }).addTo(mapShow).bindPopup(window.RESTAURANTE.nombre + ' (restaurante)');
            L.marker(cliente).addTo(mapShow).bindPopup('📍 Tu ubicación de entrega').openPopup();

            // Ruta REAL por calle (OSRM) restaurante → cliente, con encuadre.
            const info = document.getElementById('delivery-info');
            window.rutaDelivery(mapShow, resto, cliente, function (r) {
                // Compartir la distancia/tiempo POR CALLE con la barra de seguimiento.
                window.dispatchEvent(new CustomEvent('ruta-delivery-calculada', { detail: { km: r.km, min: r.min } }));
                if (!info) return;
                const tipoTxt = r.real ? 'por calle' : 'en línea recta';
                info.innerHTML = '<i class="fas fa-route mr-1" style="color:#C2410C"></i> <strong>' + r.km.toFixed(1) + ' km</strong> ' + tipoTxt
                    + ' &nbsp;·&nbsp; <i class="fas fa-clock mr-1" style="color:#C2410C"></i> llega en <strong>~' + r.min + ' min</strong>';
            }, { fit: true });
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