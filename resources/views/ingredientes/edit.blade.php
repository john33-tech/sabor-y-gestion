{{-- resources/views/ingredientes/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{ showImageModal: false, modalImageUrl: '', modalImageTitle: '' }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-primary">Editar Ingrediente: {{ $ingrediente->nombre }}</h1>
        <a href="{{ route('ingredientes.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    <div class="card">
        <form action="{{ route('ingredientes.update', $ingrediente) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-text mb-2">Nombre *</label>
                    <input type="text" 
                           name="nombre" 
                           value="{{ old('nombre', $ingrediente->nombre) }}" 
                           required
                           class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 @error('nombre') border-red-500 @enderror">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">Unidad de Medida *</label>
                    <select name="unidad_medida" 
                            required
                            class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary @error('unidad_medida') border-red-500 @enderror">
                        <option value="gr" {{ old('unidad_medida', $ingrediente->unidad_medida) == 'gr' ? 'selected' : '' }}>Gramos (gr)</option>
                        <option value="ml" {{ old('unidad_medida', $ingrediente->unidad_medida) == 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                        <option value="unidad" {{ old('unidad_medida', $ingrediente->unidad_medida) == 'unidad' ? 'selected' : '' }}>Unidad</option>
                        <option value="cda" {{ old('unidad_medida', $ingrediente->unidad_medida) == 'cda' ? 'selected' : '' }}>Cucharada (cda)</option>
                    </select>
                    @error('unidad_medida')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">Foto del Ingrediente</label>
                    <div class="mt-1 flex items-center space-x-4">
                        <div id="photoPreview" class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center border-2 border-dashed border-border overflow-hidden">
                            @if($ingrediente->foto)
                                <img src="{{ Storage::url($ingrediente->foto) }}" 
                                     class="w-full h-full object-cover cursor-pointer hover:opacity-75"
                                     @click="modalImageUrl = '{{ Storage::url($ingrediente->foto) }}'; modalImageTitle = '{{ $ingrediente->nombre }}'; showImageModal = true">
                            @else
                                <i class="fas fa-camera text-gray-400 text-2xl"></i>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" 
                                   name="foto" 
                                   id="foto"
                                   accept="image/*"
                                   class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary">
                            <p class="text-xs text-muted mt-1">
                                <i class="fas fa-info-circle"></i> 
                                Dejar vacío para mantener la foto actual. Formatos: JPG, PNG, GIF. Máximo 2MB
                            </p>
                        </div>
                    </div>
                    @error('foto')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Información adicional -->
                <div class="bg-background rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-text mb-2">Información estadística</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted">Fecha de creación:</span>
                            <p class="font-medium">
                                {{ $ingrediente->created_at ? $ingrediente->created_at->format('d/m/Y H:i') : 'No disponible' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-muted">Última actualización:</span>
                            <p class="font-medium">
                                {{ $ingrediente->updated_at ? $ingrediente->updated_at->format('d/m/Y H:i') : 'No disponible' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-muted">Usado en platos:</span>
                            <p class="font-medium text-primary">{{ $ingrediente->platos_count ?? $ingrediente->platos()->count() }} platos</p>
                        </div>
                    </div>
                </div>

                @if(($ingrediente->platos_count ?? $ingrediente->platos()->count()) > 0)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium">Este ingrediente está siendo usado en platos</p>
                                <p class="mt-1">No se puede eliminar mientras esté asociado a uno o más platos. Para eliminarlo, primero debes removerlo de los platos que lo utilizan.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('ingredientes.index') }}" class="btn-secondary px-6">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary px-6">
                        <i class="fas fa-save mr-2"></i> Actualizar Ingrediente
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
    const fotoInput = document.getElementById('foto');
    const photoPreview = document.getElementById('photoPreview');
    
    fotoInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
@endpush
@endsection