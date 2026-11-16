<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los birretes solo son NORMAL o UNIVERSITARIO. Los antiguos guardados como
 * ESTANDAR ya se trataban como Normal (mismo precio y misma regla); aquí se
 * pasan a NORMAL y se quita ESTANDAR de la lista permitida.
 *
 * No toca las togas: ellas sí siguen teniendo el tipo "Estándar".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('producto_birretes')
            ->where('tipo_birrete', 'ESTANDAR')
            ->update(['tipo_birrete' => 'NORMAL']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE producto_birretes MODIFY tipo_birrete ENUM('NORMAL', 'UNIVERSITARIO') NOT NULL");
        }
    }

    public function down(): void
    {
        // Solo se vuelve a permitir el valor; los birretes siguen como NORMAL.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE producto_birretes MODIFY tipo_birrete ENUM('ESTANDAR', 'NORMAL', 'UNIVERSITARIO') NOT NULL");
        }
    }
};
