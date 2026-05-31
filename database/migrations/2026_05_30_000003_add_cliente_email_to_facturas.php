<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email del cliente capturado al cobrar (para mostrarlo en la factura y
 * enviársela). Antes la factura mostraba el email del usuario del sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->string('cliente_email', 255)->nullable()->after('cliente_telefono');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn('cliente_email');
        });
    }
};
