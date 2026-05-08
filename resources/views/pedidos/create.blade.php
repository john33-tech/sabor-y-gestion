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
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;"
                                onfocus="this.style.borderColor='#C2410C'; this.style.boxShadow='0 0 0 3px rgba(194,65,12,0.1)'"
                                onblur="this.style.borderColor='#FED7AA'; this.style.boxShadow='none'">
                            <i class="fas fa-search absolute left-3 top-3" style="color: #78716C;"></i>
                        </div>
                    </div>

                    <div class="p-4 max-h-[600px] overflow-y-auto" id="platosContainer" style="background-color: #FFFFFF;">
                        @forelse($platos as $categoria => $platosCategoria)
                            <div class="categoria-group mb-6" data-categoria="{{ $categoria }}">
                                <h3 class="font-bold mb-3 border-l-4 pl-2" style="color: #C2410C; border-color: #C2410C;">
                                    {{ $categoria }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($platosCategoria as $plato)
                                        <div class="plato-item rounded-lg p-3 transition-all hover:shadow-md bg-surface"
                                             style="border: 1px solid #FED7AA;"
                                             data-id="{{ $plato->id }}"
                                             data-nombre="{{ $plato->nombre }}"
                                             data-precio="{{ $plato->precio }}"
                                             data-categoria="{{ $categoria }}">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <h4 class="font-semibold" style="color: #111827;">{{ $plato->nombre }}</h4>
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
                                                    class="agregar-plato text-white rounded-lg px-3 py-1.5 text-sm transition flex items-center gap-1 hover:opacity-90"
                                                    style="background-color: #C2410C;"
                                                    data-id="{{ $plato->id }}"
                                                    data-nombre="{{ $plato->nombre }}"
                                                    data-precio="{{ $plato->precio }}">
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
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;"
                                onfocus="this.style.borderColor='#C2410C'"
                                onblur="this.style.borderColor='#FED7AA'">
                                <option value="">-- Seleccione una mesa --</option>
                                @foreach($mesas as $mesa)
                                    <option value="{{ $mesa->id }}">
                                        Mesa {{ $mesa->numero_mesa }} - Cap. {{ $mesa->capacidad }} personas - {{ $mesa->area ?? 'Sin área' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Datos del Cliente -->
                        <div id="clienteContainer" class="hidden mb-6 space-y-3">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-user mr-1"></i> Nombre del Cliente *
                                </label>
                                <input type="text" name="cliente_nombre" 
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                    style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;"
                                    placeholder="Ej: Juan Pérez">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-phone mr-1"></i> Teléfono *
                                </label>
                                <input type="tel" name="cliente_telefono" 
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                    style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;"
                                    placeholder="Ej: 3001234567">
                            </div>
                            
                            <!-- Campo de Dirección para Delivery - Solo texto -->
                            <div id="direccionContainer" class="hidden">
                                <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Dirección de Entrega *
                                </label>
                                <textarea name="direccion" id="direccion" rows="3"
                                    class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                    style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;"
                                    placeholder="Ej: Av. Heroínas #123, entre calle 25 de Mayo y Ayacucho, Cochabamba"></textarea>
                                <p class="text-xs mt-1" style="color: #78716C;">
                                    <i class="fas fa-info-circle mr-1"></i> Incluya referencias para facilitar la entrega
                                </p>
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
                        <!-- MAPA -->
    <div id="delivery-map-container"
         class="hidden">

        <label class="block text-sm font-medium mb-2 mt-4"
               style="color: #111827;">

            <i class="fas fa-map mr-1"></i>
            Seleccione ubicación en el mapa

        </label>

        <div id="map"
             style="height: 350px;"
             class="rounded-lg border">
        </div>

        <input type="hidden"
               name="latitud"
               id="latitud">

        <input type="hidden"
               name="longitud"
               id="longitud">

        <p class="text-sm mt-2"
           style="color: #78716C;">

            Haga clic en el mapa para seleccionar la ubicación exacta.

        </p>

    </div>

                        <!-- Notas -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                <i class="fas fa-sticky-note mr-1"></i> Notas Adicionales
                            </label>
                            <textarea name="notas" rows="2"
                                placeholder="Instrucciones especiales para la cocina..."
                                class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;"></textarea>
                        </div>

                        <!-- Descuento -->
                        <div class="mb-6">
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
                                    <span style="color: #78716C;">IVA (13%):</span>
                                    <span id="impuesto" class="font-semibold" style="color: #111827;">Bs. 0.00</span>
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

let map;
let marker;

function initMap() {

    map = L.map('map').setView([-16.5000, -68.1500], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        document.getElementById('latitud').value = lat;
        document.getElementById('longitud').value = lng;

        if(marker) {
            map.removeLayer(marker);
        }

        marker = L.marker([lat, lng]).addTo(map);
    });
}
// Mostrar mapa solo en delivery
const tipoPedido = document.getElementById('tipo_pedido_input');
const mapContainer = document.getElementById('delivery-map-container');

function toggleMap() {

    if(tipoPedido.value === 'delivery') {

        mapContainer.classList.remove('hidden');

        setTimeout(() => {

            if(!map) {
                initMap();
            }

            map.invalidateSize();

        }, 200);

    } else {

        mapContainer.classList.add('hidden');
    }
}

tipoPedido.addEventListener('change', toggleMap);

toggleMap();

</script>
<script>
let items = [];
let searchTimeout;

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
        <div class="bg-surface border rounded-lg p-3 mb-2 shadow-sm" style="border-color: #FED7AA;">
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
                <input type="number" 
                       value="${item.cantidad}" 
                       min="1" 
                       class="w-16 px-2 py-1 border rounded text-center outline-none"
                       style="border-color: #FED7AA;"
                       onchange="updateItemQuantity(${index}, this.value)">
                <input type="text" 
                       placeholder="Notas..." 
                       value="${escapeHtml(item.notas)}"
                       class="flex-1 px-2 py-1 border rounded text-sm outline-none"
                       style="border-color: #FED7AA;"
                       onblur="updateItemNotes(${index}, this.value)">
            </div>
            <input type="hidden" name="items[${index}][plato_id]" value="${item.plato_id}">
            <input type="hidden" name="items[${index}][cantidad]" value="${item.cantidad}">
            <input type="hidden" name="items[${index}][notas]" value="${escapeHtml(item.notas)}">
        </div>
    `).join('');
    
    updateTotals();
}

function addItem(platoId, nombre, precio) {
    const existingItem = items.find(item => item.plato_id == platoId);
    
    if (existingItem) {
        existingItem.cantidad++;
        showNotification('Cantidad actualizada: ' + nombre);
    } else {
        items.push({
            plato_id: platoId,
            nombre: nombre,
            precio_unitario: precio,
            cantidad: 1,
            notas: ''
        });
        showNotification('Agregado: ' + nombre);
    }
    
    renderItems();
}

function removeItem(index) {
    const itemName = items[index].nombre;
    items.splice(index, 1);
    renderItems();
    showNotification('Eliminado: ' + itemName);
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
    let impuesto = subtotal * 0.13; // 13% IVA
    let total = subtotal + impuesto - descuento;
    
    document.getElementById('subtotal').innerHTML = `Bs. ${subtotal.toFixed(2)}`;
    document.getElementById('impuesto').innerHTML = `Bs. ${impuesto.toFixed(2)}`;
    document.getElementById('descuentoDisplay').innerHTML = `Bs. ${descuento.toFixed(2)}`;
    document.getElementById('total').innerHTML = `Bs. ${total.toFixed(2)}`;
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-20 right-4 z-50 text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in';
    notification.style.backgroundColor = '#C2410C';
    notification.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 2000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Manejo de botones de tipo de pedido
    const tipoBtns = document.querySelectorAll('.tipo-btn');
    const tipoInput = document.getElementById('tipo_pedido_input');
    const mesaContainer = document.getElementById('mesaContainer');
    const clienteContainer = document.getElementById('clienteContainer');
    const direccionContainer = document.getElementById('direccionContainer');
    
    function updateTipoPedido(tipo) {
        // Actualizar input hidden
        tipoInput.value = tipo;
        toggleMap();
        
        // Actualizar estilos de botones
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
        
        // Mostrar/ocultar contenedores
        if (tipo === 'mesa') {
            mesaContainer.classList.remove('hidden');
            clienteContainer.classList.add('hidden');
        } else {
            mesaContainer.classList.add('hidden');
            clienteContainer.classList.remove('hidden');
            
            // Si es delivery, mostrar el campo de dirección
            if (tipo === 'delivery') {
                direccionContainer.classList.remove('hidden');
            } else {
                direccionContainer.classList.add('hidden');
            }
        }
    }
    
    tipoBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            updateTipoPedido(this.dataset.tipo);
        });
    });
    
    // Inicializar con el valor por defecto
    updateTipoPedido('mesa');
    
    // Evento para el descuento
    document.querySelector('input[name="descuento"]').addEventListener('input', updateTotals);
    
    // Event delegation para botones agregar
    document.getElementById('platosContainer').addEventListener('click', function(e) {
        let btn = e.target.closest('.agregar-plato');
        if (btn) {
            e.preventDefault();
            const id = btn.dataset.id;
            const nombre = btn.dataset.nombre;
            const precio = parseFloat(btn.dataset.precio);
            addItem(id, nombre, precio);
        }
    });
    
    // Buscador con debounce
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
    document.getElementById('pedidoForm').addEventListener('submit', function(e) {
        if (items.length === 0) {
            e.preventDefault();
            alert('❌ Debe agregar al menos un plato al pedido');
            return false;
        }
        
        const tipoPedido = tipoInput.value;
        
        if (tipoPedido === 'mesa') {
            const mesaId = document.querySelector('select[name="mesa_id"]').value;
            if (!mesaId) {
                e.preventDefault();
                alert('❌ Debe seleccionar una mesa');
                return false;
            }
        } else {
            const nombre = document.querySelector('input[name="cliente_nombre"]').value.trim();
            const telefono = document.querySelector('input[name="cliente_telefono"]').value.trim();
            
            if (!nombre || !telefono) {
                e.preventDefault();
                alert('❌ Para pedidos de Delivery o Para Llevar, debe ingresar nombre y teléfono del cliente');
                return false;
            }
            
            // Validar dirección si es delivery
            if (tipoPedido === 'delivery') {
                const direccion = document.querySelector('textarea[name="direccion"]').value.trim();
                if (!direccion) {
                    e.preventDefault();
                    alert('❌ Para pedidos de Delivery, debe ingresar una dirección de entrega');
                    return false;
                }
            }
        }
        
        return true;
    });
});

// Exponer funciones globales
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
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush
@endsection