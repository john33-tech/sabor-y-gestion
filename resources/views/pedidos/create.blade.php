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

    <form action="{{ route('pedidos.store') }}" method="POST" id="pedidoForm">
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
                                                    
                                                    <!-- Mostrar información de stock de ingredientes cuando no hay stock -->
                                                    @if(!$tieneStock && count($stockInsuficiente) > 0)
                                                        <div class="mt-2 p-2 rounded text-xs" style="background-color: #FEF2F2; border: 1px solid #FECACA;">
                                                            <p class="font-semibold text-red-700 mb-1">
                                                                <i class="fas fa-box mr-1"></i> No disponible por:
                                                            </p>
                                                            @foreach($stockInsuficiente as $ingrediente)
                                                                <p class="text-red-600 ml-2">
                                                                    • {{ $ingrediente['nombre'] }}: 
                                                                    @if(isset($ingrediente['motivo']))
                                                                        {{ $ingrediente['motivo'] }}
                                                                    @else
                                                                        disponible {{ number_format($ingrediente['disponible'], 2) }} {{ $ingrediente['unidad'] }} 
                                                                        (necesita {{ number_format($ingrediente['necesario'], 2) }} {{ $ingrediente['unidad'] }})
                                                                    @endif
                                                                </p>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Mostrar stock de ingredientes cuando SI hay stock -->
                                                    @if($tieneStock && isset($plato->ingredientes) && count($plato->ingredientes) > 0)
                                                        <div class="mt-2">
                                                            <div class="flex flex-wrap gap-1 text-xs">
                                                                @foreach($plato->ingredientes as $ingrediente)
                                                                    @php
                                                                        $inventario = $ingrediente->inventario;
                                                                        $stockActual = $inventario?->cantidad_actual ?? 0;
                                                                        $stockMinimo = $inventario?->stock_minimo ?? 0;
                                                                        $isLowStock = $stockActual <= $stockMinimo;
                                                                    @endphp
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full {{ $isLowStock ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}" style="font-size: 10px;">
                                                                        <i class="fas {{ $isLowStock ? 'fa-exclamation-triangle' : 'fa-check-circle' }} mr-0.5 text-xs"></i>
                                                                        {{ $ingrediente->nombre }}: {{ number_format($stockActual, 0) }} {{ $ingrediente->unidad_medida }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Mensaje para platos sin ingredientes registrados -->
                                                    @if($plato->ingredientes->count() === 0)
                                                        <div class="mt-2 p-2 rounded text-xs" style="background-color: #FEF2F2; border: 1px solid #FECACA;">
                                                            <p class="font-semibold text-red-700 mb-1">
                                                                <i class="fas fa-exclamation-circle mr-1"></i> Configuración incompleta:
                                                            </p>
                                                            <p class="text-red-600 ml-2">
                                                                Este plato no tiene ingredientes registrados. Contacte al administrador.
                                                            </p>
                                                        </div>
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
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" class="tipo-btn p-2 rounded-lg border-2 transition-all text-center"
                                    data-tipo="mesa"
                                    style="border-color: #C2410C; background-color: #FFF7ED; color: #C2410C;">
                                    <i class="fas fa-chair text-lg"></i>
                                    <span class="block text-sm mt-1">Mesa</span>
                                </button>
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
                            <input type="hidden" name="tipo_pedido" id="tipo_pedido_input" value="mesa">
                        </div>

                        <!-- Mesa -->
                        <div id="mesaContainer" class="mb-6">
                            <label class="block text-sm font-medium mb-2" style="color: #111827;">
                                <i class="fas fa-chair mr-1"></i> Seleccionar Mesa
                            </label>
                            <select name="mesa_id" class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">
                                <option value="">-- Seleccione una mesa --</option>
                                @foreach($mesas as $mesa)
                                    <option value="{{ $mesa->id }}" {{ old('mesa_id') == $mesa->id ? 'selected' : '' }}>
                                        Mesa {{ $mesa->numero_mesa }} - Cap. {{ $mesa->capacidad }} personas
                                    </option>
                                @endforeach
                            </select>
                            @error('mesa_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @php
                            $authUser = auth()->user();
                            $esCliente = $authUser && $authUser->isCliente();
                            $nombreDefault = $esCliente ? $authUser->name : '';
                            $telefonoDefault = $esCliente ? ($authUser->celular ?? '') : '';
                            $direccionDefault = $esCliente ? ($authUser->direccion ?? '') : '';
                            $emailDefault = $esCliente ? ($authUser->email ?? '') : '';
                        @endphp

                        <!-- Email del cliente (siempre visible, sirve para enviar la factura) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                <i class="fas fa-envelope mr-1"></i> Email del cliente <span class="text-xs text-gray-500">(para enviarle la factura por correo)</span>
                            </label>
                            <input type="email" name="cliente_email"
                                value="{{ old('cliente_email', $emailDefault) }}"
                                placeholder="cliente@ejemplo.com"
                                class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">
                            @error('cliente_email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Datos del Cliente (solo para delivery / para llevar) -->
                        <div id="clienteContainer" class="hidden mb-6 space-y-3">
                            @if($esCliente)
                                <div class="px-3 py-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Hemos pre-llenado tus datos desde tu perfil. Modifícalos solo si el pedido es para otra persona.
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-user mr-1"></i> Nombre del Cliente *
                                </label>
                                <input type="text" name="cliente_nombre"
                                    value="{{ old('cliente_nombre', $nombreDefault) }}"
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                    style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">
                                @error('cliente_nombre')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-phone mr-1"></i> Teléfono *
                                </label>
                                <input type="tel" name="cliente_telefono"
                                    value="{{ old('cliente_telefono', $telefonoDefault) }}"
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                    style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">
                                @error('cliente_telefono')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="direccionContainer" class="hidden">
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Dirección de Entrega *
                                </label>
                                <textarea name="direccion" id="direccion" rows="3"
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                    style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">{{ old('direccion', $direccionDefault) }}</textarea>
                                @error('direccion')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
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
                            <div id="map" style="height: 350px;" class="rounded-lg border"></div>
                            <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud') }}">
                            <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud') }}">
                        </div>

                        <!-- Notas -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                <i class="fas fa-sticky-note mr-1"></i> Notas Adicionales
                            </label>
                            <textarea name="notas" rows="2"
                                class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">{{ old('notas') }}</textarea>
                        </div>

                        <!-- Descuento -->
                        <div class="mb-6" style="display:none">
                            <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                <i class="fas fa-tag mr-1"></i> Descuento (Bs.)
                            </label>
                            <input type="number" name="descuento" value="{{ old('descuento', 0) }}" step="0.01" min="0"
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
                            <a href="{{ route('pedidos.index') }}" class="flex-1 text-white py-2 rounded-lg transition text-center flex items-center justify-center gap-2 hover:opacity-90"
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
// Mapa de datos de platos para restaurar desde old()
const platoData = {
    @foreach($platosConStock as $categoria => $platosCategoria)
        @foreach($platosCategoria as $plato)
            "{{ $plato->id }}": {
                nombre: "{{ $plato->nombre }}",
                precio: {{ $plato->precio }},
                tieneStock: {{ $plato->tiene_stock ? 'true' : 'false' }}
            },
        @endforeach
    @endforeach
};

let items = [];
// Restaurar items desde el input antiguo si existe (por ejemplo, después de un error de validación)
const oldItems = @json(old('items', []));
if (Array.isArray(oldItems) && oldItems.length > 0) {
    items = oldItems.map(item => {
        const data = platoData[item.plato_id];
        return {
            plato_id: item.plato_id,
            nombre: data ? data.nombre : 'Plato desconocido',
            precio_unitario: data ? parseFloat(data.precio) : 0,
            cantidad: parseInt(item.cantidad) || 1,
            notas: item.notas || ''
        };
    });
}

let map;
let marker;
let searchTimeout;

// Funciones del mapa
// Default: Cochabamba (Plaza 14 de Septiembre). Si el navegador permite
// geolocalización, recentramos en la ubicación real del usuario.
const DEFAULT_CENTER = [-17.3895, -66.1568]; // Cochabamba, Bolivia
const DEFAULT_ZOOM = 14;

// Reverse geocoding con Nominatim (gratis, sin API key) — llena el textarea
// "direccion" cuando el usuario hace click en el mapa.
// Política: click en el mapa = el usuario quiere ESA dirección, así que siempre
// sobrescribe (incluso si había un valor previo del perfil o tipeado).
async function reverseGeocode(lat, lng) {
    const direccionEl = document.getElementById('direccion');
    if (!direccionEl) return;
    try {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=es`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        if (data && data.display_name) {
            direccionEl.value = data.display_name;
            direccionEl.dataset.autoFilled = 'true';
        }
    } catch (err) {
        console.warn('Reverse geocoding falló:', err);
    }
}

function setMarker(lat, lng, doReverseGeocode = true) {
    document.getElementById('latitud').value = lat;
    document.getElementById('longitud').value = lng;
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map);
    if (doReverseGeocode) reverseGeocode(lat, lng);
}

function initMap() {
    map = L.map('map').setView(DEFAULT_CENTER, DEFAULT_ZOOM);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    map.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng, true);
    });

    // Si ya hay coordenadas (restauradas de old()), poner el marcador
    const oldLat = document.getElementById('latitud').value;
    const oldLng = document.getElementById('longitud').value;
    
    if (oldLat && oldLng) {
        setMarker(parseFloat(oldLat), parseFloat(oldLng), false);
        map.setView([parseFloat(oldLat), parseFloat(oldLng)], 16);
    } else if (navigator.geolocation) {
        // Pedir geolocalización del navegador (si el usuario aprueba el permiso).
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const { latitude, longitude } = pos.coords;
                map.setView([latitude, longitude], 16);
                // No ponemos marker automáticamente — esperamos que el user haga click
                // sobre la dirección exacta de entrega.
            },
            (err) => {
                console.info('Geolocalización no disponible o denegada, usando Cochabamba por defecto.');
            },
            { timeout: 6000, enableHighAccuracy: false }
        );
    }
}

function toggleMap() {
    const tipoPedido = document.getElementById('tipo_pedido_input').value;
    const mapContainer = document.getElementById('delivery-map-container');
    
    if(tipoPedido === 'delivery') {
        mapContainer.classList.remove('hidden');
        setTimeout(() => { if(!map) initMap(); else map.invalidateSize(); }, 200);
    } else {
        mapContainer.classList.add('hidden');
    }
}

// Funciones del carrito
function renderItems() {
    const container = document.getElementById('itemsList');
    
    if (!items.length) {
        container.innerHTML = `<div class="text-center py-8" style="color: #78716C;"><i class="fas fa-shopping-cart text-4xl mb-2 block"></i><p>No hay items agregados</p></div>`;
        updateTotals();
        return;
    }
    
    container.innerHTML = items.map((item, index) => `
        <div class="bg-white border rounded-lg p-3 mb-2 shadow-sm" style="border-color: #FED7AA;">
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                    <div class="font-semibold" style="color: #111827;">${escapeHtml(item.nombre)}</div>
                    <div class="font-bold text-sm mt-1" style="color: #C2410C;">Bs. ${item.precio_unitario.toFixed(2)}</div>
                </div>
                <button type="button" onclick="removeItem(${index})" class="hover:opacity-70 ml-2" style="color: #C2410C;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="flex items-center gap-2 mt-2">
                <label class="text-sm" style="color: #78716C;">Cant:</label>
                <input type="number" value="${item.cantidad}" min="1" class="w-16 px-2 py-1 border rounded text-center outline-none" style="border-color: #FED7AA;" onchange="updateItemQuantity(${index}, this.value)">
                <input type="text" placeholder="Notas..." value="${escapeHtml(item.notas)}" class="flex-1 px-2 py-1 border rounded text-sm outline-none" style="border-color: #FED7AA;" onblur="updateItemNotes(${index}, this.value)">
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
        showNotification('❌ No hay suficiente stock para: ' + nombre, 'error');
        return false;
    }
    
    const existingItem = items.find(item => item.plato_id == platoId);
    
    if (existingItem) {
        existingItem.cantidad++;
        showNotification('✓ Cantidad actualizada: ' + nombre, 'success');
    } else {
        items.push({ plato_id: platoId, nombre: nombre, precio_unitario: precio, cantidad: 1, notas: '' });
        showNotification('✓ Agregado al carrito: ' + nombre, 'success');
    }
    
    renderItems();
    return true;
}

function removeItem(index) {
    const itemName = items[index].nombre;
    items.splice(index, 1);
    renderItems();
    showNotification('Eliminado: ' + itemName, 'info');
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
    let subtotal = items.reduce((sum, item) => sum + (item.precio_unitario * item.cantidad), 0);
    let descuento = parseFloat(document.querySelector('input[name="descuento"]').value) || 0;
    let impuesto = 0; // IVA desactivado
    let total = subtotal - descuento;
    
    document.getElementById('subtotal').innerHTML = `Bs. ${subtotal.toFixed(2)}`;
    document.getElementById('descuentoDisplay').innerHTML = `Bs. ${descuento.toFixed(2)}`;
    document.getElementById('total').innerHTML = `Bs. ${total.toFixed(2)}`;
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'fixed top-20 right-4 z-50 text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in';
    notification.style.backgroundColor = type === 'success' ? '#10B981' : (type === 'error' ? '#EF4444' : '#C2410C');
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')} mr-2"></i>${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Eventos y configuración inicial
document.addEventListener('DOMContentLoaded', function() {
    // Tipo de pedido
    const tipoBtns = document.querySelectorAll('.tipo-btn');
    const tipoInput = document.getElementById('tipo_pedido_input');
    const mesaContainer = document.getElementById('mesaContainer');
    const clienteContainer = document.getElementById('clienteContainer');
    const direccionContainer = document.getElementById('direccionContainer');
    
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
        
        if (tipo === 'mesa') {
            mesaContainer.classList.remove('hidden');
            clienteContainer.classList.add('hidden');
        } else {
            mesaContainer.classList.add('hidden');
            clienteContainer.classList.remove('hidden');
            direccionContainer.classList.toggle('hidden', tipo !== 'delivery');
        }
    }
    
    tipoBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            updateTipoPedido(this.dataset.tipo);
        });
    });
    
    updateTipoPedido('{{ old('tipo_pedido', 'mesa') }}');
    
    // Si hay items restaurados, renderizarlos
    if (items.length > 0) {
        renderItems();
    }
    
    // Evento para descuento
    document.querySelector('input[name="descuento"]').addEventListener('input', updateTotals);
    
    // Event delegation para botones agregar
    document.getElementById('platosContainer').addEventListener('click', function(e) {
        let btn = e.target.closest('.agregar-plato');
        if (btn && !btn.disabled) {
            e.preventDefault();
            const platoItem = btn.closest('.plato-item');
            addItem(btn.dataset.id, btn.dataset.nombre, parseFloat(btn.dataset.precio), platoItem.dataset.tieneStock === 'true');
        }
    });
    
    // Buscador
    const buscador = document.getElementById('buscarPlato');
    if (buscador) {
        buscador.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const term = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.plato-item').forEach(item => {
                    const nombre = item.dataset.nombre.toLowerCase();
                    item.style.display = term === '' || nombre.includes(term) ? '' : 'none';
                });
            }, 300);
        });
    }
    
    // Validación del formulario
    document.getElementById('pedidoForm').addEventListener('submit', async function(e) {
        e.preventDefault(); // Evitar envío automático
        
        if (items.length === 0) {
            showNotification('❌ Debe agregar al menos un plato al pedido', 'error');
            return false;
        }
        
        const submitBtn = this.querySelector('[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Verificando stock...';
        submitBtn.disabled = true;
        
        try {
            let stockInsuficiente = [];
            // Optimización: podríamos hacer esto en una sola petición, pero mantendremos la lógica actual corregida
            for (const item of items) {
                const response = await fetch('/api/verificar-stock-plato', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ plato_id: item.plato_id, cantidad: item.cantidad })
                });
                
                if (!response.ok) continue;
                
                const data = await response.json();
                if (!data.disponible) {
                    stockInsuficiente.push(`${item.nombre} (x${item.cantidad}) - ${data.mensaje}`);
                }
            }
            
            if (stockInsuficiente.length > 0) {
                showNotification('❌ Stock insuficiente:\n' + stockInsuficiente.join('\n'), 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                return false;
            }
            
            const tipoPedido = tipoInput.value;
            if (tipoPedido === 'mesa') {
                const mesaSelect = document.querySelector('select[name="mesa_id"]');
                if (!mesaSelect.value) {
                    showNotification('❌ Debe seleccionar una mesa', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return false;
                }
            } else {
                const nombre = document.querySelector('input[name="cliente_nombre"]').value.trim();
                const telefono = document.querySelector('input[name="cliente_telefono"]').value.trim();
                if (!nombre || !telefono) {
                    showNotification('❌ Debe ingresar nombre y teléfono del cliente', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return false;
                }
                if (tipoPedido === 'delivery' && !document.querySelector('textarea[name="direccion"]').value.trim()) {
                    showNotification('❌ Debe ingresar una dirección de entrega', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return false;
                }
            }
            
            // Si todo está correcto, enviar el formulario
            this.submit();
            
        } catch (error) {
            console.error('Error durante la validación:', error);
            showNotification('❌ Error al verificar el pedido. Intente nuevamente.', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
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