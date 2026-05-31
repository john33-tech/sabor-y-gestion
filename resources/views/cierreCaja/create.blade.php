@extends('layouts.app')

@section('title', 'Abrir Caja')

@section('content')
<div class="">
    <div class="">
        {{-- Card Container with modern design --}}
        <div class="overflow-hidden transition-all duration-200 border shadow-lg bg-surface dark:bg-gray-800 rounded-xl border-border dark:border-gray-700">
            {{-- Card Header --}}
            <div class="px-6 py-5 border-b border-border dark:border-gray-700 bg-gradient-to-r from-orange-50 to-white dark:from-gray-800 dark:to-gray-800">
                <div class="flex items-center gap-3">
                    {{-- Decorative icon (purely visual) --}}
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary/10 dark:bg-primary/20 text-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Abrir nueva caja</h2>
                        <p class="text-sm text-muted dark:text-gray-400 mt-0.5">Registra el monto inicial para comenzar la jornada</p>
                    </div>
                </div>
            </div>

            {{-- Card Body with Form --}}
            <div class="p-6 md:p-8">
                {{-- Mensajes de error / success --}}
                @if(session('error'))
                    <div class="p-4 mb-6 border-l-4 border-red-500 rounded-lg shadow-sm bg-red-50 dark:bg-red-900/20" role="alert">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="p-4 mb-6 border-l-4 border-green-500 rounded-lg shadow-sm bg-green-50 dark:bg-green-900/20" role="alert">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('caja.store') }}" id="cashRegisterForm">
                    @csrf

                    {{-- Monto inicial con moneda y mejor UX --}}
                    <div class="mb-6">
                        <label for="initial_amount" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Monto inicial <span class="text-red-500">*</span>
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="initial_amount" id="initial_amount" step="0.01" min="0"
                                   value="{{ old('initial_amount') }}"
                                   placeholder="0.00"
                                   class="block w-full pl-7 pr-12 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary focus:ring-1 dark:bg-gray-700 dark:text-white transition duration-150 @error('initial_amount') border-red-500 pr-10 @enderror"
                                   required>
                            @error('initial_amount')
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @enderror
                        </div>
                        @error('initial_amount')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @else
                            <p class="mt-1.5 text-xs text-muted dark:text-gray-400">Ingrese el monto en efectivo con el que abre la caja. Puede usar punto o coma decimal.</p>
                        @enderror
                    </div>

                    {{-- Observaciones con mejor espaciado --}}
                    <div class="mb-6">
                        <label for="observations" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Observaciones
                        </label>
                        <textarea name="observations" id="observations" rows="3"
                                  placeholder="Notas adicionales (opcional)"
                                  class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary focus:ring-1 dark:bg-gray-700 dark:text-white transition duration-150 @error('observations') border-red-500 @enderror">{{ old('observations') }}</textarea>
                        @error('observations')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @else
                            <p class="mt-1.5 text-xs text-muted dark:text-gray-400">Puede agregar comentarios sobre la apertura (ej. observaciones del turno).</p>
                        @enderror
                    </div>

                    {{-- Información adicional contextual --}}
                    <div class="p-3 mb-6 border border-blue-100 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-xs text-blue-800 dark:text-blue-200">
                                <p class="font-medium">Importante:</p>
                                <p class="mt-0.5">La caja quedará abierta hasta que se registre el cierre. Asegúrese de que no exista otra caja abierta previamente.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="flex flex-col items-center justify-end gap-3 pt-2 sm:flex-row">
                        <a href="{{ url('/dashboard') }}"
                           class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150">
                            Cancelar
                        </a>
                        <button type="submit" id="submitButton"
                                class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 text-sm font-semibold text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150">
                            <svg class="w-4 h-4 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Abrir caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Mejora UX: Feedback visual al enviar (loading state sin alterar lógica de negocio) --}}
<script>
    (function() {
        const form = document.getElementById('cashRegisterForm');
        const submitBtn = document.getElementById('submitButton');
        if (!form || !submitBtn) return;

        form.addEventListener('submit', function(e) {
            // Solo mostrar estado de carga si el formulario es válido (evita doble envío)
            if (submitBtn.disabled) return;

            // Pequeño retraso para permitir validación nativa del navegador
            setTimeout(() => {
                if (form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    // Guardar contenido original
                    const originalHTML = submitBtn.innerHTML;
                    submitBtn.innerHTML = `
                        <svg class="w-4 h-4 mr-2 -ml-1 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Abriendo caja...
                    `;
                    // Opcional: restaurar si hay algún error del servidor que recargue la página (no necesario)
                }
            }, 10);
        });
    })();
</script>
@endsection
