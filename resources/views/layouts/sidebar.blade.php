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
    }
}"
x-init="() => {
    if (localStorage.getItem('sidebar_section_operaciones') === null) {
        open = false;
        localStorage.setItem('sidebar_section_operaciones', false);
    }
}"
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
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Pedidos</span>
        </a>
        @endif


        <!-- RESERVAR MESA cliente -->
        @if(in_array($role, ['cliente']))
        <a href="{{ route('reserva.index') }}"
        class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
              <i class="w-5 text-base transition-colors fas fa-calendar-check text-white/80 sm:text-lg group-hover:text-white"></i>
              <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Reservar mesa</span>
        </a>
        @endif
        <!-- MIS PEDIDOS cliente -->
@if(in_array($role, ['cliente']))
<a href="{{ route('pedidos.misPedidos') }}"
   class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">

    <i class="w-5 text-base transition-colors fas fa-shopping-cart text-white/80 sm:text-lg group-hover:text-white"></i>

    <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)"
          class="whitespace-nowrap">
        Mis Pedidos
    </span>

</a>
@endif

        <!-- Comandas -->
        @if(in_array($role, ['admin', 'cajero', 'cocinero']))
        <a href="{{ route('comandas.index') }}"
        class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-receipt text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Comandas</span>
        </a>
        @endif

        <!-- Delivery - Solo para admin y cajero -->
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

        <!-- Pagos - Solo para admin y cajero -->
        <a href="{{ route('pagos.index') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-credit-card text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Pagos</span>
        </a>

        <!-- Cierre de Caja - Solo para admin y cajero -->
        <a href="{{ route('cierres.index') }}"
           class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-all duration-200 group">
            <i class="fas fa-cash-register text-[10px] sm:text-xs w-4"></i>
            <span x-show="sidebarExpanded || (windowWidth < 1024 && mobileSidebarOpen)" class="whitespace-nowrap">Cierre de Caja</span>
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
