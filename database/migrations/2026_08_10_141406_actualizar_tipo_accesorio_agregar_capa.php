<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alquiler_detalle_accesorios', function (Blueprint $table) {
            $table->enum('tipo_accesorio', [
                'COLLARIN',
                'BIRRETE',
                'BORLA',
                'CAPA',
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('alquiler_detalle_accesorios', function (Blueprint $table) {
            $table->enum('tipo_accesorio', [
                'COLLARIN',
                'BIRRETE',
                'BORLA',
            ])->change();
        });
    }
};