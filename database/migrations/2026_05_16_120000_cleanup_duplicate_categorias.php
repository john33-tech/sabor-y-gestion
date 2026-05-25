<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Limpia categorías duplicadas (mismo nombre, varios IDs) generadas por
 * corridas previas del seeder no-idempotente. Para cada nombre repetido:
 *  - Encuentra el ID más bajo (canónico).
 *  - Reasigna todos los platos que apuntan a IDs mayores hacia el canónico.
 *  - Elimina las filas duplicadas.
 *
 * Idempotente: si no hay duplicados, no hace nada.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('categorias')) {
            return;
        }

        // Agrupar por nombre y obtener los IDs duplicados
        $duplicadosPorNombre = DB::table('categorias')
            ->select('nombre', DB::raw('MIN(id) as canonical_id'))
            ->groupBy('nombre')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        DB::transaction(function () use ($duplicadosPorNombre) {
            foreach ($duplicadosPorNombre as $grupo) {
                // IDs duplicados (todos los que comparten nombre menos el canónico)
                $idsDuplicados = DB::table('categorias')
                    ->where('nombre', $grupo->nombre)
                    ->where('id', '!=', $grupo->canonical_id)
                    ->pluck('id')
                    ->all();

                if (empty($idsDuplicados)) {
                    continue;
                }

                // Reasignar platos a la categoría canónica
                DB::table('platos')
                    ->whereIn('categoria_id', $idsDuplicados)
                    ->update(['categoria_id' => $grupo->canonical_id]);

                // Borrar las filas duplicadas
                DB::table('categorias')->whereIn('id', $idsDuplicados)->delete();
            }
        });
    }

    public function down(): void
    {
        // No es reversible: no recreamos duplicados.
    }
};
