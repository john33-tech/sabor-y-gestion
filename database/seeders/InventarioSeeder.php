<?php

namespace Database\Seeders;

use App\Models\Inventario;
use App\Models\Ingrediente;
use Illuminate\Database\Seeder;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotente: updateOrCreate por ingrediente_id para no duplicar.
        $ingredientes = Ingrediente::all();

        foreach ($ingredientes as $ingrediente) {
            Inventario::updateOrCreate(
                ['ingrediente_id' => $ingrediente->id],
                [
                    'cantidad_actual' => rand(150, 500), // arriba del stock mínimo
                    'stock_minimo'    => 100,
                    'stock_maximo'    => 1000,
                    'ubicacion'       => 'Estante ' . chr(rand(65, 70)),
                ]
            );
        }
    }
}
