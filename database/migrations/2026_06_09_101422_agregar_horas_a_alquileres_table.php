<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->time('hora_entrega')->nullable()->after('fecha_entrega');
            $table->time('hora_devolucion_programada')->nullable()->after('fecha_devolucion_programada');
        });
    }

    public function down(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn([
                'hora_entrega',
                'hora_devolucion_programada',
            ]);
        });
    }
};