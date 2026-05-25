<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Limpieza one-shot: elimina platos duplicados (mismo nombre)
 * preservando el de id MAYOR. Maneja FKs reasignando detalle_pedidos
 * y borrando plato_ingrediente de los duplicados.
 */
return new class extends Migration {
    public function up(): void
    {
        $duplicados = DB::table('platos')
            ->select('nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('nombre')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicados === 0) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::statement('DROP TEMPORARY TABLE IF EXISTS plato_keep');
        DB::statement('CREATE TEMPORARY TABLE plato_keep AS
            SELECT MAX(id) AS keep_id, nombre FROM platos GROUP BY nombre');

        DB::statement('DELETE dp FROM detalle_pedidos dp
            JOIN platos p ON dp.plato_id = p.id
            JOIN plato_keep k ON p.nombre = k.nombre
            WHERE dp.plato_id != k.keep_id
              AND EXISTS (
                SELECT 1 FROM (SELECT * FROM detalle_pedidos) dp2
                WHERE dp2.pedido_id = dp.pedido_id AND dp2.plato_id = k.keep_id
              )');

        DB::statement('UPDATE detalle_pedidos dp
            JOIN platos p ON dp.plato_id = p.id
            JOIN plato_keep k ON p.nombre = k.nombre
            SET dp.plato_id = k.keep_id
            WHERE dp.plato_id != k.keep_id');

        DB::statement('DELETE FROM plato_ingrediente
            WHERE plato_id IN (
                SELECT id FROM platos WHERE id NOT IN (SELECT keep_id FROM plato_keep)
            )');

        DB::statement('DELETE FROM platos
            WHERE id NOT IN (SELECT keep_id FROM plato_keep)');

        DB::statement('DROP TEMPORARY TABLE IF EXISTS plato_keep');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // No-op
    }
};
