<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | BORLAS
        |--------------------------------------------------------------------------
        | La carrera ya no pertenece a la borla.
        | El color identifica la carrera cuando la borla es incluida,
        | pero una borla extra puede ser de cualquier color.
        */
        if (Schema::hasColumn('producto_borlas', 'carrera')) {
            Schema::table('producto_borlas', function (Blueprint $table) {
                $table->dropColumn('carrera');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | BIRRETES
        |--------------------------------------------------------------------------
        | Los birretes ya no pertenecen a una carrera.
        */
        if (Schema::hasColumn('producto_birretes', 'carrera')) {
            Schema::table('producto_birretes', function (Blueprint $table) {
                $table->dropColumn('carrera');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | CAPAS
        |--------------------------------------------------------------------------
        | La capa es la que define la carrera.
        */
        if (!Schema::hasColumn('producto_capas', 'carrera')) {
            Schema::table('producto_capas', function (Blueprint $table) {
                $table->enum('carrera', [
                    'ADMINISTRACION',
                    'AGRONOMIA',
                    'DERECHO',
                    'PEDAGOGIA',
                    'MEDICINA',
                    'CIENCIAS_ECONOMICAS',
                ])->after('color');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | COLLARINES
        |--------------------------------------------------------------------------
        | El collarín solamente conserva el color.
        | No representa la carrera.
        */
        if (Schema::hasTable('producto_collarines')) {
            if (Schema::hasColumn('producto_collarines', 'carrera')) {
                Schema::table('producto_collarines', function (Blueprint $table) {
                    $table->dropColumn('carrera');
                });
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('producto_birretes', 'carrera')) {
            Schema::table('producto_birretes', function (Blueprint $table) {
                $table->enum('carrera', [
                    'ADMINISTRACION',
                    'AGRONOMIA',
                    'DERECHO',
                    'PEDAGOGIA',
                ])->nullable()->after('color');
            });
        }

        if (!Schema::hasColumn('producto_borlas', 'carrera')) {
            Schema::table('producto_borlas', function (Blueprint $table) {
                $table->enum('carrera', [
                    'ADMINISTRACION',
                    'AGRONOMIA',
                    'DERECHO',
                    'PEDAGOGIA',
                    'MEDICINA',
                    'CIENCIAS_ECONOMICAS',
                ])->nullable()->after('color');
            });
        }

        if (Schema::hasColumn('producto_capas', 'carrera')) {
            Schema::table('producto_capas', function (Blueprint $table) {
                $table->dropColumn('carrera');
            });
        }
    }
};