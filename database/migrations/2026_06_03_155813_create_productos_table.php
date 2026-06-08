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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 50)->unique();
            $table->string('nombre', 150);

            $table->enum('tipo_producto', [
                'TOGA',
                'BIRRETE',
                'COLLARIN',
            ]);

            $table->text('descripcion')->nullable();

            $table->decimal('precio_alquiler', 10, 2)->default(0);

            $table->unsignedInteger('stock_total')->default(0);
            $table->unsignedInteger('stock_disponible')->default(0);
            $table->unsignedInteger('stock_alquilado')->default(0);

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};