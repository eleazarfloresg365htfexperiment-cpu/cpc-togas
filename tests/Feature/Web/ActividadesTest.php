<?php

namespace Tests\Feature\Web;

use App\Models\Alquiler;
use App\Models\Bitacora;
use App\Models\Producto;

/**
 * Módulo Actividades. "Hoy" es jueves 8 de octubre de 2026 (ver PruebaWeb),
 * así que "esta semana" va del lunes 5 al domingo 11.
 */
class ActividadesTest extends PruebaWeb
{
    private Producto $togaM;
    private Producto $togaXL;
    private Producto $collarin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->togaM = $this->toga('ESTANDAR', ['stock_total' => 10]);
        $this->togaM->toga->update(['talla' => 'M']);
        $this->togaXL = $this->toga('ESTANDAR', ['stock_total' => 4]);
        $this->togaXL->toga->update(['talla' => 'XL']);
        $this->collarin = $this->collarin('NORMAL', 'Rojo', ['stock_total' => 30]);
    }

    /** Crea un alquiler con varias togas: [producto, cantidad]. */
    private function alquiler(array $togas, string $entrega, string $devolucion, string $reserva = '2026-10-01'): Alquiler
    {
        $productos = [];

        foreach ($togas as [$toga, $cantidad]) {
            $productos[$toga->id] = [
                'seleccionado' => 1,
                'producto_id' => $toga->id,
                'cantidad' => $cantidad,
                'collarin_id' => $this->collarin->id,
            ];
        }

        $this->post(route('alquileres.store'), [
            'cliente_id' => $this->cliente()->id,
            'fecha_alquiler' => $reserva,
            'fecha_entrega' => $entrega,
            'fecha_devolucion_programada' => $devolucion,
            'productos' => $productos,
        ])->assertSessionHasNoErrors();

        return Alquiler::latest('id')->firstOrFail();
    }

    private function pagarYEntregar(Alquiler $alquiler): void
    {
        $this->post(route('pagos.store', $alquiler), ['monto' => $alquiler->fresh()->saldo_pendiente, 'metodo_pago' => 'EFECTIVO']);
        $this->post(route('alquileres.entregar', $alquiler))->assertSessionHas('success');
    }

    /** @return array{a: Alquiler, b: Alquiler, c: Alquiler, d: Alquiler} */
    private function escenario(): array
    {
        $d = $this->alquiler([[$this->togaM, 1]], '2026-10-02', '2026-10-06');   // entregado, NO devuelto (atrasado)
        $this->pagarYEntregar($d);

        $a = $this->alquiler([[$this->togaM, 3]], '2026-10-09', '2026-10-12');   // entregado
        $this->pagarYEntregar($a);

        $b = $this->alquiler([[$this->togaM, 5], [$this->togaXL, 2]], '2026-10-10', '2026-10-13'); // apartado, sin pagar
        $c = $this->alquiler([[$this->togaXL, 4]], '2026-10-20', '2026-10-22');  // apartado, sin pagar

        return compact('a', 'b', 'c', 'd');
    }

    public function test_agenda_de_la_semana_y_atrasados(): void
    {
        ['a' => $a, 'b' => $b, 'c' => $c, 'd' => $d] = $this->escenario();

        $respuesta = $this->get(route('actividades.agenda'))->assertOk();
        $agenda = $respuesta->viewData('agenda');

        $this->assertSame('2026-10-05', $respuesta->viewData('desde')->toDateString());
        $this->assertSame('2026-10-11', $respuesta->viewData('hasta')->toDateString());

        // Al entregar, la fecha de entrega pasa a ser el día real (hoy, 8):
        // A y D aparecen hoy como ya entregados.
        $this->assertEqualsCanonicalizing([$a->id, $d->id], array_column($agenda['dias']['2026-10-08']['entregas'], 'id'));
        $this->assertTrue($agenda['dias']['2026-10-08']['entregas'][0]['hecho']);
        $this->assertSame([], $agenda['dias']['2026-10-09']['entregas']);
        $this->assertSame([$b->id], array_column($agenda['dias']['2026-10-10']['entregas'], 'id'));
        $this->assertFalse($agenda['dias']['2026-10-10']['entregas'][0]['hecho']);
        $this->assertSame('Estándar: M×5, XL×2', $agenda['dias']['2026-10-10']['entregas'][0]['tallas']);
        // D debía volver el martes 6: aparece ese día, marcada como atrasada.
        $this->assertSame([$d->id], array_column($agenda['dias']['2026-10-06']['devoluciones'], 'id'));
        $this->assertTrue($agenda['dias']['2026-10-06']['devoluciones'][0]['atrasado']);
        $this->assertSame(['entregas' => 3, 'devoluciones' => 1, 'togas_entrega' => 11, 'togas_devolucion' => 1], $agenda['totales']);

        // D debía volver el 6 y sigue fuera.
        $this->assertSame([$d->id], array_column($agenda['atrasados']['devoluciones'], 'id'));
        $this->assertSame([], $agenda['atrasados']['entregas']);

        $respuesta->assertSee($b->codigo_recibo)->assertSee('Atrasados');
        $this->assertArrayNotHasKey('2026-10-20', $agenda['dias']);

        // Este mes incluye la entrega de C y las devoluciones de A y B.
        $mes = $this->get(route('actividades.agenda', ['periodo' => 'mes']))->viewData('agenda');
        $this->assertSame([$c->id], array_column($mes['dias']['2026-10-20']['entregas'], 'id'));
        $this->assertSame([$a->id], array_column($mes['dias']['2026-10-12']['devoluciones'], 'id'));
        $this->assertCount(31, $mes['dias']);
    }

    public function test_agenda_personalizada_se_limita_a_dos_meses(): void
    {
        $respuesta = $this->get(route('actividades.agenda', ['periodo' => 'personalizado', 'desde' => '2026-10-01', 'hasta' => '2027-05-01']));

        $this->assertSame('2026-12-02', $respuesta->viewData('hasta')->toDateString());
    }

    public function test_agenda_en_pdf(): void
    {
        $this->escenario();

        $respuesta = $this->get(route('actividades.agenda.pdf', ['periodo' => 'mes']))->assertOk();

        $this->assertSame('application/pdf', $respuesta->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_togas_por_talla_fuera_apartadas_y_libres(): void
    {
        ['b' => $b, 'c' => $c] = $this->escenario();

        $filas = collect($this->get(route('actividades.inventario', ['fecha' => '2026-10-10']))->assertOk()->viewData('tallas'))
            ->keyBy('talla');

        // M: 10 en total; fuera 4 (A y D); apartadas 5 (B).
        // El 10: ocupadas A(3) + B(5) + D(1, atrasada) = 9 → libre 1.
        $this->assertSame(10, $filas['M']['total']);
        $this->assertSame(4, $filas['M']['fuera']);
        $this->assertSame(6, $filas['M']['bodega']);
        $this->assertSame(5, $filas['M']['apartadas']);
        $this->assertSame(1, $filas['M']['libres_en_fecha']);
        $this->assertSame(1, $filas['M']['devoluciones_vencidas']);
        $this->assertSame('2026-10-12', $filas['M']['proxima_devolucion']['fecha']->toDateString());
        $this->assertSame([$b->codigo_recibo], array_column($filas['M']['detalle_apartadas'], 'recibo'));

        // XL: 4 en total; apartadas 2 (B) + 4 (C) = 6. El 10 solo ocupa B → 2 libres.
        $this->assertSame(6, $filas['XL']['apartadas']);
        $this->assertSame(2, $filas['XL']['libres_en_fecha']);

        // El 21 solo ocupa C (4) → 0 libres.
        $el21 = collect($this->get(route('actividades.inventario', ['fecha' => '2026-10-21']))->viewData('tallas'))->keyBy('talla');
        $this->assertSame(0, $el21['XL']['libres_en_fecha']);

        // Accesorios: el collarín está fuera (A y D) y apartado (B y C).
        $collarin = collect($this->get(route('actividades.inventario'))->viewData('accesorios')['COLLARIN'])->first();
        $this->assertSame(4, $collarin['fuera']);
        $this->assertSame(11, $collarin['apartados']);
        $this->assertSame(30 - 4 - 11, $collarin['libres']);
    }

    public function test_por_cobrar_marca_lo_que_se_entrega_pronto(): void
    {
        ['b' => $b, 'c' => $c] = $this->escenario();

        $lista = collect($this->get(route('actividades.cobros'))->assertOk()->viewData('alquileres'))->keyBy('id');

        $this->assertSame([$b->id, $c->id], $lista->keys()->all());
        $this->assertTrue($lista[$b->id]['urgente']);     // se entrega en 2 días
        $this->assertSame(2, $lista[$b->id]['dias_para_entrega']);
        $this->assertFalse($lista[$c->id]['urgente']);    // en 12 días
    }

    public function test_bitacora_registra_quien_hizo_cada_accion(): void
    {
        ['a' => $a] = $this->escenario();

        $this->assertSame(4, Bitacora::where('accion', 'Creó un alquiler')->count());
        $this->assertSame(2, Bitacora::where('accion', 'Entregó un alquiler')->count());
        $this->assertSame(2, Bitacora::where('categoria', 'PAGO')->count());
        $this->assertSame([$this->usuario->id], Bitacora::distinct()->pluck('usuario_id')->all());
        $this->assertTrue(Bitacora::where('alquiler_id', $a->id)->where('accion', 'Entregó un alquiler')->exists());

        $this->get(route('actividades.bitacora'))
            ->assertOk()
            ->assertSee('Creó un alquiler')
            ->assertSee('Usuaria De Prueba');

        $soloPagos = $this->get(route('actividades.bitacora', ['categoria' => 'PAGO']))->viewData('registros');
        $this->assertSame(2, $soloPagos->total());

        $porTexto = $this->get(route('actividades.bitacora', ['buscar' => $a->codigo_recibo]))->viewData('registros');
        $this->assertTrue($porTexto->total() >= 3);
    }

    public function test_otras_acciones_tambien_quedan_en_la_bitacora(): void
    {
        $this->post(route('clientes.store'), ['nombres' => 'Rosa', 'apellidos' => 'Pérez', 'telefono' => '5555', 'dpi' => '1999999990101']);
        $this->post(route('productos.entrada.guardar', $this->togaM), ['cantidad' => 2, 'motivo' => 'Compra']);
        $this->post(route('logout'));

        $this->assertTrue(Bitacora::where('accion', 'Registró un cliente')->where('descripcion', 'Rosa Pérez.')->exists());
        $this->assertTrue(Bitacora::where('accion', 'Registró una entrada')->where('descripcion', 'like', '+2 de%Motivo: Compra')->exists());
        $this->assertTrue(Bitacora::where('accion', 'Cerró sesión')->exists());
    }

    public function test_el_menu_lleva_a_actividades(): void
    {
        $this->get(route('dashboard'))->assertSee(route('actividades.agenda'), false)->assertDontSee('Control de alquileres');
    }
}
