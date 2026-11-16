<?php

namespace Tests\Feature\Web;

use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Producto;

/**
 * Historial de clientes y ranking de clientes en estadísticas.
 * "Hoy" es 8 de octubre de 2026 (ver PruebaWeb).
 */
class HistorialClienteTest extends PruebaWeb
{
    private Cliente $ana;
    private Cliente $beto;
    private Producto $togaM;
    private Producto $togaXL;
    private Producto $collarin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ana = $this->cliente(['nombres' => 'Ana', 'apellidos' => 'Morales']);
        $this->beto = $this->cliente(['nombres' => 'Beto', 'apellidos' => 'Ruiz']);
        $this->togaM = $this->toga('ESTANDAR', ['stock_total' => 50, 'precio_alquiler' => 50]);
        $this->togaM->toga->update(['talla' => 'M']);
        $this->togaXL = $this->toga('ESTANDAR', ['stock_total' => 50, 'precio_alquiler' => 50]);
        $this->togaXL->toga->update(['talla' => 'XL']);
        $this->collarin = $this->collarin('NORMAL', 'Rojo', ['stock_total' => 200]);
    }

    private function alquiler(Cliente $cliente, array $togas, string $fecha): Alquiler
    {
        $productos = [];

        foreach ($togas as [$toga, $cantidad]) {
            $productos[$toga->id] = ['seleccionado' => 1, 'producto_id' => $toga->id, 'cantidad' => $cantidad, 'collarin_id' => $this->collarin->id];
        }

        $entrega = date('Y-m-d', strtotime($fecha . ' +5 days'));

        $this->post(route('alquileres.store'), [
            'cliente_id' => $cliente->id,
            'fecha_alquiler' => $fecha,
            'fecha_entrega' => $entrega,
            'fecha_devolucion_programada' => date('Y-m-d', strtotime($entrega . ' +3 days')),
            'productos' => $productos,
        ])->assertSessionHasNoErrors();

        return Alquiler::latest('id')->firstOrFail();
    }

    private function escenario(): array
    {
        // Ana: 2025 (M×10) y 2026 (M×4 + XL×2, con pago de Q100) y uno cancelado (XL×7).
        $a2025 = $this->alquiler($this->ana, [[$this->togaM, 10]], '2025-10-01');
        $a2026 = $this->alquiler($this->ana, [[$this->togaM, 4], [$this->togaXL, 2]], '2026-10-02');
        $this->post(route('pagos.store', $a2026), ['monto' => 100, 'metodo_pago' => 'EFECTIVO']);
        $cancelado = $this->alquiler($this->ana, [[$this->togaXL, 7]], '2026-10-03');
        $this->post(route('alquileres.cancelar', $cancelado), ['motivo_cancelacion' => 'Prueba']);

        // Beto: 2026 (M×3).
        $b2026 = $this->alquiler($this->beto, [[$this->togaM, 3]], '2026-10-04');

        return compact('a2025', 'a2026', 'cancelado', 'b2026');
    }

    public function test_pantalla_del_cliente_con_historial_por_anio(): void
    {
        ['a2025' => $a2025, 'a2026' => $a2026, 'cancelado' => $cancelado] = $this->escenario();

        $respuesta = $this->get(route('clientes.show', $this->ana))->assertOk();
        $resumen = $respuesta->viewData('resumen');
        $anios = $respuesta->viewData('anios');

        // Sin contar el cancelado: 2 alquileres, 16 togas, Q800 (16 × Q50), pagado Q100.
        $this->assertSame(2, $resumen['alquileres']);
        $this->assertSame(1, $resumen['cancelados']);
        $this->assertSame(16, $resumen['togas']);
        $this->assertSame(800.0, $resumen['total']);
        $this->assertSame(100.0, $resumen['pagado']);
        $this->assertSame(700.0, $resumen['saldo']);
        $this->assertSame(2, $resumen['anios_activos']);
        $this->assertSame('2025-10-01', $resumen['primer_alquiler']->toDateString());

        // Años del más reciente al más antiguo.
        $this->assertSame([2026, 2025], array_column($anios, 'anio'));
        $this->assertSame(6, $anios[0]['togas']);
        $this->assertSame(10, $anios[1]['togas']);
        $this->assertCount(2, $anios[0]['alquileres']);   // incluye el cancelado, que no suma

        $respuesta
            ->assertSee($a2025->codigo_recibo)
            ->assertSee($a2026->codigo_recibo)
            ->assertSee($cancelado->codigo_recibo)
            ->assertSee('M×4, XL×2')
            ->assertSee('Collarín×6');
    }

    public function test_cliente_sin_alquileres_y_cliente_inexistente(): void
    {
        $nuevo = $this->cliente();

        $this->get(route('clientes.show', $nuevo))->assertOk()->assertSee('todavía no tiene alquileres');
        $this->get('/clientes/999999')->assertNotFound();
        $this->get(route('clientes.create'))->assertOk();   // "crear" no se confunde con un id
    }

    public function test_exportar_historial_de_un_cliente(): void
    {
        ['a2026' => $a2026, 'b2026' => $b2026] = $this->escenario();

        $pdf = $this->get(route('clientes.show.pdf', $this->ana))->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $excel = $this->get(route('clientes.show.excel', $this->ana))->assertOk();
        $this->assertStringContainsString('historial_ana_morales.xls', $excel->headers->get('content-disposition'));
        $excel->assertSee('Ana Morales')->assertSee($a2026->codigo_recibo)->assertDontSee($b2026->codigo_recibo)->assertDontSee('Beto Ruiz');
    }

    public function test_exportar_historial_de_todos_respeta_filtros(): void
    {
        ['b2026' => $b2026] = $this->escenario();
        $sinAlquileres = $this->cliente(['nombres' => 'Zoila', 'apellidos' => 'Sinalquiler']);

        $excel = $this->get(route('clientes.historial.excel'))->assertOk();
        $excel->assertSee('Ana Morales')->assertSee('Beto Ruiz')->assertSee($b2026->codigo_recibo)
            ->assertSee('TOTALES (2 clientes)')
            ->assertDontSee('Zoila');   // por defecto, solo clientes con alquileres

        $this->get(route('clientes.historial.excel', ['solo_con_alquileres' => 0]))->assertSee('Zoila');
        $this->get(route('clientes.historial.excel', ['buscar' => 'Beto']))->assertSee('Beto Ruiz')->assertDontSee('Ana Morales');

        $pdf = $this->get(route('clientes.historial.pdf'))->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_listado_de_clientes_enlaza_al_historial(): void
    {
        $this->get(route('clientes.index'))
            ->assertOk()
            ->assertSee(route('clientes.show', $this->ana), false)
            ->assertSee('Historial en PDF');
    }

    public function test_ranking_de_clientes_en_estadisticas(): void
    {
        $this->escenario();

        // Año 2026: Ana 6 togas (el cancelado no cuenta), Beto 3.
        $top = $this->get(route('estadisticas.index', ['tipo_vista' => 'anio', 'anio' => 2026]))
            ->assertOk()
            ->assertSee('clientes que más togas alquilaron')
            ->viewData('topClientes');

        $this->assertSame(['Ana', 'Beto'], $top->pluck('nombres')->all());
        $this->assertSame([6, 3], $top->pluck('total')->map(fn ($n) => (int) $n)->all());
        $this->assertSame(1, (int) $top[0]->alquileres);

        $xlsx = $this->get(route('estadisticas.exportar.xlsx', ['tipo_vista' => 'anio', 'anio' => 2026]))->assertOk();
        $this->assertStringContainsString('.xlsx', $xlsx->headers->get('content-disposition'));
    }

    public function test_pdf_de_estadisticas_trae_las_graficas_con_sus_valores(): void
    {
        $this->escenario();

        // La vista del PDF dibuja las barras con el valor escrito (sin JavaScript).
        $html = view('estadisticas.pdf', $this->datosPdf())->render();

        foreach ([
            'Gráficas del periodo',
            'Alquileres por periodo',
            'Resumen financiero por periodo',
            'Productos más alquilados',
            'Tallas de toga más alquiladas',
            'Clientes que más togas alquilaron',
            '6 togas',   // Ana
            '3 togas',   // Beto
            'Q100.00',   // pago de Ana
        ] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }

        $pdf = $this->post(route('estadisticas.exportar.pdf'), ['tipo_vista' => 'anio', 'anio' => 2026])->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    private function datosPdf(): array
    {
        $controlador = app(\App\Http\Controllers\EstadisticasController::class);
        $metodo = new \ReflectionMethod($controlador, 'obtenerDatosExportacion');

        return $metodo->invoke($controlador, request()->merge(['tipo_vista' => 'anio', 'anio' => 2026]));
    }
}
