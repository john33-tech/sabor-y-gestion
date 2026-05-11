<?php

namespace Database\Seeders;

use App\Models\Inventario;
use App\Models\Ingrediente;
use Illuminate\Database\Seeder;

class InventarioSeeder extends Seeder
{
    public function run()
    {
        $ingredientes = Ingrediente::all();
        
        foreach ($ingredientes as $ingrediente) {
            Inventario::create([
                'ingrediente_id' => $ingrediente->id,
                'cantidad_actual' => rand(0, 500),
                'stock_minimo' => 100,
                'stock_maximo' => 1000,
                'ubicacion' => 'Estante ' . chr(rand(65, 70))
            ]);
        }
    }
}