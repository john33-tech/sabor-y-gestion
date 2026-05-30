<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de "Cierre de Caja" (arqueo de turno). Cada registro es un arqueo:
 * el cajero cierra todo lo cobrado desde el último cierre, con totales por
 * método de pago, el efectivo contado físicamente y la diferencia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users'); // cajero que cerró la caja
            $table->dateTime('fecha_cierre');

            // Totales registrados por el sistema (de las facturas pagadas)
            $table->decimal('total_efectivo', 10, 2)->default(0);
            $table->decimal('total_tarjeta', 10, 2)->default(0);
            $table->decimal('total_qr', 10, 2)->default(0);
            $table->decimal('total_transferencia', 10, 2)->default(0);
            $table->decimal('total_general', 10, 2)->default(0);
            $table->unsignedInteger('cantidad_facturas')->default(0);

            // Arqueo: efectivo contado a mano vs efectivo registrado
            $table->decimal('efectivo_contado', 10, 2)->default(0);
            $table->decimal('diferencia', 10, 2)->default(0); // efectivo_contado - total_efectivo

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('fecha_cierre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_caja');
    }
};
