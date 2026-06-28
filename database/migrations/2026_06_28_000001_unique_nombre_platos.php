<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega un índice UNIQUE en platos.nombre para impedir nombres duplicados.
 *
 * Antes de crear el índice, elimina los duplicados que pudieran existir
 * en la BD conservando siempre el registro con el id MAYOR (el más reciente).
 * Los detalles de pedido y la tabla pivote plato_ingrediente se reasignan /
 * limpian para respetar las FK antes del borrado.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Paso 1 — deduplicar platos con el mismo nombre (si los hay).
        // Idéntico a la limpieza de 2026_05_11 pero usa sintaxis portátil
        // para TiDB (sin TEMPORARY TABLE, que no soporta bien la reutilización
        // dentro de la misma sesión en algunas versiones).
        $duplicados = DB::table('platos')
            ->select('nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('nombre')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicados as $dup) {
            // El id que conservamos (el mayor).
            $keepId = DB::table('platos')
                ->where('nombre', $dup->nombre)
                ->orderByDesc('id')
                ->value('id');

            // Ids a eliminar.
            $dropIds = DB::table('platos')
                ->where('nombre', $dup->nombre)
                ->where('id', '!=', $keepId)
                ->pluck('id');

            foreach ($dropIds as $dropId) {
                // Reasignar detalles de pedido al plato que conservamos.
                DB::table('detalle_pedidos')
                    ->where('plato_id', $dropId)
                    ->update(['plato_id' => $keepId]);

                // Eliminar la receta (escandallo) del duplicado.
                DB::table('plato_ingrediente')
                    ->where('plato_id', $dropId)
                    ->delete();

                // Eliminar el plato duplicado.
                DB::table('platos')->where('id', $dropId)->delete();
            }
        }

        // Paso 2 — agregar el índice UNIQUE.
        Schema::table('platos', function (Blueprint $table) {
            $table->unique('nombre', 'platos_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('platos', function (Blueprint $table) {
            $table->dropUnique('platos_nombre_unique');
        });
    }
};
