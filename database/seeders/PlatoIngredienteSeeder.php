<?php
// database/seeders/PlatoIngredienteSeeder.php
namespace Database\Seeders;

use App\Models\Plato;
use App\Models\Ingrediente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Recetas de cada plato (boliviano): qué ingredientes consume y cuánto.
 * Idempotente: updateOrInsert por (plato_id, ingrediente_id). Busca por nombre
 * para no depender de IDs. Cantidades en la unidad del ingrediente (gr/ml/unid).
 */
class PlatoIngredienteSeeder extends Seeder
{
    public function run(): void
    {
        $platos       = Plato::all()->keyBy('nombre');
        $ingredientes = Ingrediente::all()->keyBy('nombre');

        $recetas = [
            // Entradas
            'Salteña de Carne'    => ['Carne molida' => 100, 'Papa' => 60, 'Arveja' => 30, 'Huevo' => 1, 'Harina' => 120],
            'Salteña de Pollo'    => ['Pollo' => 100, 'Papa' => 60, 'Arveja' => 30, 'Huevo' => 1, 'Harina' => 120],
            'Tucumana'            => ['Carne molida' => 90, 'Papa' => 70, 'Cebolla' => 30, 'Harina' => 110],
            'Empanada de Queso'   => ['Queso' => 80, 'Harina' => 100],
            'Cuñapé'              => ['Yuca' => 70, 'Queso' => 60],

            // Sopas
            'Sopa de Maní'        => ['Maní' => 120, 'Carne de res' => 100, 'Papa' => 100, 'Fideo' => 50, 'Arveja' => 30],
            'Chairo Paceño'       => ['Carne de res' => 100, 'Chuño' => 80, 'Papa' => 80, 'Haba' => 50, 'Mondongo' => 60],
            'Sopa de Quinua'      => ['Quinua' => 90, 'Papa' => 80, 'Zanahoria' => 50, 'Carne de res' => 80],
            'Fricasé'             => ['Carne de cerdo' => 180, 'Maíz' => 100, 'Chuño' => 80, 'Ají amarillo' => 30],

            // Platos Principales
            'Silpancho'           => ['Carne de res' => 150, 'Arroz' => 120, 'Papa' => 100, 'Huevo' => 1, 'Tomate' => 50, 'Cebolla' => 40],
            'Pique Macho'         => ['Carne de res' => 150, 'Chorizo' => 80, 'Papa' => 150, 'Tomate' => 50, 'Cebolla' => 50, 'Locoto' => 20, 'Huevo' => 1],
            'Charquekan'          => ['Charque' => 120, 'Maíz' => 100, 'Papa' => 100, 'Huevo' => 1, 'Queso' => 50],
            'Majadito'            => ['Arroz' => 150, 'Charque' => 100, 'Huevo' => 1, 'Cebolla' => 40],
            'Sajta de Pollo'      => ['Pollo' => 180, 'Chuño' => 80, 'Ají amarillo' => 30, 'Cebolla' => 40, 'Papa' => 80],
            'Picante de Pollo'    => ['Pollo' => 180, 'Ají colorado' => 30, 'Papa' => 90, 'Chuño' => 70, 'Arroz' => 100],
            'Falso Conejo'        => ['Carne de res' => 150, 'Arroz' => 120, 'Papa' => 90, 'Arveja' => 30, 'Ají amarillo' => 20],
            'Ranga Ranga'         => ['Mondongo' => 150, 'Papa' => 120, 'Ají colorado' => 30],
            'Lengua a la Plancha' => ['Lengua de res' => 160, 'Papa' => 100, 'Arroz' => 120, 'Tomate' => 40],

            // Parrillas
            'Pacumutu'            => ['Carne de res' => 250, 'Yuca' => 120, 'Arroz' => 120],
            'Chicharrón de Cerdo' => ['Carne de cerdo' => 220, 'Maíz' => 100, 'Papa' => 120],
            'Pollo a la Brasa'    => ['Pollo' => 350, 'Papa' => 150],
            'Anticucho'           => ['Carne de res' => 120, 'Papa' => 100, 'Maní' => 40],

            // Ensaladas
            'Ensalada Criolla'    => ['Tomate' => 80, 'Cebolla' => 60, 'Locoto' => 20, 'Perejil' => 10],
            'Ensalada de Quinua'  => ['Quinua' => 80, 'Lechuga' => 50, 'Tomate' => 50, 'Zanahoria' => 40],

            // Postres
            'Arroz con Leche'     => ['Arroz' => 80, 'Leche' => 200, 'Azúcar' => 50, 'Canela' => 5],
            'Tawa Tawa'           => ['Harina' => 120, 'Huevo' => 1, 'Azúcar' => 40],
            'Flan Casero'         => ['Huevo' => 2, 'Leche' => 200, 'Azúcar' => 60],

            // Bebidas
            'Mocochinchi'         => ['Durazno' => 60, 'Azúcar' => 50, 'Canela' => 5],
            'Api con Pastel'      => ['Maíz morado' => 100, 'Azúcar' => 50, 'Canela' => 5, 'Harina' => 80],
            'Refresco de Linaza'  => ['Linaza' => 50, 'Limón' => 1, 'Azúcar' => 40],
            'Café con Leche'      => ['Café' => 15, 'Leche' => 150, 'Azúcar' => 20],
        ];

        foreach ($recetas as $nombrePlato => $items) {
            $plato = $platos[$nombrePlato] ?? null;
            if (!$plato) {
                continue;
            }
            foreach ($items as $nombreIng => $cantidad) {
                $ing = $ingredientes[$nombreIng] ?? null;
                if (!$ing) {
                    continue;
                }
                DB::table('plato_ingrediente')->updateOrInsert(
                    ['plato_id' => $plato->id, 'ingrediente_id' => $ing->id],
                    ['cantidad' => $cantidad]
                );
            }
        }
    }
}
