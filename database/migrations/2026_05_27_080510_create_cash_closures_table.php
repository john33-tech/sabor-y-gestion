<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->index();
            $table->decimal('initial_amount', 10, 2);
            $table->decimal('final_amount', 10, 2)->nullable();
            $table->decimal('total_sales', 10, 2)->nullable();
            $table->decimal('total_cash', 10, 2)->nullable();
            $table->decimal('total_card', 10, 2)->nullable();
            $table->decimal('total_qr', 10, 2)->nullable();
            $table->decimal('difference', 10, 2)->nullable();
            $table->datetime('opening_date')->useCurrent(); // default now()
            $table->datetime('closing_date')->nullable();
            $table->enum('status', ['Open', 'Closed'])->default('Open');
            $table->text('observations')->nullable();
            $table->timestamps();

            // Índices adicionales
            $table->index('status');
            $table->index('opening_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_closures');
    }
};
