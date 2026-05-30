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
                    // Stock generoso para que la demo no se quede sin insumos
                    // (las recetas consumen 80-250 por plato).
                    'cantidad_actual' => 5000,
                    'stock_minimo'    => 100,
                    'stock_maximo'    => 10000,
                    'ubicacion'       => 'Estante ' . chr(rand(65, 70)),
                ]
            );
        }
    }
}
