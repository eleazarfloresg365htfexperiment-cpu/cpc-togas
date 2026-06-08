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
        Schema::create('producto_collarines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->enum('tipo_collarin', [
                'NORMAL',
                'UNIVERSITARIO',
            ]);

            $table->enum('color', [
                'DORADO',
                'ROJO',
                'VERDE',
            ])->nullable();

            $table->enum('tamano', [
                'PEQUENO',
                'GRANDE',
            ])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_collarines');
    }
};