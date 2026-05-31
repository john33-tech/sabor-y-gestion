@php
    $user = Auth::user();
    $role = $user ? $user->role : null;
@endphp


@php
    use Illuminate\Support\Facades\Cache;

    $lowStockCount = Cache::remember('low_stock_count_direct', 300, function() {
        return \App\Models\Inventario::with('ingrediente')
            ->get()
            ->filter(fn($inv) => $inv->isLowStock())
            ->count();
    });
@endphp


<aside class="flex flex-col h-full text-white transition-all duration-300 ease-in-out shadow-xl bg-primary"
       :class="{
           'w-72': sidebarExpanded,
           'w-20': !sidebarExpanded && windowWidth >= 1024,
           'w-72': mobileSidebarOpen && windowWidth < 1024
       }">

    <!-- Logo compacto -->
    <div class="flex items-center justify-between p-4 border-b sm:p-5 border-white/10">
        <div class="flex items-center gap-2 overflow-hidden sm:gap-3">
            <div class="flex-shrink-0">
                <img src="{{ asset('logo.png') }}" alt="SaborGestion Logo"
                     class="object-contain w-10 h-10 rounded-full sm:w-12 sm:h-12">
            </div>
            <div x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 class="whitespace-nowrap">
                <h1 class="text-lg font-bold text-white sm:text-xl">Sabor Gestión</h1>
                <p class="text-[10px] sm:text-xs text-white/70">Sistema de Gestión</p>
            </div>
        </div>
    </div>

    <!-- Navegación principal -->
    <nav class="flex-1 px-2 py-4 overflow-x-hidden overflow-y-auto sm:py-6 sm:px-3 custom-scrollbar">
        <div class="space-y-1">

            <!-- Inteligencia de Negocios -->
            @if(in_array($role, ['admin', 'mesero', 'cocinero', 'cajero', 'cliente']))
            <div x-data="{
                open: localStorage.getItem('sidebar_section_inteligencia') === 'true',
                toggle() {
                    if (window.innerWidth >= 1024) {
                        this.open = !this.open;
                        localStorage.setItem('sidebar_section_inteligencia', this.open);
                    } else {
                        this.open = !this.open;
                        localStorage.setItem('sidebar_section_inteligencia', this.open);
                    }
                }
            }"
            x-init="() => {
                if (localStorage.getItem('sidebar_section_inteligencia') === null) {
                    open = false;
                    localStorage.setItem('sidebar_section_inteligencia', false);
                }
            }"
            class="mb-1">
                <button @click="toggle()"
                        class="w-full flex items-center justify-between px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200 hover:bg-white/10 group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <i class="w-5 text-base transition-colors fas fa-chart-line text-white/80 sm:text-lg group-hover:text-white"></i>
                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                              x-transition.duration.200
                              class="text-xs font-medium sm:text-sm text-white/80 group-hover:text-white whitespace-nowrap">
                            Int. de Neg. BI
                        </span>
                    </div>
                    <i x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                       :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
                       class="text-xs transition-transform duration-200 fas text-white/50"></i>
                </button>

                <div x-show="open"
                     x-collapse
                     x-cloak
                     class="mt-1 ml-2 space-y-1 sm:ml-3">
                    @if($role == 'admin')
                        <a href="{{ route('dashboard.administrador') }}"
                           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                            <i class="fas fa-chart-pie text-[10px] sm:text-xs w-4"></i>
                            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Dashboard Admin</span>
                        </a>
                    @endif
                    @if($role == 'mesero')
                        <a href="{{ route('dashboard.mesero') }}"
                           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                            <i class="fas fa-chart-simple text-[10px] sm:text-xs w-4"></i>
                            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Dashboard Mesero</span>
                        </a>
                    @endif
                    @if($role == 'cocinero')
                        <a href="{{ route('dashboard.cocinero') }}"
                           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                            <i class="fas fa-chart-line text-[10px] sm:text-xs w-4"></i>
                            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Dashboard Cocinero</span>
                        </a>
                    @endif
                    @if($role == 'cajero')
                        <a href="{{ route('dashboard.cajero') }}"
                           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                            <i class="fas fa-chart-bar text-[10px] sm:text-xs w-4"></i>
                            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Dashboard Cajero</span>
                        </a>
                    @endif
                    @if($role == 'cliente')
                        <a href="{{ route('dashboard.cliente') }}"
                           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                            <i class="fas fa-chart-pie text-[10px] sm:text-xs w-4"></i>
                            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Dashboard Cliente</span>
                        </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Catálogo y Menú -->
            @if(in_array($role, ['admin', 'cocinero']))
            <div x-data="{
                open: localStorage.getItem('sidebar_section_catalogo') === 'true',
                toggle() {
                    this.open = !this.open;
                    localStorage.setItem('sidebar_section_catalogo', this.open);
                }
            }"
            x-init="() => {
                if (localStorage.getItem('sidebar_section_catalogo') === null) {
                    open = false;
                    localStorage.setItem('sidebar_section_catalogo', false);
                }
            }"
            class="mb-1">
                <button @click="toggle()"
                        class="w-full flex items-center justify-between px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200 hover:bg-white/10 group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <i class="w-5 text-base transition-colors fas fa-book-open text-white/80 sm:text-lg group-hover:text-white"></i>
                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                              x-transition.duration.200
                              class="text-xs font-medium sm:text-sm text-white/80 group-hover:text-white whitespace-nowrap">
                            Catálogo y Menú
                        </span>
                    </div>
                    <i x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                       :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
                       class="text-xs transition-transform duration-200 fas text-white/50"></i>
                </button>

                <div x-show="open"
                     x-collapse
                     x-cloak
                     class="mt-1 ml-2 space-y-1 sm:ml-3">
                    <a href="{{ route('platos.index') }}"
                       class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                        <i class="fas fa-utensils text-[10px] sm:text-xs w-4"></i>
                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Productos</span>
                    </a>
                </div>
                <div x-show="open"
                    x-collapse
                    x-cloak
                    class="mt-1 ml-2 space-y-1 sm:ml-3">
                    <a href="{{ route('categorias.index') }}"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">

                        <i class="fas fa-tags text-[10px] sm:text-xs w-4"></i>

                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                            class="whitespace-nowrap">
                            Categorías
                        </span>
                    </a>
                </div>

                <div x-show="open"
                    x-collapse
                    x-cloak
                    class="mt-1 ml-2 space-y-1 sm:ml-3">
                    <a href="{{ route('ingredientes.index') }}"
                    class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">

                        <i class="fas fa-carrot text-[10px] sm:text-xs w-4"></i>

                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                            class="whitespace-nowrap">
                            Ingredientes
                        </span>
                    </a>
                </div>
   @endif



        @if(in_array($role, ['admin', 'cocinero']))
            <div x-show="open"
                x-collapse
                x-cloak
                class="mt-1 ml-2 space-y-1 sm:ml-3">

                <a href="{{ route('inventario.index') }}"
                class="flex items-center justify-between gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">

                    <div class="flex items-center gap-2 sm:gap-3">
                        <i class="fas fa-boxes text-[10px] sm:text-xs w-4"></i>
                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">
                            Inventario
                        </span>
                    </div>

                    @if($lowStockCount > 0)
                        <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-black bg-yellow-400 animate-pulse rounded-full">
                            {{ $lowStockCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
        @endif




            <!-- Mesas -->
            @if(in_array($role, ['admin', 'mesero']))
            <div x-data="{
                open: localStorage.getItem('sidebar_section_mesas') === 'true',
                toggle() {
                    this.open = !this.open;
                    localStorage.setItem('sidebar_section_mesas', this.open);
                }
            }"
            x-init="() => {
                if (localStorage.getItem('sidebar_section_mesas') === null) {
                    open = false;
                    localStorage.setItem('sidebar_section_mesas', false);
                }
            }"
            class="mb-1">
                <button @click="toggle()"
                        class="w-full flex items-center justify-between px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200 hover:bg-white/10 group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <i class="w-5 text-base transition-colors fas fa-chair text-white/80 sm:text-lg group-hover:text-white"></i>
                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                              x-transition.duration.200
                              class="text-xs font-medium sm:text-sm text-white/80 group-hover:text-white whitespace-nowrap">
                            Gestión de Mesas
                        </span>
                    </div>
                    <i x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                       :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
                       class="text-xs transition-transform duration-200 fas text-white/50"></i>
                </button>

                <div x-show="open"
                     x-collapse
                     x-cloak
                     class="mt-1 ml-2 sm:ml-3">
                    <a href="{{ route('mesas.index') }}"
                       class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                        <i class="fas fa-table text-[10px] sm:text-xs w-4"></i>
                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Mesas</span>
                    </a>
                </div>
            </div>
            @endif


<!-- Operaciones -->
@if(in_array($role, ['admin', 'cajero', 'mesero', 'cocinero', 'cliente']))
<div x-data="{
    open: localStorage.getItem('sidebar_section_operaciones') === 'true',
    toggle() {
        this.open = !this.open;
        localStorage.setItem('sidebar_section_operaciones', this.open);
    },
    pendingOrdersCount: 0,
    fetchPendingOrders() {
        fetch('/notificaciones/pedidos')
            .then(res => res.json())
            .then(data => this.pendingOrdersCount = data.cantidad)
            .catch(err => console.error(err));
    },
    initNotifications() {
        this.fetchPendingOrders();
        if (typeof window.Echo !== 'undefined') {
            window.Echo.channel('pedidos.cocineros')
                .listen('.pedido.creado', (e) => { this.fetchPendingOrders(); })
                .listen('.pedido.estado_actualizado', (e) => { this.fetchPendingOrders(); });
        }
    }
}"
x-init="
    initNotifications();
    if (localStorage.getItem('sidebar_section_operaciones') === null) {
        open = {{ in_array($role, ['admin', 'cajero']) ? 'true' : 'false' }};
        localStorage.setItem('sidebar_section_operaciones', open);
    }
"
class="mb-1">
    <button @click="toggle()"
            class="w-full flex items-center justify-between px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200 hover:bg-white/10 group">
        <div class="flex items-center gap-2 sm:gap-3">
            <i class="w-5 text-base transition-colors fas fa-cash-register text-white/80 sm:text-lg group-hover:text-white"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                  x-transition.duration.200
                  class="text-xs font-medium sm:text-sm text-white/80 group-hover:text-white whitespace-nowrap">
                Operaciones
            </span>
        </div>
        <i x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
           :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
           class="text-xs transition-transform duration-200 fas text-white/50"></i>
    </button>

    <div x-show="open"
         x-collapse
         x-cloak
         class="mt-1 ml-2 space-y-1 sm:ml-3">

        @if(in_array($role, ['admin', 'cajero', 'mesero']))
        <!-- Pedidos - Ahora visible para mesero también -->
        <a href="{{ route('pedidos.index') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-clipboard-list text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="flex items-center justify-between flex-1 pr-2 whitespace-nowrap">
                <span>Pedidos</span>
                @if(in_array($role, ['admin']))
                <span x-show="pendingOrdersCount > 0" x-text="pendingOrdersCount" x-cloak class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-orange-500 rounded-full"></span>
                @endif
            </span>
        </a>
        @endif


        <!-- RESERVAS (cliente: las propias / admin·mesero: todas) -->
        @if(in_array($role, ['cliente', 'admin', 'mesero']))
        <a href="{{ route('reserva.index') }}"
        class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
              <i class="w-5 text-base transition-colors fas fa-calendar-check text-white/80 sm:text-lg group-hover:text-white"></i>
              <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">
                  {{ $role === 'cliente' ? 'Reservar mesa' : 'Reservas' }}
              </span>
        </a>
        @endif
@if(in_array($role, ['cliente']))
<a href="{{ route('pedidos.misPedidos') }}"
   class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">

    <i class="w-5 text-base transition-colors fas fa-shopping-cart text-white/80 sm:text-lg group-hover:text-white"></i>

    <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
          class="whitespace-nowrap">
        Mis Pedidos
    </span>

</a>

<div x-data="clientePagoManager()" x-init="init()">
    <button @click="openSelectionModal()"
       class="w-full flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200 hover:bg-white/10 group">
        <i class="w-5 text-base transition-colors fas fa-qrcode text-white/80 sm:text-lg group-hover:text-white"></i>
        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
              x-transition.duration.200
              class="text-xs font-medium sm:text-sm text-white/80 group-hover:text-white whitespace-nowrap">
            Pagar con QR
        </span>
    </button>

    <!-- Modals Teleportados -->
    <template x-teleport="body">
        <div>
            <!-- Modal: Selección de Pedido -->
            <x-modal name="select-pedido-pago" focusable>
                <div class="p-6">
                    <h2 class="mb-4 text-lg font-medium text-gray-900">
                        <i class="mr-2 fas fa-list-ul text-primary"></i> Seleccione el Pedido a Pagar
                    </h2>

                    <div x-show="loadingPedidos" class="flex justify-center py-10">
                        <i class="text-3xl fas fa-spinner fa-spin text-primary"></i>
                    </div>

                    <div x-show="!loadingPedidos && pedidosPendientes.length === 0" class="py-10 text-center">
                        <p class="text-gray-500">No tienes pedidos pendientes de pago.</p>
                    </div>

                    <div x-show="!loadingPedidos && pedidosPendientes.length > 0" class="pr-2 space-y-3 overflow-y-auto max-h-96">
                        <template x-for="pedido in pedidosPendientes" :key="pedido.id">
                            <div class="flex items-center justify-between p-4 transition border border-gray-200 bg-gray-50 rounded-xl hover:bg-gray-100">
                                <div>
                                    <div class="font-bold text-primary" x-text="'#' + pedido.numero_pedido"></div>
                                    <div class="text-xs text-gray-500" x-text="new Date(pedido.created_at).toLocaleString()"></div>
                                    <div class="mt-1 text-sm font-semibold text-green-600" x-text="'Bs. ' + parseFloat(pedido.total).toFixed(2)"></div>
                                </div>
                                <button @click="generarQr(pedido)"
                                        :disabled="qrLoading"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-purple-600 border border-transparent rounded-md hover:bg-purple-700 disabled:opacity-50">
                                    <i class="mr-2 fas fa-cash-register"></i> Pagar
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-secondary-button x-on:click="$dispatch('close')">Cerrar</x-secondary-button>
                    </div>
                </div>
            </x-modal>

            <!-- Modal: QR de Pago -->
            <div x-show="qrModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[100] overflow-y-auto"
                style="display: none;">

                <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>

                <div class="flex items-center justify-center min-h-screen p-4">
                    <div x-show="qrModalOpen"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="relative w-full max-w-md overflow-hidden bg-white shadow-2xl rounded-2xl">

                        <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600">
                            <div class="flex items-center justify-between">
                                <h3 class="flex items-center text-lg font-bold text-white">
                                    <i class="mr-2 fas fa-qrcode"></i> Pago con QR
                                </h3>
                                <button @click="cerrarQrModal()"
                                        class="transition text-white/80 hover:text-white"
                                        x-show="!qrPagado">
                                    <i class="text-xl fas fa-times"></i>
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-purple-200" x-text="'Pedido: ' + (selectedPedido?.numero_pedido || '')"></p>
                        </div>

                        <div class="p-6">
                            <div x-show="!qrPagado">
                                <div class="flex justify-center mb-5">
                                    <div class="p-3 bg-white border-2 border-gray-200 shadow-inner rounded-xl"
                                        x-html="qrSvg">
                                    </div>
                                </div>

                                <div class="p-4 mb-5 space-y-2 text-gray-800 bg-gray-50 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">Monto Total:</span>
                                        <span class="text-xl font-black text-purple-700" x-text="'Bs. ' + qrFacturaData?.total"></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-center gap-3 py-3">
                                    <div class="relative flex w-3 h-3">
                                        <span class="absolute inline-flex w-full h-full bg-purple-400 rounded-full opacity-75 animate-ping"></span>
                                        <span class="relative inline-flex w-3 h-3 bg-purple-500 rounded-full"></span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-500 animate-pulse">Esperando confirmación...</span>
                                </div>
                            </div>

                            <div x-show="qrPagado" class="py-8 text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 mb-4 bg-green-100 rounded-full qr-success-check">
                                    <i class="text-4xl text-green-600 fas fa-check"></i>
                                </div>
                                <h3 class="mb-2 text-2xl font-bold text-green-700">¡Pago Confirmado!</h3>
                                <p class="mb-1 text-gray-600">Tu pago fue procesado exitosamente.</p>
                                <p class="text-sm text-gray-500">Actualizando información...</p>
                                <div class="mt-4">
                                    <div class="w-full bg-green-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-green-500 h-1.5 rounded-full qr-progress-bar"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function clientePagoManager() {
        return {
            pedidosPendientes: [],
            loadingPedidos: false,
            selectedPedido: null,
            qrModalOpen: false,
            qrSvg: '',
            qrEmisor: '',
            qrLoading: false,
            qrPagado: false,
            qrFacturaData: null,
            echoChannel: null,

            init() {
                console.log('Pago QR Cliente inicializado');
            },

            async openSelectionModal() {
                this.loadingPedidos = true;
                this.$dispatch('open-modal', 'select-pedido-pago');

                try {
                    const response = await fetch('/misPedidosPendientes');
                    this.pedidosPendientes = await response.json();
                } catch (error) {
                    console.error('Error cargando pedidos:', error);
                } finally {
                    this.loadingPedidos = false;
                }
            },

            async generarQr(pedido) {
                this.selectedPedido = pedido;
                this.qrLoading = true;

                try {
                    const response = await fetch(`/cliente/pedidos/${pedido.id}/generar-qr`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) throw new Error('Error al generar QR');

                    const data = await response.json();
                    this.qrSvg = data.qr_svg;
                    this.qrEmisor = data.emisor;
                    this.qrFacturaData = data.factura;
                    this.qrPagado = false;

                    this.$dispatch('close-modal', 'select-pedido-pago');
                    this.qrModalOpen = true;

                    this.suscribirCanal(data.emisor);
                } catch (error) {
                    console.error('Error:', error);
                    alert('No se pudo generar el QR.');
                } finally {
                    this.qrLoading = false;
                }
            },

            suscribirCanal(emisor) {
                if (this.echoChannel) {
                    window.Echo.leave('emisor-' + this.qrEmisor);
                }

                if (!window.Echo) return;

                this.echoChannel = window.Echo.channel('emisor-' + emisor)
                    .listen('.pago.confirmado', (e) => {
                        this.procesarPagoConfirmado(e);
                    });
            },

            async procesarPagoConfirmado(eventData) {
                this.qrPagado = true;

                // Actualizar estado en servidor
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    await fetch(`/facturas/${this.qrFacturaData.id}/pagar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ metodo_pago: 'qr' })
                    });
                } catch (err) {
                    console.log('Error o ya actualizado:', err);
                }

                setTimeout(() => {
                    window.location.reload();
                }, 2800);
            },

            cerrarQrModal() {
                if (this.echoChannel && this.qrEmisor) {
                    window.Echo.leave('emisor-' + this.qrEmisor);
                }
                this.qrModalOpen = false;
                this.qrSvg = '';
            }
        }
    }
</script>

<style>
    .qr-success-check { animation: successPop 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
    @keyframes successPop { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    .qr-progress-bar { animation: progressFill 2.5s linear forwards; }
    @keyframes progressFill { 0% { width: 0%; } 100% { width: 100%; } }
</style>
@endif

        <!-- Comandas -->
        @if(in_array($role, ['admin', 'cajero', 'cocinero']))
        <a href="{{ route('comandas.index') }}"
        class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-receipt text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="flex items-center justify-between flex-1 pr-2 whitespace-nowrap">
                <span>Comandas</span>
                @if(in_array($role, ['cocinero']))
                <span x-show="pendingOrdersCount > 0" x-text="pendingOrdersCount" x-cloak class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-orange-500 rounded-full"></span>
                @endif
            </span>
        </a>
        @endif

        <!-- Delivery - Solo para admin y cajero (stub: redirige a Pedidos) -->
        @if(in_array($role, ['admin', 'cajero']))
        <a href="{{ route('delivery.index') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-motorcycle text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Delivery</span>
        </a>
        @endif

        <div class="h-px my-2 bg-white/10"></div>

        <!-- Pre-factura - Solo para admin y cajero -->
        @if(in_array($role, ['admin', 'cajero']))
        <a href="{{ route('facturas.index') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-file-invoice text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Pre-factura</span>
        </a>

        <!-- Pagos - stub que redirige a Facturas -->
        <a href="{{ route('pagos.index') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-credit-card text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Pagos</span>
        </a>

        @endif

        <!-- Cierre de Cuenta (cobro por mesa) - admin y cajero -->
        @if(in_array($role, ['admin', 'cajero']))
        <a href="{{ route('cierres.index') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-cash-register text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Cierre de Cuenta</span>
        </a>

        <!-- Cierre de Caja (arqueo del turno) - admin y cajero -->
        <a href="{{ route('caja.index') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-calculator text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Cierre de Caja</span>
        </a>
        @endif

        @if(in_array($role, ['admin', 'cajero']))
            @php
                $openClosure = \App\Models\CashClosure::where('status', 'open')->first();
            @endphp
            <a href="{{ $openClosure ? route('caja.show', $openClosure) : route('caja.create') }}"
               class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                <i class="fas fa-vault text-[10px] sm:text-xs w-4"></i>
                <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">
                    {{ $openClosure ? 'Cerrar Caja' : 'Abrir Caja' }}
                </span>
            </a>
        @endif
    </div>
</div>
@endif


            <!-- Administración -->
            @if($role == 'admin')
            <div x-data="{
                open: localStorage.getItem('sidebar_section_administracion') === 'true',
                toggle() {
                    this.open = !this.open;
                    localStorage.setItem('sidebar_section_administracion', this.open);
                }
            }"
            x-init="() => {
                if (localStorage.getItem('sidebar_section_administracion') === null) {
                    open = false;
                    localStorage.setItem('sidebar_section_administracion', false);
                }
            }"
            class="mb-1">
                <button @click="toggle()"
                        class="w-full flex items-center justify-between px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200 hover:bg-white/10 group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <i class="w-5 text-base transition-colors fas fa-user-shield text-white/80 sm:text-lg group-hover:text-white"></i>
                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                              x-transition.duration.200
                              class="text-xs font-medium sm:text-sm text-white/80 group-hover:text-white whitespace-nowrap">
                            Administración
                        </span>
                    </div>
                    <i x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                       :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
                       class="text-xs transition-transform duration-200 fas text-white/50"></i>
                </button>

                <div x-show="open"
                     x-collapse
                     x-cloak
                     class="mt-1 ml-2 sm:ml-3">
                    <a href="{{ route('usuarios.index') }}"
                       class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                        <i class="fas fa-users text-[10px] sm:text-xs w-4"></i>
                        <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Usuarios</span>
                    </a>
                </div>
            </div>
            @endif


        <!-- Reportes -->
@if(in_array($role, ['admin', 'cocinero']))
<div x-data="{
    open: localStorage.getItem('sidebar_section_reportes') === 'true',
    toggle() {
        this.open = !this.open;
        localStorage.setItem('sidebar_section_reportes', this.open);
    }
}"
x-init="() => {
    if (localStorage.getItem('sidebar_section_reportes') === null) {
        open = false;
        localStorage.setItem('sidebar_section_reportes', false);
    }
}"
class="mb-1">
    <button @click="toggle()"
            class="w-full flex items-center justify-between px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition-all duration-200 hover:bg-white/10 group">
        <div class="flex items-center gap-2 sm:gap-3">
            <i class="w-5 text-base transition-colors fas fa-chart-line text-white/80 sm:text-lg group-hover:text-white"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
                  x-transition.duration.200
                  class="text-xs font-medium sm:text-sm text-white/80 group-hover:text-white whitespace-nowrap">
                Reportes
            </span>
        </div>
        <i x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
           :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
           class="text-xs transition-transform duration-200 fas text-white/50"></i>
    </button>

    <div x-show="open"
         x-collapse
         x-cloak
         class="mt-1 ml-2 space-y-1 sm:ml-3">
        <a href="{{ route('reportes.consumos') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-receipt text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">
                Consumos
            </span>
        </a>
    </div>
</div>
@endif

            <!-- Versión -->
            <div class="pt-4 mt-4 border-t border-white/10">
                <div x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="px-4 py-2">
                    <p class="text-xs text-white/40">v2.0.0</p>
                </div>
            </div>

        </div>
    </nav>
</aside>

<style>
    /* Scrollbar personalizada */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.08);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.4);
    }

    [x-cloak] {
        display: none !important;
    }
</style>
