<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CashClosure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivamos FKs por si algún seeder hace updates cruzados.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Orden estricto por dependencias:
        //  - Categoria   ← Plato
        //  - Ingrediente ← Inventario, PlatoIngrediente
        //  - Plato       ← PlatoIngrediente
        $this->call([
            UserSeeder::class,
            CategoriaSeeder::class,
            MesaSeeder::class,
            IngredienteSeeder::class,
            PlatoSeeder::class,
            InventarioSeeder::class,
            PlatoIngredienteSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $cashier = User::where('email', 'cajero@saborgestion.com')->first();

        if ($cashier) {
            // Cierre 1: Abierto (sin cerrar)
            CashClosure::factory()->create([
                'user_id' => $cashier->id,
                'status' => 'Open',
                'opening_date' => now(),
                'closing_date' => null,
                'final_amount' => null,
                'total_sales' => null,
                'total_cash' => null,
                'total_card' => null,
                'total_qr' => null,
                'difference' => null,
                'observations' => 'Apertura de caja del día',
            ]);

            // Cierre 2: Cerrado (hace 5 días)
            CashClosure::factory()->create([
                'user_id' => $cashier->id,
                'status' => 'Closed',
                'opening_date' => now()->subDays(5)->setTime(8, 0),
                'closing_date' => now()->subDays(5)->setTime(20, 0),
                'initial_amount' => 1000.00,
                'final_amount' => 2350.50,
                'total_sales' => 1350.50,
                'total_cash' => 800.00,
                'total_card' => 450.00,
                'total_qr' => 100.50,
                'difference' => 0.00,
                'observations' => 'Cierre normal sin novedades',
            ]);

            // Cierre 3: Cerrado (hace 2 días)
            CashClosure::factory()->create([
                'user_id' => $cashier->id,
                'status' => 'Closed',
                'opening_date' => now()->subDays(2)->setTime(8, 0),
                'closing_date' => now()->subDays(2)->setTime(21, 30),
                'initial_amount' => 1200.00,
                'final_amount' => 3100.00,
                'total_sales' => 1900.00,
                'total_cash' => 1100.00,
                'total_card' => 700.00,
                'total_qr' => 100.00,
                'difference' => 10.00,
                'observations' => 'Diferencia positiva por redondeo',
            ]);
        }
    }
}
