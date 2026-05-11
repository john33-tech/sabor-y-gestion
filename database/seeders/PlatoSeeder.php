<?php
// database/seeders/PlatoSeeder.php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Plato;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PlatoSeeder extends Seeder  // ← Debe ser PlatoSeeder, NO DatabaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener las categorías por su nombre para relacionar
        $categorias = Categoria::all()->keyBy('nombre');
        
        // Definir los platos con sus respectivas categorías y fechas
        $platos = [
            // Entradas
            [
                'nombre' => 'Bruschetta Italiana',
                'precio' => 8.50,
                'categoria_nombre' => 'Entradas',
                'imagen' => 'bruschetta.jpg',
                'disponible' => true,
                'score' => 4.5,
                'descripcion' => 'Pan tostado con tomates frescos, albahaca y aceite de oliva',
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
            ],
            [
                'nombre' => 'Nachos con Queso',
                'precio' => 7.90,
                'categoria_nombre' => 'Entradas',
                'imagen' => 'nachos.jpg',
                'disponible' => true,
                'score' => 4.2,
                'descripcion' => 'Totopos de maíz con queso fundido y guacamole',
                'created_at' => Carbon::now()->subDays(28),
                'updated_at' => Carbon::now()->subDays(28),
            ],
            
            // Platos Principales
            [
                'nombre' => 'Lomo Saltado',
                'precio' => 18.50,
                'categoria_nombre' => 'Platos Principales',
                'imagen' => 'lomo_saltado.jpg',
                'disponible' => true,
                'score' => 4.8,
                'descripcion' => 'Lomo de res salteado con cebolla, tomate y papas fritas',
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(25),
            ],
            [
                'nombre' => 'Pollo a la Brasa',
                'precio' => 15.90,
                'categoria_nombre' => 'Platos Principales',
                'imagen' => 'pollo_brasa.jpg',
                'disponible' => true,
                'score' => 4.7,
                'descripcion' => 'Pollo asado al estilo tradicional con papas y ensalada',
                'created_at' => Carbon::now()->subDays(27),
                'updated_at' => Carbon::now()->subDays(27),
            ],
            [
                'nombre' => 'Ceviche Mixto',
                'precio' => 22.00,
                'categoria_nombre' => 'Platos Principales',
                'imagen' => 'ceviche.jpg',
                'disponible' => true,
                'score' => 4.9,
                'descripcion' => 'Pescado y mariscos marinados en limón con ají y camote',
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            
            // Bebidas
            [
                'nombre' => 'Pisco Sour',
                'precio' => 12.00,
                'categoria_nombre' => 'Bebidas',
                'imagen' => 'pisco_sour.jpg',
                'disponible' => true,
                'score' => 4.9,
                'descripcion' => 'Bebida tradicional peruana con pisco, limón y claras de huevo',
                'created_at' => Carbon::now()->subDays(29),
                'updated_at' => Carbon::now()->subDays(29),
            ],
            [
                'nombre' => 'Chicha Morada',
                'precio' => 5.00,
                'categoria_nombre' => 'Bebidas',
                'imagen' => 'chicha_morada.jpg',
                'disponible' => true,
                'score' => 4.6,
                'descripcion' => 'Refresco de maíz morado con especias y frutas',
                'created_at' => Carbon::now()->subDays(26),
                'updated_at' => Carbon::now()->subDays(26),
            ],
            [
                'nombre' => 'Limonada Frozen',
                'precio' => 6.50,
                'categoria_nombre' => 'Bebidas',
                'imagen' => 'limonada_frozen.jpg',
                'disponible' => true,
                'score' => 4.4,
                'descripcion' => 'Limonada con hielo raspado y hierba buena',
                'created_at' => Carbon::now()->subDays(24),
                'updated_at' => Carbon::now()->subDays(24),
            ],
            
            // Postres
            [
                'nombre' => 'Suspiro a la Limeña',
                'precio' => 9.00,
                'categoria_nombre' => 'Postres',
                'imagen' => 'suspiro.jpg',
                'disponible' => true,
                'score' => 4.8,
                'descripcion' => 'Dulce de leche con merengue de vino oporto',
                'created_at' => Carbon::now()->subDays(23),
                'updated_at' => Carbon::now()->subDays(23),
            ],
            [
                'nombre' => 'Volcán de Chocolate',
                'precio' => 8.50,
                'categoria_nombre' => 'Postres',
                'imagen' => 'volcan_chocolate.jpg',
                'disponible' => true,
                'score' => 4.7,
                'descripcion' => 'Pastel de chocolate con centro líquido y helado de vainilla',
                'created_at' => Carbon::now()->subDays(22),
                'updated_at' => Carbon::now()->subDays(22),
            ],
            
            // Ensaladas
            [
                'nombre' => 'Ensalada César',
                'precio' => 11.50,
                'categoria_nombre' => 'Ensaladas',
                'imagen' => 'ensalada_cesar.jpg',
                'disponible' => true,
                'score' => 4.3,
                'descripcion' => 'Lechuga romana, pollo grillado, crutones y salsa césar',
                'created_at' => Carbon::now()->subDays(21),
                'updated_at' => Carbon::now()->subDays(21),
            ],
            [
                'nombre' => 'Ensalada de Quinoa',
                'precio' => 12.00,
                'categoria_nombre' => 'Ensaladas',
                'imagen' => 'quinoa.jpg',
                'disponible' => true,
                'score' => 4.5,
                'descripcion' => 'Quinoa orgánica con vegetales asados y vinagreta balsámica',
                'created_at' => Carbon::now()->subDays(19),
                'updated_at' => Carbon::now()->subDays(19),
            ],
        ];
        
        // Insertar los platos (idempotente: updateOrCreate por nombre).
        foreach ($platos as $plato) {
            $categoria = $categorias[$plato['categoria_nombre']] ?? null;

            if ($categoria) {
                Plato::updateOrCreate(
                    ['nombre' => $plato['nombre']],
                    [
                        'precio'       => $plato['precio'],
                        'categoria_id' => $categoria->id,
                        'imagen'       => $plato['imagen'],
                        'disponible'   => $plato['disponible'],
                        'score'        => $plato['score'],
                        'descripcion'  => $plato['descripcion'],
                        'created_at'   => $plato['created_at'],
                        'updated_at'   => $plato['updated_at'],
                    ]
                );
            }
        }
    }
}