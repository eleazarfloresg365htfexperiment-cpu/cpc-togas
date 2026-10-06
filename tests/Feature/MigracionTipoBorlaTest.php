<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\Support\ConBaseDeDatosDePrueba;
use Tests\Support\CreaDatos;
use Tests\TestCase;

/**
 * La migración del tipo de borla clasifica las borlas que ya existían
 * sin tocar su color ni su código.
 */
class MigracionTipoBorlaTest extends TestCase
{
    use ConBaseDeDatosDePrueba;
    use CreaDatos;

    public function test_clasifica_las_borlas_existentes_sin_cambiar_color_ni_codigo(): void
    {
        $colores = ['Dorado', 'Rojo', 'Verde', 'Celeste', 'Amarillo', 'Naranja', 'Rojo-Derecho'];

        foreach ($colores as $color) {
            $this->borla($color, ['codigo' => 'BOR-' . $color]);
        }

        // Como estaría una base antigua: todas sin clasificar (NORMAL por defecto).
        DB::table('producto_borlas')->update(['tipo_borla' => 'NORMAL']);
        $antes = DB::table('producto_borlas')->orderBy('id')->get(['color', 'codigo_color']);

        $migracion = require database_path('migrations/2026_10_06_130000_agregar_tipo_a_producto_borlas.php');
        $migracion->up();

        $tipos = DB::table('producto_borlas')->pluck('tipo_borla', 'color')->all();

        $this->assertSame([
            'Dorado' => 'NORMAL',
            'Rojo' => 'NORMAL',
            'Verde' => 'NORMAL',
            'Celeste' => 'UNIVERSITARIA',
            'Amarillo' => 'UNIVERSITARIA',
            'Naranja' => 'UNIVERSITARIA',
            'Rojo-Derecho' => 'UNIVERSITARIA',
        ], $tipos);

        $this->assertEquals($antes, DB::table('producto_borlas')->orderBy('id')->get(['color', 'codigo_color']));
    }
}
