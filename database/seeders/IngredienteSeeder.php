<?php
// database/seeders/IngredienteSeeder.php
namespace Database\Seeders;

use App\Models\Ingrediente;
use Illuminate\Database\Seeder;

/**
 * Catálogo de ingredientes para una cocina BOLIVIANA. Cada uno con su unidad
 * de medida (gr / ml / unidad). Idempotente: updateOrCreate por nombre.
 */
class IngredienteSeeder extends Seeder
{
    public function run(): void
    {
        $ingredientes = [
            // Proteínas
            ['nombre' => 'Carne de res',    'unidad_medida' => 'gr'],
            ['nombre' => 'Carne molida',    'unidad_medida' => 'gr'],
            ['nombre' => 'Pollo',           'unidad_medida' => 'gr'],
            ['nombre' => 'Carne de cerdo',  'unidad_medida' => 'gr'],
            ['nombre' => 'Charque',         'unidad_medida' => 'gr'],
            ['nombre' => 'Lengua de res',   'unidad_medida' => 'gr'],
            ['nombre' => 'Mondongo',        'unidad_medida' => 'gr'],
            ['nombre' => 'Chorizo',         'unidad_medida' => 'gr'],
            ['nombre' => 'Huevo',           'unidad_medida' => 'unidad'],

            // Granos y carbohidratos
            ['nombre' => 'Papa',            'unidad_medida' => 'gr'],
            ['nombre' => 'Chuño',           'unidad_medida' => 'gr'],
            ['nombre' => 'Arroz',           'unidad_medida' => 'gr'],
            ['nombre' => 'Fideo',           'unidad_medida' => 'gr'],
            ['nombre' => 'Maíz',            'unidad_medida' => 'gr'],
            ['nombre' => 'Quinua',          'unidad_medida' => 'gr'],
            ['nombre' => 'Maní',            'unidad_medida' => 'gr'],
            ['nombre' => 'Harina',          'unidad_medida' => 'gr'],
            ['nombre' => 'Yuca',            'unidad_medida' => 'gr'],
            ['nombre' => 'Pan',             'unidad_medida' => 'unidad'],

            // Verduras
            ['nombre' => 'Tomate',          'unidad_medida' => 'gr'],
            ['nombre' => 'Cebolla',         'unidad_medida' => 'gr'],
            ['nombre' => 'Lechuga',         'unidad_medida' => 'gr'],
            ['nombre' => 'Locoto',          'unidad_medida' => 'gr'],
            ['nombre' => 'Zanahoria',       'unidad_medida' => 'gr'],
            ['nombre' => 'Haba',            'unidad_medida' => 'gr'],
            ['nombre' => 'Arveja',          'unidad_medida' => 'gr'],
            ['nombre' => 'Perejil',         'unidad_medida' => 'gr'],
            ['nombre' => 'Albahaca',        'unidad_medida' => 'gr'],

            // Lácteos y grasas
            ['nombre' => 'Queso',           'unidad_medida' => 'gr'],
            ['nombre' => 'Leche',           'unidad_medida' => 'ml'],
            ['nombre' => 'Aceite',          'unidad_medida' => 'ml'],

            // Condimentos y dulces
            ['nombre' => 'Ají amarillo',    'unidad_medida' => 'gr'],
            ['nombre' => 'Ají colorado',    'unidad_medida' => 'gr'],
            ['nombre' => 'Canela',          'unidad_medida' => 'gr'],
            ['nombre' => 'Azúcar',          'unidad_medida' => 'gr'],

            // Frutas y bases de bebidas
            ['nombre' => 'Durazno',         'unidad_medida' => 'gr'],
            ['nombre' => 'Limón',           'unidad_medida' => 'unidad'],
            ['nombre' => 'Maíz morado',     'unidad_medida' => 'gr'],
            ['nombre' => 'Linaza',          'unidad_medida' => 'gr'],
            ['nombre' => 'Café',            'unidad_medida' => 'gr'],
        ];

        foreach ($ingredientes as $ing) {
            Ingrediente::updateOrCreate(
                ['nombre' => $ing['nombre']],
                ['unidad_medida' => $ing['unidad_medida']]
            );
        }
    }
}
