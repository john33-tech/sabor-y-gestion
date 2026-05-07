@extends('layouts.app')

@section('title', 'Editar Reserva')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Título -->
    <div class="flex items-center justify-between">

        <h1 class="text-2xl font-bold text-primary">
            <i class="fas fa-edit mr-2"></i>Editar Reserva
        </h1>

        <a href="{{ route('reserva.index') }}"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-300 shadow-md">

            <i class="fas fa-arrow-left mr-2"></i>Volver

        </a>

    </div>

    <!-- Error -->
    @if(session('error'))

        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">

            {{ session('error') }}

        </div>

    @endif

    <!-- Errores de validación -->
    @if($errors->any())

        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">

            <ul class="list-disc list-inside">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <!-- Formulario -->
    <div class="bg-surface rounded-lg shadow-lg border border-border p-6">

        <form action="{{ route('reserva.update', $reserva->id) }}"
              method="POST"
              class="space-y-6">

            @csrf
            @method('PUT')

            <!-- Mesa -->
            <div>

                <label class="block text-sm font-medium mb-2">
                    Mesa
                </label>

                <select name="mesa_id"
                        class="w-full border border-border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">

                    @foreach($mesas as $mesa)

                        <option value="{{ $mesa->id }}"
                            {{ $reserva->mesa_id == $mesa->id ? 'selected' : '' }}>

                            Mesa {{ $mesa->numero_mesa }}
                            - {{ $mesa->area }}

                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Fecha -->
            <div>

                <label class="block text-sm font-medium mb-2">
                    Fecha de Reserva
                </label>

                <input type="date"
                       name="fecha_reserva"
                       value="{{ $reserva->fecha_reserva }}"
                       class="w-full border border-border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">

            </div>

            <!-- Hora -->
            <div>

                <label class="block text-sm font-medium mb-2">
                    Hora de Reserva
                </label>

                <input type="time"
                       name="hora_reserva"
                       value="{{ $reserva->hora_reserva }}"
                       class="w-full border border-border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">

            </div>

            <!-- Personas -->
            <div>

                <label class="block text-sm font-medium mb-2">
                    Número de Personas
                </label>

                <input type="number"
                       name="personas"
                       min="1"
                       max="20"
                       value="{{ $reserva->personas }}"
                       class="w-full border border-border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">

            </div>

            <!-- Notas -->
            <div>

                <label class="block text-sm font-medium mb-2">
                    Notas Adicionales
                </label>

                <textarea name="notas"
                          rows="4"
                          class="w-full border border-border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">{{ $reserva->notas }}</textarea>

            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-4">

                <a href="{{ route('reserva.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition duration-300 shadow-md">

                    Cancelar

                </a>

                <button type="submit"
                        class="bg-primary hover:bg-secondary text-white px-6 py-3 rounded-lg transition duration-300 shadow-md">

                    <i class="fas fa-save mr-2"></i>
                    Actualizar Reserva

                </button>

            </div>

        </form>

    </div>

</div>

@endsection