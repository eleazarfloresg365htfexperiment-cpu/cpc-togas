<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->string('institucion_representada')->nullable()->after('cliente_id');
            $table->string('representante_alquiler')->nullable()->after('institucion_representada');

            $table->time('hora_entrega_inicio')->nullable()->after('fecha_entrega');
            $table->time('hora_entrega_fin')->nullable()->after('hora_entrega_inicio');

            $table->date('fecha_limite_pago_final')->nullable()->after('saldo_pendiente');
        });
    }

    public function down(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn([
                'institucion_representada',
                'representante_alquiler',
                'hora_entrega_inicio',
                'hora_entrega_fin',
                'fecha_limite_pago_final',
            ]);
        });
    }
};