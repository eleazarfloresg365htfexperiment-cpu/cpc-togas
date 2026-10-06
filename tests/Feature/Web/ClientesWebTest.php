<?php

namespace Tests\Feature\Web;

use App\Models\Cliente;

class ClientesWebTest extends PruebaWeb
{
    private function datos(array $extra = []): array
    {
        return $extra + [
            'nombres' => 'María José',
            'apellidos' => 'López',
            'telefono' => '55551234',
            'dpi' => '2999999990101',
            'direccion' => 'Jalapa',
            'institucion_representada' => 'Instituto',
            'observaciones' => 'Nota',
        ];
    }

    public function test_listado_y_filtros(): void
    {
        $this->cliente(['nombres' => 'Ana', 'apellidos' => 'Pérez']);
        $this->cliente(['nombres' => 'Beto', 'apellidos' => 'Gómez', 'activo' => false]);

        $this->get(route('clientes.index'))->assertOk()->assertSee('Ana')->assertSee('Beto');
        $this->get(route('clientes.index', ['buscar' => 'Gómez']))->assertOk()->assertSee('Beto')->assertDontSee('Ana');
        $this->get(route('clientes.index', ['estado' => '0']))->assertOk()->assertSee('Beto')->assertDontSee('Ana');
        $this->get(route('clientes.index', ['estado' => '1']))->assertOk()->assertSee('Ana')->assertDontSee('Beto');
    }

    public function test_pantalla_de_crear_y_editar_cargan(): void
    {
        $cliente = $this->cliente();

        $this->get(route('clientes.create'))->assertOk();
        $this->get(route('clientes.edit', $cliente->id))->assertOk()->assertSee($cliente->nombres);
        $this->get(route('clientes.edit', 9999))->assertNotFound();
    }

    public function test_crea_cliente_activo(): void
    {
        $this->post(route('clientes.store'), $this->datos())
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success', 'Cliente registrado correctamente.');

        $cliente = Cliente::where('dpi', '2999999990101')->firstOrFail();
        $this->assertTrue($cliente->activo);
        $this->assertSame('María José', $cliente->nombres);
    }

    public function test_valida_campos_obligatorios_y_dpi_unico(): void
    {
        $existente = $this->cliente();

        $this->from(route('clientes.create'))
            ->post(route('clientes.store'), [])
            ->assertSessionHasErrors(['nombres', 'apellidos']);

        $this->from(route('clientes.create'))
            ->post(route('clientes.store'), $this->datos(['dpi' => $existente->dpi]))
            ->assertSessionHasErrors('dpi');
    }

    public function test_actualiza_cliente_y_permite_conservar_su_dpi(): void
    {
        $cliente = $this->cliente();
        $otro = $this->cliente();

        $this->put(route('clientes.update', $cliente->id), $this->datos(['dpi' => $cliente->dpi, 'nombres' => 'Nuevo']))
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success', 'Cliente actualizado correctamente.');
        $this->assertSame('Nuevo', $cliente->fresh()->nombres);

        $this->from(route('clientes.edit', $cliente->id))
            ->put(route('clientes.update', $cliente->id), $this->datos(['dpi' => $otro->dpi]))
            ->assertSessionHasErrors('dpi');
    }

    public function test_desactivar_y_reactivar(): void
    {
        $cliente = $this->cliente();

        $this->post(route('clientes.desactivar', $cliente->id))
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success', 'Cliente desactivado correctamente.');
        $this->assertFalse($cliente->fresh()->activo);

        $this->post(route('clientes.reactivar', $cliente->id))
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success', 'Cliente reactivado correctamente.');
        $this->assertTrue($cliente->fresh()->activo);
    }
}
