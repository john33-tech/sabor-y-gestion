<?php
// database/seeders/IngredienteSeeder.php
namespace Database\Seeders;

use App\Models\Ingrediente;
use Illuminate\Database\Seeder;

class IngredienteSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotente: usa updateOrCreate por nombre para no duplicar al re-ejecutar.
        $ingredientes = [
            ['nombre' => 'Pollo',   'unidad_medida' => 'gr'],
            ['nombre' => 'Carne',   'unidad_medida' => 'gr'],
            ['nombre' => 'Arroz',   'unidad_medida' => 'gr'],
            ['nombre' => 'Papa',    'unidad_medida' => 'gr'],
            ['nombre' => 'Lechuga', 'unidad_medida' => 'gr'],
            ['nombre' => 'Tomate',  'unidad_medida' => 'gr'],
        ];

        foreach ($ingredientes as $ing) {
            Ingrediente::updateOrCreate(
                ['nombre' => $ing['nombre']],
                ['unidad_medida' => $ing['unidad_medida']]
            );
        }
    }
}
