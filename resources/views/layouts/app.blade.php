<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::check() ? Auth::id() : '' }}">
    <meta name="user-role" content="{{ Auth::check() ? Auth::user()->role : '' }}">
    <title>SaborGestion - {{ $title ?? 'Sistema de Gestión' }}</title>

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @include('layouts.pwa')

    {{-- Datos del restaurante + helpers de envío a domicilio (distancia/tiempo), globales --}}
    <script>
        window.RESTAURANTE = {
            nombre: @json(config('restaurante.nombre')),
            direccion: @json(config('restaurante.direccion')),
            lat: {{ config('restaurante.lat') }},
            lng: {{ config('restaurante.lng') }},
            velocidadKmh: {{ config('restaurante.velocidad_kmh') }},
            minutosBase: {{ config('restaurante.minutos_base') }},
            envioBase: {{ config('restaurante.envio_base', 0) }},
            envioPorKm: {{ config('restaurante.envio_por_km', 0) }},
        };
        // Costo de envío (Bs) dada la distancia en km. La distancia se redondea a
        // 1 decimal (igual que se muestra) para que "km mostrado × tarifa" cuadre.
        window.costoEnvio = function (km) {
            const kmR = Math.round(km * 10) / 10;
            return Math.round((window.RESTAURANTE.envioBase + window.RESTAURANTE.envioPorKm * kmR) * 100) / 100;
        };
        // Distancia Haversine (km) entre dos coordenadas.
        window.distanciaKm = function (lat1, lng1, lat2, lng2) {
            const R = 6371, toRad = (d) => d * Math.PI / 180;
            const dLat = toRad(lat2 - lat1), dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        };
        // Tiempo estimado de entrega (min) dada la distancia en km.
        window.tiempoEntregaMin = function (km) {
            return Math.round(window.RESTAURANTE.minutosBase + (km / window.RESTAURANTE.velocidadKmh) * 60);
        };

        // Dibuja la RUTA por calle (OSRM, gratis, sin API key) entre origen y
        // destino sobre un mapa Leaflet. Muestra primero una línea recta al
        // instante y luego la reemplaza por la ruta real. Si OSRM no responde,
        // se queda la recta. onInfo recibe { km, min, real }.
        // Devuelve una función para borrar la ruta dibujada.
        window.rutaDelivery = function (map, origen, destino, onInfo, opts) {
            opts = opts || {};
            const recalcInfo = (km, real) => onInfo && onInfo({ km: km, min: window.tiempoEntregaMin(km), real: real });

            // 1) Línea recta inmediata (feedback al toque).
            let layer = L.polyline([origen, destino], { color: '#C2410C', weight: 4, opacity: 0.6, dashArray: '8,8' }).addTo(map);
            if (opts.fit) map.fitBounds(layer.getBounds().pad(0.25));
            recalcInfo(window.distanciaKm(origen[0], origen[1], destino[0], destino[1]), false);

            // 2) Ruta real por calle (asíncrona). Pedimos alternativas y elegimos
            //    la MÁS CORTA en distancia (OSRM por defecto da la más rápida).
            const url = `https://router.project-osrm.org/route/v1/driving/${origen[1]},${origen[0]};${destino[1]},${destino[0]}?overview=full&geometries=geojson&alternatives=3`;
            const ctrl = new AbortController();
            const t = setTimeout(() => ctrl.abort(), 7000);
            fetch(url, { signal: ctrl.signal })
                .then((r) => (r.ok ? r.json() : Promise.reject()))
                .then((data) => {
                    clearTimeout(t);
                    const rutas = data && data.routes;
                    if (!rutas || !rutas.length) return;
                    // Elegir la ruta de MENOR distancia entre las alternativas.
                    const route = rutas.reduce((a, b) => (b.distance < a.distance ? b : a));
                    if (!route || !route.geometry) return;
                    const coords = route.geometry.coordinates.map((c) => [c[1], c[0]]); // [lng,lat] -> [lat,lng]
                    if (map._rutaLayer) map.removeLayer(map._rutaLayer);
                    layer = L.polyline(coords, { color: '#C2410C', weight: 5, opacity: 0.85 }).addTo(map);
                    map._rutaLayer = layer;
                    if (opts.fit) map.fitBounds(layer.getBounds().pad(0.15));
                    const km = route.distance / 1000;
                    const min = Math.round(window.RESTAURANTE.minutosBase + route.duration / 60);
                    if (onInfo) onInfo({ km: km, min: min, real: true });
                })
                .catch(() => clearTimeout(t));

            map._rutaLayer = layer;
            return () => { if (map._rutaLayer) { map.removeLayer(map._rutaLayer); map._rutaLayer = null; } };
        };
    </script>

    @stack('styles')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-hidden antialiased bg-gray-50">
    <div x-data="appLayout()"
         x-init="init()"
         class="relative h-screen overflow-hidden">

        <!-- Overlay para móvil cuando sidebar está abierto -->
        <div x-show="mobileSidebarOpen"
             x-transition.opacity.duration.300
             @click="closeMobileSidebar()"
             class="fixed inset-0 z-20 bg-black/50 lg:hidden"
             style="display: none;">
        </div>

        <div class="flex h-full">
            <!-- Sidebar -->
            <div x-show="sidebarOpen || (window.innerWidth >= 1024 && !mobileSidebarOpen)"
                 x-transition:enter="transition-transform duration-300 ease-in-out"
                 x-transition:enter-start="-translate-x-full lg:translate-x-0"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition-transform duration-300 ease-in-out"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full lg:translate-x-0"
                 class="fixed inset-y-0 left-0 z-30 lg:relative lg:z-0"
                 :class="{
                     'w-72': sidebarExpanded,
                     'w-20': !sidebarExpanded && window.innerWidth >= 1024,
                     'w-72': mobileSidebarOpen && window.innerWidth < 1024
                 }">
                @include('layouts.sidebar')
            </div>

            <!-- Contenido Principal -->
            <div class="flex flex-col flex-1 min-w-0 overflow-hidden"
                 :class="{
                     'lg:ml-0': true,
                     'ml-0': true
                 }">

                <!-- Navbar Superior con Perfil y Logout -->
                <nav class="sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between px-3 py-2 sm:px-6 sm:py-3">
                        <!-- Sección izquierda: Botón hamburguesa + Datos usuario -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <!-- Botón hamburguesa para móvil -->
                            <button @click="toggleMobileSidebar()"
                                    class="p-1.5 sm:p-2 -ml-1.5 sm:-ml-2 rounded-lg lg:hidden hover:bg-gray-100 transition-colors">
                                <i class="text-lg text-gray-600 fas fa-bars sm:text-xl"></i>
                            </button>

                            <!-- Datos del Usuario (siempre visibles) -->
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="flex items-center justify-center rounded-lg w-7 h-7 sm:w-8 sm:h-8 bg-primary bg-opacity-10">
                                    <i class="text-xs fas fa-user-alt text-primary sm:text-sm"></i>
                                </div>

                                <div class="text-left">
                                    <p class="text-xs font-medium text-gray-700 sm:text-sm">
                                        {{ Auth::user()->name }}
                                    </p>
                                    <p class="text-[10px] sm:text-xs text-gray-500 hidden xs:block">
                                        {{ ucfirst(Auth::user()->role) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- DERECHA: Refrescar + Logout -->
                        <div class="flex items-center gap-1 sm:gap-2">
                            <!-- Botón refrescar: clave en la PWA instalada (no tiene barra del navegador) -->
                            <button type="button" title="Actualizar"
                                    onclick="this.querySelector('i').classList.add('fa-spin'); location.reload();"
                                    class="flex items-center justify-center p-1.5 sm:p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="text-base fas fa-arrows-rotate sm:text-lg"></i>
                            </button>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors whitespace-nowrap">
                                    <i class="text-sm text-red-500 fas fa-sign-out-alt sm:text-base"></i>
                                    <span class="text-xs sm:text-sm">Cerrar Sesión</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>

                <!-- Contenido Principal -->
                <main class="flex-1 overflow-y-auto bg-gray-50">
                    <div class="container px-3 py-4 mx-auto sm:px-4 lg:px-6 sm:py-6">





                            <!-- 👇 AGREGAR ESTO - Componente de Alertas -->
                            <x-alert-messages />

                            @if(isset($breadcrumbs))
                                <!-- Breadcrumbs opcional -->
                                <div class="mb-4">
                                    <!-- ... breadcrumbs ... -->
                                </div>
                            @endif










                        @if(isset($breadcrumbs))
                            <!-- Breadcrumbs opcional -->
                            <div class="mb-4">
                                <nav class="flex items-center gap-2 overflow-x-auto text-sm">
                                    @foreach($breadcrumbs as $crumb)
                                        @if(!$loop->last)
                                            <a href="{{ $crumb['url'] }}" class="text-gray-500 transition-colors hover:text-primary whitespace-nowrap">
                                                {{ $crumb['label'] }}
                                            </a>
                                            <i class="flex-shrink-0 text-xs text-gray-400 fas fa-chevron-right"></i>
                                        @else
                                            <span class="font-medium text-gray-800 whitespace-nowrap">{{ $crumb['label'] }}</span>
                                        @endif
                                    @endforeach
                                </nav>
                            </div>
                        @endif

                        @yield('content')
                        {{ $slot ?? '' }}
                    </div>
                </main>
            </div>
        </div>
    </div>



    <style>
        @keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
 </style>




    <script>
        function appLayout() {
            return {
                sidebarExpanded: localStorage.getItem('sidebarExpanded') !== 'false',
                mobileSidebarOpen: false,
                windowWidth: window.innerWidth,

                init() {
                    this.windowWidth = window.innerWidth;
                    window.addEventListener('resize', () => {
                        this.windowWidth = window.innerWidth;
                        if (this.windowWidth >= 1024) {
                            this.mobileSidebarOpen = false;
                        }
                    });
                },

                get sidebarOpen() {
                    if (this.windowWidth >= 1024) {
                        return true;
                    }
                    return this.mobileSidebarOpen;
                },

                toggleSidebar() {
                    if (this.windowWidth >= 1024) {
                        this.sidebarExpanded = !this.sidebarExpanded;
                        localStorage.setItem('sidebarExpanded', this.sidebarExpanded);
                    }
                },

                toggleMobileSidebar() {
                    this.mobileSidebarOpen = !this.mobileSidebarOpen;
                },

                closeMobileSidebar() {
                    this.mobileSidebarOpen = false;
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
