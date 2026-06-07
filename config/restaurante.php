<?php

/**
 * Datos del restaurante (origen de los envíos a domicilio).
 * Usado para calcular distancia / tiempo estimado y dibujar la ruta en los mapas.
 */
return [
    'nombre'    => 'Sabor & Gestión',
    'direccion' => 'Av. Libertador Simón Bolívar 1141, Cochabamba',
    'lat'       => -17.3795921440698,
    'lng'       => -66.16032484561605,

    // Velocidad promedio urbana estimada (km/h) para calcular el tiempo de viaje.
    'velocidad_kmh' => 22,
    // Minutos base (preparación + salida del repartidor) que se suman al viaje.
    'minutos_base'  => 12,

    // Costo de envío (delivery): cobro = envio_base + envio_por_km * distancia_km.
    // Actual: solo por km (Bs 2/km, sin base).
    'envio_base'    => 0,
    'envio_por_km'  => 2,
];
