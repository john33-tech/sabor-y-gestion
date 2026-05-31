<?php
// database/seeders/PlatoSeeder.php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Plato;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Menú profesional de cocina BOLIVIANA (~30 platos). Cada plato tiene su
 * categoría, precio (Bs), descripción y puntuación. La imagen queda en null;
 * se sube luego desde Platos → Editar. Idempotente (updateOrCreate por nombre).
 */
class PlatoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = Categoria::all()->keyBy('nombre');

        $platos = [
            // ── Entradas ─────────────────────────────────────────────
            ['nombre' => 'Salteña de Carne',     'precio' => 8.00,  'cat' => 'Entradas', 'score' => 4.9, 'desc' => 'Empanada jugosa de carne con papa, arveja y huevo.'],
            ['nombre' => 'Salteña de Pollo',     'precio' => 8.00,  'cat' => 'Entradas', 'score' => 4.8, 'desc' => 'Empanada jugosa de pollo con papa y arveja.'],
            ['nombre' => 'Tucumana',             'precio' => 6.00,  'cat' => 'Entradas', 'score' => 4.6, 'desc' => 'Empanada frita rellena de carne, papa y cebolla.'],
            ['nombre' => 'Empanada de Queso',    'precio' => 5.00,  'cat' => 'Entradas', 'score' => 4.5, 'desc' => 'Empanada al horno rellena de queso fundido.'],
            ['nombre' => 'Cuñapé',               'precio' => 4.00,  'cat' => 'Entradas', 'score' => 4.7, 'desc' => 'Pancito de almidón de yuca con queso.'],

            // ── Sopas ────────────────────────────────────────────────
            ['nombre' => 'Sopa de Maní',         'precio' => 15.00, 'cat' => 'Sopas', 'score' => 4.9, 'desc' => 'Tradicional sopa de maní con carne, papa y fideo.'],
            ['nombre' => 'Chairo Paceño',        'precio' => 16.00, 'cat' => 'Sopas', 'score' => 4.7, 'desc' => 'Sopa paceña con chuño, carne, mondongo y haba.'],
            ['nombre' => 'Sopa de Quinua',       'precio' => 14.00, 'cat' => 'Sopas', 'score' => 4.6, 'desc' => 'Sopa nutritiva de quinua con verduras y carne.'],
            ['nombre' => 'Fricasé',              'precio' => 18.00, 'cat' => 'Sopas', 'score' => 4.8, 'desc' => 'Cerdo en caldo de ají amarillo con mote y chuño.'],

            // ── Platos Principales ───────────────────────────────────
            ['nombre' => 'Silpancho',            'precio' => 22.00, 'cat' => 'Platos Principales', 'score' => 4.9, 'desc' => 'Carne apanada sobre arroz y papa con huevo frito y ensalada.'],
            ['nombre' => 'Pique Macho',          'precio' => 30.00, 'cat' => 'Platos Principales', 'score' => 5.0, 'desc' => 'Carne, salchicha y papas fritas con tomate, cebolla y locoto.'],
            ['nombre' => 'Charquekan',           'precio' => 25.00, 'cat' => 'Platos Principales', 'score' => 4.7, 'desc' => 'Charque desmenuzado con mote, papa, huevo y queso.'],
            ['nombre' => 'Majadito',             'precio' => 20.00, 'cat' => 'Platos Principales', 'score' => 4.6, 'desc' => 'Arroz graneado con charque y huevo frito.'],
            ['nombre' => 'Sajta de Pollo',       'precio' => 20.00, 'cat' => 'Platos Principales', 'score' => 4.7, 'desc' => 'Pollo en salsa de ají amarillo con chuño y papa.'],
            ['nombre' => 'Picante de Pollo',     'precio' => 20.00, 'cat' => 'Platos Principales', 'score' => 4.7, 'desc' => 'Pollo en ají colorado con papa, chuño y arroz.'],
            ['nombre' => 'Falso Conejo',         'precio' => 18.00, 'cat' => 'Platos Principales', 'score' => 4.5, 'desc' => 'Carne apanada en salsa con arroz, papa y arveja.'],
            ['nombre' => 'Ranga Ranga',          'precio' => 18.00, 'cat' => 'Platos Principales', 'score' => 4.4, 'desc' => 'Guiso de mondongo en ají colorado con papa.'],
            ['nombre' => 'Lengua a la Plancha',  'precio' => 24.00, 'cat' => 'Platos Principales', 'score' => 4.6, 'desc' => 'Lengua de res a la plancha con arroz y papa.'],
            ['nombre' => 'Lasaña',               'precio' => 24.00, 'cat' => 'Platos Principales', 'score' => 4.8, 'desc' => 'Italiana: capas de pasta con carne, salsa de tomate y queso gratinado.'],
            ['nombre' => 'Hamburguesa Clásica',  'precio' => 20.00, 'cat' => 'Platos Principales', 'score' => 4.7, 'desc' => 'Pan, carne, queso, tomate, lechuga y cebolla con papas fritas.'],
            ['nombre' => 'Milanesa Napolitana',  'precio' => 26.00, 'cat' => 'Platos Principales', 'score' => 4.8, 'desc' => 'Argentina: milanesa con salsa de tomate y queso gratinado, con papas.'],

            // ── Parrillas ────────────────────────────────────────────
            ['nombre' => 'Pacumutu',             'precio' => 35.00, 'cat' => 'Parrillas', 'score' => 4.8, 'desc' => 'Brocheta gigante de carne con yuca y arroz.'],
            ['nombre' => 'Chicharrón de Cerdo',  'precio' => 28.00, 'cat' => 'Parrillas', 'score' => 4.9, 'desc' => 'Chicharrón crocante con mote y papa.'],
            ['nombre' => 'Pollo a la Brasa',     'precio' => 25.00, 'cat' => 'Parrillas', 'score' => 4.7, 'desc' => 'Pollo asado a la brasa con papas fritas.'],
            ['nombre' => 'Anticucho',            'precio' => 15.00, 'cat' => 'Parrillas', 'score' => 4.6, 'desc' => 'Brocheta a la parrilla con papa y salsa de maní.'],
            ['nombre' => 'Churrasco',            'precio' => 35.00, 'cat' => 'Parrillas', 'score' => 4.9, 'desc' => 'Argentino: jugoso bife a la parrilla con papa y arroz.'],
            ['nombre' => 'Costillas BBQ',        'precio' => 38.00, 'cat' => 'Parrillas', 'score' => 4.8, 'desc' => 'Costillas de cerdo glaseadas en salsa BBQ con papa.'],
            ['nombre' => 'Brochetas Mixtas',     'precio' => 28.00, 'cat' => 'Parrillas', 'score' => 4.7, 'desc' => 'Brochetas de res y pollo con cebolla, tomate y locoto.'],

            // ── Ensaladas ────────────────────────────────────────────
            ['nombre' => 'Ensalada Criolla',     'precio' => 8.00,  'cat' => 'Ensaladas', 'score' => 4.4, 'desc' => 'Tomate, cebolla y locoto picados con perejil.'],
            ['nombre' => 'Ensalada de Quinua',   'precio' => 12.00, 'cat' => 'Ensaladas', 'score' => 4.6, 'desc' => 'Quinua con lechuga, tomate y zanahoria.'],
            ['nombre' => 'Ensalada César',       'precio' => 14.00, 'cat' => 'Ensaladas', 'score' => 4.7, 'desc' => 'Clásica italiana: lechuga, pollo, queso parmesano y crutones.'],
            ['nombre' => 'Ensalada Caprese',     'precio' => 13.00, 'cat' => 'Ensaladas', 'score' => 4.6, 'desc' => 'Italiana: tomate, queso fresco, albahaca y aceite de oliva.'],
            ['nombre' => 'Ensalada Rusa',        'precio' => 10.00, 'cat' => 'Ensaladas', 'score' => 4.5, 'desc' => 'Rusa: papa, zanahoria, arveja y huevo con mayonesa.'],

            // ── Postres ──────────────────────────────────────────────
            ['nombre' => 'Arroz con Leche',      'precio' => 8.00,  'cat' => 'Postres', 'score' => 4.7, 'desc' => 'Postre cremoso de arroz con leche y canela.'],
            ['nombre' => 'Tawa Tawa',            'precio' => 6.00,  'cat' => 'Postres', 'score' => 4.5, 'desc' => 'Masa frita dulce bañada en miel.'],
            ['nombre' => 'Flan Casero',          'precio' => 8.00,  'cat' => 'Postres', 'score' => 4.6, 'desc' => 'Flan de huevo con caramelo.'],

            // ── Bebidas ──────────────────────────────────────────────
            ['nombre' => 'Mocochinchi',          'precio' => 5.00,  'cat' => 'Bebidas', 'score' => 4.6, 'desc' => 'Refresco de durazno deshidratado con canela.'],
            ['nombre' => 'Api con Pastel',       'precio' => 8.00,  'cat' => 'Bebidas', 'score' => 4.8, 'desc' => 'Bebida caliente de maíz morado con pastel frito.'],
            ['nombre' => 'Refresco de Linaza',   'precio' => 5.00,  'cat' => 'Bebidas', 'score' => 4.4, 'desc' => 'Refresco de linaza con limón.'],
            ['nombre' => 'Café con Leche',       'precio' => 5.00,  'cat' => 'Bebidas', 'score' => 4.5, 'desc' => 'Café caliente con leche.'],
            ['nombre' => 'Jugo de Naranja',      'precio' => 7.00,  'cat' => 'Bebidas', 'score' => 4.6, 'desc' => 'Jugo de naranja recién exprimido.'],
            ['nombre' => 'Capuchino',            'precio' => 8.00,  'cat' => 'Bebidas', 'score' => 4.7, 'desc' => 'Italiano: espresso con leche espumada.'],
            ['nombre' => 'Té Helado',            'precio' => 6.00,  'cat' => 'Bebidas', 'score' => 4.4, 'desc' => 'Té frío con limón y un toque de azúcar.'],
        ];

        $i = 0;
        foreach ($platos as $p) {
            $categoria = $categorias[$p['cat']] ?? null;
            if (!$categoria) {
                continue;
            }
            $fecha = Carbon::now()->subDays(30 - ($i % 30));
            Plato::updateOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'precio'       => $p['precio'],
                    'categoria_id' => $categoria->id,
                    'imagen'       => null,
                    'disponible'   => true,
                    'score'        => $p['score'],
                    'descripcion'  => $p['desc'],
                    'created_at'   => $fecha,
                    'updated_at'   => $fecha,
                ]
            );
            $i++;
        }
    }
}
