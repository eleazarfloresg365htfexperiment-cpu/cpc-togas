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
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dateTime('fecha_hora_devolucion_real')
                ->nullable()
                ->after('fecha_devolucion_real');

            $table->unsignedInteger('dias_mora')
                ->default(0)
                ->after('fecha_hora_devolucion_real');

            $table->decimal('monto_mora_calculado', 10, 2)
                ->default(0)
                ->after('dias_mora');

            $table->decimal('descuento_mora', 10, 2)
                ->default(0)
                ->after('monto_mora_calculado');

            $table->decimal('monto_mora', 10, 2)
                ->default(0)
                ->after('descuento_mora');

            $table->text('observacion_mora')
                ->nullable()
                ->after('monto_mora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_hora_devolucion_real',
                'dias_mora',
                'monto_mora_calculado',
                'descuento_mora',
                'monto_mora',
                'observacion_mora',
            ]);
        });
    }
};