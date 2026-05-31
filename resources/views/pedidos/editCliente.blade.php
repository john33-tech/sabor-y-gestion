@extends('layouts.app')

@section('title', 'Editar Pedido #' . $pedido->numero_pedido)

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold" style="color: #C2410C;">
                    <i class="fas fa-edit mr-2"></i> Editar Pedido #{{ $pedido->numero_pedido }}
                </h1>
                <p class="text-muted mt-1">Seleccione los platos y complete la información del pedido</p>
            </div>
            <div class="px-4 py-2 rounded-lg font-semibold" style="background-color: #FED7AA; color: #C2410C;">
                <i class="fas fa-receipt mr-2"></i> {{ $pedido->numero_pedido }}
            </div>
        </div>
    </div>

    <form action="{{ route('pedidos.update.cliente', $pedido)}}" method="POST" id="pedidoForm">
        @csrf
        @method('PUT')

        <!-- IMPORTANTE -->
        <input type="hidden" name="tipo_pedido" value="{{ $pedido->tipo_pedido }}">
        
        @if($pedido->tipo_pedido == 'mesa')
            <input type="hidden" name="mesa_id" value="{{ $pedido->mesa_id }}">
        @endif

        <input type="hidden" name="cliente_nombre" value="{{ $pedido->cliente_nombre }}">
        <input type="hidden" name="cliente_telefono" value="{{ $pedido->cliente_telefono }}">
        <input type="hidden" name="direccion" value="{{ $pedido->direccion }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Panel Izquierdo - Platos -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md" style="border: 1px solid #FED7AA;">
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

                    <div class="p-4 max-h-[500px] overflow-y-auto" id="platosContainer" style="background-color: #FFFFFF;">
                        @forelse($platos as $categoria => $platosCategoria)
                            <div class="categoria-group mb-6" data-categoria="{{ $categoria }}">
                                <h3 class="font-bold mb-3 border-l-4 pl-2" style="color: #C2410C; border-color: #C2410C;">
                                    {{ $categoria }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($platosCategoria as $plato)
                                        @php
                                            $tieneStock = $plato->tiene_stock ?? $plato->verificarStock(1);
                                            $stockInsuficiente = $plato->stock_insuficiente ?? [];
                                            
                                            if (!$tieneStock && empty($stockInsuficiente)) {
                                                foreach ($plato->ingredientes as $ingrediente) {
                                                    $inventario = $ingrediente->inventario;
                                                    $cantidadNecesaria = $ingrediente->pivot->cantidad;
                                                    
                                                    if (!$inventario || $inventario->cantidad_actual < $cantidadNecesaria) {
                                                        $stockInsuficiente[] = [
                                                            'nombre' => $ingrediente->nombre,
                                                            'disponible' => $inventario?->cantidad_actual ?? 0,
                                                            'necesario' => $cantidadNecesaria,
                                                            'unidad' => $ingrediente->unidad_medida
                                                        ];
                                                    }
                                                }
                                            }
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
                <div class="bg-white rounded-lg shadow-md sticky top-4" style="border: 1px solid #FED7AA;">
                    <div class="p-4 border-b rounded-t-lg" style="background-color: #FFF7ED; border-color: #FED7AA;">
                        <h2 class="text-xl font-semibold" style="color: #C2410C;">
                            <i class="fas fa-shopping-cart mr-2"></i> Carrito de Pedido
                        </h2>
                    </div>

                    <div class="p-4" style="background-color: #FFFFFF;">

                        @if($pedido->tipo_pedido == 'delivery')
                        <!-- MAPA Delivery -->
                        <div id="delivery-map-container" class="mb-6">
                            <label class="block text-sm font-medium mb-2 mt-4" style="color: #111827;">
                                <i class="fas fa-map mr-1"></i> Editar ubicación de entrega
                            </label>
                            <div id="map" style="height: 350px;" class="rounded-lg border z-0"></div>
                            <input type="hidden" name="latitud" id="latitud" value="{{ $pedido->latitud }}">
                            <input type="hidden" name="longitud" id="longitud" value="{{ $pedido->longitud }}">
                            <div class="mt-2 text-xs text-gray-500">Haz clic en el mapa para actualizar tu ubicación.</div>
                        </div>
                        @endif

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

                        <!-- Notas -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                <i class="fas fa-sticky-note mr-1"></i> Notas Adicionales
                            </label>
                            <textarea name="notas" rows="2"
                                class="w-full px-3 py-2 rounded-lg outline-none transition-all"
                                style="border: 1px solid #FED7AA; background-color: #FFFFFF; color: #111827;">{{ $pedido->notas }}</textarea>
                        </div>

                        <!-- Descuento -->
                        <div class="mb-6 hidden">
                            <label class="block text-sm font-medium mb-1" style="color: #111827;">
                                <i class="fas fa-tag mr-1"></i> Descuento (Bs.)
                            </label>
                            <input type="number" name="descuento" value="{{ $pedido->descuento }}" step="0.01" min="0"
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
                                <i class="fas fa-save mr-1"></i> Actualizar Pedido
                            </button>
                            <a href="{{ route('pedidos.show', $pedido) }}" class="flex-1 text-white py-2 rounded-lg transition text-center flex items-center justify-center gap-2 hover:opacity-90"
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

@push('scripts')
<script>
let items = [];
let searchTimeout;

// Cargar items existentes
@foreach($pedido->detalles as $detalle)
items.push({
    plato_id: {{ $detalle->plato_id }},
    nombre: '{{ addslashes($detalle->plato->nombre) }}',
    precio_unitario: {{ $detalle->precio_unitario }},
    cantidad: {{ $detalle->cantidad }},
    notas: '{{ addslashes($detalle->notas) }}'
});
@endforeach

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
    // IVA eliminado: el total es subtotal - descuento.
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
    
    // Evento para descuento
    document.querySelector('input[name="descuento"]').addEventListener('input', updateTotals);
    
    // Advertencia si cambia el estado
    document.getElementById('estadoPedido')?.addEventListener('change', function() {
        const nuevoEstado = this.options[this.selectedIndex].text;
        const estadoActual = '{{ $pedido->estado }}';
        
        if (estadoActual !== this.value) {
            if (!confirm(`¿Cambiar el estado del pedido a "${nuevoEstado}"?\n\nEsto puede afectar el inventario si el pedido ya estaba listo o entregado.`)) {
                this.value = '{{ $pedido->estado }}';
            }
        }
    });
    
    // Validación del formulario
    document.getElementById('pedidoForm').addEventListener('submit', async function(e) {
        if (items.length === 0) {
            e.preventDefault();
            showNotification('❌ Debe agregar al menos un plato al pedido', 'error');
            return false;
        }
        
        e.preventDefault();
        
        const submitBtn = this.querySelector('[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Verificando stock...';
        submitBtn.disabled = true;
        
        let stockInsuficiente = [];
        for (const item of items) {
            try {
                const response = await fetch('/api/verificar-stock-plato', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ plato_id: item.plato_id, cantidad: item.cantidad })
                });
                const data = await response.json();
                if (!data.disponible) {
                    stockInsuficiente.push(`${item.nombre} (x${item.cantidad}) - ${data.mensaje}`);
                }
            } catch (error) { console.error(error); }
        }
        
        if (stockInsuficiente.length > 0) {
            showNotification('❌ Stock insuficiente:\n' + stockInsuficiente.join('\n'), 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return false;
        }
        
        this.submit();
    });
    
    renderItems();
});

@if($pedido->tipo_pedido == 'delivery')
    let map = L.map('map').setView([{{ $pedido->latitud ?? -16.5000 }}, {{ $pedido->longitud ?? -68.1500 }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = L.marker([{{ $pedido->latitud ?? -16.5000 }}, {{ $pedido->longitud ?? -68.1500 }}]).addTo(map);

    map.on('click', function(e) {
        document.getElementById('latitud').value = e.latlng.lat;
        document.getElementById('longitud').value = e.latlng.lng;
        if(marker) map.removeLayer(marker);
        marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
    });
@endif

// Funciones globales
window.removeItem = removeItem;
window.updateItemQuantity = updateItemQuantity;
window.updateItemNotes = updateItemNotes;
</script>
@endpush
@endsection