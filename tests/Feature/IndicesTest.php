<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\Support\ConBaseDeDatosDePrueba;
use Tests\TestCase;

class IndicesTest extends TestCase
{
    use ConBaseDeDatosDePrueba;

    public function test_los_indices_de_busqueda_existen_y_la_migracion_se_puede_repetir(): void
    {
        foreach ([
            ['alquileres', 'alquileres_estado_index'],
            ['alquileres', 'alquileres_estado_pago_index'],
            ['alquileres', 'alquileres_fecha_entrega_index'],
            ['movimientos_inventario', 'movimientos_inventario_tipo_movimiento_index'],
            ['productos', 'productos_tipo_producto_index'],
            ['clientes', 'clientes_activo_index'],
        ] as [$tabla, $indice]) {
            $this->assertTrue(Schema::hasIndex($tabla, $indice), $indice);
        }

        // Correrla otra vez no falla (salta los índices que ya existen).
        $migracion = require database_path('migrations/2026_10_06_120000_agregar_indices_de_busqueda.php');
        $migracion->up();
        $this->assertTrue(Schema::hasIndex('alquileres', 'alquileres_estado_index'));
    }
}
