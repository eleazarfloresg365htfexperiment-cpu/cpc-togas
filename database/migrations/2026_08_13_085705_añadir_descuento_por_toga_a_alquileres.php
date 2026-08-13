<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega la columna que faltaba para persistir la tarifa de
     * descuento por toga ingresada por el usuario. Hasta ahora solo
     * se guardaba el resultado ya calculado (descuento_toga), pero no
     * la tarifa que lo originó.
     */
    public function up(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->decimal('descuento_por_toga', 10, 2)
                ->default(0)
                ->after('descuento_manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn('descuento_por_toga');
        });
    }
};