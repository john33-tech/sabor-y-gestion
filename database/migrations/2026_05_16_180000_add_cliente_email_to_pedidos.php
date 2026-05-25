<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email opcional del cliente del pedido. Se usa como destinatario de la
 * factura al confirmar pago QR o al cerrar cuenta. Cuando está vacío,
 * el sistema cae al email del usuario que creó el pedido.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('cliente_email', 255)->nullable()->after('cliente_telefono');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('cliente_email');
        });
    }
};
