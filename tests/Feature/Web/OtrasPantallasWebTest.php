<?php

namespace Tests\Feature\Web;

use App\Models\Alquiler;

/**
 * Pantallas de consulta: panel, control, calendario, estadísticas y
 * exportaciones. Comprueba que cargan con y sin datos.
 */
class OtrasPantallasWebTest extends PruebaWeb
{
    private function conDatos(): Alquiler
    {
        $cliente = $this->cliente();
        $toga = $this->toga('ESTANDAR', ['precio_alquiler' => 50]);
        $collarin = $this->collarin();

        $this->post(route('alquileres.store'), $this->formularioAlquiler($cliente, $toga, $collarin));
        $alquiler = Alquiler::firstOrFail();
        $this->post(route('pagos.store', $alquiler->id), ['monto' => 100, 'metodo_pago' => 'EFECTIVO']);

        return $alquiler;
    }

    public function test_raiz_redirige_al_panel(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_panel_carga_sin_datos_y_con_datos(): void
    {
        $this->get('/dashboard')->assertOk();

        $this->conDatos();
        $this->get('/dashboard')->assertOk()->assertSee('Q');
    }

    public function test_control_de_alquileres(): void
    {
        $this->get(route('control-alquileres.index'))->assertOk();

        $alquiler = $this->conDatos();
        $this->get(route('control-alquileres.index'))->assertOk()->assertSee($alquiler->codigo_recibo);
    }

    public function test_calendario_y_sus_eventos(): void
    {
        $this->get(route('calendario.index'))->assertOk();
        $this->get(route('calendario.eventos'))->assertOk();

        $this->conDatos();
        $this->get(route('calendario.eventos', ['start' => '2026-10-01', 'end' => '2026-10-31']))
            ->assertOk();
    }

    public function test_estadisticas_cargan(): void
    {
        $this->get(route('estadisticas.index'))->assertOk();

        $this->conDatos();
        $this->get(route('estadisticas.index'))->assertOk();
    }

    public function test_exportaciones_responden(): void
    {
        $this->conDatos();

        foreach ([
            'exportaciones.alquileres.excel',
            'exportaciones.movimientos.excel',
            'exportaciones.alquileres.pdf',
            'exportaciones.movimientos.pdf',
            'estadisticas.exportar.xlsx',
        ] as $ruta) {
            $respuesta = $this->get(route($ruta));
            $this->assertTrue(
                $respuesta->isSuccessful(),
                "{$ruta} respondió {$respuesta->getStatusCode()}"
            );
        }
    }
}
