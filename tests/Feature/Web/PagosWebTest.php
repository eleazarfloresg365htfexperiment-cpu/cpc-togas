<?php

namespace Tests\Feature\Web;

use App\Models\Alquiler;
use App\Models\Pago;

class PagosWebTest extends PruebaWeb
{
    private Alquiler $alquiler;

    protected function setUp(): void
    {
        parent::setUp();

        $cliente = $this->cliente();
        $toga = $this->toga('ESTANDAR', ['precio_alquiler' => 50]);
        $collarin = $this->collarin();

        $this->post(route('alquileres.store'), $this->formularioAlquiler($cliente, $toga, $collarin));
        $this->alquiler = Alquiler::firstOrFail(); // total Q100
    }

    public function test_pantalla_de_pago_carga_si_hay_saldo(): void
    {
        $this->get(route('pagos.create', $this->alquiler->id))
            ->assertOk()
            ->assertSee($this->alquiler->codigo_recibo);
    }

    public function test_pantalla_de_pago_redirige_si_ya_esta_pagado(): void
    {
        $this->post(route('pagos.store', $this->alquiler->id), ['monto' => 100, 'metodo_pago' => 'EFECTIVO']);

        $this->get(route('pagos.create', $this->alquiler->id))
            ->assertRedirect(route('alquileres.index'))
            ->assertSessionHas('error', 'Este alquiler ya está pagado completamente.');
    }

    public function test_registra_pago_parcial_y_actualiza_saldo_y_estado_de_pago(): void
    {
        $this->post(route('pagos.store', $this->alquiler->id), [
            'monto' => 30,
            'metodo_pago' => 'TRANSFERENCIA',
            'referencia' => 'TR-1',
            'observaciones' => 'Anticipo',
        ])
            ->assertRedirect(route('alquileres.show', $this->alquiler->id))
            ->assertSessionHas('success', 'Pago o descuento registrado correctamente.');

        $pago = Pago::firstOrFail();
        $this->assertSame(30.0, (float) $pago->monto);
        $this->assertSame('TRANSFERENCIA', $pago->metodo_pago);

        $this->assertSame(70.0, (float) $this->alquiler->fresh()->saldo_pendiente);
        $this->assertNotSame('PAGADO', $this->alquiler->fresh()->estado_pago);

        $this->post(route('pagos.store', $this->alquiler->id), ['monto' => 70, 'metodo_pago' => 'EFECTIVO']);
        $this->assertSame(0.0, (float) $this->alquiler->fresh()->saldo_pendiente);
        $this->assertSame('PAGADO', $this->alquiler->fresh()->estado_pago);
    }

    public function test_guarda_la_fecha_limite_de_pago_final(): void
    {
        $this->post(route('pagos.store', $this->alquiler->id), [
            'monto' => 20,
            'metodo_pago' => 'EFECTIVO',
            'fecha_limite_pago_final' => '2026-10-14',
        ]);

        $this->assertSame('2026-10-14', $this->alquiler->fresh()->fecha_limite_pago_final->format('Y-m-d'));
    }

    public function test_rechaza_pago_mayor_al_saldo_o_metodo_invalido(): void
    {
        $this->from(route('pagos.create', $this->alquiler->id))
            ->post(route('pagos.store', $this->alquiler->id), ['monto' => 150, 'metodo_pago' => 'EFECTIVO'])
            ->assertSessionHasErrors('monto');

        $this->from(route('pagos.create', $this->alquiler->id))
            ->post(route('pagos.store', $this->alquiler->id), ['monto' => 10, 'metodo_pago' => 'CHEQUE'])
            ->assertSessionHasErrors('metodo_pago');

        $this->assertSame(0, Pago::count());
    }

    public function test_exige_un_pago_o_un_descuento_mayor_a_cero(): void
    {
        $this->post(route('pagos.store', $this->alquiler->id), ['monto' => 0, 'metodo_pago' => 'EFECTIVO'])
            ->assertRedirect(route('pagos.create', $this->alquiler->id))
            ->assertSessionHasErrors(['monto' => 'Debe ingresar un pago o un descuento mayor a cero.']);
    }

    public function test_descuento_exige_observacion_y_no_puede_exceder_el_saldo(): void
    {
        $this->post(route('pagos.store', $this->alquiler->id), [
            'monto' => 50, 'descuento_aplicado' => 10, 'metodo_pago' => 'EFECTIVO',
        ])->assertSessionHasErrors(['observacion_descuento' => 'Debe ingresar una observación para justificar el descuento aplicado.']);

        $this->post(route('pagos.store', $this->alquiler->id), [
            'monto' => 80, 'descuento_aplicado' => 30, 'metodo_pago' => 'EFECTIVO', 'observacion_descuento' => 'x',
        ])->assertSessionHasErrors(['monto' => 'La suma del pago y el descuento no puede ser mayor al saldo pendiente.']);

        $this->assertSame(0, Pago::count());
    }

    public function test_descuento_con_observacion_reduce_el_saldo(): void
    {
        $this->post(route('pagos.store', $this->alquiler->id), [
            'monto' => 50,
            'descuento_aplicado' => 10,
            'metodo_pago' => 'EFECTIVO',
            'observacion_descuento' => 'Cliente frecuente',
        ])->assertSessionHas('success');

        $this->assertSame(40.0, (float) $this->alquiler->fresh()->saldo_pendiente);
    }

    public function test_no_se_paga_un_alquiler_cancelado(): void
    {
        $this->post(route('alquileres.cancelar', $this->alquiler->id), ['motivo_cancelacion' => 'x']);

        // Al cancelar, el saldo pendiente queda en 0, así que cualquier monto
        // supera el máximo permitido y el formulario lo rechaza.
        $this->from(route('pagos.create', $this->alquiler->id))
            ->post(route('pagos.store', $this->alquiler->id), ['monto' => 10, 'metodo_pago' => 'EFECTIVO'])
            ->assertSessionHasErrors('monto');

        $this->assertSame(0, Pago::count());
    }
}
