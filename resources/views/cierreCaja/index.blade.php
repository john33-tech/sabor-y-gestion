@extends('layouts.app') {{-- or your main layout --}}

@section('title', 'Historial de Cierres de Caja')

@section('content')
<div class="py-6">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Cierres de Caja</h1>
            @php
                $openClosure = App\Models\CashClosure::where('status', 'Open')->first();
            @endphp
            @if(!$openClosure && in_array(auth()->user()->role, ['admin', 'cajero']))
                <a href="{{ route('caja.create') }}" class="px-4 py-2 font-bold text-white rounded bg-primary hover:bg-orange-700">
                    + Abrir Caja
                </a>
            @endif
        </div>

        {{-- Filters --}}
        <div class="p-4 mb-6 bg-white rounded-lg shadow-md">
            <form method="GET" action="{{ route('caja.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha desde</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha hasta</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Abierta</option>
                        <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Cerrada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Usuario (nombre)</label>
                    <input type="text" name="user_name" value="{{ request('user_name') }}" placeholder="Ej: Juan" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="flex justify-end space-x-2 md:col-span-4">
                    <button type="submit" class="px-4 py-2 text-white rounded bg-primary hover:bg-orange-700">Filtrar</button>
                    <a href="{{ route('caja.index') }}" class="px-4 py-2 text-white bg-gray-500 rounded hover:bg-gray-700">Limpiar</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden bg-white rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Apertura</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Cierre</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Monto inicial</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Total ventas</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($closures as $closure)
                        <tr>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $closure->id }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $closure->user->name }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $closure->opening_date->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                {{ $closure->closing_date ? $closure->closing_date->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">S/ {{ number_format($closure->initial_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">S/ {{ number_format($closure->total_sales ?? 0, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($closure->status == 'Open')
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 text-yellow-800 bg-yellow-100 rounded-full">Abierta</span>
                                @else
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Cerrada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <a href="{{ route('caja.show', $closure) }}" class="text-primary hover:text-orange-700">Ver detalles</a>
                                @if($closure->status === 'Closed')
                                    <a href="{{ route('caja.pdf', $closure) }}" title="Descargar PDF" class="ml-2 text-primary hover:text-orange-700">
                                        <svg class="inline w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m-6 4h6M4 4h16v16H4V4z"></path>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No hay cierres registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $closures->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
