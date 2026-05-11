@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<style>
    #map-delivery { height: 500px; border-radius: 0.5rem; z-index: 0; }
    .leaflet-popup-content { font-family: inherit; }
    .pin-pendiente   { filter: hue-rotate(0deg); }
    .pin-listo       { filter: hue-rotate(120deg); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-3xl font-bold text-primary">GPS Delivery</h1>
            <p class="text-sm text-gray-500">Pedidos delivery activos en mapa. Cada pin es un pedido con su dirección y estado.</p>
        </div>
        <form method="GET" action="{{ route('delivery.index') }}" class="flex gap-2">
            <select name="estado" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                <option value="activos"        {{ $estado=='activos'        ? 'selected' : '' }}>Activos (no entregados)</option>
                <option value="pendiente"      {{ $estado=='pendiente'      ? 'selected' : '' }}>Pendientes</option>
                <option value="en_preparacion" {{ $estado=='en_preparacion' ? 'selected' : '' }}>En preparación</option>
                <option value="listo"          {{ $estado=='listo'          ? 'selected' : '' }}>Listos para enviar</option>
                <option value="entregado"      {{ $estado=='entregado'      ? 'selected' : '' }}>Entregados</option>
                <option value="todos"          {{ $estado=='todos'          ? 'selected' : '' }}>Todos</option>
            </select>
        </form>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-600 to-blue-500 rounded-lg shadow-lg p-4 text-white">
            <p class="text-sm opacity-90">Pedidos hoy</p>
            <p class="text-2xl font-bold">{{ $kpis['total_hoy'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-amber-600 to-amber-500 rounded-lg shadow-lg p-4 text-white">
            <p class="text-sm opacity-90">Pendientes</p>
            <p class="text-2xl font-bold">{{ $kpis['pendientes'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-600 to-purple-500 rounded-lg shadow-lg p-4 text-white">
            <p class="text-sm opacity-90">En camino</p>
            <p class="text-2xl font-bold">{{ $kpis['en_camino'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-500 rounded-lg shadow-lg p-4 text-white">
            <p class="text-sm opacity-90">Entregados hoy</p>
            <p class="text-2xl font-bold">{{ $kpis['entregados_hoy'] }}</p>
        </div>
    </div>

    {{-- Mapa + lista lateral --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-3">
            <div id="map-delivery"></div>
            @if($puntos->isEmpty())
                <p class="text-sm text-gray-500 mt-3 text-center">
                    <i class="fas fa-info-circle mr-1"></i>
                    No hay pedidos delivery con coordenadas GPS en este filtro. Cuando un pedido delivery tenga lat/lng aparecerá un pin en el mapa.
                </p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-3 overflow-y-auto" style="max-height: 500px;">
            <h3 class="font-semibold text-gray-800 mb-2">Pedidos ({{ $pedidos->count() }})</h3>
            @forelse($pedidos as $p)
                <div class="border rounded-md p-3 mb-2 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-sm text-gray-900">{{ $p->numero_pedido }}</p>
                            <p class="text-xs text-gray-600">{{ $p->cliente_nombre ?? 'Sin nombre' }}</p>
                            @if($p->cliente_telefono)
                                <p class="text-xs text-gray-500"><i class="fas fa-phone text-[10px] mr-1"></i>{{ $p->cliente_telefono }}</p>
                            @endif
                            @if($p->direccion)
                                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-map-marker-alt text-[10px] mr-1"></i>{{ $p->direccion }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">Bs {{ number_format($p->total, 2) }}</p>
                            @php
                                $colorEstado = [
                                    'pendiente'      => 'bg-amber-100 text-amber-800',
                                    'en_preparacion' => 'bg-blue-100 text-blue-800',
                                    'listo'          => 'bg-purple-100 text-purple-800',
                                    'entregado'      => 'bg-emerald-100 text-emerald-800',
                                    'cancelado'      => 'bg-red-100 text-red-800',
                                    'facturado'      => 'bg-gray-100 text-gray-800',
                                ][$p->estado] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $colorEstado }}">{{ \App\Models\Pedido::getEstados()[$p->estado] ?? $p->estado }}</span>
                        </div>
                    </div>
                    @if($p->latitud && $p->longitud)
                        <button type="button" onclick="centerMap({{ $p->latitud }}, {{ $p->longitud }})" class="mt-2 text-xs text-blue-600 hover:underline">
                            <i class="fas fa-location-arrow mr-1"></i>Ver en mapa
                        </button>
                    @else
                        <p class="text-[10px] text-gray-400 mt-2"><i class="fas fa-exclamation-circle mr-1"></i>Sin coordenadas GPS</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-6">No hay pedidos delivery en este filtro.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
const puntos = @json($puntos);

// Centro por defecto: Cochabamba, Bolivia. Se reajusta a los pins si hay alguno.
const map = L.map('map-delivery').setView([-17.3895, -66.1568], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
}).addTo(map);

const colorEstadoIcon = {
    pendiente:      'orange',
    en_preparacion: 'blue',
    listo:          'violet',
    entregado:      'green',
    cancelado:      'red',
};

const markers = [];
puntos.forEach(pt => {
    const color = colorEstadoIcon[pt.estado] ?? 'grey';
    const icon = L.icon({
        iconUrl:    `https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-${color}.png`,
        shadowUrl:  'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-shadow.png',
        iconSize:   [25, 41],
        iconAnchor: [12, 41],
        popupAnchor:[1, -34],
        shadowSize: [41, 41],
    });
    const m = L.marker([pt.lat, pt.lng], { icon }).addTo(map);
    m.bindPopup(`
        <div style="min-width:180px">
            <p style="font-weight:600;margin:0 0 4px">${pt.numero}</p>
            <p style="margin:2px 0;font-size:12px"><strong>${pt.cliente ?? 'Cliente'}</strong></p>
            ${pt.telefono ? `<p style="margin:2px 0;font-size:12px">📞 ${pt.telefono}</p>` : ''}
            ${pt.direccion ? `<p style="margin:2px 0;font-size:12px">📍 ${pt.direccion}</p>` : ''}
            <p style="margin:4px 0 0;font-size:12px"><strong>Bs ${pt.total.toFixed(2)}</strong> • ${pt.estado} • ${pt.creado}</p>
        </div>
    `);
    markers.push(m);
});

if (markers.length > 0) {
    const group = L.featureGroup(markers);
    map.fitBounds(group.getBounds().pad(0.2));
}

window.centerMap = function(lat, lng) {
    map.setView([lat, lng], 17, { animate: true });
};
</script>
@endpush
