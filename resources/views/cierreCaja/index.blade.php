@extends('layouts.app')

@section('title', 'Historial de Cierres de Caja')

@section('content')
<div class="min-h-screen py-8 bg-background">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Header Section --}}
        <div class="flex flex-col gap-6 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-text">Cierres de Caja</h1>
                <p class="mt-2 text-muted">Gestión detallada de aperturas y cierres de sesión.</p>
            </div>

            @php
                $openClosure = App\Models\CashClosure::where('status', 'Open')->first();
            @endphp
            @if(!$openClosure && in_array(auth()->user()->role, ['admin', 'cajero']))
                <a href="{{ route('caja.create') }}"
                   class="inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white bg-primary rounded-xl shadow-lg shadow-orange-200 hover:bg-primary/90 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Abrir Nueva Caja
                </a>
            @endif
        </div>

        {{-- Filters Section --}}
        <div class="p-6 mb-8 border shadow-sm bg-surface rounded-2xl border-border/50">
            <form method="GET" action="{{ route('caja.index') }}">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block mb-2 text-xs font-bold tracking-wider uppercase text-muted">Fecha desde</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full border-none rounded-lg bg-background focus:ring-2 focus:ring-primary text-text placeholder:text-gray-400">
                    </div>
                    <div>
                        <label class="block mb-2 text-xs font-bold tracking-wider uppercase text-muted">Fecha hasta</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full border-none rounded-lg bg-background focus:ring-2 focus:ring-primary text-text">
                    </div>
                    <div>
                        <label class="block mb-2 text-xs font-bold tracking-wider uppercase text-muted">Estado</label>
                        <select name="status" class="w-full border-none rounded-lg bg-background focus:ring-2 focus:ring-primary text-text">
                            <option value="">Todos</option>
                            <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Abierta</option>
                            <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Cerrada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-xs font-bold tracking-wider uppercase text-muted">Usuario</label>
                        <input type="text" name="user_name" value="{{ request('user_name') }}" placeholder="Nombre..." class="w-full border-none rounded-lg bg-background focus:ring-2 focus:ring-primary text-text">
                    </div>
                </div>
                <div class="flex flex-col gap-3 mt-6 sm:flex-row">
                    <button type="submit" class="px-6 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-secondary transition">Aplicar Filtros</button>
                    <a href="{{ route('caja.index') }}" class="px-6 py-2.5 bg-gray-100 text-text font-semibold rounded-lg hover:bg-gray-200 transition">Limpiar</a>
                </div>
            </form>
        </div>

        {{-- Table Section --}}
        <div class="overflow-hidden border shadow-sm bg-surface rounded-2xl border-border/50">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-left uppercase text-muted">ID</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-left uppercase text-muted">Usuario</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-left uppercase text-muted">Apertura</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-left uppercase text-muted">Monto Inicial</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-left uppercase text-muted">Total Ventas</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-left uppercase text-muted">Estado</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-right uppercase text-muted">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($closures as $closure)
                        <tr class="transition hover:bg-orange-50/30">
                            <td class="px-6 py-5 text-sm font-bold text-text">#{{ $closure->id }}</td>
                            <td class="px-6 py-5 text-sm text-text">{{ $closure->user->name }}</td>
                            <td class="px-6 py-5 text-sm text-muted">{{ $closure->opening_date->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-5 text-sm font-semibold text-text">$ {{ number_format($closure->initial_amount, 2) }}</td>
                            <td class="px-6 py-5 text-sm font-semibold text-primary">$ {{ number_format($closure->total_sales ?? 0, 2) }}</td>
                            <td class="px-6 py-5">
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full {{ $closure->status == 'Open' ? 'bg-orange-100 text-primary' : 'bg-green-100 text-green-700' }}">
                                    {{ $closure->status == 'Open' ? 'Abierta' : 'Cerrada' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 space-x-3 text-right">
                                <a href="{{ route('caja.show', $closure) }}" class="font-bold text-primary hover:underline">Ver</a>
                                @if($closure->status === 'Closed')
                                    <a href="{{ route('caja.pdf', $closure) }}" class="text-muted hover:text-primary">PDF</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-muted">
                                <p class="text-lg font-medium">No hay cierres registrados</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($closures->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $closures->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
