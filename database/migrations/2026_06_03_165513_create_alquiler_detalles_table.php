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
        Schema::create('alquiler_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alquiler_id')
                ->constrained('alquileres')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete();

            $table->unsignedInteger('cantidad');

            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);

            $table->enum('estado', [
                'PENDIENTE',
                'ENTREGADO',
                'DEVUELTO',
                'CANCELADO',
            ])->default('PENDIENTE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alquiler_detalles');
    }
};