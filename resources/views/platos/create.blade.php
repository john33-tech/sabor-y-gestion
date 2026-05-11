{{-- resources/views/platos/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ 
    selectedIngrediente: null,
    ingredientesSeleccionados: [],
    showDropdown: false,
    
    get cantidadSeleccionada() {
        return this.selectedIngrediente ? this.selectedIngrediente.cantidad : '';
    },
    
    set cantidadSeleccionada(value) {
        if (this.selectedIngrediente) {
            this.selectedIngrediente.cantidad = value;
        }
    },
    
    agregarIngrediente() {
        if (this.selectedIngrediente && this.selectedIngrediente.cantidad) {
            const existe = this.ingredientesSeleccionados.some(i => i.id === this.selectedIngrediente.id);
            if (!existe) {
                this.ingredientesSeleccionados.push({
                    ...this.selectedIngrediente,
                    cantidad: this.selectedIngrediente.cantidad
                });
            }
            this.selectedIngrediente = null;
            this.showDropdown = false;
        }
    },
    
    eliminarIngrediente(index) {
        this.ingredientesSeleccionados.splice(index, 1);
    },
    
    getImagenUrl(foto) {
        return foto ? `/storage/${foto}` : null;
    }
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-primary">Crear Nuevo Producto</h1>
        <a href="{{ route('platos.index') }}" class="text-sm font-medium text-primary hover:text-secondary transition-colors duration-200" style="color: #C2410C;">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    <div class="card">
        <form action="{{ route('platos.store') }}" method="POST" enctype="multipart/form-data" id="platoForm">
            @csrf
            
            <div class="space-y-6">
                <!-- Información básica -->
                <div>
                    <h2 class="text-xl font-semibold text-text mb-4">Información del Producto</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text mb-2">Nombre *</label>
                            <input type="text" name="nombre" required value="{{ old('nombre') }}" class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-text mb-2">Precio *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">Bs</span>
                                <input type="number" name="precio" step="0.01" required value="{{ old('precio') }}" class="w-full pl-8 pr-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                            </div>
                            @error('precio')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-text mb-2">Categoría *</label>
                            <div class="flex gap-2">
                                <select name="categoria_id" required class="flex-1 px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                                    <option value="">Seleccionar categoría</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('categorias.create') }}" 
                                   target="_blank" 
                                   class="btn-secondary px-4 inline-flex items-center justify-center">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                            <p class="text-xs text-muted mt-1">
                                <i class="fas fa-info-circle"></i> 
                                ¿No encuentras la categoría? 
                                <a href="{{ route('categorias.create') }}" target="_blank" class="text-primary">Créala aquí</a> 
                                y actualiza la página
                            </p>
                            @error('categoria_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-text mb-2">Imagen</label>
                            <input type="file" name="imagen" accept="image/*" class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                            @error('imagen')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-text mb-2">Descripción</label>
                            <textarea name="descripcion" rows="3" class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">{{ old('descripcion') }}</textarea>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="disponible" value="1" {{ old('disponible', true) ? 'checked' : '' }} class="mr-2">
                                <span class="text-sm text-text">Disponible para la venta</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Ingredientes -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-text">Ingredientes</h2>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-text mb-2">Agregar Ingrediente</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <!-- Selector personalizado -->
                                <div @click="showDropdown = !showDropdown" 
                                     class="w-full px-4 py-2 rounded-lg border border-border cursor-pointer flex justify-between items-center bg-white">
                                    <span x-text="selectedIngrediente ? selectedIngrediente.nombre : 'Seleccionar ingrediente'"></span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                
                                <!-- Dropdown -->
                                <div x-show="showDropdown" 
                                     x-cloak
                                     @click.away="showDropdown = false"
                                     class="absolute z-10 w-full mt-1 bg-white border border-border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    @foreach($ingredientes as $ingrediente)
                                    <div class="p-2 hover:bg-gray-100 cursor-pointer flex items-center space-x-3"
                                         @click="selectedIngrediente = {
                                             id: {{ $ingrediente->id }},
                                             nombre: '{{ $ingrediente->nombre }}',
                                             foto: '{{ $ingrediente->foto }}',
                                             unidad: '{{ $ingrediente->unidad_medida }}',
                                             cantidad: ''
                                         }; showDropdown = false">
                                        <div class="w-8 h-8">
                                            @if($ingrediente->foto)
                                                <img src="{{ Storage::url($ingrediente->foto) }}" class="w-full h-full object-cover rounded">
                                            @else
                                                <div class="w-full h-full bg-gray-200 rounded flex items-center justify-center">
                                                    <i class="fas fa-carrot text-gray-400 text-xs"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ $ingrediente->nombre }}</p>
                                            <p class="text-xs text-muted">{{ $ingrediente->unidad_medida }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 mt-2">
                            <input type="number" 
                                   x-model="cantidadSeleccionada"
                                   placeholder="Cantidad"
                                   step="0.01"
                                   class="flex-1 px-4 py-2 rounded-lg border border-border">
                            <button type="button" 
                                    @click="agregarIngrediente()"
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Agregar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Lista de ingredientes seleccionados -->
                    <div class="space-y-3">
                        <template x-for="(ingrediente, index) in ingredientesSeleccionados" :key="index">
                            <div class="flex gap-3 items-center border border-border rounded-lg p-3 bg-background">
                                <!-- Mostrar imagen del ingrediente -->
                                <div class="w-12 h-12 flex-shrink-0">
                                    <template x-if="ingrediente.foto">
                                        <img :src="getImagenUrl(ingrediente.foto)" 
                                             :alt="ingrediente.nombre"
                                             class="w-full h-full object-cover rounded-lg">
                                    </template>
                                    <template x-if="!ingrediente.foto">
                                        <div class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-carrot text-gray-400 text-2xl"></i>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Información del ingrediente -->
                                <div class="flex-1">
                                    <p class="font-medium text-text" x-text="ingrediente.nombre"></p>
                                    <p class="text-xs text-muted" x-text="'Unidad: ' + ingrediente.unidad"></p>
                                </div>
                                
                                <!-- Campos ocultos para enviar -->
                                <input type="hidden" :name="'ingredientes[' + index + '][id]'" :value="ingrediente.id">
                                <input type="hidden" :name="'ingredientes[' + index + '][cantidad]'" :value="ingrediente.cantidad">
                                
                                <!-- Mostrar cantidad -->
                                <div class="w-32 px-4 py-2 bg-gray-50 rounded-lg text-center">
                                    <span x-text="ingrediente.cantidad"></span>
                                    <span class="text-xs text-muted ml-1" x-text="ingrediente.unidad"></span>
                                </div>
                                
                                <button type="button" 
                                        @click="eliminarIngrediente(index)"
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </template>
                        
                        <!-- Mensaje cuando no hay ingredientes -->
                        <div x-show="ingredientesSeleccionados.length === 0" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle"></i> No hay ingredientes agregados
                        </div>
                    </div>
                    
                    <p class="text-xs text-muted mt-2">
                        <i class="fas fa-info-circle"></i> 
                        ¿No encuentras el ingrediente? 
                        <a href="{{ route('ingredientes.create') }}" target="_blank" class="text-primary">Créalo aquí</a> 
                        y actualiza la página
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-save mr-2"></i> Guardar Producto
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Función para recargar cuando la ventana recupera el foco
    let ventanaRecargada = false;
    window.addEventListener('focus', function() {
        if (!ventanaRecargada && localStorage.getItem('recargarPlatos') === 'true') {
            localStorage.removeItem('recargarPlatos');
            ventanaRecargada = true;
            location.reload();
        }
    });
    
    // Marcar que se necesita recargar cuando se abre una nueva pestaña
    document.querySelectorAll('a[target="_blank"]').forEach(link => {
        link.addEventListener('click', () => {
            localStorage.setItem('recargarPlatos', 'true');
        });
    });
    
    // Cargar ingredientes existentes del old() si hay error de validación
    document.addEventListener('DOMContentLoaded', function() {
        @if(old('ingredientes'))
            const ingredientesExistentes = @json(old('ingredientes'));
            const alpineData = document.querySelector('[x-data]').__x.$data;
            const ingredientesDisponibles = @json($ingredientes);
            
            ingredientesExistentes.forEach(ingExistente => {
                const ingredienteCompleto = ingredientesDisponibles.find(i => i.id == ingExistente.id);
                if (ingredienteCompleto) {
                    alpineData.ingredientesSeleccionados.push({
                        id: ingredienteCompleto.id,
                        nombre: ingredienteCompleto.nombre,
                        foto: ingredienteCompleto.foto,
                        unidad: ingredienteCompleto.unidad_medida,
                        cantidad: ingExistente.cantidad
                    });
                }
            });
        @endif
    });
</script>
@endpush
@endsection