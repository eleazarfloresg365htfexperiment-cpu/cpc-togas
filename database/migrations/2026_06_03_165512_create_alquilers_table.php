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
        Schema::create('alquileres', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->restrictOnDelete();

            $table->string('codigo_recibo', 100)->unique();

            $table->date('fecha_alquiler');
            $table->date('fecha_entrega')->nullable();
            $table->date('fecha_devolucion_programada')->nullable();
            $table->date('fecha_devolucion_real')->nullable();

            $table->enum('estado', [
                'RESERVADO',
                'ENTREGADO',
                'DEVUELTO',
                'CANCELADO',
            ])->default('RESERVADO');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);

            $table->text('observaciones')->nullable();

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
        Schema::dropIfExists('alquileres');
    }
};