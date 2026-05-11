<?php
// database/seeders/PlatoIngredienteSeeder.php
namespace Database\Seeders;

use App\Models\Plato;
use App\Models\Ingrediente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatoIngredienteSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotente: usamos updateOrInsert por (plato_id, ingrediente_id).
        // Buscamos por nombre para no depender de IDs hardcodeados, ya que
        // platos/ingredientes pueden tener IDs distintos según orden de seed.
        $platos       = Plato::all()->keyBy('nombre');
        $ingredientes = Ingrediente::all()->keyBy('nombre');

        // Tabla: receta básica de algunos platos.
        $recetas = [
            ['plato' => 'Pollo a la Brasa', 'ingrediente' => 'Pollo',   'cantidad' => 250],
            ['plato' => 'Pollo a la Brasa', 'ingrediente' => 'Papa',    'cantidad' => 200],
            ['plato' => 'Lomo Saltado',     'ingrediente' => 'Carne',   'cantidad' => 200],
            ['plato' => 'Lomo Saltado',     'ingrediente' => 'Papa',    'cantidad' => 150],
            ['plato' => 'Lomo Saltado',     'ingrediente' => 'Tomate',  'cantidad' => 80],
            ['plato' => 'Lomo Saltado',     'ingrediente' => 'Arroz',   'cantidad' => 200],
            ['plato' => 'Ensalada César',   'ingrediente' => 'Lechuga', 'cantidad' => 150],
            ['plato' => 'Ensalada César',   'ingrediente' => 'Pollo',   'cantidad' => 120],
        ];

        foreach ($recetas as $r) {
            $plato = $platos[$r['plato']] ?? null;
            $ing   = $ingredientes[$r['ingrediente']] ?? null;
            if (!$plato || !$ing) {
                continue; // si falta cualquiera, salteamos sin romper
            }

            DB::table('plato_ingrediente')->updateOrInsert(
                ['plato_id' => $plato->id, 'ingrediente_id' => $ing->id],
                ['cantidad' => $r['cantidad']]
            );
        }
    }
}
