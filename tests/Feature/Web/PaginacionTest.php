<?php

namespace Tests\Feature\Web;

use App\Models\MovimientoInventario;

/**
 * Los listados largos se muestran por páginas, pero las tarjetas de resumen
 * cuentan TODO lo que cumple el filtro (no solo la página visible).
 */
class PaginacionTest extends PruebaWeb
{
    public function test_clientes_por_paginas_con_resumen_completo(): void
    {
        for ($i = 0; $i < 27; $i++) {
            $this->cliente(['activo' => $i < 20]);
        }

        $pagina1 = $this->get(route('clientes.index'))->assertOk();
        $this->assertCount(25, $pagina1->viewData('clientes'));
        $this->assertSame(['total' => 27, 'activos' => 20, 'inactivos' => 7], $pagina1->viewData('resumen'));
        $pagina1->assertSee('27 registros')->assertSee('page=2', false);

        $pagina2 = $this->get(route('clientes.index', ['page' => 2]))->assertOk();
        $this->assertCount(2, $pagina2->viewData('clientes'));
    }

    public function test_la_paginacion_conserva_los_filtros(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->cliente(['apellidos' => 'Buscado' . $i]);
        }

        $this->get(route('clientes.index', ['buscar' => 'Buscado']))
            ->assertOk()
            ->assertSee('buscar=Buscado', false);
    }

    public function test_movimientos_resumen_cuenta_todos_no_solo_la_pagina(): void
    {
        $toga = $this->toga();

        foreach (['ENTRADA' => 15, 'AJUSTE' => 10, 'ALQUILER' => 3] as $tipo => $veces) {
            for ($i = 0; $i < $veces; $i++) {
                MovimientoInventario::create([
                    'producto_id' => $toga->id,
                    'tipo_movimiento' => $tipo,
                    'cantidad' => 1,
                    'stock_anterior_disponible' => 0,
                    'stock_nuevo_disponible' => 1,
                    'stock_anterior_alquilado' => 0,
                    'stock_nuevo_alquilado' => 0,
                    'motivo' => 'Prueba',
                ]);
            }
        }

        $respuesta = $this->get(route('inventario.movimientos'))->assertOk();

        $this->assertCount(20, $respuesta->viewData('movimientos'));
        $this->assertSame(
            ['total' => 28, 'entradas' => 15, 'alquileres' => 3, 'devoluciones' => 0, 'ajustes' => 10],
            $respuesta->viewData('resumen')
        );
        $respuesta->assertSee('page=2', false);

        $this->assertSame(10, $this->get(route('inventario.movimientos', ['tipo' => 'AJUSTE']))->viewData('resumen')['total']);
    }

    public function test_alquileres_listado_paginado(): void
    {
        $this->get(route('alquileres.index'))->assertOk()->assertSee('0 registros');
        $this->assertSame(0, $this->get(route('alquileres.index'))->viewData('resumen')['total']);
    }
}
