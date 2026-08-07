<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alquiler_fabricaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alquiler_id')
                ->constrained('alquileres')
                ->cascadeOnDelete();

            $table->foreignId('alquiler_detalle_id')
                ->nullable()
                ->constrained('alquiler_detalles')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete();

            $table->integer('cantidad_solicitada')->default(0);
            $table->integer('cantidad_pendiente')->default(0);

            $table->string('responsable', 255)->nullable();
            $table->string('motivo', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha')->nullable();
            $table->string('estado', 50)->default('AUTORIZADO');
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alquiler_fabricaciones');
    }
};
