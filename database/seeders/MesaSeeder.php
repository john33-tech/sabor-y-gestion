<?php
// database/seeders/MesaSeeder.php
namespace Database\Seeders;

use App\Models\Mesa;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        // No truncate (rompe FKs si hay pedidos referenciando mesas).
        // Usamos updateOrCreate por numero_mesa más abajo, idempotente.

        // Crear mesas específicas con datos realistas
        $mesasEspecificas = [
            // Mesas Planta Baja
            [
                'numero_mesa' => '101', 
                'estado' => 'libre', 
                'area' => 'Planta Baja', 
                'capacidad' => 4,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            [
                'numero_mesa' => '102', 
                'estado' => 'ocupado', 
                'area' => 'Planta Baja', 
                'capacidad' => 4,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            [
                'numero_mesa' => '103', 
                'estado' => 'reservado', 
                'area' => 'Planta Baja', 
                'capacidad' => 6,
                'hora_reserva' => Carbon::now()->addDays(1)->setTime(20, 0),
                'cliente_reserva' => 'Carlos Rodríguez',
                'telefono_reserva' => 3114567890
            ],
            [
                'numero_mesa' => '104', 
                'estado' => 'libre', 
                'area' => 'Planta Baja', 
                'capacidad' => 2,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            
            // Mesas Segundo Piso
            [
                'numero_mesa' => '201', 
                'estado' => 'libre', 
                'area' => 'Segundo Piso', 
                'capacidad' => 8,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            [
                'numero_mesa' => '202', 
                'estado' => 'ocupado', 
                'area' => 'Segundo Piso', 
                'capacidad' => 4,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            [
                'numero_mesa' => '203', 
                'estado' => 'reservado', 
                'area' => 'Segundo Piso', 
                'capacidad' => 6,
                'hora_reserva' => Carbon::now()->addDays(2)->setTime(19, 30),
                'cliente_reserva' => 'Ana Martínez',
                'telefono_reserva' => 3109876543
            ],
            [
                'numero_mesa' => '204', 
                'estado' => 'libre', 
                'area' => 'Segundo Piso', 
                'capacidad' => 4,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            
            // Mesas Jardín PB
            [
                'numero_mesa' => 'J01', 
                'estado' => 'libre', 
                'area' => 'Jardín PB', 
                'capacidad' => 6,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            [
                'numero_mesa' => 'J02', 
                'estado' => 'reservado', 
                'area' => 'Jardín PB', 
                'capacidad' => 8,
                'hora_reserva' => Carbon::now()->addDays(3)->setTime(21, 0),
                'cliente_reserva' => 'Laura Gómez',
                'telefono_reserva' => 3221234567
            ],
            [
                'numero_mesa' => 'J03', 
                'estado' => 'ocupado', 
                'area' => 'Jardín PB', 
                'capacidad' => 4,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            [
                'numero_mesa' => 'J04', 
                'estado' => 'libre', 
                'area' => 'Jardín PB', 
                'capacidad' => 2,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            
            // Mesas Terraza
            [
                'numero_mesa' => 'T01', 
                'estado' => 'libre', 
                'area' => 'Terraza', 
                'capacidad' => 4,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            [
                'numero_mesa' => 'T02', 
                'estado' => 'reservado', 
                'area' => 'Terraza', 
                'capacidad' => 6,
                'hora_reserva' => Carbon::now()->addDays(1)->setTime(18, 0),
                'cliente_reserva' => 'Pedro Sánchez',
                'telefono_reserva' => 3156789012
            ],
            [
                'numero_mesa' => 'T03', 
                'estado' => 'libre', 
                'area' => 'Terraza', 
                'capacidad' => 2,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            
            // Mesas VIP
            [
                'numero_mesa' => 'VIP1', 
                'estado' => 'libre', 
                'area' => 'VIP', 
                'capacidad' => 10,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
            [
                'numero_mesa' => 'VIP2', 
                'estado' => 'reservado', 
                'area' => 'VIP', 
                'capacidad' => 8,
                'hora_reserva' => Carbon::now()->addDays(5)->setTime(20, 30),
                'cliente_reserva' => 'Ricardo López',
                'telefono_reserva' => 3004567890
            ],
            [
                'numero_mesa' => 'VIP3', 
                'estado' => 'ocupado', 
                'area' => 'VIP', 
                'capacidad' => 12,
                'hora_reserva' => null,
                'cliente_reserva' => null,
                'telefono_reserva' => null
            ],
        ];

        // Crear las mesas específicas (idempotente con updateOrCreate por numero_mesa).
        foreach ($mesasEspecificas as $mesa) {
            Mesa::updateOrCreate(
                ['numero_mesa' => $mesa['numero_mesa']],
                $mesa
            );
        }

        // Nota: NO usamos Mesa::factory() porque Faker es dev-only y no está
        // disponible en el contenedor de producción. Las 18 mesas específicas
        // de arriba son suficientes.
    }
}