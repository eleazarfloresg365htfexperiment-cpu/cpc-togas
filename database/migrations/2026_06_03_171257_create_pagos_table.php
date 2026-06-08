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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alquiler_id')
                ->constrained('alquileres')
                ->cascadeOnDelete();

            $table->decimal('monto', 10, 2);

            $table->enum('metodo_pago', [
                'EFECTIVO',
                'TRANSFERENCIA',
                'TARJETA',
                'OTRO',
            ])->default('EFECTIVO');

            $table->string('referencia', 150)->nullable();
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
        Schema::dropIfExists('pagos');
    }
};
