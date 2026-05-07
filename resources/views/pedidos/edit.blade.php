@extends('layouts.app')

@section('title', 'Editar Pedido #' . $pedido->numero_pedido)

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex justify-between items-center">

        <h1 class="text-3xl font-bold text-primary">
            <i class="fas fa-edit mr-2"></i>
            Editar Pedido #{{ $pedido->numero_pedido }}
        </h1>

        <a href="{{ route('pedidos.show', $pedido) }}"
           class="btn-gray">

            <i class="fas fa-arrow-left mr-2"></i>
            Volver

        </a>

    </div>

    <!-- Errores -->
    @if($errors->any())

        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">

            <ul class="list-disc pl-5">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <!-- Formulario -->
    <form action="{{ route('pedidos.update', $pedido) }}"
          method="POST"
          id="pedidoForm">

        @csrf
        @method('PUT')

        <!-- IMPORTANTE -->
        <input type="hidden"
               name="tipo_pedido"
               value="{{ $pedido->tipo_pedido }}">

        <!-- Mesa -->
        @if($pedido->tipo_pedido == 'mesa')

            <input type="hidden"
                   name="mesa_id"
                   value="{{ $pedido->mesa_id }}">

        @endif

        <!-- Cliente -->
        <input type="hidden"
               name="cliente_nombre"
               value="{{ $pedido->cliente_nombre }}">

        <input type="hidden"
               name="cliente_telefono"
               value="{{ $pedido->cliente_telefono }}">

        <input type="hidden"
               name="direccion"
               value="{{ $pedido->direccion }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Panel Platos -->
            <div class="lg:col-span-2">

                <div class="card">

                    <div class="mb-4">

                        <h2 class="text-xl font-semibold">

                            <i class="fas fa-utensils mr-2 text-primary"></i>
                            Seleccionar Platos

                        </h2>

                        <div class="relative mt-3">

                            <input type="text"
                                   id="buscarPlato"
                                   placeholder="Buscar plato..."
                                   class="w-full px-4 py-2 pl-10 rounded-lg border">

                            <i class="fas fa-search absolute left-3 top-3 text-muted"></i>

                        </div>

                    </div>

                    <div class="max-h-[500px] overflow-y-auto space-y-6">

                        @foreach($platos as $categoria => $platosCategoria)

                            <div>

                                <h3 class="font-semibold mb-3 border-b pb-1">
                                    {{ $categoria }}
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                    @foreach($platosCategoria as $plato)

                                        <div class="plato-item border rounded-xl p-3 hover:shadow-lg cursor-pointer bg-white"
                                             data-id="{{ $plato->id }}"
                                             data-nombre="{{ $plato->nombre }}"
                                             data-precio="{{ $plato->precio }}">

                                            <div class="flex justify-between items-start">

                                                <div>

                                                    <h4 class="font-semibold">
                                                        {{ $plato->nombre }}
                                                    </h4>

                                                    <p class="text-sm text-primary font-bold">
                                                        ${{ number_format($plato->precio, 2) }}
                                                    </p>

                                                </div>

                                                <button type="button"
                                                        class="agregar-plato bg-blue-500 text-white px-3 py-1 rounded text-sm">

                                                    +

                                                </button>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

            <!-- Panel Pedido -->
            <div>

                <div class="card sticky top-4">

                    <h2 class="text-xl font-semibold mb-4">
                        Items del Pedido
                    </h2>

                    <div id="itemsList"
                         class="space-y-2 max-h-96 overflow-y-auto mb-4">

                    </div>

                    <!-- Notas -->
                    <textarea name="notas"
                              rows="2"
                              placeholder="Notas adicionales..."
                              class="input mb-3">{{ $pedido->notas }}</textarea>

                    <!-- Descuento -->
                    <input type="number"
                           name="descuento"
                           value="{{ $pedido->descuento }}"
                           step="0.01"
                           min="0"
                           class="input mb-4">

                    <!-- Totales -->
                    <div class="border-t pt-3 space-y-1 text-sm">

                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span id="subtotal">$0.00</span>
                        </div>

                        <div class="flex justify-between">
                            <span>IVA</span>
                            <span id="impuesto">$0.00</span>
                        </div>

                        <div class="flex justify-between">
                            <span>Descuento</span>
                            <span id="descuentoDisplay">$0.00</span>
                        </div>

                        <div class="flex justify-between font-bold text-lg text-primary">
                            <span>Total</span>
                            <span id="total">$0.00</span>
                        </div>

                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 mt-4">

                        <button type="submit"
                                class="btn-success w-full">

                            <i class="fas fa-save mr-1"></i>
                            Actualizar

                        </button>

                        <a href="{{ route('pedidos.show', $pedido) }}"
                           class="btn-secondary w-full text-center">

                            Cancelar

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>

<style>
.card {
    @apply bg-white rounded-xl shadow-lg p-4;
}

.input {
    @apply w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20;
}

.btn-success {
    @apply bg-green-600 text-white rounded-lg hover:bg-green-700 transition py-2;
}

.btn-secondary {
    @apply bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition py-2;
}

.btn-gray {
    @apply bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition inline-flex items-center;
}

.text-muted {
    @apply text-gray-500;
}
</style>

@push('scripts')

<script>

let items = [];

// Cargar items existentes
@foreach($pedido->detalles as $detalle)

items.push({
    plato_id: {{ $detalle->plato_id }},
    nombre: '{{ $detalle->plato->nombre }}',
    precio_unitario: {{ $detalle->precio_unitario }},
    cantidad: {{ $detalle->cantidad }},
    notas: '{{ addslashes($detalle->notas) }}'
});

@endforeach

function renderItems(){

    const container = document.getElementById('itemsList');

    if(!items.length){

        container.innerHTML = `
            <div class="text-muted text-center py-4">
                No hay items
            </div>
        `;

        updateTotals();

        return;
    }

    container.innerHTML = items.map((item, index) => `

        <div class="border rounded-lg p-3 bg-gray-50">

            <div class="flex justify-between items-start mb-2">

                <div>

                    <span class="font-semibold">
                        ${item.nombre}
                    </span>

                    <span class="text-sm text-muted ml-2">
                        $${item.precio_unitario.toFixed(2)}
                    </span>

                </div>

                <button type="button"
                        onclick="removeItem(${index})"
                        class="text-red-500 hover:text-red-700">

                    <i class="fas fa-trash"></i>

                </button>

            </div>

            <div class="flex items-center gap-3">

                <label class="text-sm">
                    Cantidad:
                </label>

                <input type="number"
                       value="${item.cantidad}"
                       min="1"
                       class="w-20 px-2 py-1 border rounded text-center"
                       onchange="updateItem(${index}, this.value)">

                <input type="text"
                       placeholder="Notas..."
                       value="${item.notas}"
                       class="flex-1 px-2 py-1 border rounded text-sm"
                       onblur="updateNotas(${index}, this.value)">

            </div>

            <!-- INPUTS IMPORTANTES -->

            <input type="hidden"
                   name="items[${index}][plato_id]"
                   value="${item.plato_id}">

            <input type="hidden"
                   name="items[${index}][cantidad]"
                   value="${item.cantidad}">

            <input type="hidden"
                   name="items[${index}][notas]"
                   value="${item.notas}">

        </div>

    `).join('');

    updateTotals();
}

function removeItem(index){

    items.splice(index, 1);

    renderItems();
}

function updateItem(index, newCantidad){

    items[index].cantidad = parseInt(newCantidad);

    renderItems();
}

function updateNotas(index, notas){

    items[index].notas = notas;

    renderItems();
}

function updateTotals(){

    let subtotal = items.reduce(
        (sum, item) => sum + (item.precio_unitario * item.cantidad),
        0
    );

    let descuento = parseFloat(
        document.querySelector('[name="descuento"]').value
    ) || 0;

    let impuesto = subtotal * 0.19;

    let total = subtotal + impuesto - descuento;

    document.getElementById('subtotal').textContent =
        `$${subtotal.toFixed(2)}`;

    document.getElementById('impuesto').textContent =
        `$${impuesto.toFixed(2)}`;

    document.getElementById('descuentoDisplay').textContent =
        `$${descuento.toFixed(2)}`;

    document.getElementById('total').textContent =
        `$${total.toFixed(2)}`;
}

// Agregar plato
document.querySelectorAll('.agregar-plato').forEach(btn => {

    btn.addEventListener('click', function(e){

        e.stopPropagation();

        const p = this.closest('.plato-item');

        const id = p.dataset.id;

        const nombre = p.dataset.nombre;

        const precio = parseFloat(p.dataset.precio);

        let item = items.find(i => i.plato_id == id);

        if(item){

            item.cantidad++;

        } else {

            items.push({
                plato_id: id,
                nombre: nombre,
                precio_unitario: precio,
                cantidad: 1,
                notas: ''
            });
        }

        renderItems();
    });
});

// Descuento
document.querySelector('[name="descuento"]')
    .addEventListener('input', updateTotals);

// Buscar platos
document.getElementById('buscarPlato')
    .addEventListener('input', function(e){

        let searchTerm = e.target.value.toLowerCase();

        document.querySelectorAll('.plato-item').forEach(item => {

            let nombre = item.dataset.nombre.toLowerCase();

            item.style.display =
                nombre.includes(searchTerm)
                    ? ''
                    : 'none';
        });
});

// Validar formulario
document.getElementById('pedidoForm')
    .addEventListener('submit', function(e){

        if(items.length === 0){

            e.preventDefault();

            alert('Debe agregar al menos un plato al pedido');
        }
});

renderItems();

</script>

@endpush

@endsection