<?php
// database/seeders/CategoriaSeeder.php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Entradas', 'icono' => 'fa-bread-slice', 'descripcion' => 'Aperitivos y entradas'],
            ['nombre' => 'Platos Principales', 'icono' => 'fa-utensils', 'descripcion' => 'Nuestros platos estrella'],
            ['nombre' => 'Bebidas', 'icono' => 'fa-wine-bottle', 'descripcion' => 'Bebidas refrescantes'],
            ['nombre' => 'Postres', 'icono' => 'fa-ice-cream', 'descripcion' => 'Dulces tentaciones'],
            ['nombre' => 'Ensaladas', 'icono' => 'fa-leaf', 'descripcion' => 'Opciones saludables'],
        ];
        
        // Usamos firstOrCreate para que el seeder sea idempotente y no
        // duplique las categorías si corre varias veces (ej. RUN_SEEDERS=true
        // en varios deploys consecutivos).
        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }
    }
}