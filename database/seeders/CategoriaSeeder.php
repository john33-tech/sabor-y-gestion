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
            ['nombre' => 'Entradas',           'icono' => 'fa-empanada',     'descripcion' => 'Salteñas, tucumanas y empanadas'],
            ['nombre' => 'Sopas',              'icono' => 'fa-mug-hot',      'descripcion' => 'Sopas y caldos tradicionales'],
            ['nombre' => 'Platos Principales', 'icono' => 'fa-utensils',     'descripcion' => 'Lo mejor de la cocina criolla boliviana'],
            ['nombre' => 'Parrillas',          'icono' => 'fa-fire',         'descripcion' => 'Carnes a la brasa y parrilla'],
            ['nombre' => 'Ensaladas',          'icono' => 'fa-leaf',         'descripcion' => 'Opciones frescas y saludables'],
            ['nombre' => 'Postres',            'icono' => 'fa-ice-cream',    'descripcion' => 'Dulces típicos para cerrar'],
            ['nombre' => 'Bebidas',            'icono' => 'fa-wine-bottle',  'descripcion' => 'Refrescos y bebidas tradicionales'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }
    }
}
