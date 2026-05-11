{{-- resources/views/platos/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ 
    showImageModal: false, modalImageUrl: '', modalImageTitle: '',
    ingredientesSeleccionados: {{ Js::from($ingredientesSeleccionados) }},
    ingredientesDisponibles: {{ Js::from($ingredientes) }},
    selectedIngrediente: null,
    showDropdown: false,
    cantidadIngrediente: '',
    
    agregarIngrediente() {
        if (this.selectedIngrediente && this.cantidadIngrediente && this.cantidadIngrediente > 0) {
            const existe = this.ingredientesSeleccionados.some(i => i.id === this.selectedIngrediente.id);
            if (!existe) {
                this.ingredientesSeleccionados.push({
                    id: this.selectedIngrediente.id,
                    nombre: this.selectedIngrediente.nombre,
                    foto: this.selectedIngrediente.foto,
                    unidad: this.selectedIngrediente.unidad_medida,
                    cantidad: this.cantidadIngrediente
                });
            }
            this.selectedIngrediente = null;
            this.cantidadIngrediente = '';
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
        <h1 class="text-3xl font-bold text-primary">Editar Producto: {{ $plato->nombre }}</h1>
        <a href="{{ route('platos.index') }}" class="text-sm font-medium text-primary hover:text-secondary transition-colors duration-200" style="color: #C2410C;">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    <div class="card">
        <form action="{{ route('platos.update', $plato) }}" method="POST" enctype="multipart/form-data" id="form-editar-plato">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Información básica -->
                <div>
                    <h2 class="text-xl font-semibold text-text mb-4">Información del Producto</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text mb-2">Nombre *</label>
                            <input type="text" name="nombre" required value="{{ old('nombre', $plato->nombre) }}" class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-text mb-2">Precio *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">Bs </span>
                                <input type="number" name="precio" step="0.01" required value="{{ old('precio', $plato->precio) }}" class="w-full pl-8 pr-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
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
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id', $plato->categoria_id) == $categoria->id ? 'selected' : '' }}>
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
                            @if($plato->imagen)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($plato->imagen) }}" 
                                         alt="{{ $plato->nombre }}" 
                                         class="w-20 h-20 object-cover rounded-lg cursor-pointer hover:opacity-75"
                                         @click="modalImageUrl = '{{ Storage::url($plato->imagen) }}'; modalImageTitle = '{{ $plato->nombre }}'; showImageModal = true">
                                </div>
                            @endif
                            <input type="file" name="imagen" accept="image/*" class="w-full">
                            <p class="text-xs text-muted mt-1">Dejar vacío para mantener la imagen actual</p>
                            @error('imagen')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-text mb-2">Descripción</label>
                            <textarea name="descripcion" rows="3" class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">{{ old('descripcion', $plato->descripcion) }}</textarea>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="disponible" value="1" {{ $plato->disponible ? 'checked' : '' }} class="mr-2">
                                <span class="text-sm text-text">Disponible para la venta</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Ingredientes con Alpine.js -->
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
                                    <template x-for="ingrediente in ingredientesDisponibles" :key="ingrediente.id">
                                        <div class="p-2 hover:bg-gray-100 cursor-pointer flex items-center space-x-3"
                                             @click="selectedIngrediente = ingrediente; showDropdown = false">
                                            <div class="w-8 h-8">
                                                <template x-if="ingrediente.foto">
                                                    <img :src="'/storage/' + ingrediente.foto" class="w-full h-full object-cover rounded">
                                                </template>
                                                <template x-if="!ingrediente.foto">
                                                    <div class="w-full h-full bg-gray-200 rounded flex items-center justify-center">
                                                        <i class="fas fa-carrot text-gray-400 text-xs"></i>
                                                    </div>
                                                </template>
                                            </div>
                                            <div>
                                                <p class="font-medium" x-text="ingrediente.nombre"></p>
                                                <p class="text-xs text-muted" x-text="'Unidad: ' + ingrediente.unidad_medida"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <a href="{{ route('ingredientes.create') }}" 
                               target="_blank" 
                               class="btn-secondary px-4 inline-flex items-center justify-center"
                               title="Crear nuevo ingrediente">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                        
                        <div class="flex gap-2 mt-2">
                            <input type="number" 
                                   x-model="cantidadIngrediente"
                                   placeholder="Cantidad"
                                   step="0.01"
                                   :disabled="!selectedIngrediente"
                                   class="flex-1 px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary disabled:bg-gray-100">
                            <button type="button" 
                                    @click="agregarIngrediente()"
                                    :disabled="!selectedIngrediente || !cantidadIngrediente"
                                    class="btn-primary px-4 disabled:opacity-50 disabled:cursor-not-allowed">
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
                                <div class="w-32">
                                    <input type="number" 
                                           x-model="ingredientesSeleccionados[index].cantidad"
                                           step="0.01"
                                           class="w-full px-3 py-1 border border-border rounded-lg text-center text-sm">
                                </div>
                                <span class="text-xs text-muted" x-text="ingrediente.unidad"></span>
                                
                                <button type="button" 
                                        @click="eliminarIngrediente(index)"
                                        class="text-red-600 hover:text-red-800 ml-2">
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
                        <i class="fas fa-save mr-2"></i> Actualizar Producto
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal de Imagen -->
    <div x-show="showImageModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75"
         x-cloak
         @keydown.escape.window="showImageModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="relative max-w-4xl w-full bg-white rounded-xl overflow-hidden shadow-2xl"
             @click.away="showImageModal = false"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-xl font-bold text-text" x-text="modalImageTitle"></h3>
                <button @click="showImageModal = false" class="text-muted hover:text-text transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div class="p-2 bg-gray-50 flex justify-center items-center">
                <img :src="modalImageUrl" :alt="modalImageTitle" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-inner">
            </div>
            
            <div class="p-4 flex justify-end">
                <button @click="showImageModal = false" class="btn-secondary">Cerrar</button>
            </div>
        </div>
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
</script>
@endpush
@endsection