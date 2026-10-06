<?php

namespace Tests\Feature\Web;

use Illuminate\Support\Facades\DB;
use Tests\Support\ConBaseDeDatosDePrueba;
use Tests\Support\CreaDatos;
use Tests\TestCase;

/**
 * Base de las pruebas de las pantallas web: base de datos limpia en cada
 * prueba (SQLite en memoria) y constructores de datos.
 */
abstract class PruebaWeb extends TestCase
{
    use ConBaseDeDatosDePrueba;
    use CreaDatos;

    protected function setUp(): void
    {
        parent::setUp();

        // Las pruebas no necesitan los archivos compilados de Vite.
        $this->withoutVite();

        $this->registrarFuncionesDeMysql();
    }

    /**
     * Algunas consultas del sistema usan funciones propias de MySQL
     * (CURDATE, DATE_FORMAT). SQLite no las trae, así que se imitan aquí
     * solo para las pruebas.
     */
    private function registrarFuncionesDeMysql(): void
    {
        $pdo = DB::connection()->getPdo();

        $pdo->sqliteCreateFunction('CURDATE', fn () => date('Y-m-d'), 0);

        $pdo->sqliteCreateFunction('DATE_FORMAT', function ($fecha, $formato) {
            if ($fecha === null) {
                return null;
            }

            $equivalencias = ['%Y' => 'Y', '%m' => 'm', '%d' => 'd', '%H' => 'H', '%i' => 'i', '%s' => 's'];

            return date(strtr($formato, $equivalencias), strtotime($fecha));
        }, 2);
    }
}
