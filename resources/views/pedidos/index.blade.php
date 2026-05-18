@extends('layouts.app')

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-primary">
            <i class="fas fa-receipt mr-2"></i> Pedidos
        </h1>
        <a href="{{ route('pedidos.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 shadow-sm">
            <i class="fas fa-plus mr-2"></i> Nuevo Pedido
        </a>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-400 p-4 rounded-lg shadow text-white">
            <p class="text-sm">Pendientes</p>
            <p class="text-2xl font-bold">{{ $pedidos->where('estado','pendiente')->count() }}</p>
        </div>

        <div class="bg-gradient-to-br from-blue-600 to-blue-500 p-4 rounded-lg shadow text-white">
            <p class="text-sm">En preparación</p>
            <p class="text-2xl font-bold">{{ $pedidos->where('estado','en_preparacion')->count() }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-600 to-green-500 p-4 rounded-lg shadow text-white">
            <p class="text-sm">Listos</p>
            <p class="text-2xl font-bold">{{ $pedidos->where('estado','listo')->count() }}</p>
        </div>

        <div class="bg-gradient-to-br from-purple-600 to-purple-500 p-4 rounded-lg shadow text-white">
            <p class="text-sm">Facturados</p>
            <p class="text-2xl font-bold">{{ $pedidos->where('estado','facturado')->count() }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('pedidos.index') }}" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-info-circle mr-1 text-primary"></i> Estado
                    </label>
                    <select name="estado" id="estadoFilter"
                        class="w-full px-4 py-2 pl-10 rounded-lg border border-border focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">Todos</option>
                        @foreach($estados as $key => $label)
                            <option value="{{ $key }}" {{ request('estado') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-tags mr-1 text-primary"></i> Tipo
                    </label>
                    <select name="tipo_pedido" id="tipoFilter"
                        class="w-full px-4 py-2 pl-10 rounded-lg border border-border focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">Todos</option>
                        @foreach($tipos as $key => $label)
                            <option value="{{ $key }}" {{ request('tipo_pedido') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Mesa -->
                <div>
                    <label class="block text-sm font-medium text-text mb-2">
                        <i class="fas fa-chair mr-1 text-primary"></i> Mesa
                    </label>
                    <select name="mesa_id"
                        class="w-full px-4 py-2 pl-10 rounded-lg border border-border focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">Todas</option>
                        @foreach($mesas as $mesa)
                            <option value="{{ $mesa->id }}" {{ request('mesa_id') == $mesa->id ? 'selected' : '' }}>
                                Mesa {{ $mesa->numero_mesa }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botón -->
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-border bg-background">
                        <th class="py-3 px-4 text-left">Pedido</th>
                        <th class="py-3 px-4 text-left">Tipo</th>
                        <th class="py-3 px-4 text-left">Cliente/Mesa</th>
                        <th class="py-3 px-4 text-left">Total</th>
                        <th class="py-3 px-4 text-left">Estado</th>
                        <th class="py-3 px-4 text-left">Fecha</th>
                        <th class="py-3 px-4 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                    <tr class="border-b border-border hover:bg-background transition">

                        <td class="py-3 px-4 font-semibold">
                            #{{ $pedido->numero_pedido }}
                        </td>

                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $pedido->tipo_pedido == 'mesa' ? 'bg-green-100 text-green-800' :
                                   ($pedido->tipo_pedido == 'delivery' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ $tipos[$pedido->tipo_pedido] }}
                            </span>
                        </td>

                        <td class="py-3 px-4">
                            @if($pedido->tipo_pedido == 'mesa')
                                <div>
                                    <i class="fas fa-chair text-primary mr-1"></i>
                                    Mesa {{ $pedido->mesa->numero_mesa ?? 'N/A' }}
                                </div>
                            @else
                                <div>
                                    <i class="fas fa-user mr-1"></i> {{ $pedido->cliente_nombre }}
                                </div>
                                <div class="text-xs text-muted">
                                    {{ $pedido->cliente_telefono }}
                                </div>
                            @endif
                        </td>

                        <td class="py-3 px-4 font-bold text-primary">
                            ${{ number_format($pedido->total,2) }}
                        </td>

                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $pedido->estado == 'pendiente' ? 'bg-yellow-100 text-yellow-800' :
                                   ($pedido->estado == 'en_preparacion' ? 'bg-blue-100 text-blue-800' :
                                   ($pedido->estado == 'listo' ? 'bg-green-100 text-green-800' :
                                   'bg-gray-100 text-gray-800')) }}">
                                {{ $estados[$pedido->estado] }}
                            </span>
                        </td>

                        <td class="py-3 px-4 text-sm text-muted">
                            {{ $pedido->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="py-3 px-4">
                            <div class="flex space-x-3">
                                <a href="{{ route('pedidos.show',$pedido) }}" class="text-primary hover:text-secondary" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if(in_array($pedido->estado,['pendiente','en_preparacion']))
                                <a href="{{ route('pedidos.edit',$pedido) }}" class="text-yellow-600 hover:text-yellow-800" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif

                                <a href="{{ route('pedidos.imprimir',$pedido) }}" target="_blank" class="text-gray-600 hover:text-black" title="Imprimir">
                                    <i class="fas fa-print"></i>
                                </a>

                                {{-- Marcar entregado: visible para staff cuando el pedido está "listo" --}}
                                @auth
                                    @if($pedido->estado === 'listo' && in_array(auth()->user()->role, ['admin','mesero','cajero']))
                                        <form method="POST" action="{{ route('pedidos.cambiar-estado', $pedido) }}"
                                              class="inline"
                                              onsubmit="return confirm('¿Confirmas que entregaste el pedido {{ $pedido->numero_pedido }} a la mesa?');">
                                            @csrf
                                            <input type="hidden" name="estado" value="entregado">
                                            <button type="submit"
                                                    class="inline-flex items-center px-2 py-1 text-xs text-white bg-emerald-600 rounded hover:bg-emerald-700 transition"
                                                    title="Marcar como entregado">
                                                <i class="fas fa-check-double mr-1"></i> Entregado
                                            </button>
                                        </form>
                                    @endif
                                @endauth

                                {{-- Eliminar: solo si el pedido está pendiente (regla del controller) --}}
                                @if($pedido->estado === 'pendiente')
                                <form method="POST" action="{{ route('pedidos.destroy',$pedido) }}"
                                      class="inline form-eliminar-pedido"
                                      data-numero-pedido="{{ $pedido->numero_pedido }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-muted">
                            <i class="fas fa-box-open text-4xl mb-2 block"></i>
                            No hay pedidos registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $pedidos->links() }}
        </div>
    </div>
</div>

<style>
.btn-primary {
    @apply bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition inline-flex items-center;
}

.card {
    @apply bg-white rounded-lg shadow-lg p-4;
}

[x-cloak] { display:none !important; }
</style>

<!-- SweetAlert2 para confirmaciones bonitas (CDN, sin npm install) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // auto filtro
    const estado = document.getElementById('estadoFilter');
    const tipo = document.getElementById('tipoFilter');
    const form = document.getElementById('filterForm');

    if(estado) estado.addEventListener('change', () => form.submit());
    if(tipo) tipo.addEventListener('change', () => form.submit());

    // Modal SweetAlert2 para eliminar pedido
    document.querySelectorAll('.form-eliminar-pedido').forEach(formEl => {
        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            const numero = formEl.dataset.numeroPedido || '';

            Swal.fire({
                title: `¿Eliminar pedido #${numero}?`,
                text: 'Esta acción no se puede deshacer. La mesa quedará liberada.',
                icon: 'warning',
                iconColor: '#dc2626',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                    formEl.submit();
                }
            });
        });
    });

});
</script>

@endsection