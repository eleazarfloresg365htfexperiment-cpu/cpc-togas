<?php

namespace Tests\Feature\Web;

use App\Models\Alquiler;
use App\Models\AlquilerFabricacion;
use App\Models\Cliente;
use App\Models\Producto;

class AlquileresWebTest extends PruebaWeb
{
    private Cliente $cliente;
    private Producto $toga;
    private Producto $collarin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cliente = $this->cliente();
        $this->toga = $this->toga('ESTANDAR', ['precio_alquiler' => 50, 'stock_total' => 10]);
        $this->collarin = $this->collarin('NORMAL', 'Rojo', ['stock_total' => 10]);
    }

    private function crear(array $extraToga = [], array $extraForm = []): \Illuminate\Testing\TestResponse
    {
        return $this->post(
            route('alquileres.store'),
            $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, $extraToga, $extraForm)
        );
    }

    private function alquilerCreado(array $extraToga = [], array $extraForm = []): Alquiler
    {
        $this->crear($extraToga, $extraForm)->assertSessionHasNoErrors();

        return Alquiler::latest('id')->firstOrFail();
    }

    // ------------------------------------------------------------ pantallas

    public function test_pantalla_de_crear_alquiler_ofrece_clientes_y_productos_disponibles(): void
    {
        $inactivo = $this->cliente(['nombres' => 'Inactivo', 'activo' => false]);
        $sinStock = $this->toga('ESTANDAR', ['nombre' => 'Toga Agotada', 'stock_total' => 0]);

        $this->get(route('alquileres.create'))
            ->assertOk()
            ->assertSee($this->cliente->nombres)
            ->assertSee($this->toga->nombre)
            ->assertSee($this->collarin->nombre)
            ->assertDontSee('Inactivo')
            ->assertDontSee('Toga Agotada');
    }

    public function test_listado_con_filtros(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->get(route('alquileres.index'))->assertOk()->assertSee($alquiler->codigo_recibo);
        $this->get(route('alquileres.index', ['buscar' => $this->cliente->apellidos]))->assertOk()->assertSee($alquiler->codigo_recibo);
        $this->get(route('alquileres.index', ['buscar' => 'ZZZ-no-existe']))->assertOk()->assertDontSee($alquiler->codigo_recibo);
        $this->get(route('alquileres.index', ['estado' => 'RESERVADO']))->assertOk()->assertSee($alquiler->codigo_recibo);
        $this->get(route('alquileres.index', ['estado' => 'CANCELADO']))->assertOk()->assertDontSee($alquiler->codigo_recibo);
        $this->get(route('alquileres.index', ['estado_pago' => 'PENDIENTE']))->assertOk();
    }

    public function test_detalle_recibo_y_cartas_cargan(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->get(route('alquileres.show', $alquiler->id))->assertOk()->assertSee($alquiler->codigo_recibo);
        $this->get(route('alquileres.recibo', $alquiler->id))->assertOk()->assertSee($alquiler->codigo_recibo);
        $this->get(route('alquileres.terminos', $alquiler->id))->assertOk();
        $this->get(route('alquileres.devolucion-carta', $alquiler->id))->assertOk();
        $this->get(route('alquileres.show', 9999))->assertNotFound();
    }

    public function test_cartas_usan_la_plantilla_segun_el_tipo_de_toga(): void
    {
        // Estándar
        $estandar = $this->alquilerCreado();
        $this->get(route('alquileres.terminos', $estandar->id))
            ->assertSee('<div class="sheet normal-p1">', false)
            ->assertDontSee('<div class="sheet universitaria-p1">', false);
        $this->get(route('alquileres.devolucion-carta', $estandar->id))
            ->assertSee('<div class="sheet devolucion-normal"', false)
            ->assertDontSee('<div class="sheet devolucion-universitaria"', false);

        // Universitaria
        $togaU = $this->toga('UNIVERSITARIA');
        $collarinU = $this->collarin('UNIVERSITARIO', 'Azul');
        $capa = $this->capa('DERECHO', 'Rojo');
        $birreteU = $this->birrete('UNIVERSITARIO');

        $this->post(route('alquileres.store'), $this->formularioAlquiler(
            $this->cliente, $togaU, $collarinU,
            ['capa_id' => $capa->id, 'cantidad' => 1, 'birrete_incluido' => 1, 'birrete_id' => $birreteU->id]
        ))->assertSessionHasNoErrors();

        $universitaria = Alquiler::latest('id')->first();

        $this->get(route('alquileres.terminos', $universitaria->id))
            ->assertSee('<div class="sheet universitaria-p1">', false)
            ->assertDontSee('<div class="sheet normal-p1">', false);
        $this->get(route('alquileres.devolucion-carta', $universitaria->id))
            ->assertSee('<div class="sheet devolucion-universitaria"', false)
            ->assertDontSee('<div class="sheet devolucion-normal"', false);
    }

    // ------------------------------------------------------------- creación

    public function test_crea_alquiler_reservado_con_totales_y_datos_de_la_carta(): void
    {
        $this->crear([], [
            'institucion_representada' => 'Instituto Demo',
            'representante_alquiler' => 'Profe Demo',
            'hora_entrega' => '09:00',
            'hora_devolucion_programada' => '17:00',
            'hora_entrega_inicio' => '08:00',
            'hora_entrega_fin' => '10:00',
            'fecha_limite_pago_final' => '2026-10-11',
            'observaciones' => 'Nota',
        ])
            ->assertRedirect(route('alquileres.index'))
            ->assertSessionHas('success', 'Alquiler creado correctamente.');

        $alquiler = Alquiler::firstOrFail();

        $this->assertSame($this->cliente->id, $alquiler->cliente_id);
        $this->assertSame('RESERVADO', $alquiler->estado);
        $this->assertSame(100.0, (float) $alquiler->total);
        $this->assertSame(100.0, (float) $alquiler->saldo_pendiente);
        $this->assertSame('Instituto Demo', $alquiler->institucion_representada);
        $this->assertSame('Profe Demo', $alquiler->representante_alquiler);
        $this->assertSame('2026-10-11', $alquiler->fecha_limite_pago_final->format('Y-m-d'));
        $this->assertCount(1, $alquiler->detalles);
        $this->assertSame(2, (int) $alquiler->detalles->first()->cantidad);
        $this->assertCount(1, $alquiler->detalles->first()->accesorios);
        $this->assertNotEmpty($alquiler->codigo_recibo);
    }

    public function test_reservar_no_descuenta_inventario_hasta_la_entrega(): void
    {
        $this->crear();

        $this->assertSame(10, $this->toga->fresh()->stock_disponible);
        $this->assertSame(0, $this->toga->fresh()->stock_alquilado);
    }

    public function test_exige_collarin_cliente_y_al_menos_una_toga(): void
    {
        $form = $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin);

        unset($form['productos'][$this->toga->id]['collarin_id']);
        $this->from(route('alquileres.create'))->post(route('alquileres.store'), $form)
            ->assertRedirect(route('alquileres.create'))
            ->assertSessionHasErrors(['productos.0.collarin_id' => 'Cada toga seleccionada debe tener un collarín obligatorio.']);

        $form = $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin);
        $form['productos'][$this->toga->id]['seleccionado'] = 0;
        $this->from(route('alquileres.create'))->post(route('alquileres.store'), $form)
            ->assertSessionHasErrors(['productos' => 'Debes seleccionar al menos una toga.']);

        $form = $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin);
        unset($form['cliente_id']);
        $this->from(route('alquileres.create'))->post(route('alquileres.store'), $form)
            ->assertSessionHasErrors('cliente_id');

        $this->assertSame(0, Alquiler::count());
    }

    public function test_valida_orden_de_fechas_y_horas(): void
    {
        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, [], [
                'fecha_alquiler' => '2026-10-20',
                'fecha_entrega' => '2026-10-12',
            ]))
            ->assertSessionHasErrors(['fecha_alquiler', 'fecha_entrega']);

        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, [], [
                'fecha_devolucion_programada' => '2026-10-11',
            ]))
            ->assertSessionHasErrors('fecha_devolucion_programada');

        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, [], [
                'fecha_entrega' => '2026-10-12',
                'fecha_devolucion_programada' => '2026-10-12',
                'hora_entrega' => '10:00',
                'hora_devolucion_programada' => '09:00',
            ]))
            ->assertSessionHasErrors('hora_devolucion_programada');

        $this->assertSame(0, Alquiler::count());
    }

    public function test_extras_exigen_producto_y_cantidad_juntos(): void
    {
        $birrete = $this->birrete();
        $borla = $this->borla();

        $casos = [
            [['birrete_extra_cantidad' => 2], 'colocaste cantidad de birrete extra'],
            [['birrete_extra_id' => $birrete->id], 'seleccionaste un birrete extra'],
            [['borla_extra_cantidad' => 1], 'colocaste cantidad de borla extra'],
            [['borla_extra_id' => $borla->id], 'seleccionaste una borla extra'],
        ];

        foreach ($casos as [$extra, $fragmento]) {
            $respuesta = $this->from(route('alquileres.create'))
                ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, $extra));

            $respuesta->assertSessionHasErrors('productos');
            $this->assertStringContainsString($fragmento, session('errors')->first('productos'));
        }

        $this->assertSame(0, Alquiler::count());
    }

    public function test_extras_se_cobran(): void
    {
        $birrete = $this->birrete('ESTANDAR', ['precio_alquiler' => 10]);

        $this->crear(['birrete_extra_id' => $birrete->id, 'birrete_extra_cantidad' => 3])
            ->assertSessionHasNoErrors();

        $alquiler = Alquiler::firstOrFail();
        $this->assertGreaterThan(100.0, (float) $alquiler->total);
        $this->assertCount(2, $alquiler->detalles->first()->accesorios);
    }

    public function test_sin_stock_suficiente_y_sin_autorizacion_se_rechaza_con_mensaje_claro(): void
    {
        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, ['cantidad' => 15]))
            ->assertRedirect(route('alquileres.create'))
            ->assertSessionHasErrors('productos');

        $this->assertStringContainsString('solo hay 10 disponible', session('errors')->first('productos'));
        $this->assertSame(0, Alquiler::count());
    }

    public function test_con_fabricacion_autorizada_queda_en_fabricacion_y_cobra_todo(): void
    {
        $this->crear(['cantidad' => 15, 'fabricacion_autorizada' => 1], [
            'fabricacion_responsable' => 'Taller',
            'fabricacion_motivo' => 'Faltante',
        ])->assertSessionHasNoErrors();

        $alquiler = Alquiler::firstOrFail();

        $this->assertSame('EN_FABRICACION', $alquiler->estado);
        $this->assertSame(750.0, (float) $alquiler->total);

        $fabricacion = AlquilerFabricacion::where('alquiler_id', $alquiler->id)->where('producto_id', $this->toga->id)->firstOrFail();
        $this->assertSame(5, (int) $fabricacion->cantidad_pendiente);
        $this->assertSame('Taller', $fabricacion->responsable);
    }

    public function test_toga_universitaria_exige_capa(): void
    {
        $togaU = $this->toga('UNIVERSITARIA');
        $collarinU = $this->collarin('UNIVERSITARIO', 'Azul');

        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $togaU, $collarinU, ['cantidad' => 1]))
            ->assertSessionHasErrors('productos');

        $this->assertSame(0, Alquiler::count());
    }

    public function test_borla_incluida_debe_coincidir_con_el_color_de_la_capa(): void
    {
        $togaU = $this->toga('UNIVERSITARIA');
        $collarinU = $this->collarin('UNIVERSITARIO', 'Azul');
        $capa = $this->capa('DERECHO', 'Rojo');
        $birreteU = $this->birrete('UNIVERSITARIO');
        $borlaVerde = $this->borla('Verde', [], 'UNIVERSITARIA');
        $borlaRoja = $this->borla('Rojo', [], 'UNIVERSITARIA');

        $base = ['capa_id' => $capa->id, 'cantidad' => 1, 'birrete_incluido' => 1, 'birrete_id' => $birreteU->id, 'borla_incluida' => 1];

        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $togaU, $collarinU, $base + ['borla_id' => $borlaVerde->id]))
            ->assertSessionHasErrors('productos');
        $this->assertSame(0, Alquiler::count());

        $this->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $togaU, $collarinU, $base + ['borla_id' => $borlaRoja->id]))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, Alquiler::count());
    }

    public function test_rojo_normal_y_rojo_universitario_no_se_confunden(): void
    {
        // Universitaria (Derecho): solo sirve la borla roja universitaria.
        $togaU = $this->toga('UNIVERSITARIA');
        $collarinU = $this->collarin('UNIVERSITARIO', 'Azul');
        $capa = $this->capa('DERECHO', 'Rojo');
        $birreteU = $this->birrete('UNIVERSITARIO');
        $rojaNormal = $this->borla('Rojo', [], 'NORMAL');
        $rojaUniversitaria = $this->borla('Rojo', [], 'UNIVERSITARIA');

        $baseU = ['capa_id' => $capa->id, 'cantidad' => 1, 'birrete_incluido' => 1, 'birrete_id' => $birreteU->id, 'borla_incluida' => 1];

        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $togaU, $collarinU, $baseU + ['borla_id' => $rojaNormal->id]))
            ->assertSessionHasErrors('productos');
        $this->assertSame(0, Alquiler::count());

        // Estándar con collarín rojo: solo sirve la borla roja normal.
        $collarinRojo = $this->collarin('NORMAL', 'Rojo');
        $birrete = $this->birrete('NORMAL');
        $baseE = ['cantidad' => 1, 'birrete_incluido' => 1, 'birrete_id' => $birrete->id, 'borla_incluida' => 1];

        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $collarinRojo, $baseE + ['borla_id' => $rojaUniversitaria->id]))
            ->assertSessionHasErrors('productos');
        $this->assertSame(0, Alquiler::count());

        $this->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $togaU, $collarinU, $baseU + ['borla_id' => $rojaUniversitaria->id]))
            ->assertSessionHasNoErrors();
        $this->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $collarinRojo, $baseE + ['borla_id' => $rojaNormal->id]))
            ->assertSessionHasNoErrors();
        $this->assertSame(2, Alquiler::count());
    }

    public function test_birrete_o_borla_incluidos_sin_elegir_cual_avisan_en_vez_de_perderse(): void
    {
        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, [
                'cantidad' => 1,
                'birrete_incluido' => 1,
                'birrete_id' => '',
            ]))
            ->assertRedirect(route('alquileres.create'))
            ->assertSessionHasErrors(['productos' => 'En la toga seleccionada #1, marcaste "Birrete incluido" pero no elegiste cuál birrete.']);

        $birrete = $this->birrete('NORMAL');

        $this->from(route('alquileres.create'))
            ->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, [
                'cantidad' => 1,
                'birrete_incluido' => 1,
                'birrete_id' => $birrete->id,
                'borla_incluida' => 1,
                'borla_id' => '',
            ]))
            ->assertSessionHasErrors(['productos' => 'En la toga seleccionada #1, marcaste "Borla incluida" pero no elegiste cuál borla.']);

        $this->assertSame(0, Alquiler::count());

        // Con el birrete elegido sí se guarda, y queda en el alquiler.
        $this->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, [
            'cantidad' => 1,
            'birrete_incluido' => 1,
            'birrete_id' => $birrete->id,
        ]))->assertSessionHasNoErrors();

        $alquiler = Alquiler::with('detalles.accesorios')->firstOrFail();
        $this->assertTrue(
            $alquiler->detalles->first()->accesorios->contains(fn ($a) => (int) $a->producto_id === $birrete->id && $a->tipo_cobro === 'INCLUIDO')
        );
    }

    public function test_detalles_rapidos_muestran_birretes_borlas_y_carrera(): void
    {
        $this->toga->toga->update(['talla' => 'XL']);
        $birrete = $this->birrete('NORMAL', ['nombre' => 'Birrete negro']);
        $borla = $this->borla('Rojo', ['nombre' => 'Borla roja'], 'NORMAL');
        $borlaExtra = $this->borla('Dorado', ['nombre' => 'Borla dorada'], 'NORMAL');

        $this->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $this->toga, $this->collarin, [
            'cantidad' => 1,
            'birrete_incluido' => 1,
            'birrete_id' => $birrete->id,
            'borla_incluida' => 1,
            'borla_id' => $borla->id,
            'borla_extra_id' => $borlaExtra->id,
            'borla_extra_cantidad' => 2,
        ]))->assertSessionHasNoErrors();

        // Universitaria con capa de Derecho: la carrera sale de la capa.
        $togaU = $this->toga('UNIVERSITARIA');
        $collarinU = $this->collarin('UNIVERSITARIO', 'Azul');
        $capa = $this->capa('DERECHO', 'Rojo');
        $birreteU = $this->birrete('UNIVERSITARIO', ['nombre' => 'Birrete U']);
        $borlaU = $this->borla('Rojo', ['nombre' => 'Borla roja U'], 'UNIVERSITARIA');

        $this->post(route('alquileres.store'), $this->formularioAlquiler($this->cliente, $togaU, $collarinU, [
            'cantidad' => 1,
            'capa_id' => $capa->id,
            'birrete_incluido' => 1,
            'birrete_id' => $birreteU->id,
            'borla_incluida' => 1,
            'borla_id' => $borlaU->id,
        ]))->assertSessionHasNoErrors();

        [$normal, $universitario] = Alquiler::orderBy('id')->get()->all();

        foreach ([route('alquileres.show', $normal), route('alquileres.recibo', $normal)] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Birrete negro (Normal) x1')
                ->assertSee('Borla roja (Rojo · Normal) x1')
                ->assertSee('Borla dorada (Dorado · Normal) x2 (extra)');
        }

        foreach ([route('alquileres.show', $universitario), route('alquileres.recibo', $universitario)] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Birrete U (Universitario) x1')
                ->assertSee('Borla roja U (Rojo · Universitaria) x1')
                ->assertSee('Derecho');
        }
    }

    // ------------------------------------------------- entrega y devolución

    public function test_no_se_entrega_sin_pago_completo(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->post(route('alquileres.entregar', $alquiler->id))
            ->assertRedirect(route('alquileres.index'))
            ->assertSessionHas('error');

        $this->assertSame('RESERVADO', $alquiler->fresh()->estado);
    }

    public function test_flujo_completo_pagar_entregar_y_devolver_mueve_el_inventario(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->post(route('pagos.store', $alquiler->id), ['monto' => 100, 'metodo_pago' => 'EFECTIVO'])
            ->assertRedirect(route('alquileres.show', $alquiler->id));

        $this->post(route('alquileres.entregar', $alquiler->id))
            ->assertRedirect(route('alquileres.index'))
            ->assertSessionHas('success', 'Alquiler entregado correctamente.');

        $this->assertSame('ENTREGADO', $alquiler->fresh()->estado);
        $this->assertSame(8, $this->toga->fresh()->stock_disponible);
        $this->assertSame(2, $this->toga->fresh()->stock_alquilado);

        $this->from(route('alquileres.show', $alquiler->id))
            ->post(route('alquileres.devolver', $alquiler->id), ['descuento_mora' => 0])
            ->assertRedirect(route('alquileres.show', $alquiler->id))
            ->assertSessionHas('success', 'Alquiler devuelto correctamente.');

        $this->assertSame('DEVUELTO', $alquiler->fresh()->estado);
        $this->assertSame(10, $this->toga->fresh()->stock_disponible);
        $this->assertSame(0, $this->toga->fresh()->stock_alquilado);
    }

    public function test_devolver_un_alquiler_no_entregado_muestra_error(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->from(route('alquileres.show', $alquiler->id))
            ->post(route('alquileres.devolver', $alquiler->id), [])
            ->assertRedirect(route('alquileres.show', $alquiler->id))
            ->assertSessionHas('error');
    }

    // ------------------------------------------------------------ cancelar

    public function test_cancelar_exige_motivo(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->from(route('alquileres.show', $alquiler->id))
            ->post(route('alquileres.cancelar', $alquiler->id), [])
            ->assertSessionHasErrors(['motivo_cancelacion' => 'Debes indicar el motivo de la cancelación.']);

        $this->assertSame('RESERVADO', $alquiler->fresh()->estado);
    }

    public function test_cancelar_con_pagos_los_retiene_y_deja_historial(): void
    {
        $alquiler = $this->alquilerCreado();
        $this->post(route('pagos.store', $alquiler->id), ['monto' => 40, 'metodo_pago' => 'EFECTIVO']);

        $this->post(route('alquileres.cancelar', $alquiler->id), [
            'motivo_cancelacion' => 'El cliente desistió',
            'responsable' => 'Secretaría',
        ])
            ->assertRedirect(route('alquileres.show', $alquiler->id))
            ->assertSessionHas('success', 'Alquiler cancelado correctamente. Los pagos registrados quedan retenidos (sin reembolso).');

        $alquiler->refresh();
        $this->assertSame('CANCELADO', $alquiler->estado);
        $this->assertSame('El cliente desistió', $alquiler->motivo_cancelacion);
        $this->assertSame(1, $alquiler->pagos()->count());
        $this->assertTrue($alquiler->historial()->where('accion', 'CANCELACION')->exists());
    }

    public function test_no_se_puede_cancelar_un_alquiler_ya_devuelto(): void
    {
        $alquiler = $this->alquilerCreado();
        $this->post(route('pagos.store', $alquiler->id), ['monto' => 100, 'metodo_pago' => 'EFECTIVO']);
        $this->post(route('alquileres.entregar', $alquiler->id));
        $this->post(route('alquileres.devolver', $alquiler->id), []);

        $this->post(route('alquileres.cancelar', $alquiler->id), ['motivo_cancelacion' => 'x'])
            ->assertSessionHas('error');

        $this->assertSame('DEVUELTO', $alquiler->fresh()->estado);
    }

    // -------------------------------------------------------------- editar

    public function test_editar_cambia_fechas_y_registra_historial(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->get(route('alquileres.edit', $alquiler->id))->assertOk();

        $this->put(route('alquileres.update', $alquiler->id), [
            'fecha_entrega' => '2026-10-13',
            'fecha_devolucion_programada' => '2026-10-18',
            'motivo_cambio' => 'Pidió más días',
            'responsable' => 'Secretaría',
        ])
            ->assertRedirect(route('alquileres.show', $alquiler->id))
            ->assertSessionHas('success', 'Alquiler actualizado correctamente. El cambio quedó en el historial.');

        $alquiler->refresh();
        $this->assertSame('2026-10-13', $alquiler->fecha_entrega->format('Y-m-d'));
        $this->assertSame('2026-10-18', $alquiler->fecha_devolucion_programada->format('Y-m-d'));
        $this->assertTrue($alquiler->historial()->where('accion', 'EDICION')->where('motivo', 'Pidió más días')->exists());
    }

    public function test_editar_exige_motivo_y_no_aplica_a_cancelados(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->from(route('alquileres.edit', $alquiler->id))
            ->put(route('alquileres.update', $alquiler->id), [
                'fecha_entrega' => '2026-10-13',
                'fecha_devolucion_programada' => '2026-10-18',
            ])
            ->assertSessionHasErrors(['motivo_cambio' => 'Debes indicar el motivo del cambio.']);

        $this->post(route('alquileres.cancelar', $alquiler->id), ['motivo_cancelacion' => 'x']);

        $this->get(route('alquileres.edit', $alquiler->id))
            ->assertRedirect(route('alquileres.show', $alquiler->id))
            ->assertSessionHas('error');
    }

    // ------------------------------------------------------------- daños

    private function alquilerDevuelto(): Alquiler
    {
        $alquiler = $this->alquilerCreado();
        $this->post(route('pagos.store', $alquiler->id), ['monto' => 100, 'metodo_pago' => 'EFECTIVO']);
        $this->post(route('alquileres.entregar', $alquiler->id));
        $this->post(route('alquileres.devolver', $alquiler->id), []);

        return $alquiler->fresh();
    }

    public function test_registrar_dano_suma_al_saldo_y_extravio_da_de_baja_inventario(): void
    {
        $alquiler = $this->alquilerDevuelto();
        $this->assertSame(0.0, (float) $alquiler->saldo_pendiente);

        $this->post(route('alquileres.danos.store', $alquiler->id), [
            'producto_id' => $this->toga->id,
            'tipo' => 'DANO',
            'cantidad' => 1,
            'monto' => 25,
            'descripcion' => 'Manga rota',
        ])
            ->assertRedirect(route('alquileres.show', $alquiler->id) . '#danos')
            ->assertSessionHas('success');

        $this->assertSame(25.0, (float) $alquiler->fresh()->saldo_pendiente);
        $this->assertSame(10, $this->toga->fresh()->stock_total);

        $this->post(route('alquileres.danos.store', $alquiler->id), [
            'producto_id' => $this->collarin->id,
            'tipo' => 'EXTRAVIO',
            'cantidad' => 1,
            'monto' => 15,
        ])->assertSessionHas('success');

        $this->assertSame(40.0, (float) $alquiler->fresh()->saldo_pendiente);
        $this->assertSame(9, $this->collarin->fresh()->stock_total);
    }

    public function test_eliminar_dano_revierte_el_cargo_y_el_inventario(): void
    {
        $alquiler = $this->alquilerDevuelto();

        $this->post(route('alquileres.danos.store', $alquiler->id), [
            'producto_id' => $this->collarin->id, 'tipo' => 'EXTRAVIO', 'cantidad' => 1, 'monto' => 15,
        ]);
        $dano = $alquiler->danos()->firstOrFail();

        $this->delete(route('alquileres.danos.destroy', [$alquiler->id, $dano->id]), [])
            ->assertSessionHasErrors('motivo_eliminacion');

        $this->delete(route('alquileres.danos.destroy', [$alquiler->id, $dano->id]), ['motivo_eliminacion' => 'Se encontró'])
            ->assertRedirect(route('alquileres.show', $alquiler->id) . '#danos')
            ->assertSessionHas('success');

        $this->assertSame(0, $alquiler->danos()->count());
        $this->assertSame(0.0, (float) $alquiler->fresh()->saldo_pendiente);
        $this->assertSame(10, $this->collarin->fresh()->stock_total);
    }

    public function test_dano_valida_datos_y_solo_aplica_a_devueltos(): void
    {
        $alquiler = $this->alquilerCreado();

        $this->post(route('alquileres.danos.store', $alquiler->id), [])
            ->assertSessionHasErrors(['producto_id', 'tipo', 'cantidad', 'monto']);

        $this->post(route('alquileres.danos.store', $alquiler->id), [
            'producto_id' => $this->toga->id, 'tipo' => 'DANO', 'cantidad' => 1, 'monto' => 10,
        ])->assertSessionHas('error');
    }

    // -------------------------------------------------------- fabricación

    public function test_completar_fabricacion_entra_al_inventario_y_deja_listo_para_entrega(): void
    {
        $this->crear(['cantidad' => 15, 'fabricacion_autorizada' => 1])->assertSessionHasNoErrors();
        $alquiler = Alquiler::firstOrFail();
        $fabricacion = AlquilerFabricacion::where('alquiler_id', $alquiler->id)->where('producto_id', $this->toga->id)->firstOrFail();

        $this->get(route('alquileres.show', $alquiler->id))->assertOk()->assertSee('Fabricación');

        $this->post(route('alquileres.fabricaciones.completar', [$alquiler->id, $fabricacion->id]), [])
            ->assertSessionHasErrors('cantidad_completada');

        $this->post(route('alquileres.fabricaciones.completar', [$alquiler->id, $fabricacion->id]), [
            'cantidad_completada' => 5,
            'responsable' => 'Taller',
        ])
            ->assertRedirect(route('alquileres.show', $alquiler->id) . '#fabricacion')
            ->assertSessionHas('success');

        $this->assertSame(15, $this->toga->fresh()->stock_total);
        $this->assertSame(0, (int) $fabricacion->fresh()->cantidad_pendiente);
    }
}
