<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_mesa', 10)->unique();
            $table->enum('estado', ['libre', 'ocupado', 'reservado','fuera_servicio'])->default('libre');
            $table->string('area')->nullable();
            $table->integer('capacidad')->default(4);
            $table->datetime('hora_reserva')->nullable();
            $table->string('cliente_reserva')->nullable();
            $table->string('telefono_reserva', 20)->nullable(); // Cambiado a string
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};