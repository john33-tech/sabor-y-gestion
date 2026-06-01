@extends('layouts.app')

@section('content')
<div class="min-h-screen px-4 py-6 bg-gradient-to-br from-amber-50/30 via-orange-50/20 to-rose-50/30 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- ── Encabezado ── --}}
        <div class="relative mb-8 overflow-hidden bg-white shadow-xl rounded-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-primary/5 via-secondary/5 to-accent/5"></div>
            <div class="absolute top-0 right-0 w-64 h-64 translate-x-32 -translate-y-32 rounded-full bg-gradient-to-br from-primary/10 to-transparent blur-3xl"></div>
            <div class="relative px-6 py-8 sm:px-8 sm:py-10">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 shadow-lg bg-gradient-to-br from-primary to-secondary rounded-xl">
                            <i class="text-2xl text-white fas fa-concierge-bell"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight text-transparent bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text sm:text-4xl">
                                Dashboard Mesero
                            </h1>
                            <p class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                                <i class="text-xs fas fa-smile-wink text-amber-500"></i>
                                Panel de atención al cliente y gestión de mesas
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-0 flex items-center gap-3 px-4 py-2 border border-gray-100 shadow-sm bg-gray-50/80 backdrop-blur-sm rounded-2xl">
                        <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded-xl">
                            <i class="text-xs text-white fas fa-calendar-alt"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-medium tracking-wider text-gray-500 uppercase">Fecha actual</p>
                            <p class="text-sm font-semibold text-gray-800">{{ now()->format('l, d F Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Badges de estado --}}
                <div class="flex flex-wrap gap-2 mt-6">
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-full text-primary bg-primary/10">
                        <i class="text-xs fas fa-chart-line"></i> Servicio activo
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-full text-secondary bg-secondary/10">
                        <i class="text-xs fas fa-clock"></i> Turno: {{ now()->format('H:i') }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-full text-amber-600 bg-amber-100">
                        <i class="text-xs fas fa-spinner fa-pulse"></i> Activos: {{ $pedidos->count() }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-medium rounded-full text-emerald-600 bg-emerald-100">
                        <i class="text-xs fas fa-check-circle"></i> Entregados hoy: {{ $pedidosFinalizados->count() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ── KPI Cards ── --}}
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Pedidos activos --}}
            <div class="relative overflow-hidden transition-all duration-500 bg-white shadow-xl group rounded-2xl hover:shadow-2xl hover:scale-[1.02]">
                <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-gradient-to-br from-amber-400/10 to-transparent blur-2xl"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-semibold tracking-wider text-gray-400 uppercase">En proceso</p>
                            <p class="text-4xl font-bold text-gray-800">{{ $pedidos->whereIn('estado', ['pendiente','en_preparacion'])->count() }}</p>
                            <p class="text-xs text-amber-600">En cocina ahora</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-amber-100 to-amber-50 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="text-3xl fas fa-fire text-amber-500"></i>
                        </div>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-400 scale-x-0 origin-left group-hover:scale-x-100 transition-transform duration-500"></div>
            </div>

            {{-- Listos para entregar --}}
            <div class="relative overflow-hidden transition-all duration-500 bg-white shadow-xl group rounded-2xl hover:shadow-2xl hover:scale-[1.02]">
                <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-gradient-to-br from-green-400/10 to-transparent blur-2xl"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Listos p/entregar</p>
                            <p class="text-4xl font-bold text-green-700">{{ $pedidos->where('estado', 'listo')->count() }}</p>
                            <p class="text-xs text-green-600">¡Sirve ahora!</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-50 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="text-3xl fas fa-bell text-green-500"></i>
                        </div>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 to-emerald-500 scale-x-0 origin-left group-hover:scale-x-100 transition-transform duration-500"></div>
            </div>

            {{-- Entregados --}}
            <div class="relative overflow-hidden transition-all duration-500 bg-white shadow-xl group rounded-2xl hover:shadow-2xl hover:scale-[1.02]">
                <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-gradient-to-br from-primary/10 to-transparent blur-2xl"></div>
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Entregados al cajero</p>
                            <p class="text-4xl font-bold text-gray-800">{{ $pedidosFinalizados->count() }}</p>
                            <p class="text-xs text-gray-400">En caja</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-primary/10 to-primary/5 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="text-3xl fas fa-cash-register text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-primary to-secondary scale-x-0 origin-left group-hover:scale-x-100 transition-transform duration-500"></div>
            </div>

            {{-- Nuevo pedido --}}
            <div class="relative overflow-hidden transition-all duration-500 bg-white shadow-xl group rounded-2xl hover:shadow-2xl hover:scale-[1.02]">
                <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-gradient-to-br from-secondary/10 to-transparent blur-2xl"></div>
                <div class="relative p-6 flex flex-col justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-gray-400 uppercase mb-2">Nuevo pedido</p>
                        <p class="text-sm text-gray-500">Registra un pedido para una mesa</p>
                    </div>
                    <a href="{{ route('pedidos.create') }}"
                       class="mt-4 inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-primary to-secondary rounded-xl shadow hover:shadow-lg hover:scale-105 transition-all duration-200">
                        <i class="fas fa-plus"></i> Crear pedido
                    </a>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-secondary to-accent scale-x-0 origin-left group-hover:scale-x-100 transition-transform duration-500"></div>
            </div>
        </div>

        {{-- ── Tabs ── --}}
        <div x-data="{ tab: 'activos' }">

            {{-- Tab buttons --}}
            <div class="flex gap-2 mb-4">
                <button @click="tab = 'activos'"
                    :class="tab === 'activos' ? 'bg-primary text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50'"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl font-semibold text-sm transition-all duration-200">
                    <i class="fas fa-clipboard-list"></i>
                    Pedidos activos
                    @if($pedidos->where('estado','listo')->count() > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full bg-green-400 text-white">
                            {{ $pedidos->where('estado','listo')->count() }}
                        </span>
                    @endif
                </button>
                <button @click="tab = 'finalizados'"
                    :class="tab === 'finalizados' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50'"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl font-semibold text-sm transition-all duration-200">
                    <i class="fas fa-check-double"></i>
                    Entregados al cajero
                </button>
            </div>

            {{-- ── Tab: Pedidos Activos ── --}}
            <div x-show="tab === 'activos'" x-cloak>
                <div class="overflow-hidden bg-white shadow-xl rounded-2xl">
                    <div class="relative px-6 py-5 border-b border-gray-100">
                        <div class="absolute inset-0 bg-gradient-to-r from-primary/3 to-transparent"></div>
                        <div class="relative flex items-center gap-2">
                            <div class="w-1 h-6 rounded-full bg-gradient-to-b from-primary to-secondary"></div>
                            <h2 class="text-lg font-bold text-gray-800">Pedidos Activos</h2>
                            <span class="ml-2 text-xs text-gray-400">Los listos para entregar se marcan en verde</span>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($pedidos->isEmpty())
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="relative mb-4">
                                    <div class="absolute inset-0 rounded-full bg-gradient-to-r from-primary/10 to-secondary/10 blur-xl"></div>
                                    <div class="relative flex items-center justify-center w-20 h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl shadow-inner">
                                        <i class="text-4xl text-gray-300 fas fa-receipt"></i>
                                    </div>
                                </div>
                                <p class="font-semibold text-gray-600">No hay pedidos activos</p>
                                <p class="mt-1 text-sm text-gray-400">Los pedidos en cocina aparecerán aquí</p>
                                <a href="{{ route('pedidos.create') }}"
                                   class="mt-5 inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-primary to-secondary rounded-xl shadow hover:shadow-lg hover:scale-105 transition-all duration-200">
                                    <i class="fas fa-plus"></i> Nuevo pedido
                                </a>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($pedidos as $pedido)
                                    @php
                                        $esListo = $pedido->estado === \App\Models\Pedido::ESTADO_LISTO;
                                        $esEnPreparacion = $pedido->estado === \App\Models\Pedido::ESTADO_EN_PREPARACION;
                                    @endphp
                                    <div class="flex items-center justify-between p-4 rounded-xl border transition-all duration-200
                                        {{ $esListo ? 'bg-green-50 border-green-300 shadow-md' : ($esEnPreparacion ? 'bg-amber-50 border-amber-200' : 'bg-gray-50 border-gray-200') }}">
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center justify-center w-10 h-10 rounded-xl shadow
                                                {{ $esListo ? 'bg-green-100' : ($esEnPreparacion ? 'bg-amber-100' : 'bg-gray-100') }}">
                                                <i class="fas {{ $esListo ? 'fa-bell text-green-600' : ($esEnPreparacion ? 'fa-fire text-amber-500' : 'fa-hourglass-half text-gray-400') }}"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800">Pedido #{{ $pedido->numero_pedido }}</p>
                                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                                        {{ $esListo ? 'bg-green-200 text-green-800' : ($esEnPreparacion ? 'bg-amber-200 text-amber-800' : 'bg-gray-200 text-gray-700') }}">
                                                        {{ $esListo ? '✅ Listo para entregar' : ($esEnPreparacion ? '🍳 En preparación' : '⏳ En espera') }}
                                                    </span>
                                                    @if($pedido->mesa)
                                                        <span class="text-xs text-gray-400"><i class="fas fa-chair mr-1"></i>Mesa {{ $pedido->mesa->numero }}</span>
                                                    @endif
                                                    <span class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i>{{ $pedido->created_at->format('H:i') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @if($esListo)
                                            <div class="flex flex-col gap-2 sm:flex-row">
                                                <form method="POST" action="{{ route('pedidos.cambiar-estado', $pedido) }}">
                                                    @csrf
                                                    <input type="hidden" name="estado" value="{{ \App\Models\Pedido::ESTADO_ENTREGADO }}">
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow hover:shadow-lg hover:scale-105 transition-all duration-200">
                                                        <i class="fas fa-hand-holding"></i> Entregar
                                                    </button>
                                                </form>
                                                {{-- Fase 4: confirmar entrega y solicitar la cuenta (solo pedidos de mesa) --}}
                                                @if($pedido->tipo_pedido === \App\Models\Pedido::TIPO_MESA && $pedido->mesa_id)
                                                    <form method="POST" action="{{ route('pedidos.solicitar-cuenta', $pedido) }}"
                                                          onsubmit="return confirm('¿Confirmar entrega y solicitar la cuenta para esta mesa? Pasará a Caja.');">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-primary to-secondary rounded-xl shadow hover:shadow-lg hover:scale-105 transition-all duration-200">
                                                            <i class="fas fa-receipt"></i> Solicitar cuenta
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Esperando cocina…</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Tab: Pedidos Finalizados ── --}}
            <div x-show="tab === 'finalizados'" x-cloak>
                <div class="overflow-hidden bg-white shadow-xl rounded-2xl">
                    <div class="relative px-6 py-5 border-b border-gray-100">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-transparent"></div>
                        <div class="relative flex items-center gap-2">
                            <div class="w-1 h-6 rounded-full bg-gradient-to-b from-emerald-500 to-teal-500"></div>
                            <h2 class="text-lg font-bold text-gray-800">Entregados al Cajero</h2>
                            <span class="ml-2 text-xs text-gray-400">Pedidos que ya pasaron a caja</span>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($pedidosFinalizados->isEmpty())
                            <div class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="flex items-center justify-center w-20 h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl shadow-inner mb-4">
                                    <i class="text-4xl text-gray-300 fas fa-clipboard-check"></i>
                                </div>
                                <p class="font-semibold text-gray-600">Sin pedidos entregados aún</p>
                                <p class="mt-1 text-sm text-gray-400">Aquí aparecerán los pedidos que hayas enviado a caja</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($pedidosFinalizados as $pedido)
                                    <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center justify-center w-10 h-10 bg-emerald-100 rounded-xl shadow">
                                                <i class="fas fa-check-double text-emerald-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800">Pedido #{{ $pedido->numero_pedido }}</p>
                                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-emerald-200 text-emerald-800">
                                                        ✅ Entregado
                                                    </span>
                                                    @if($pedido->mesa)
                                                        <span class="text-xs text-gray-400"><i class="fas fa-chair mr-1"></i>Mesa {{ $pedido->mesa->numero }}</span>
                                                    @endif
                                                    <span class="text-xs text-gray-400">
                                                        <i class="fas fa-clock mr-1"></i>{{ $pedido->updated_at->format('d/m H:i') }}
                                                    </span>
                                                    <span class="text-xs font-semibold text-emerald-700">
                                                        Total: ${{ number_format($pedido->total, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-xs text-emerald-600 font-medium italic">En caja</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>{{-- end x-data --}}

    </div>
</div>
@endsection