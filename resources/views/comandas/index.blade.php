@extends('layouts.app')

@section('title', 'Comandas de Cocina')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold" style="color: #C2410C;">
                    <i class="fas fa-kitchen-set mr-2"></i> Comandas de Cocina
                </h1>
                <p class="text-muted mt-1">Gestione los pedidos pendientes y en preparación</p>
            </div>
            <div>
                <button onclick="window.location.reload()" class="px-4 py-2 rounded-lg transition" style="background-color: #FED7AA; color: #C2410C;">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs de estados -->
    <div class="mb-6">
        <div class="border-b" style="border-color: #FED7AA;">
            <nav class="flex space-x-8">
                <button class="tab-btn py-2 px-1 font-medium transition-all" data-tab="todos" style="color: #C2410C; border-bottom: 2px solid #C2410C;">
                    <i class="fas fa-list mr-2"></i> Todos
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs" style="background-color: #FED7AA;">{{ $stats['total'] }}</span>
                </button>
                <button class="tab-btn py-2 px-1 font-medium transition-all" data-tab="pendiente" style="color: #78716C;">
                    <i class="fas fa-hourglass-half mr-2"></i> Pendientes
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs" style="background-color: #FEF3C7;">{{ $stats['pendientes'] }}</span>
                </button>
                <button class="tab-btn py-2 px-1 font-medium transition-all" data-tab="en_preparacion" style="color: #78716C;">
                    <i class="fas fa-fire mr-2"></i> En Preparación
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs" style="background-color: #DBEAFE;">{{ $stats['en_preparacion'] }}</span>
                </button>
                <button class="tab-btn py-2 px-1 font-medium transition-all" data-tab="listo" style="color: #78716C;">
                    <i class="fas fa-check-circle mr-2"></i> Listos
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs" style="background-color: #D1FAE5;">{{ $stats['listos'] }}</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Lista de Comandas -->
    <div id="comandas-container" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('comandas.partials.comanda-cards', ['comandas' => $comandas])
    </div>

    <div class="mt-6" id="pagination-container">
        {{ $comandas->links() }}
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tabs functionality
    const tabs = document.querySelectorAll('.tab-btn');
    const container = document.getElementById('comandas-container');
    const paginationContainer = document.getElementById('pagination-container');
    
    function loadComandas(tab) {
        // Mostrar loading
        container.innerHTML = `
            <div class="col-span-2 text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl" style="color: #C2410C;"></i>
                <p class="mt-2 text-muted">Cargando comandas...</p>
            </div>
        `;
        
        fetch(`{{ route('comandas.index') }}?estado=${tab}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            container.innerHTML = data.html;
            if (data.pagination) {
                paginationContainer.innerHTML = data.pagination;
            }
            // Re-inicializar eventos
            inicializarEventos();
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="col-span-2 text-center py-12">
                    <i class="fas fa-exclamation-circle text-4xl" style="color: #EF4444;"></i>
                    <p class="mt-2 text-muted">Error al cargar las comandas</p>
                </div>
            `;
        });
    }
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Actualizar estilos de tabs
            tabs.forEach(t => {
                t.style.color = '#78716C';
                t.style.borderBottom = 'none';
            });
            this.style.color = '#C2410C';
            this.style.borderBottom = '2px solid #C2410C';
            
            // Cargar comandas según el tab
            const tabValue = this.dataset.tab;
            loadComandas(tabValue);
        });
    });
    
    function inicializarEventos() {
        // Eventos para estado de detalles
        document.querySelectorAll('.estado-detalle').forEach(select => {
            select.addEventListener('change', function() {
                const detalleId = this.dataset.id;
                const nuevoEstado = this.value;
                
                fetch(`/comandas/detalle/${detalleId}/actualizar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ estado: nuevoEstado })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.mensaje, 'success');
                        setTimeout(() => location.reload(), 1000);
                    }
                });
            });
        });
        
        // Eventos para botones de acción
        document.querySelectorAll('.btn-iniciar').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('¿Iniciar preparación de este pedido?')) {
                    e.preventDefault();
                }
            });
        });
        
        document.querySelectorAll('.btn-listo').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('¿Marcar este pedido como listo?')) {
                    e.preventDefault();
                }
            });
        });
    }


    // Función para actualizar el contador del sidebar
    function actualizarContadorInventario() {
        fetch('/api/stock-count')
            .then(response => response.json())
            .then(data => {
                const contador = document.querySelector('.sidebar-inventario-counter');
                if (contador) {
                    if (data.count > 0) {
                        contador.textContent = data.count;
                        contador.classList.remove('hidden');
                    } else {
                        contador.classList.add('hidden');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = 'fixed top-20 right-4 z-50 text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in';
        notification.style.backgroundColor = type === 'success' ? '#10B981' : '#EF4444';
        notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${message}`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
    
    inicializarEventos();
});
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