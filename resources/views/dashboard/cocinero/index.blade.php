@extends('layouts.app')


@section('content')
<div class="min-h-screen px-4 py-6 bg-gradient-to-br from-amber-50/30 via-orange-50/20 to-rose-50/30 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <!-- Encabezado mejorado con temática de cocina -->
        <div class="relative mb-8 overflow-hidden bg-white shadow-xl rounded-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-primary/5 via-secondary/5 to-accent/5"></div>
            <div class="absolute top-0 right-0 w-64 h-64 translate-x-32 -translate-y-32 rounded-full bg-gradient-to-br from-primary/10 to-transparent blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full bg-gradient-to-tr from-accent/5 to-transparent blur-2xl"></div>
            <div class="relative px-6 py-8 sm:px-8 sm:py-10">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <div class="p-2 shadow-lg bg-gradient-to-br from-primary to-secondary rounded-xl">
                                <i class="text-2xl text-white fas fa-utensil-spoon"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold tracking-tight text-transparent bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text sm:text-4xl">
                                    Dashboard Cocinero
                                </h1>
                                <p class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                                    <i class="text-xs text-orange-500 fas fa-fire"></i>
                                    Panel de control de cocina y preparación
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <div class="flex items-center gap-3 px-4 py-2 border border-gray-100 shadow-sm bg-gray-50/80 backdrop-blur-sm rounded-2xl">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-xl">
                                <i class="text-xs text-white fas fa-calendar-alt"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium tracking-wider text-gray-500 uppercase">Fecha actual</p>
                                <p class="text-sm font-semibold text-gray-800">{{ now()->format('l, d F Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Badges de estado de cocina -->
                <div class="flex flex-wrap gap-2 mt-6">
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-full text-primary bg-primary/10 backdrop-blur-sm">
                        <i class="text-xs fas fa-chart-line"></i>
                        Cocina activa
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-full text-secondary bg-secondary/10 backdrop-blur-sm">
                        <i class="text-xs fas fa-clock"></i>
                        Turno: {{ now()->format('H:i') }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-full text-accent bg-accent/10 backdrop-blur-sm">
                        <i class="text-xs fas fa-utensils"></i>
                        Preparaciones en curso: <span id="count-preparacion">{{ $stats['en_preparacion'] }}</span>
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-full text-amber-600 bg-amber-100 backdrop-blur-sm">
                        <i class="text-xs fas fa-hourglass-half"></i>
                        Pendientes: <span id="count-pendiente">{{ $stats['pendientes'] }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Secciones principales con temática de cocina -->
        <div class="grid grid-cols-1 gap-12">
            <!-- Ventas Recientes - Preparaciones -->
            <div class="overflow-hidden transition-all duration-300 bg-white shadow-xl rounded-2xl hover:shadow-2xl">
                <div class="relative px-6 py-5 border-b border-gray-100">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/3 to-transparent"></div>
                    <div class="relative flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-1 h-6 rounded-full bg-gradient-to-b from-primary to-secondary"></div>
                                <h2 class="text-lg font-bold text-gray-800">Preparaciones Recientes</h2>
                            </div>
                            <p class="text-xs text-gray-500">Últimos pedidos en la cocina</p>
                        </div>
                        <a href="{{ route('comandas.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium transition-all duration-200 group bg-gray-50 text-primary rounded-xl hover:bg-primary hover:text-white hover:shadow-md">
                            <span>Ver todas</span>
                            <i class="text-xs transition-transform fas fa-arrow-right group-hover:translate-x-1"></i>
                        </a>
                    </div>
                </div>
                <div class="p-8">
                    <div id="comandas-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @include('comandas.partials.comanda-cards', ['comandas' => $comandas, 'soloPlatos' => true])
                    </div>
                    
                    <div class="mt-6" id="pagination-container">
                        {{ $comandas->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección adicional: Estado de Cocina y Preparaciones -->
        <div class="mt-6">
            <div class="overflow-hidden transition-all duration-300 bg-white shadow-xl rounded-2xl hover:shadow-2xl">
                <div class="relative px-6 py-5 border-b border-gray-100">
                    <div class="absolute inset-0 bg-gradient-to-r from-accent/3 to-transparent"></div>
                    <div class="relative flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-1 h-6 rounded-full bg-gradient-to-b from-accent to-amber-500"></div>
                                <h2 class="text-lg font-bold text-gray-800">Estado de Cocina</h2>
                            </div>
                            <p class="text-xs text-gray-500">Monitoreo en tiempo real de la producción</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="refreshComandas()" class="p-2 text-gray-400 transition-colors bg-gray-50 rounded-xl hover:bg-gray-100 hover:text-gray-600">
                                <i class="text-xs fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div class="p-4 text-center bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl">
                            <div class="inline-flex items-center justify-center w-12 h-12 mb-3 bg-green-100 rounded-xl">
                                <i class="text-xl text-green-600 fas fa-check-circle"></i>
                            </div>
                            <p class="text-xs tracking-wider text-gray-500 uppercase">Completados (Listos)</p>
                            <p class="mt-1 text-2xl font-bold text-gray-800" id="stat-listo">{{ $stats['listos'] }}</p>
                            <p class="mt-1 text-xs text-green-600">Pedidos listos para entrega</p>
                        </div>
                        <div class="p-4 text-center bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl">
                            <div class="inline-flex items-center justify-center w-12 h-12 mb-3 bg-amber-100 rounded-xl">
                                <i class="text-xl text-amber-600 fas fa-clock"></i>
                            </div>
                            <p class="text-xs tracking-wider text-gray-500 uppercase">En Preparación</p>
                            <p class="mt-1 text-2xl font-bold text-gray-800" id="stat-en_preparacion">{{ $stats['en_preparacion'] }}</p>
                            <p class="mt-1 text-xs text-amber-600">En proceso actualmente</p>
                        </div>
                        <div class="p-4 text-center bg-gradient-to-br from-red-50 to-rose-50 rounded-2xl">
                            <div class="inline-flex items-center justify-center w-12 h-12 mb-3 bg-red-100 rounded-xl">
                                <i class="text-xl text-red-600 fas fa-hourglass-half"></i>
                            </div>
                            <p class="text-xs tracking-wider text-gray-500 uppercase">Pendientes</p>
                            <p class="mt-1 text-2xl font-bold text-gray-800" id="stat-pendiente">{{ $stats['pendientes'] }}</p>
                            <p class="mt-1 text-xs text-red-600">Esperando preparación</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de alertas de inventario para cocina -->
        <div class="mt-6">
            <div class="overflow-hidden transition-all duration-300 border shadow-xl bg-gradient-to-r from-amber-50/50 to-orange-50/50 backdrop-blur-sm rounded-2xl border-amber-200">
                <div class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-amber-100 rounded-xl">
                            <i class="text-amber-600 fas fa-exclamation-circle"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-amber-800">Alertas de Inventario</h3>
                            <p class="text-xs text-amber-600">Stock bajo detectado en ingredientes clave</p>
                        </div>
                        <a href="{{ route('inventario.index') }}" class="text-xs font-medium text-amber-700 hover:text-amber-900">
                            Ver inventario →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function refreshComandas() {
        const container = document.getElementById('comandas-container');
        const paginationContainer = document.getElementById('pagination-container');
        
        fetch('{{ route("comandas.index") }}?ajax=1&soloPlatos=1&soloHoy=1&soloPendientes=1', {
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
            if (data.stats) {
                updateStats(data.stats);
            }
            inicializarEventos();
        })
        .catch(error => console.error('Error refreshing comandas:', error));
    }

    function updateStats(stats) {
        // Actualizar badges superiores
        const countPreparacion = document.getElementById('count-preparacion');
        const countPendiente = document.getElementById('count-pendiente');
        if (countPreparacion) countPreparacion.textContent = stats.en_preparacion;
        if (countPendiente) countPendiente.textContent = stats.pendientes;

        // Actualizar sección de estado de cocina
        const statListo = document.getElementById('stat-listo');
        const statEnPreparacion = document.getElementById('stat-en_preparacion');
        const statPendiente = document.getElementById('stat-pendiente');
        if (statListo) statListo.textContent = stats.listos;
        if (statEnPreparacion) statEnPreparacion.textContent = stats.en_preparacion;
        if (statPendiente) statPendiente.textContent = stats.pendientes;

        // Actualizar badge del dashboard header
        const badgePreparaciones = document.querySelector('.inline-flex.items-center.gap-2.px-3.py-1.text-xs.font-medium.rounded-full.text-accent.bg-accent\\/10.backdrop-blur-sm');
        if (badgePreparaciones) {
            badgePreparaciones.innerHTML = `<i class="text-xs fas fa-utensils"></i> Preparaciones en curso: ${stats.en_preparacion}`;
        }
        const badgePendientes = document.querySelector('.inline-flex.items-center.gap-2.px-3.py-1.text-xs.font-medium.rounded-full.text-amber-600.bg-amber-100.backdrop-blur-sm');
        if (badgePendientes) {
            badgePendientes.innerHTML = `<i class="text-xs fas fa-hourglass-half"></i> Pendientes: ${stats.pendientes}`;
        }
    }

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
                        refreshComandas(); // Recargar cards y stats sin recargar página
                    }
                });
            });
        });
        
        // Eventos para botones de acción (convertir forms a AJAX para evitar recarga)
        document.querySelectorAll('.comanda-card form').forEach(form => {
            form.onsubmit = function(e) {
                e.preventDefault();
                const action = this.action;
                const formData = new FormData(this);
                
                if (this.querySelector('.btn-iniciar') && !confirm('¿Iniciar preparación de este pedido?')) return;
                if (this.querySelector('.btn-listo') && !confirm('¿Marcar este pedido como listo?')) return;

                fetch(action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    // Como los métodos de ComandaController redirigen, 
                    // simplemente recargamos las comandas vía AJAX
                    refreshComandas();
                    showNotification('Operación realizada con éxito', 'success');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al procesar la solicitud', 'error');
                });
            };
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        inicializarEventos(); // Inicializar para la carga inicial
        
        if (typeof window.Echo !== 'undefined') {
            console.log('🔌 Escuchando pedidos.cocineros para actualizaciones en tiempo real');
            
            window.Echo.channel('pedidos.cocineros')
                .listen('.pedido.creado', (e) => {
                    console.log('🍳 Nuevo pedido recibido:', e);
                    showNotification(e.mensaje, 'success');
                    refreshComandas();
                    playNotificationSound();
                })
                .listen('.pedido.estado_actualizado', (e) => {
                    console.log('🔄 Estado de pedido actualizado:', e);
                    refreshComandas();
                });
        }
    });

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = 'fixed top-20 right-4 z-[100] text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in';
        notification.style.backgroundColor = type === 'success' ? '#10B981' : '#EF4444';
        notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${message}`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }

    function playNotificationSound() {
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        audio.play().catch(e => console.log('Error al reproducir sonido:', e));
    }
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
