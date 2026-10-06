<?php

namespace Tests\Feature\Web;

/**
 * El formulario de productos es el mismo al crear y al editar
 * (resources/views/productos/partials). Estas pruebas cuidan que los dos
 * sigan armándose igual y que las tablas de colores sean las de config.
 */
class FormularioProductoTest extends PruebaWeb
{
    private const BLOQUES = ['campos-toga', 'campos-capa', 'campos-birrete', 'campos-collarin', 'campos-borla'];

    public function test_crear_dibuja_los_cinco_bloques_ocultos_y_carga_el_javascript(): void
    {
        $respuesta = $this->get(route('productos.create'))->assertOk();

        foreach (self::BLOQUES as $bloque) {
            $respuesta->assertSee('id="' . $bloque . '" class="tipo-extra d-none"', false);
        }

        $respuesta
            ->assertSee('id="formProducto" data-modo="crear"', false)
            ->assertSee('js/productos-formulario.js', false)
            ->assertSee('window.CPC_PRODUCTOS', false)
            ->assertSee('"B-CE"', false);   // código de la borla Celeste (config)

        $this->assertFileExists(public_path('js/productos-formulario.js'));
    }

    public function test_editar_dibuja_solo_el_bloque_de_su_tipo_y_sin_ocultar(): void
    {
        $productos = [
            'campos-toga' => $this->toga(),
            'campos-capa' => $this->capa(),
            'campos-birrete' => $this->birrete(),
            'campos-collarin' => $this->collarin(),
            'campos-borla' => $this->borla(),
        ];

        foreach ($productos as $bloque => $producto) {
            $respuesta = $this->get(route('productos.edit', $producto))
                ->assertOk()
                ->assertSee('id="' . $bloque . '" class="tipo-extra "', false)
                ->assertSee('data-modo="editar"', false);

            foreach (array_diff(self::BLOQUES, [$bloque]) as $otro) {
                $respuesta->assertDontSee('id="' . $otro . '"', false);
            }
        }
    }

    public function test_editar_no_deja_cambiar_el_tipo(): void
    {
        $toga = $this->toga();

        $this->get(route('productos.edit', $toga))
            ->assertOk()
            ->assertSee('El tipo de producto no se modifica desde esta pantalla.')
            ->assertDontSee('name="tipo_producto"', false);
    }

    public function test_editar_conserva_colores_antiguos_que_ya_no_estan_en_la_lista(): void
    {
        $borla = $this->borla('Rojo-Derecho');
        $capa = $this->capa('DERECHO', 'Gris');

        $this->get(route('productos.edit', $borla))
            ->assertOk()
            ->assertSee('<option value="Rojo-Derecho" selected>', false);

        $this->get(route('productos.edit', $capa))
            ->assertOk()
            ->assertSee('<option value="Gris" selected>', false);
    }

    public function test_el_collarin_universitario_solo_ofrece_azul(): void
    {
        $universitario = $this->collarin('UNIVERSITARIO', 'Azul');
        $normal = $this->collarin('NORMAL', 'Rojo');

        $this->get(route('productos.edit', $universitario))
            ->assertOk()
            ->assertSee('<option value="Azul" selected>', false)
            ->assertDontSee('<option value="Dorado"', false);

        $this->get(route('productos.edit', $normal))
            ->assertOk()
            ->assertSee('<option value="Rojo" selected>', false)
            ->assertDontSee('<option value="Azul"', false);
    }

    public function test_las_listas_del_servidor_salen_de_la_misma_configuracion(): void
    {
        $datos = [
            'codigo' => 'CAPA-X',
            'nombre' => 'Capa X',
            'tipo_producto' => 'CAPA',
            'precio_alquiler' => 0,
            'stock_total' => 1,
            'activo' => 1,
            'color_capa' => 'Rojo',
            'codigo_color_capa' => 'DER',
        ];

        // Una carrera que no está en config/alquiler.php se rechaza...
        $this->from(route('productos.create'))
            ->post(route('productos.store'), $datos + ['carrera_capa' => 'ARQUITECTURA'])
            ->assertSessionHasErrors('carrera_capa');

        // ...y cada carrera de la configuración se acepta.
        foreach (array_keys(config('alquiler.carreras_capa')) as $i => $carrera) {
            $this->post(route('productos.store'), array_merge($datos, [
                'codigo' => 'CAPA-' . $i,
                'carrera_capa' => $carrera,
            ]))->assertSessionHasNoErrors();
        }

        // Los colores antiguos de borla siguen siendo válidos al guardar.
        $borla = $this->borla('Verde-Agronomia');

        $this->put(route('productos.update', $borla), [
            'codigo' => $borla->codigo,
            'nombre' => $borla->nombre,
            'precio_alquiler' => 5,
            'stock_total' => $borla->stock_total,
            'activo' => 1,
            'borla_color' => 'Verde-Agronomia',
            'borla_codigo_color' => 'B-VA',
        ])->assertSessionHasNoErrors();
    }

    public function test_birrete_solo_ofrece_normal_y_universitario_y_lo_antiguo_se_ve_como_normal(): void
    {
        $antiguo = $this->birrete('ESTANDAR');

        $this->get(route('productos.edit', $antiguo))
            ->assertOk()
            ->assertSee('<option value="NORMAL" selected>', false)
            ->assertSee('<option value="UNIVERSITARIO"', false)
            ->assertDontSee('value="ESTANDAR"', false)
            ->assertDontSee('Estándar');

        // En crear, ESTANDAR solo existe para la toga, no para el birrete.
        $html = $this->get(route('productos.create'))->assertOk()->getContent();
        preg_match('/<select name="tipo_birrete".*?<\/select>/s', $html, $selectBirrete);
        $this->assertNotEmpty($selectBirrete);
        $this->assertStringNotContainsString('ESTANDAR', $selectBirrete[0]);
        $this->assertStringContainsString('value="NORMAL"', $selectBirrete[0]);
    }

    public function test_borla_tiene_tipo_y_el_codigo_depende_del_tipo(): void
    {
        $base = [
            'nombre' => 'Borla',
            'tipo_producto' => 'BORLA',
            'precio_alquiler' => 5,
            'stock_total' => 1,
            'activo' => 1,
            'borla_codigo_color' => '',
        ];

        $this->post(route('productos.store'), $base + ['codigo' => 'BR-N', 'tipo_borla' => 'NORMAL', 'borla_color' => 'Rojo'])
            ->assertSessionHasNoErrors();
        $this->post(route('productos.store'), $base + ['codigo' => 'BR-U', 'tipo_borla' => 'UNIVERSITARIA', 'borla_color' => 'Rojo'])
            ->assertSessionHasNoErrors();

        $normal = \App\Models\Producto::where('codigo', 'BR-N')->firstOrFail()->borla;
        $universitaria = \App\Models\Producto::where('codigo', 'BR-U')->firstOrFail()->borla;

        $this->assertSame(['NORMAL', 'B-RO'], [$normal->tipo_borla, $normal->codigo_color]);
        $this->assertSame(['UNIVERSITARIA', 'B-RO-U'], [$universitaria->tipo_borla, $universitaria->codigo_color]);

        // No existe borla normal celeste ni universitaria dorada.
        $this->from(route('productos.create'))
            ->post(route('productos.store'), $base + ['codigo' => 'BR-X', 'tipo_borla' => 'NORMAL', 'borla_color' => 'Celeste'])
            ->assertSessionHasErrors('borla_color');
        $this->from(route('productos.create'))
            ->post(route('productos.store'), $base + ['codigo' => 'BR-Y', 'tipo_borla' => 'UNIVERSITARIA', 'borla_color' => 'Dorado'])
            ->assertSessionHasErrors('borla_color');

        // Sin tipo se deduce del color cuando solo existe en uno.
        $this->post(route('productos.store'), $base + ['codigo' => 'BR-C', 'borla_color' => 'Celeste'])
            ->assertSessionHasNoErrors();
        $this->assertSame('UNIVERSITARIA', \App\Models\Producto::where('codigo', 'BR-C')->firstOrFail()->borla->tipo_borla);
    }

    public function test_editar_borla_muestra_su_tipo(): void
    {
        $universitaria = $this->borla('Verde', [], 'UNIVERSITARIA');

        $this->get(route('productos.edit', $universitaria))
            ->assertOk()
            ->assertSee('id="borla_universitaria" value="UNIVERSITARIA"', false)
            ->assertSee('<option value="Celeste"', false)
            ->assertDontSee('<option value="Dorado"', false);
    }

    public function test_los_accesorios_no_tienen_precio_propio(): void
    {
        // Aunque llegue un precio, un accesorio se guarda en 0.
        $this->post(route('productos.store'), [
            'codigo' => 'BIR-PRECIO',
            'nombre' => 'Birrete',
            'tipo_producto' => 'BIRRETE',
            'tipo_birrete' => 'NORMAL',
            'precio_alquiler' => 99,
            'stock_total' => 1,
            'activo' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame(0.0, (float) \App\Models\Producto::where('codigo', 'BIR-PRECIO')->value('precio_alquiler'));

        // Al editar un accesorio el campo de precio está oculto y en 0.
        $borla = $this->borla('Dorado', ['precio_alquiler' => 5]);

        $this->get(route('productos.edit', $borla))
            ->assertOk()
            ->assertSee('d-none" id="grupo-precio"', false);

        // La toga sí conserva su precio y el campo se ve.
        $toga = $this->toga('ESTANDAR', ['precio_alquiler' => 75]);

        $this->get(route('productos.edit', $toga))
            ->assertOk()
            ->assertDontSee('d-none" id="grupo-precio"', false)
            ->assertSee('value="75.00"', false);

        $this->get(route('productos.index'))
            ->assertOk()
            ->assertSee('Incluido')
            ->assertSee('Q 75.00');
    }
}
