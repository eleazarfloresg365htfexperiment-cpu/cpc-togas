<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * El sistema está en español: mensajes de validación y nombres de meses.
 */
class IdiomaTest extends TestCase
{
    public function test_el_idioma_es_espanol(): void
    {
        $this->assertSame('es', app()->getLocale());
    }

    public function test_los_mensajes_de_validacion_salen_en_espanol_con_nombres_legibles(): void
    {
        $errores = Validator::make(
            ['dpi' => str_repeat('1', 30), 'precio_alquiler' => 'abc'],
            [
                'nombres' => ['required'],
                'dpi' => ['max:13'],
                'precio_alquiler' => ['numeric'],
                'fecha_entrega' => ['required', 'date'],
            ]
        )->errors();

        $this->assertSame('Falta completar los nombres.', $errores->first('nombres'));
        $this->assertSame('El DPI no puede tener más de 13 caracteres.', $errores->first('dpi'));
        $this->assertSame('El precio de alquiler debe ser un número.', $errores->first('precio_alquiler'));
        $this->assertSame('Falta completar la fecha de entrega.', $errores->first('fecha_entrega'));
    }

    public function test_campos_de_productos_dentro_de_listas_usan_su_nombre(): void
    {
        $errores = Validator::make(
            ['productos' => [['cantidad' => 0]]],
            ['productos.*.cantidad' => ['integer', 'min:1'], 'productos.*.collarin_id' => ['required']]
        )->errors();

        $this->assertSame('La cantidad debe ser al menos 1.', $errores->first('productos.0.cantidad'));
        $this->assertSame('Falta completar el collarín.', $errores->first('productos.0.collarin_id'));
    }

    public function test_los_meses_salen_en_espanol(): void
    {
        $this->assertSame('octubre 2026', Carbon::create(2026, 10, 6)->translatedFormat('F Y'));
    }
}
