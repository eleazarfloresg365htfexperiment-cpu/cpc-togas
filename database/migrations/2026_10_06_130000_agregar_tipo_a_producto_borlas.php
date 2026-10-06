<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el tipo de borla (NORMAL o UNIVERSITARIA). Rojo y Verde existen en
 * los dos tipos, así que el color ya no basta para distinguirlas.
 *
 * No cambia colores ni códigos. Solo clasifica:
 *  - Celeste, Amarillo, Naranja (colores que solo tienen las carreras) y los
 *    nombres antiguos "Rojo-Derecho" / "Verde-Agronomia" → UNIVERSITARIA.
 *  - Todo lo demás (Dorado, Rojo, Verde...) → NORMAL. Las universitarias
 *    rojas o verdes se marcan después, editando cada borla.
 */
return new class extends Migration
{
    private const SOLO_UNIVERSITARIAS = ['Celeste', 'Amarillo', 'Naranja', 'Rojo-Derecho', 'Verde-Agronomia'];

    public function up(): void
    {
        if (!Schema::hasColumn('producto_borlas', 'tipo_borla')) {
            Schema::table('producto_borlas', function (Blueprint $table) {
                $table->string('tipo_borla', 20)->default('NORMAL')->after('producto_id');
            });
        }

        DB::table('producto_borlas')
            ->whereIn('color', self::SOLO_UNIVERSITARIAS)
            ->update(['tipo_borla' => 'UNIVERSITARIA']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('producto_borlas', 'tipo_borla')) {
            Schema::table('producto_borlas', function (Blueprint $table) {
                $table->dropColumn('tipo_borla');
            });
        }
    }
};
