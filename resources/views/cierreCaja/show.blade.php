@extends('layouts.app')

@section('title', 'Detalle de caja')

@section('content')
<div class="py-10">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Caja #{{ $cierre->id }}</h2>
                    <span class="px-3 py-1 text-sm rounded-full {{ $cierre->status === 'open' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $cierre->status === 'open' ? 'ABIERTA' : 'CERRADA' }}
                    </span>
                </div>

                @if(session('success'))
                    <div class="relative px-4 py-3 mb-4 text-green-700 bg-green-100 border border-green-400 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="relative px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Usuario</p>
                        <p class="font-medium">{{ $cierre->user->name }} ({{ ucfirst($cierre->user->role ?? 'sin rol') }})</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Fecha de apertura</p>
                        <p class="font-medium">{{ $cierre->opening_date->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monto inicial</p>
                        <p class="font-medium">$ {{ number_format($cierre->initial_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Observaciones</p>
                        <p class="font-medium">{{ $cierre->observations ?: '—' }}</p>
                    </div>
                </div>

                @if($cierre->status === 'closed')
                    <hr class="my-4 border-gray-300 dark:border-gray-700">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fecha de cierre</p>
                            <p class="font-medium">{{ $cierre->closing_date ? $cierre->closing_date->format('d/m/Y H:i:s') : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Monto final</p>
                            <p class="font-medium">$ {{ number_format($cierre->final_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total ventas</p>
                            <p class="font-medium">$ {{ number_format($cierre->total_sales, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Diferencia</p>
                            <p class="font-medium">$ {{ number_format($cierre->difference, 2) }}</p>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end mt-8">
                    @if($cierre->status === 'open')
                        <a href="{{ route('cierres.edit', $cierre) }}"
                           class="inline-flex items-center px-4 py-2 font-semibold text-white transition duration-150 ease-in-out bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Cerrar caja
                        </a>
                    @else
                        <span class="text-sm italic text-gray-500">Esta caja ya está cerrada.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
