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
        Schema::create('producto_birretes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->enum('tipo_birrete', [
                'ESTANDAR',
                'NORMAL',
                'UNIVERSITARIO',
            ]);

            $table->string('color', 50)->nullable();

            $table->enum('carrera', [
                'ADMINISTRACION',
                'AGRONOMIA',
                'DERECHO',
                'PEDAGOGIA',
            ])->nullable();

            $table->boolean('tiene_borlas_extra')->default(false);
            $table->text('descripcion_borlas_extra')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_birretes');
    }
};