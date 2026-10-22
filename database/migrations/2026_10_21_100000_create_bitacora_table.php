<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora: quién hizo qué y cuándo (alquileres, pagos, entregas,
 * inventario, productos, clientes, usuarios, inicio de sesión).
 * Tabla nueva: no toca ningún dato existente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('categoria', 30);   // ALQUILER, PAGO, INVENTARIO, PRODUCTO, CLIENTE, USUARIO, SESION
            $table->string('accion', 60);      // texto corto: "Registró un pago"
            $table->text('descripcion');       // detalle legible
            $table->foreignId('alquiler_id')->nullable()->constrained('alquileres')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at']);
            $table->index(['categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
