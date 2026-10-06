<?php

namespace Tests\Feature\Web;

use App\Models\MovimientoInventario;

class InventarioWebTest extends PruebaWeb
{
    public function test_pantallas_de_entrada_y_ajuste_cargan(): void
    {
        $toga = $this->toga();

        $this->get(route('productos.entrada', $toga->id))->assertOk()->assertSee($toga->nombre);
        $this->get(route('productos.ajuste', $toga->id))->assertOk()->assertSee($toga->nombre);
        $this->get(route('productos.entrada', 9999))->assertNotFound();
    }

    public function test_registra_una_entrada_de_inventario(): void
    {
        $toga = $this->toga('ESTANDAR', ['stock_total' => 5]);

        $this->post(route('productos.entrada.guardar', $toga->id), [
            'cantidad' => 4,
            'motivo' => 'Compra',
            'referencia' => 'F-100',
        ])
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Entrada de inventario registrada correctamente.');

        $toga->refresh();
        $this->assertSame(9, $toga->stock_total);
        $this->assertSame(9, $toga->stock_disponible);

        $movimiento = MovimientoInventario::where('producto_id', $toga->id)->latest('id')->first();
        $this->assertSame('ENTRADA', $movimiento->tipo_movimiento);
        $this->assertSame('Compra', $movimiento->motivo);
    }

    public function test_entrada_invalida_vuelve_con_error(): void
    {
        $toga = $this->toga();

        $this->from(route('productos.entrada', $toga->id))
            ->post(route('productos.entrada.guardar', $toga->id), ['cantidad' => 0])
            ->assertRedirect(route('productos.entrada', $toga->id))
            ->assertSessionHasErrors('cantidad');
    }

    public function test_registra_un_ajuste_de_inventario(): void
    {
        $toga = $this->toga('ESTANDAR', ['stock_total' => 10]);

        $this->post(route('productos.ajuste.guardar', $toga->id), [
            'nuevo_stock_disponible' => 8,
            'motivo' => 'Conteo físico',
        ])
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Ajuste de inventario registrado correctamente.');

        $this->assertSame(8, $toga->fresh()->stock_disponible);
        $this->assertSame('AJUSTE', MovimientoInventario::where('producto_id', $toga->id)->latest('id')->first()->tipo_movimiento);
    }

    public function test_ajuste_exige_motivo(): void
    {
        $toga = $this->toga();

        $this->from(route('productos.ajuste', $toga->id))
            ->post(route('productos.ajuste.guardar', $toga->id), ['nuevo_stock_disponible' => 3])
            ->assertSessionHasErrors('motivo');
    }

    public function test_movimientos_con_filtros(): void
    {
        $toga = $this->toga('ESTANDAR', ['nombre' => 'Toga Movida']);
        $this->post(route('productos.entrada.guardar', $toga->id), ['cantidad' => 2, 'motivo' => 'Reposición especial']);

        $this->get(route('inventario.movimientos'))->assertOk()->assertSee('Toga Movida');
        $this->get(route('inventario.movimientos', ['tipo' => 'ENTRADA']))->assertOk()->assertSee('Toga Movida');
        $this->get(route('inventario.movimientos', ['tipo' => 'AJUSTE']))->assertOk()->assertDontSee('Toga Movida');
        $this->get(route('inventario.movimientos', ['buscar' => 'Reposición especial']))->assertOk()->assertSee('Toga Movida');
        $this->get(route('inventario.movimientos', ['buscar' => 'nada-que-coincida']))->assertOk()->assertDontSee('Toga Movida');
    }
}
