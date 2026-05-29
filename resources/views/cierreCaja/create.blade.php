@extends('layouts.app')

@section('title', 'Abrir Caja')

@section('content')
<div class="py-10">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="mb-6 text-2xl font-bold">Abrir nueva caja</h2>

                <!-- Mensajes de error/success -->
                @if(session('error'))
                    <div class="relative px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('cierres.store') }}">
                    @csrf

                    <!-- Monto inicial -->
                    <div class="mb-4">
                        <label for="initial_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto inicial *</label>
                        <input type="number" name="initial_amount" id="initial_amount" step="0.01" min="0"
                               value="{{ old('initial_amount') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('initial_amount') border-red-500 @enderror"
                               required>
                        @error('initial_amount')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-4">
                        <label for="observations" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                        <textarea name="observations" id="observations" rows="3"
                                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('observations') }}</textarea>
                        @error('observations')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 font-semibold text-gray-700 transition duration-150 ease-in-out bg-gray-200 border border-transparent rounded-md dark:bg-gray-600 dark:text-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 font-semibold text-white transition duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Abrir caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
