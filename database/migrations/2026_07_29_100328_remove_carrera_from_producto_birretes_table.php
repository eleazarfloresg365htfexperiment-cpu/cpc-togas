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
        Schema::table('producto_birretes', function (Blueprint $table) {
            $table->dropColumn('carrera');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto_birretes', function (Blueprint $table) {
            $table->enum('carrera', [
                'ADMINISTRACION',
                'AGRONOMIA',
                'DERECHO',
                'PEDAGOGIA',
            ])->nullable()->after('color');
        });
    }
};