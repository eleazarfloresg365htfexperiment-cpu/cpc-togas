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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete();

            $table->enum('tipo_movimiento', [
                'ENTRADA',
                'SALIDA',
                'ALQUILER',
                'DEVOLUCION',
                'AJUSTE',
            ]);

            $table->unsignedInteger('cantidad');

            $table->unsignedInteger('stock_anterior_disponible');
            $table->unsignedInteger('stock_nuevo_disponible');

            $table->unsignedInteger('stock_anterior_alquilado');
            $table->unsignedInteger('stock_nuevo_alquilado');

            $table->string('motivo', 255)->nullable();
            $table->string('referencia', 100)->nullable();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};