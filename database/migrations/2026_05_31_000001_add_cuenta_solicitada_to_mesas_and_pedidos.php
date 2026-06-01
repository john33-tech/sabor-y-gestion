<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fase 4 de la especificación: cuando el mesero/cliente "Solicita la cuenta",
 * la mesa pasa al estado "cuenta_solicitada" y aparece habilitada en Caja.
 *
 * - Agrega 'cuenta_solicitada' al enum de estado de mesas.
 * - Agrega la marca booleana 'cuenta_solicitada' a pedidos (la cuenta se pide
 *   sobre los pedidos de la mesa; la Caja filtra por esta marca).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Enum de mesas: agregar el nuevo estado (MySQL/TiDB).
        DB::statement("ALTER TABLE mesas MODIFY COLUMN estado ENUM('libre','ocupado','reservado','fuera_servicio','cuenta_solicitada') NOT NULL DEFAULT 'libre'");

        Schema::table('pedidos', function (Blueprint $table) {
            $table->boolean('cuenta_solicitada')->default(false)->after('estado');
            $table->dateTime('cuenta_solicitada_at')->nullable()->after('cuenta_solicitada');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['cuenta_solicitada', 'cuenta_solicitada_at']);
        });

        DB::statement("ALTER TABLE mesas MODIFY COLUMN estado ENUM('libre','ocupado','reservado','fuera_servicio') NOT NULL DEFAULT 'libre'");
    }
};
