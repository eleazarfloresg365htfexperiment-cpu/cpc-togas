<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alquiler_detalle_accesorios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alquiler_detalle_id')
                ->constrained('alquiler_detalles')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete();

            $table->enum('tipo_accesorio', [
                'COLLARIN',
                'BIRRETE',
                'BORLA',
            ]);

            $table->enum('tipo_cobro', [
                'INCLUIDO',
                'EXTRA',
            ])->default('INCLUIDO');

            $table->integer('cantidad')->default(1);

            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('total_linea', 10, 2)->default(0);

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alquiler_detalle_accesorios');
    }
};