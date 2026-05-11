{{-- resources/views/ingredientes/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-primary">Crear Nuevo Ingrediente</h1>
        <a href="{{ route('ingredientes.index') }}" class="btn-secondary">
            <i class="mr-2 fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card">
        <form action="{{ route('ingredientes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <div>
                    <label class="block mb-2 text-sm font-medium text-text">Nombre *</label>
                    <input type="text"
                           name="nombre"
                           value="{{ old('nombre') }}"
                           required
                           class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 @error('nombre') border-red-500 @enderror">
                    @error('nombre')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-text">Unidad de Medida *</label>
                    <select name="unidad_medida"
                            required
                            class="w-full px-4 py-2 rounded-lg border border-border focus:outline-none focus:border-primary @error('unidad_medida') border-red-500 @enderror">
                        <option value="gr" {{ old('unidad_medida') == 'gr' ? 'selected' : '' }}>Gramos (gr)</option>
                        <option value="ml" {{ old('unidad_medida') == 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                        <option value="unidad" {{ old('unidad_medida') == 'unidad' ? 'selected' : '' }}>Unidad</option>
                        <option value="cda" {{ old('unidad_medida') == 'cda' ? 'selected' : '' }}>Cucharada (cda)</option>
                    </select>
                    @error('unidad_medida')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-text">Foto del Ingrediente</label>
                    <div class="flex items-center mt-1 space-x-4">
                        <div id="photoPreview" class="flex items-center justify-center w-24 h-24 bg-gray-100 border-2 border-dashed rounded-lg border-border">
                            <i class="text-2xl text-gray-400 fas fa-camera"></i>
                        </div>
                        <div class="flex-1">
                            <input type="file"
                                   name="foto"
                                   id="foto"
                                   accept="image/*"
                                   class="w-full px-4 py-2 border rounded-lg border-border focus:outline-none focus:border-primary">
                            <p class="mt-1 text-xs text-muted">
                                <i class="fas fa-info-circle"></i>
                                Formatos permitidos: JPG, PNG, GIF. Máximo 2MB
                            </p>
                        </div>
                    </div>
                    @error('foto')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-4 rounded-lg bg-background">
                    <h3 class="mb-2 text-sm font-semibold text-text">Información adicional</h3>
                    <p class="text-sm text-muted">
                        <i class="mr-1 fas fa-info-circle"></i>
                        Los ingredientes pueden ser utilizados en múltiples platos. La cantidad se especificará al momento de crear o editar cada plato.
                    </p>
                </div>

                <div class="flex justify-end pt-4 space-x-3">
                    <a href="{{ route('ingredientes.index') }}" class="px-6 btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 btn-primary">
                        <i class="mr-2 fas fa-save"></i> Guardar Ingrediente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Vista previa de la foto
    const fotoInput = document.getElementById('foto');
    const photoPreview = document.getElementById('photoPreview');

    fotoInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.innerHTML = `<img src="${e.target.result}" class="object-cover w-full h-full rounded-lg">`;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
@endpush
@endsection
