<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===========================
        // PRODUCTO_CAPAS
        // ===========================
        Schema::table('producto_capas', function (Blueprint $table) {

            if (!Schema::hasColumn('producto_capas', 'codigo_color')) {
                $table->string('codigo_color', 20)
                    ->nullable()
                    ->after('talla');
            }

        });

        // ===========================
        // PRODUCTO_COLLARINES
        // ===========================
        Schema::table('producto_collarines', function (Blueprint $table) {

            if (!Schema::hasColumn('producto_collarines', 'codigo_color')) {
                $table->string('codigo_color', 20)
                    ->nullable()
                    ->before('color');
            }

        });

        // ===========================
        // PRODUCTO_BORLAS
        // ===========================
        Schema::table('producto_borlas', function (Blueprint $table) {

            if (!Schema::hasColumn('producto_borlas', 'codigo_color')) {
                $table->string('codigo_color', 20)
                    ->nullable()
                    ->before('color');
            }

        });
    }

    public function down(): void
    {
        Schema::table('producto_capas', function (Blueprint $table) {

            if (Schema::hasColumn('producto_capas', 'codigo_color')) {
                $table->dropColumn('codigo_color');
            }

        });

        Schema::table('producto_collarines', function (Blueprint $table) {

            if (Schema::hasColumn('producto_collarines', 'codigo_color')) {
                $table->dropColumn('codigo_color');
            }

        });

        Schema::table('producto_borlas', function (Blueprint $table) {

            if (Schema::hasColumn('producto_borlas', 'codigo_color')) {
                $table->dropColumn('codigo_color');
            }

        });
    }
};