<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula cada factura al arqueo (cierre de caja) en que fue contabilizada.
 * NULL = factura pagada aún no arqueada (pertenece al turno en curso).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->foreignId('cierre_caja_id')
                ->nullable()
                ->after('estado')
                ->constrained('cierres_caja')
                ->nullOnDelete();
            $table->index('cierre_caja_id');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropForeign(['cierre_caja_id']);
            $table->dropIndex(['cierre_caja_id']);
            $table->dropColumn('cierre_caja_id');
        });
    }
};
