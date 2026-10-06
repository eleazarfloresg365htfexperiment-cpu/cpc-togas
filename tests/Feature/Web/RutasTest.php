<?php

namespace Tests\Feature\Web;

/**
 * Direcciones del sistema: esquema /recurso, /recurso/crear, /recurso/{id}/editar
 * y redirecciones desde las direcciones anteriores (-web).
 */
class RutasTest extends PruebaWeb
{
    public function test_direcciones_nuevas(): void
    {
        $cliente = $this->cliente();
        $producto = $this->toga();

        $this->assertSame('/dashboard', route('dashboard', [], false));
        $this->assertSame('/productos', route('productos.index', [], false));
        $this->assertSame('/productos/crear', route('productos.create', [], false));
        $this->assertSame("/productos/{$producto->id}/editar", route('productos.edit', $producto, false));
        $this->assertSame('/productos/administrar/editar', route('productos.administrar.accion', 'editar', false));
        $this->assertSame('/clientes', route('clientes.index', [], false));
        $this->assertSame("/clientes/{$cliente->id}/editar", route('clientes.edit', $cliente, false));
        $this->assertSame('/alquileres', route('alquileres.index', [], false));
        $this->assertSame('/alquileres/crear', route('alquileres.create', [], false));
        $this->assertSame('/alquileres/5', route('alquileres.show', 5, false));
        $this->assertSame('/alquileres/5/pagar', route('pagos.create', 5, false));
        $this->assertSame('/calendario', route('calendario.index', [], false));
    }

    public function test_pantallas_principales_responden_en_las_direcciones_nuevas(): void
    {
        $producto = $this->toga();
        $cliente = $this->cliente();

        foreach ([
            '/dashboard',
            '/productos',
            '/productos/crear',
            '/productos/administrar',
            '/productos/administrar/editar',
            "/productos/{$producto->id}/editar",
            "/productos/{$producto->id}/entrada",
            "/productos/{$producto->id}/ajuste",
            '/inventario/movimientos',
            '/clientes',
            '/clientes/crear',
            "/clientes/{$cliente->id}/editar",
            '/alquileres',
            '/alquileres/crear',
            '/calendario',
            '/control-alquileres',
            '/estadisticas',
        ] as $ruta) {
            $this->get($ruta)->assertOk();
        }
    }

    public function test_la_raiz_lleva_al_dashboard(): void
    {
        $this->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_no_hay_ruta_que_tape_a_administrar(): void
    {
        // "administrar" no debe confundirse con el id de un producto.
        $this->get('/productos/administrar')->assertOk();
    }

    public function test_las_direcciones_anteriores_redirigen_a_las_nuevas(): void
    {
        $producto = $this->toga();
        $cliente = $this->cliente();

        foreach ([
            '/productos-web' => '/productos',
            '/productos-web/crear' => '/productos/crear',
            '/productos-web/administrar' => '/productos/administrar',
            '/productos-web/administrar/editar' => '/productos/administrar/editar',
            "/productos-web/{$producto->id}/editar" => "/productos/{$producto->id}/editar",
            '/clientes-web' => '/clientes',
            "/clientes-web/{$cliente->id}/editar" => "/clientes/{$cliente->id}/editar",
            '/alquileres-web' => '/alquileres',
            '/alquileres-web/crear' => '/alquileres/crear',
            '/alquileres-web/12' => '/alquileres/12',
            '/alquileres-web/12/recibo' => '/alquileres/12/recibo',
            '/calendario-web' => '/calendario',
            '/calendario-web/eventos' => '/calendario/eventos',
        ] as $anterior => $nueva) {
            $this->get($anterior)
                ->assertStatus(308)
                ->assertRedirect(url($nueva));
        }
    }

    public function test_la_redireccion_conserva_filtros_de_la_url(): void
    {
        // Los filtros llegan completos (el orden puede cambiar: se ordenan por nombre).
        $this->get('/alquileres-web?estado=ENTREGADO&buscar=Ana%20L%C3%B3pez')
            ->assertStatus(308)
            ->assertRedirect(url('/alquileres?buscar=Ana%20L%C3%B3pez&estado=ENTREGADO'));
    }

    public function test_un_formulario_viejo_puede_enviarse_a_la_direccion_anterior(): void
    {
        // El navegador repite el POST (con su cuerpo) en la dirección nueva.
        $this->post('/clientes-web', ['nombres' => 'X'])
            ->assertStatus(308)
            ->assertRedirect(url('/clientes'));

        $this->put('/clientes-web/7', ['nombres' => 'X'])
            ->assertStatus(308)
            ->assertRedirect(url('/clientes/7'));

        $this->patch('/productos-web/7/desactivar')
            ->assertStatus(308)
            ->assertRedirect(url('/productos/7/desactivar'));
    }

    public function test_la_redireccion_no_toca_otras_direcciones(): void
    {
        $this->get('/otra-cosa-web')->assertNotFound();
        $this->get('/estadisticas-web')->assertNotFound();
    }
}
