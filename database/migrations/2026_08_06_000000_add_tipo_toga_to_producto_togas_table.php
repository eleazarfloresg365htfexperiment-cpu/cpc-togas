<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('producto_togas') && !Schema::hasColumn('producto_togas', 'tipo_toga')) {
            Schema::table('producto_togas', function (Blueprint $table) {
                $table->enum('tipo_toga', ['ESTANDAR', 'UNIVERSITARIA'])
                    ->default('ESTANDAR')
                    ->after('producto_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('producto_togas') && Schema::hasColumn('producto_togas', 'tipo_toga')) {
            Schema::table('producto_togas', function (Blueprint $table) {
                $table->dropColumn('tipo_toga');
            });
        }
    }
};
