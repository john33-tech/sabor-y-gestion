@forelse($comandas as $comanda)
<div class="comanda-card bg-white rounded-lg shadow-md overflow-hidden" style="border: 1px solid #FED7AA;" data-estado="{{ $comanda->estado }}">
    <!-- Cabecera -->
    <div class="p-4 border-b" style="background-color: #FFF7ED;">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="font-bold text-lg" style="color: #C2410C;">
                    <i class="fas fa-receipt mr-1"></i> #{{ $comanda->numero_pedido }}
                </h3>
                <p class="text-xs text-muted mt-1">
                    <i class="far fa-clock mr-1"></i> 
                    {{ $comanda->created_at->format('d/m/Y H:i') }}
                    <span class="mx-1">•</span>
                    <i class="fas fa-user mr-1"></i>
                    {{ $comanda->usuario->name ?? 'N/A' }}
                </p>
            </div>
            <div>
                @if($comanda->estado == 'pendiente')
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                        <i class="fas fa-hourglass-half mr-1"></i> Pendiente
                    </span>
                @elseif($comanda->estado == 'en_preparacion')
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        <i class="fas fa-fire mr-1"></i> En Preparación
                    </span>
                @elseif($comanda->estado == 'listo')
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i> Listo
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Cuerpo -->
    <div class="p-4">
        <!-- Información del Pedido -->
        @if(!isset($soloPlatos) || !$soloPlatos)
        <div class="mb-3 pb-3 border-b" style="border-color: #FED7AA;">
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                    <span class="text-muted">Tipo:</span>
                    <span class="font-semibold ml-1">{{ $tipos[$comanda->tipo_pedido] }}</span>
                </div>
                @if($comanda->tipo_pedido == 'mesa')
                    <div>
                        <span class="text-muted">Mesa:</span>
                        <span class="font-semibold ml-1">Mesa {{ $comanda->mesa->numero_mesa ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-muted">Área:</span>
                        <span class="font-semibold ml-1">{{ $comanda->mesa->area ?? 'Sin área' }}</span>
                    </div>
                    <div>
                        <span class="text-muted">Capacidad:</span>
                        <span class="font-semibold ml-1">{{ $comanda->mesa->capacidad ?? 'N/A' }} personas</span>
                    </div>
                @else
                    <div class="col-span-2">
                        <span class="text-muted">Cliente:</span>
                        <span class="font-semibold ml-1">{{ $comanda->cliente_nombre }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-muted">Teléfono:</span>
                        <span class="font-semibold ml-1">{{ $comanda->cliente_telefono }}</span>
                    </div>
                    @if($comanda->direccion)
                        <div class="col-span-2">
                            <span class="text-muted">Dirección:</span>
                            <span class="font-semibold ml-1">{{ $comanda->direccion }}</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
        @endif

        <!-- Lista de Platos -->
        <div class="mb-3">
            <h4 class="font-semibold mb-2 text-sm" style="color: #111827;">
                <i class="fas fa-utensils mr-1"></i> Platos:
                <span class="text-xs text-muted font-normal">({{ $comanda->detalles->count() }} items)</span>
            </h4>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($comanda->detalles as $detalle)
                <div class="detalle-item flex justify-between items-start p-2 rounded" style="background-color: #FFF7ED;">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-sm">{{ $detalle->plato->nombre }}</span>
                            <span class="text-xs text-muted">x{{ $detalle->cantidad }}</span>
                        </div>
                        <div class="text-xs text-muted mt-1">
                            <i class="fas fa-dollar-sign mr-1"></i> Bs. {{ number_format($detalle->precio_unitario, 2) }}
                        </div>
                        @if($detalle->notas)
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-sticky-note mr-1"></i> {{ $detalle->notas }}
                            </div>
                        @endif
                    </div>
                    <div class="ml-2">
                        <select class="estado-detalle text-xs px-2 py-1 rounded border" 
                                data-id="{{ $detalle->id }}"
                                data-pedido="{{ $comanda->id }}"
                                style="border-color: #FED7AA; background-color: white;">
                            <option value="pendiente" {{ $detalle->estado == 'pendiente' ? 'selected' : '' }}>
                                ⏳ Pendiente
                            </option>
                            <option value="en_preparacion" {{ $detalle->estado == 'en_preparacion' ? 'selected' : '' }}>
                                🔥 En Preparación
                            </option>
                            <option value="listo" {{ $detalle->estado == 'listo' ? 'selected' : '' }}>
                                ✅ Listo
                            </option>
                            <option value="entregado" {{ $detalle->estado == 'entregado' ? 'selected' : '' }}>
                                📦 Entregado
                            </option>
                        </select>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Notas adicionales del pedido -->
        @if($comanda->notas)
            <div class="mb-3 p-2 rounded text-sm" style="background-color: #FFF7ED; color: #78716C;">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Notas del pedido:</strong> {{ $comanda->notas }}
            </div>
        @endif

        <!-- Temporizador (opcional) -->
        <div class="mb-3 text-xs text-muted">
            <i class="fas fa-hourglass-start mr-1"></i>
            Tiempo transcurrido: <span class="font-semibold" data-timestamp="{{ $comanda->created_at->timestamp }}">calculando...</span>
        </div>

        <!-- Botones de acción -->
        <div class="flex gap-2 mt-3 pt-3 border-t" style="border-color: #FED7AA;">
            @if($comanda->estado == 'pendiente')
                <form action="{{ route('comandas.iniciar-preparacion', $comanda) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="btn-iniciar w-full px-3 py-2 rounded-lg text-white transition hover:opacity-90" style="background-color: #3B82F6;">
                        <i class="fas fa-play mr-1"></i> Iniciar Preparación
                    </button>
                </form>
            @endif
            
            @if(in_array($comanda->estado, ['pendiente', 'en_preparacion']))
                <form action="{{ route('comandas.marcar-listo', $comanda) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="btn-listo w-full px-3 py-2 rounded-lg text-white transition hover:opacity-90" style="background-color: #10B981;">
                        <i class="fas fa-check mr-1"></i> Marcar Listo
                    </button>
                </form>
            @endif
            
            <a href="{{ route('comandas.print', $comanda) }}" target="_blank" class="flex-1">
                <button type="button" class="w-full px-3 py-2 rounded-lg text-white transition hover:opacity-90" style="background-color: #78716C;">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </button>
            </a>
        </div>
    </div>
</div>
@empty
<div class="col-span-2">
    <div class="text-center py-12 bg-white rounded-lg shadow-md" style="border: 1px solid #FED7AA;">
        <i class="fas fa-check-circle text-6xl mb-4" style="color: #10B981;"></i>
        <h3 class="text-xl font-semibold mb-2" style="color: #111827;">¡Cocina al día!</h3>
        <p class="text-muted">No hay pedidos pendientes o en preparación</p>
    </div>
</div>
@endforelse