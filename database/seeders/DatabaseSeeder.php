<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

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
    }
}
