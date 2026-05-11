<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingrediente_id')->constrained()->onDelete('cascade');
            $table->decimal('cantidad_actual', 10, 2)->default(0);
            $table->decimal('stock_minimo', 10, 2)->default(100);
            $table->decimal('stock_maximo', 10, 2)->nullable();
            $table->string('ubicacion')->nullable();
            $table->timestamps();
            
            $table->unique('ingrediente_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventario');
    }
};