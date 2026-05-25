@props(['estado'])

@php
    // El cliente ve un timeline lineal: pendiente → en_preparacion → listo → entregado.
    // "cancelado" y "facturado" se manejan como estados terminales fuera del flujo.
    $pasos = [
        'pendiente'      => ['icon' => 'fa-receipt',       'label' => 'Recibido'],
        'en_preparacion' => ['icon' => 'fa-fire',          'label' => 'En cocina'],
        'listo'          => ['icon' => 'fa-bell',          'label' => 'Listo'],
        'entregado'      => ['icon' => 'fa-check-double',  'label' => 'Entregado'],
    ];

    $orden = array_keys($pasos);
    $idxActual = array_search($estado, $orden);

    $cancelado = $estado === 'cancelado';
    $facturado = $estado === 'facturado';

    // Cuando el pedido llega al último paso del flujo ("entregado"), todos
    // los círculos quedan verdes con check — no hay paso "en progreso".
    $esFinal = $estado === 'entregado';
@endphp

<div class="w-full">
    @if($cancelado)
        <div class="flex items-center justify-center gap-2 px-4 py-3 text-sm bg-red-50 border border-red-200 text-red-700 rounded-lg">
            <i class="fas fa-ban"></i> Pedido cancelado
        </div>
    @elseif($facturado)
        <div class="flex items-center justify-center gap-2 px-4 py-3 text-sm bg-blue-50 border border-blue-200 text-blue-700 rounded-lg">
            <i class="fas fa-file-invoice-dollar"></i> Pedido facturado
        </div>
    @else
        <ol class="flex items-center w-full">
            @foreach($orden as $i => $clave)
                @php
                    $paso = $pasos[$clave];
                    $hecho = $idxActual !== false && ($i < $idxActual || $esFinal);
                    $actual = $i === $idxActual && !$esFinal;
                    $futuro = $idxActual === false || ($i > $idxActual && !$esFinal);
                @endphp
                <li class="relative flex-1 flex items-center @if(!$loop->last) after:content-[''] after:w-full after:h-1 after:inline-block after:absolute after:top-4 after:left-1/2
                    @if($hecho) after:bg-emerald-500 @else after:bg-gray-200 @endif
                    @endif">
                    <div class="flex flex-col items-center relative z-10 w-full">
                        <span class="flex items-center justify-center w-9 h-9 rounded-full ring-4 ring-white
                            @if($hecho) bg-emerald-500 text-white
                            @elseif($actual) bg-amber-500 text-white animate-pulse
                            @else bg-gray-200 text-gray-400
                            @endif">
                            @if($hecho)
                                <i class="fas fa-check text-xs"></i>
                            @else
                                <i class="fas {{ $paso['icon'] }} text-xs"></i>
                            @endif
                        </span>
                        <span class="mt-2 text-[10px] sm:text-xs text-center font-medium
                            @if($hecho) text-emerald-700
                            @elseif($actual) text-amber-700
                            @else text-gray-400
                            @endif">
                            {{ $paso['label'] }}
                        </span>
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
