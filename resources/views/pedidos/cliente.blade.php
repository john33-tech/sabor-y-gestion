@extends('layouts.app')

@section('title', 'Nuevo Pedido')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold" style="color: #C2410C;">
                    <i class="fas fa-plus-circle mr-2"></i> Nuevo Pedido
                </h1>
                <p class="text-muted mt-1">Seleccione los platos y complete la información del pedido</p>
            </div>
            <div class="px-4 py-2 rounded-lg font-semibold" style="background-color: #FED7AA; color: #C2410C;">
                <i class="fas fa-receipt mr-2"></i> #{{ $numeroPedido }}
            </div>
        </div>
    </div>

    <form action="{{ route('pedidos.store.cliente') }}" method="POST" id="pedidoForm">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Panel Izquierdo - Platos -->
            <div class="lg:col-span-2">
                <div class="bg-surface rounded-lg shadow-md" style="border: 1px solid #FED7AA;">
                    <div class="p-4 border-b rounded-t-lg" style="background-color: #FFF7ED; border-color: #FED7AA;">
                        <h2 class="text-xl font-semibold" style="color: #C2410C;">
                            <i class="fas fa-utensils mr-2"></i> Seleccionar Platos
                        </h2>
                        <div class="relative mt-3">
                            <input type="text" 
                                id="buscarPlato"
                                placeholder="🔍 Buscar plato por nombre..."
                                autocomplete="off"
                                class="w-full px-4 py-2 pl-10 rounded-lg outline-none transition-all"
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">
                            <i class="fas fa-search absolute left-3 top-3" style="color: #78716C;"></i>
                        </div>
                    </div>

                    <div class="p-4 max-h-[600px] overflow-y-auto" id="platosContainer" style="background-color: #FFFFFF;">
                        @forelse($platosConStock as $categoria => $platosCategoria)
                            <div class="categoria-group mb-6" data-categoria="{{ $categoria }}">
                                <h3 class="font-bold mb-3 border-l-4 pl-2" style="color: #C2410C; border-color: #C2410C;">
                                    {{ $categoria }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($platosCategoria as $plato)
                                        @php
                                            $tieneStock = $plato->tiene_stock;
                                            $stockInsuficiente = $plato->stock_insuficiente;
                                        @endphp
                                        <div class="plato-item rounded-lg p-3 transition-all hover:shadow-md {{ $tieneStock ? 'bg-white' : 'bg-red-50' }}"
                                            style="border: 1px solid {{ $tieneStock ? '#FED7AA' : '#FECACA' }};"
                                            data-id="{{ $plato->id }}"
                                            data-nombre="{{ $plato->nombre }}"
                                            data-precio="{{ $plato->precio }}"
                                            data-categoria="{{ $categoria }}"
                                            data-tiene-stock="{{ $tieneStock ? 'true' : 'false' }}">
                                            
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <h4 class="font-semibold {{ $tieneStock ? 'text-gray-800' : 'text-red-600' }}">
                                                            {{ $plato->nombre }}
                                                        </h4>
                                                        @if(!$tieneStock)
                                                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                                                                <i class="fas fa-exclamation-triangle mr-1"></i> Sin Stock
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="font-bold mt-1" style="color: #C2410C;">
                                                        Bs. {{ number_format($plato->precio, 2) }}
                                                    </p>
                                                    @if($plato->descripcion)
                                                        <p class="text-xs mt-1" style="color: #78716C;">
                                                            {{ Str::limit($plato->descripcion, 50) }}
                                                        </p>
                                                    @endif
                                                    

                                                </div>
                                                
                                                <button type="button"
                                                    class="agregar-plato text-white rounded-lg px-3 py-1.5 text-sm transition flex items-center gap-1"
                                                    style="background-color: {{ $tieneStock ? '#C2410C' : '#9CA3AF' }}; cursor: {{ $tieneStock ? 'pointer' : 'not-allowed' }}; opacity: {{ $tieneStock ? '1' : '0.5' }};"
                                                    data-id="{{ $plato->id }}"
                                                    data-nombre="{{ $plato->nombre }}"
                                                    data-precio="{{ $plato->precio }}"
                                                    {{ !$tieneStock ? 'disabled' : '' }}>
                                                    <i class="fas fa-plus text-xs"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8" style="color: #78716C;">
                                <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                                <p>No hay platos disponibles</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Panel Derecho - Detalles del Pedido -->
            <div>
                <div class="bg-surface rounded-lg shadow-md sticky top-4" style="border: 1px solid #FED7AA;">
                    <div class="p-4 border-b rounded-t-lg" style="background-color: #FFF7ED; border-color: #FED7AA;">
                        <h2 class="text-xl font-semibold" style="color: #C2410C;">
                            <i class="fas fa-shopping-cart mr-2"></i> Carrito de Pedido
                        </h2>
                    </div>

                    <div class="p-4" style="background-color: #FFFFFF;">
                        <!-- Tipo de Pedido -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2" style="color: #111827;">Tipo de Pedido</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="tipo-btn p-2 rounded-lg border-2 transition-all text-center"
                                    data-tipo="para_llevar"
                                    style="border-color: #FED7AA; background-color: #FFFFFF; color: #78716C;">
                                    <i class="fas fa-box text-lg"></i>
                                    <span class="block text-sm mt-1">Para Llevar</span>
                                </button>
                                <button type="button" class="tipo-btn p-2 rounded-lg border-2 transition-all text-center"
                                    data-tipo="delivery"
                                    style="border-color: #FED7AA; background-color: #FFFFFF; color: #78716C;">
                                    <i class="fas fa-motorcycle text-lg"></i>
                                    <span class="block text-sm mt-1">Delivery</span>
                                </button>
                            </div>
                            <input type="hidden" 
                                name="tipo_pedido"
                                id="tipo_pedido_input"
                                value="{{ isset($pedido) ? $pedido->tipo_pedido : 'para_llevar' }}">
                        </div>


                        <!-- Datos del Cliente -->
                        <div id="clienteContainer" class="hidden mb-6 space-y-3">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-user mr-1"></i> Nombre del Cliente *
                                </label>
                                <input type="text" 
                                    name="cliente_nombre"
                                    value="{{ $usuario->name }}"
                                    readonly
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all bg-gray-100"
                                    style="border: 1px solid #FED7AA; color: #111827;">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-phone mr-1"></i> Teléfono *
                                </label>
                                <input type="tel"
                                    name="cliente_telefono"
                                    value="{{ $usuario->telefono ?? '' }}"
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                    style="border: 1px solid #FED7AA; color: #111827;">
                            </div>
                            
                            <div id="direccionContainer" class="hidden">
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Dirección de Entrega *
                                </label>
                                <textarea name="direccion"
                                    rows="2"
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                    style="border: 1px solid #FED7AA; color: #111827;">{{ $usuario->direccion ?? '' }}</textarea>
                            </div>
                        </div>

                        <!-- Items del Pedido -->
                        <div class="mb-6">
                            <h3 class="font-semibold mb-2" style="color: #111827;">
                                <i class="fas fa-list mr-1"></i> Items del Pedido
                            </h3>
                            <div id="itemsList" class="space-y-2 max-h-80 overflow-y-auto rounded-lg p-2" style="background-color: #FFF7ED;">
                                <div class="text-center py-8" style="color: #78716C;">
                                    <i class="fas fa-shopping-cart text-4xl mb-2 block"></i>
                                    <p>No hay items agregados</p>
                                </div>
                            </div>
                        </div>

                        <!-- MAPA Delivery -->
                        <div id="delivery-map-container" class="hidden">
                            <label class="block text-sm font-medium mb-2 mt-4" style="color: #111827;">
                                <i class="fas fa-map mr-1"></i> Seleccione ubicación en el mapa
                            </label>
                            <button type="button" id="btn-mi-ubicacion" onclick="usarMiUbicacion()"
                                class="w-full mb-2 py-2 rounded-lg text-white font-semibold flex items-center justify-center gap-2 hover:opacity-90 transition"
                                style="background-color: #2563eb;">
                                <i class="fas fa-location-crosshairs"></i> Usar mi ubicación actual
                            </button>
                            <div id="map" style="height: 350px;" class="rounded-lg border"></div>
                            <div id="delivery-info" class="hidden mt-2 text-sm text-gray-700 rounded-lg px-3 py-2"
                                 style="background-color:#FFF7ED; border:1px solid #FED7AA;"></div>
                            <input type="hidden" name="latitud" id="latitud">
                            <input type="hidden" name="longitud" id="longitud">
                            <input type="hidden" name="distancia_km" id="distancia_km">
                        </div>

                        <!-- Notas -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                <i class="fas fa-sticky-note mr-1"></i> Notas Adicionales
                            </label>
                            <textarea name="notas" rows="2"
                                class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;"></textarea>
                        </div>

                        <!-- Descuento -->
                        <div class="mb-6" style="display:none">
                            <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                <i class="fas fa-tag mr-1"></i> Descuento (Bs.)
                            </label>
                            <input type="number" name="descuento" value="0" step="0.01" min="0"
                                class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">
                        </div>
                    
                        <!-- Totales -->
                        <div class="rounded-lg p-4 mb-6" style="background-color: #FFF7ED;">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span style="color: #78716C;">Subtotal:</span>
                                    <span id="subtotal" class="font-semibold" style="color: #111827;">Bs. 0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span style="color: #78716C;">Descuento:</span>
                                    <span id="descuentoDisplay" class="font-semibold" style="color: #C2410C;">Bs. 0.00</span>
                                </div>
                                <div class="flex justify-between text-sm" id="envioRow" style="display:none;">
                                    <span style="color: #78716C;"><i class="fas fa-motorcycle mr-1"></i> Costo de envío:</span>
                                    <span id="envioDisplay" class="font-semibold" style="color: #C2410C;">Bs. 0.00</span>
                                </div>
                                <div class="border-t pt-2 mt-2" style="border-color: #FED7AA;">
                                    <div class="flex justify-between font-bold text-lg">
                                        <span style="color: #111827;">TOTAL:</span>
                                        <span id="total" style="color: #C2410C;">Bs. 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 text-white py-2 rounded-lg transition flex items-center justify-center gap-2 hover:opacity-90"
                                style="background-color: #C2410C;">
                                <i class="fas fa-check-circle"></i> Crear Pedido
                            </button>
                            <a href="{{ route('pedidos.misPedidos') }}" class="flex-1 text-white py-2 rounded-lg transition text-center flex items-center justify-center gap-2 hover:opacity-90"
                                style="background-color: #78716C;">
                                <i class="fas fa-times-circle"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let items = [];
let map;
let marker;
let limpiarRuta;
let markerResto;
let searchTimeout;

// ======================
// FUNCIONES DEL MAPA
// ======================
function initMap() {

    const resto = [window.RESTAURANTE.lat, window.RESTAURANTE.lng];
    map = L.map('map').setView(resto, 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Pin del restaurante (origen del envío).
    const iconResto = L.divIcon({ html: '<div style="font-size:24px;line-height:1">🍴</div>', className: '', iconSize: [24, 24], iconAnchor: [12, 12] });
    markerResto = L.marker(resto, { icon: iconResto }).addTo(map)
        .bindPopup(window.RESTAURANTE.nombre + ' (restaurante)');

    map.on('click', function(e) {
        colocarMarcador(e.latlng.lat, e.latlng.lng);
    });
}

// Coloca/actualiza el pin del cliente, guarda las coordenadas, y dibuja la
// ruta + distancia/tiempo estimado desde el restaurante.
function colocarMarcador(lat, lng) {
    document.getElementById('latitud').value = lat;
    document.getElementById('longitud').value = lng;

    if (marker) {
        map.removeLayer(marker);
    }
    marker = L.marker([lat, lng]).addTo(map).bindPopup('📍 Tu ubicación de entrega');

    // Ruta REAL por calle restaurante → cliente (con fallback a línea recta).
    const resto = [window.RESTAURANTE.lat, window.RESTAURANTE.lng];
    if (limpiarRuta) limpiarRuta();
    const info = document.getElementById('delivery-info');
    if (info) info.classList.remove('hidden');
    limpiarRuta = window.rutaDelivery(map, resto, [lat, lng], function (r) {
        // Guardar la distancia por calle (o recta si OSRM falla) para cobrar el
        // envío por ESA distancia (la que se muestra en el mapa).
        const dk = document.getElementById('distancia_km');
        if (dk) dk.value = r.km;
        updateTotals();
        if (!info) return;
        const tipoTxt = r.real ? 'por calle' : 'en línea recta';
        info.innerHTML = '<i class="fas fa-route mr-1" style="color:#C2410C"></i> <strong>' + r.km.toFixed(1) + ' km</strong> ' + tipoTxt
            + ' &nbsp;·&nbsp; <i class="fas fa-clock mr-1" style="color:#C2410C"></i> llega en <strong>~' + r.min + ' min</strong>';
    });

    // Autocompletar la dirección de entrega (geocodificación inversa) — tanto al
    // hacer clic en el mapa como al usar el GPS.
    autocompletarDireccion(lat, lng);

    // Recalcular totales: ya hay ubicación → se puede cobrar el envío.
    updateTotals();
}

// Geocodificación inversa (Nominatim): rellena el campo "dirección" desde lat/lng.
// Si falla, cae a las coordenadas para que el campo nunca quede vacío.
function autocompletarDireccion(lat, lng) {
    const dir = document.querySelector('textarea[name="direccion"]');
    if (!dir) return;
    const coordsTxt = `Ubicación GPS (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
    dir.value = 'Obteniendo dirección…';
    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=es`)
        .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
        .then((d) => { dir.value = (d && d.display_name) ? d.display_name : coordsTxt; })
        .catch(() => { dir.value = coordsTxt; });
}

// Geolocalización del dispositivo (GPS del navegador / PWA).
function usarMiUbicacion() {
    const btn = document.getElementById('btn-mi-ubicacion');

    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización. Marca tu ubicación tocando el mapa.');
        return;
    }

    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obteniendo tu ubicación…';

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            if (!map) initMap();
            map.setView([lat, lng], 17);
            colocarMarcador(lat, lng); // ya autocompleta la dirección

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> ¡Ubicación detectada!';
            setTimeout(() => { btn.innerHTML = original; }, 2500);
        },
        (err) => {
            btn.disabled = false;
            btn.innerHTML = original;
            const msg = err.code === 1
                ? 'Permiso de ubicación denegado. Actívalo o marca tu ubicación en el mapa.'
                : 'No pudimos obtener tu ubicación. Marca tu ubicación tocando el mapa.';
            alert(msg);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function toggleMap() {

    const tipoPedido = document.getElementById('tipo_pedido_input').value;

    const mapContainer = document.getElementById('delivery-map-container');

    if (tipoPedido === 'delivery') {

        mapContainer.classList.remove('hidden');

        setTimeout(() => {

            if (!map) {
                initMap();
            } else {
                map.invalidateSize();
            }

        }, 200);

    } else {

        mapContainer.classList.add('hidden');
    }

    updateTotals(); // recalcular: el envío solo aplica a delivery
}

// ======================
// FUNCIONES DEL CARRITO
// ======================
function renderItems() {

    const container = document.getElementById('itemsList');

    if (!items.length) {

        container.innerHTML = `
            <div class="text-center py-8" style="color: #78716C;">
                <i class="fas fa-shopping-cart text-4xl mb-2 block"></i>
                <p>No hay items agregados</p>
            </div>
        `;

        updateTotals();

        return;
    }

    container.innerHTML = items.map((item, index) => `
        <div class="bg-white border rounded-lg p-3 mb-2 shadow-sm" style="border-color: #FED7AA;">

            <div class="flex justify-between items-start mb-2">

                <div class="flex-1">

                    <div class="font-semibold" style="color: #111827;">
                        ${escapeHtml(item.nombre)}
                    </div>

                    <div class="font-bold text-sm mt-1" style="color: #C2410C;">
                        Bs. ${item.precio_unitario.toFixed(2)}
                    </div>

                </div>

                <button
                    type="button"
                    onclick="removeItem(${index})"
                    class="hover:opacity-70 ml-2"
                    style="color: #C2410C;"
                >
                    <i class="fas fa-trash"></i>
                </button>

            </div>

            <div class="flex items-center gap-2 mt-2">

                <label class="text-sm" style="color: #78716C;">
                    Cant:
                </label>

                <input
                    type="number"
                    value="${item.cantidad}"
                    min="1"
                    class="w-16 px-2 py-1 border rounded text-center outline-none"
                    style="border-color: #FED7AA;"
                    onchange="updateItemQuantity(${index}, this.value)"
                >

                <input
                    type="text"
                    placeholder="Notas..."
                    value="${escapeHtml(item.notas)}"
                    class="flex-1 px-2 py-1 border rounded text-sm outline-none"
                    style="border-color: #FED7AA;"
                    onblur="updateItemNotes(${index}, this.value)"
                >

            </div>

            <input type="hidden" name="items[${index}][plato_id]" value="${item.plato_id}">
            <input type="hidden" name="items[${index}][cantidad]" value="${item.cantidad}">
            <input type="hidden" name="items[${index}][notas]" value="${escapeHtml(item.notas)}">

        </div>
    `).join('');

    updateTotals();
}

function addItem(platoId, nombre, precio, tieneStock) {

    if (!tieneStock) {

        showNotification(
            '❌ No hay suficiente stock para: ' + nombre,
            'error'
        );

        return false;
    }

    const existingItem = items.find(
        item => item.plato_id == platoId
    );

    if (existingItem) {

        existingItem.cantidad++;

        showNotification(
            '✓ Cantidad actualizada: ' + nombre,
            'success'
        );

    } else {

        items.push({
            plato_id: platoId,
            nombre: nombre,
            precio_unitario: precio,
            cantidad: 1,
            notas: ''
        });

        showNotification(
            '✓ Agregado al carrito: ' + nombre,
            'success'
        );
    }

    renderItems();

    return true;
}

function removeItem(index) {

    const itemName = items[index].nombre;

    items.splice(index, 1);

    renderItems();

    showNotification(
        'Eliminado: ' + itemName,
        'info'
    );
}

function updateItemQuantity(index, newQuantity) {

    const quantity = parseInt(newQuantity);

    if (quantity > 0) {

        items[index].cantidad = quantity;

        renderItems();
    }
}

function updateItemNotes(index, notes) {

    items[index].notas = notes;
}

function updateTotals() {

    let subtotal = items.reduce((sum, item) => {
        return sum + (item.precio_unitario * item.cantidad);
    }, 0);

    let descuento = parseFloat(
    document.querySelector('input[name="descuento"]').value
) || 0;

    // Costo de envío: solo delivery con ubicación elegida. Se cobra por la
    // distancia POR CALLE (la del mapa), guardada en #distancia_km.
    let envio = 0;
    const tipo = document.getElementById('tipo_pedido_input')?.value;
    const distKm = parseFloat(document.getElementById('distancia_km')?.value);
    const envioRow = document.getElementById('envioRow');
    if (tipo === 'delivery' && !isNaN(distKm) && distKm > 0 && window.costoEnvio) {
        envio = window.costoEnvio(distKm);
        if (envioRow) {
            envioRow.style.display = 'flex';
            document.getElementById('envioDisplay').innerHTML = `Bs. ${envio.toFixed(2)}`;
        }
    } else if (envioRow) {
        envioRow.style.display = 'none';
    }

    // IVA eliminado: total = subtotal - descuento + envío.
    let total = subtotal - descuento + envio;

    document.getElementById('subtotal').innerHTML =
        `Bs. ${subtotal.toFixed(2)}`;

    document.getElementById('descuentoDisplay').innerHTML =
        `Bs. ${descuento.toFixed(2)}`;

    document.getElementById('total').innerHTML =
        `Bs. ${total.toFixed(2)}`;
}

function showNotification(message, type = 'success') {

    const notification = document.createElement('div');

    notification.className =
        'fixed top-20 right-4 z-50 text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in';

    notification.style.backgroundColor =
        type === 'success'
            ? '#10B981'
            : (
                type === 'error'
                    ? '#EF4444'
                    : '#C2410C'
            );

    notification.innerHTML = `
        <i class="fas fa-${
            type === 'success'
                ? 'check-circle'
                : (
                    type === 'error'
                        ? 'exclamation-circle'
                        : 'info-circle'
                )
        } mr-2"></i>${message}
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function escapeHtml(text) {

    if (!text) return '';

    const div = document.createElement('div');

    div.textContent = text;

    return div.innerHTML;
}

// ======================
// EVENTOS
// ======================
document.addEventListener('DOMContentLoaded', function() {

    const tipoBtns = document.querySelectorAll('.tipo-btn');

    const tipoInput = document.getElementById('tipo_pedido_input');

    const clienteContainer =
        document.getElementById('clienteContainer');

    const direccionContainer =
        document.getElementById('direccionContainer');

    function updateTipoPedido(tipo) {

        tipoInput.value = tipo;

        toggleMap();

        tipoBtns.forEach(btn => {

            const btnTipo = btn.dataset.tipo;

            if (btnTipo === tipo) {

                btn.style.borderColor = '#C2410C';
                btn.style.backgroundColor = '#FFF7ED';
                btn.style.color = '#C2410C';

            } else {

                btn.style.borderColor = '#FED7AA';
                btn.style.backgroundColor = '#FFFFFF';
                btn.style.color = '#78716C';
            }
        });

        clienteContainer.classList.remove('hidden');

        direccionContainer.classList.toggle(
            'hidden',
            tipo !== 'delivery'
        );
    }

    tipoBtns.forEach(btn => {

        btn.addEventListener('click', function() {

            updateTipoPedido(this.dataset.tipo);
        });
    });

   updateTipoPedido('para_llevar');

    // ======================
    // AGREGAR PLATOS
    // ======================
    document.getElementById('platosContainer')
    .addEventListener('click', function(e) {

        const btn = e.target.closest('.agregar-plato');

        if (btn && !btn.disabled) {

            e.preventDefault();

            const platoItem = btn.closest('.plato-item');

            addItem(
                btn.dataset.id,
                btn.dataset.nombre,
                parseFloat(btn.dataset.precio),
                platoItem.dataset.tieneStock === 'true'
            );
        }
    });

    // ======================
    // BUSCADOR
    // ======================
    const buscador = document.getElementById('buscarPlato');

    if (buscador) {

        buscador.addEventListener('input', function(e) {

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {

                const term = e.target.value
                    .toLowerCase()
                    .trim();

                document.querySelectorAll('.plato-item')
                .forEach(item => {

                    const nombre = item.dataset.nombre
                        .toLowerCase();

                    item.style.display =
                        term === '' || nombre.includes(term)
                            ? ''
                            : 'none';
                });

            }, 300);
        });
    }

    // ======================
    // VALIDAR FORMULARIO
    // ======================
    document.getElementById('pedidoForm')
    .addEventListener('submit', async function(e) {

        if (items.length === 0) {

            e.preventDefault();

            showNotification(
                '❌ Debe agregar al menos un plato al pedido',
                'error'
            );

            return false;
        }

        const submitBtn =
            this.querySelector('[type="submit"]');

        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML =
            '<i class="fas fa-spinner fa-spin mr-2"></i> Verificando stock...';

        submitBtn.disabled = true;

        const tipoPedido = tipoInput.value;

        const nombre = document
            .querySelector('input[name="cliente_nombre"]')
            .value
            .trim();

        const telefono = document
            .querySelector('input[name="cliente_telefono"]')
            .value
            .trim();

        if (!nombre || !telefono) {

            e.preventDefault();

            showNotification(
                '❌ Debe ingresar nombre y teléfono',
                'error'
            );

            submitBtn.innerHTML = originalText;

            submitBtn.disabled = false;

            return false;
        }

        if (tipoPedido === 'delivery') {

            const direccion = document
                .querySelector('textarea[name="direccion"]')
                .value
                .trim();

            if (!direccion) {

                e.preventDefault();

                showNotification(
                    '❌ Debe ingresar dirección',
                    'error'
                );

                submitBtn.innerHTML = originalText;

                submitBtn.disabled = false;

                return false;
            }

            const latitud =
                document.getElementById('latitud').value;

            const longitud =
                document.getElementById('longitud').value;

            if (!latitud || !longitud) {

                e.preventDefault();

                showNotification(
                    '❌ Debe seleccionar ubicación en el mapa',
                    'error'
                );

                submitBtn.innerHTML = originalText;

                submitBtn.disabled = false;

                return false;
            }
        }

        submitBtn.innerHTML =
            '<i class="fas fa-check-circle mr-2"></i> Creando pedido...';

        return true;
    });
});


// Funciones globales
window.removeItem = removeItem;
window.updateItemQuantity = updateItemQuantity;
window.updateItemNotes = updateItemNotes;
</script>
@endpush

@push('styles')
<style>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush
@endsection