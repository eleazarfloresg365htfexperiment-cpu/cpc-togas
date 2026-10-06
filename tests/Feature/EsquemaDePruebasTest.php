<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\Support\ConBaseDeDatosDePrueba;
use Tests\TestCase;

class EsquemaDePruebasTest extends TestCase
{
    use ConBaseDeDatosDePrueba;

    public function test_el_esquema_de_pruebas_incluye_las_tablas_del_sistema(): void
    {
        foreach ([
            'productos', 'producto_togas', 'producto_capas', 'producto_birretes',
            'producto_collarines', 'producto_borlas', 'clientes', 'alquileres',
            'alquiler_detalles', 'alquiler_detalle_accesorios', 'pagos',
            'movimientos_inventario', 'alquiler_historial', 'alquiler_danos', 'alquiler_fabricaciones',
        ] as $tabla) {
            $this->assertTrue(Schema::hasTable($tabla), "Falta la tabla {$tabla}");
        }
    }
}
