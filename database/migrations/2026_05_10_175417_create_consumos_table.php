<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consumos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido');
            $table->foreignId('pedido_id')->constrained()->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo_pedido'); // mesa, delivery, para_llevar
            $table->string('estado'); // completado, cancelado
            $table->decimal('subtotal', 10, 2);
            $table->decimal('impuesto', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->json('detalles'); // Guardar los platos consumidos
            $table->dateTime('fecha_consumo');
            $table->timestamps();

            $table->index(['fecha_consumo', 'tipo_pedido', 'estado']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('consumos');
    }
};
