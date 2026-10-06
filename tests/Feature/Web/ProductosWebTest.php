<?php

namespace Tests\Feature\Web;

use App\Models\MovimientoInventario;
use App\Models\Producto;

class ProductosWebTest extends PruebaWeb
{
    private function datosProducto(string $tipo, array $extra = []): array
    {
        return array_merge([
            'codigo' => "NUEVO-{$tipo}",
            'nombre' => "Producto {$tipo}",
            'tipo_producto' => $tipo,
            'descripcion' => 'Descripción',
            'precio_alquiler' => 45.5,
            'stock_total' => 6,
            'activo' => 1,
        ], $extra);
    }

    public function test_listado_muestra_productos_y_aplica_filtros(): void
    {
        $this->toga('ESTANDAR', ['nombre' => 'Toga Azulada']);
        $this->collarin('NORMAL', 'Rojo', ['nombre' => 'Collarín Granate']);
        $this->producto('BIRRETE', ['nombre' => 'Birrete Inactivo', 'activo' => false]);

        $this->get(route('productos.index'))
            ->assertOk()
            ->assertSee('Toga Azulada')
            ->assertSee('Collarín Granate');

        $this->get(route('productos.index', ['tipo' => 'TOGA']))
            ->assertOk()
            ->assertSee('Toga Azulada')
            ->assertDontSee('Collarín Granate');

        $this->get(route('productos.index', ['buscar' => 'Granate']))
            ->assertOk()
            ->assertSee('Collarín Granate')
            ->assertDontSee('Toga Azulada');

        $this->get(route('productos.index', ['estado' => '0']))
            ->assertOk()
            ->assertSee('Birrete Inactivo')
            ->assertDontSee('Toga Azulada');
    }

    public function test_pantalla_de_crear_producto_carga(): void
    {
        $this->get(route('productos.create'))->assertOk();
    }

    public function test_crea_una_toga_con_su_detalle_y_movimiento_de_entrada(): void
    {
        $this->post(route('productos.store'), $this->datosProducto('TOGA', [
            'tipo_toga' => 'UNIVERSITARIA',
            'talla_toga' => 'L',
            'color_toga' => 'Negro',
        ]))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Producto registrado correctamente.');

        $producto = Producto::where('codigo', 'NUEVO-TOGA')->firstOrFail();

        $this->assertSame(6, $producto->stock_total);
        $this->assertSame(6, $producto->stock_disponible);
        $this->assertSame(0, $producto->stock_alquilado);
        $this->assertSame('UNIVERSITARIA', $producto->toga->tipo_toga);
        $this->assertSame('L', $producto->toga->talla);

        $movimiento = MovimientoInventario::where('producto_id', $producto->id)->firstOrFail();
        $this->assertSame('ENTRADA', $movimiento->tipo_movimiento);
        $this->assertSame(6, (int) $movimiento->cantidad);
    }

    public function test_crea_toga_sin_datos_de_detalle_usa_valores_por_defecto(): void
    {
        $this->post(route('productos.store'), $this->datosProducto('TOGA'))
            ->assertRedirect(route('productos.index'));

        $toga = Producto::where('codigo', 'NUEVO-TOGA')->firstOrFail()->toga;

        $this->assertSame('ESTANDAR', $toga->tipo_toga);
        $this->assertSame('No especificado', $toga->talla);
        $this->assertSame('No especificado', $toga->color);
    }

    public function test_crea_producto_sin_stock_no_genera_movimiento(): void
    {
        $this->post(route('productos.store'), $this->datosProducto('BIRRETE', [
            'tipo_birrete' => 'UNIVERSITARIO',
            'stock_total' => 0,
        ]))->assertRedirect(route('productos.index'));

        $producto = Producto::where('codigo', 'NUEVO-BIRRETE')->firstOrFail();

        $this->assertSame('UNIVERSITARIO', $producto->birrete->tipo_birrete);
        $this->assertSame(0, MovimientoInventario::where('producto_id', $producto->id)->count());
    }

    public function test_crea_collarin_normal_y_universitario(): void
    {
        $this->post(route('productos.store'), $this->datosProducto('COLLARIN', [
            'codigo' => 'COL-N',
            'tipo_collarin' => 'NORMAL',
            'color_collarin' => 'Dorado',
            'codigo_color_collarin' => 'C-DO',
        ]))->assertRedirect(route('productos.index'));

        $this->post(route('productos.store'), $this->datosProducto('COLLARIN', [
            'codigo' => 'COL-U',
            'tipo_collarin' => 'UNIVERSITARIO',
            'color_collarin' => 'Azul',
            'codigo_color_collarin' => 'C-AZ',
        ]))->assertRedirect(route('productos.index'));

        $this->assertSame('Dorado', Producto::where('codigo', 'COL-N')->first()->collarin->color);
        $this->assertSame('Azul', Producto::where('codigo', 'COL-U')->first()->collarin->color);
    }

    public function test_collarin_exige_tipo_color_y_codigo(): void
    {
        $base = $this->datosProducto('COLLARIN');

        $this->from(route('productos.create'))
            ->post(route('productos.store'), $base)
            ->assertRedirect(route('productos.create'))
            ->assertSessionHasErrors(['tipo_collarin' => 'Debe seleccionar el tipo de collarín.']);

        $this->from(route('productos.create'))
            ->post(route('productos.store'), $base + ['tipo_collarin' => 'NORMAL'])
            ->assertSessionHasErrors(['color_collarin' => 'Debe seleccionar el color del collarín.']);

        $this->from(route('productos.create'))
            ->post(route('productos.store'), $base + ['tipo_collarin' => 'NORMAL', 'color_collarin' => 'Rojo'])
            ->assertSessionHasErrors(['codigo_color_collarin' => 'Debe indicar el código de color del collarín.']);

        $this->assertSame(0, Producto::count());
    }

    public function test_collarin_azul_solo_para_universitario(): void
    {
        $this->from(route('productos.create'))
            ->post(route('productos.store'), $this->datosProducto('COLLARIN', [
                'tipo_collarin' => 'NORMAL',
                'color_collarin' => 'Azul',
                'codigo_color_collarin' => 'C-AZ',
            ]))
            ->assertSessionHasErrors(['color_collarin' => 'El color azul corresponde únicamente a los collarines universitarios.']);

        $this->from(route('productos.create'))
            ->post(route('productos.store'), $this->datosProducto('COLLARIN', [
                'tipo_collarin' => 'UNIVERSITARIO',
                'color_collarin' => 'Rojo',
                'codigo_color_collarin' => 'C-RO',
            ]))
            ->assertSessionHasErrors(['color_collarin' => 'Los collarines universitarios deben utilizar el color azul.']);

        $this->assertSame(0, Producto::count());
    }

    public function test_birrete_exige_tipo(): void
    {
        $this->from(route('productos.create'))
            ->post(route('productos.store'), $this->datosProducto('BIRRETE'))
            ->assertSessionHasErrors(['tipo_birrete' => 'Debe seleccionar el tipo de birrete.']);
    }

    public function test_capa_exige_carrera_codigo_y_color_y_se_guarda(): void
    {
        $this->from(route('productos.create'))
            ->post(route('productos.store'), $this->datosProducto('CAPA', ['carrera_capa' => 'DERECHO']))
            ->assertSessionHasErrors(['carrera_capa' => 'Debe indicar la carrera, el código y el color de la capa.']);

        $this->post(route('productos.store'), $this->datosProducto('CAPA', [
            'carrera_capa' => 'DERECHO',
            'codigo_color_capa' => 'DER',
            'color_capa' => 'Rojo',
            'talla_capa' => 'M',
        ]))->assertRedirect(route('productos.index'));

        $capa = Producto::where('codigo', 'NUEVO-CAPA')->firstOrFail()->capa;
        $this->assertSame('DERECHO', $capa->carrera);
        $this->assertSame('DER', $capa->codigo_color);
        $this->assertSame('Rojo', $capa->color);
    }

    public function test_borla_se_guarda_y_completa_el_codigo_si_llega_vacio(): void
    {
        $this->post(route('productos.store'), $this->datosProducto('BORLA', [
            'codigo' => 'BOR-A',
            'borla_color' => 'Dorado',
            'borla_codigo_color' => 'B-X1',
        ]))->assertRedirect(route('productos.index'));

        $this->post(route('productos.store'), $this->datosProducto('BORLA', [
            'codigo' => 'BOR-B',
            'borla_color' => 'Dorado',
        ]))->assertRedirect(route('productos.index'));

        $this->assertSame('B-X1', Producto::where('codigo', 'BOR-A')->first()->borla->codigo_color);
        $this->assertSame('B-DOR', Producto::where('codigo', 'BOR-B')->first()->borla->codigo_color);
    }

    public function test_borla_exige_color(): void
    {
        $this->from(route('productos.create'))
            ->post(route('productos.store'), $this->datosProducto('BORLA'))
            ->assertSessionHasErrors(['borla_color' => 'Debe indicar el código y el color de la borla.']);
    }

    public function test_valida_datos_basicos_del_producto(): void
    {
        $existente = $this->toga();

        $this->from(route('productos.create'))
            ->post(route('productos.store'), $this->datosProducto('TOGA', ['codigo' => $existente->codigo]))
            ->assertSessionHasErrors('codigo');

        $this->from(route('productos.create'))
            ->post(route('productos.store'), ['tipo_producto' => 'ZAPATO'])
            ->assertSessionHasErrors(['codigo', 'nombre', 'tipo_producto', 'precio_alquiler', 'stock_total', 'activo']);
    }

    public function test_pantalla_de_editar_carga_para_cada_tipo(): void
    {
        foreach ([
            $this->toga(),
            $this->collarin(),
            $this->capa(),
            $this->birrete(),
            $this->borla(),
        ] as $producto) {
            $this->get(route('productos.edit', $producto->id))
                ->assertOk()
                ->assertSee($producto->codigo);
        }
    }

    public function test_editar_producto_inexistente_da_404(): void
    {
        $this->get(route('productos.edit', 9999))->assertNotFound();
    }

    public function test_actualiza_datos_generales_y_ajusta_el_stock_disponible(): void
    {
        $toga = $this->toga('ESTANDAR', ['stock_total' => 10]);
        $toga->update(['stock_disponible' => 7, 'stock_alquilado' => 3]);

        $this->put(route('productos.update', $toga->id), [
            'codigo' => 'TOGA-EDIT',
            'nombre' => 'Toga editada',
            'precio_alquiler' => 60,
            'stock_total' => 15,
            'activo' => 1,
            'tipo_toga' => 'UNIVERSITARIA',
            'talla_toga' => 'XL',
            'color_toga' => 'Azul',
        ])
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Producto actualizado correctamente.');

        $toga->refresh();

        $this->assertSame('TOGA-EDIT', $toga->codigo);
        $this->assertSame(15, $toga->stock_total);
        $this->assertSame(12, $toga->stock_disponible);
        $this->assertSame(3, $toga->stock_alquilado);
        $this->assertSame('UNIVERSITARIA', $toga->toga->tipo_toga);
        $this->assertSame('XL', $toga->toga->talla);
    }

    public function test_actualizar_permite_conservar_su_propio_codigo_pero_no_el_de_otro(): void
    {
        $uno = $this->toga();
        $dos = $this->toga();

        $datos = fn (string $codigo) => [
            'codigo' => $codigo,
            'nombre' => 'X',
            'precio_alquiler' => 10,
            'stock_total' => 10,
            'activo' => 1,
        ];

        $this->put(route('productos.update', $uno->id), $datos($uno->codigo))
            ->assertRedirect(route('productos.index'));

        $this->from(route('productos.edit', $uno->id))
            ->put(route('productos.update', $uno->id), $datos($dos->codigo))
            ->assertSessionHasErrors('codigo');
    }

    public function test_actualizar_collarin_aplica_las_mismas_reglas_de_color(): void
    {
        $collarin = $this->collarin('NORMAL', 'Rojo');

        $this->from(route('productos.edit', $collarin->id))
            ->put(route('productos.update', $collarin->id), [
                'codigo' => $collarin->codigo,
                'nombre' => $collarin->nombre,
                'precio_alquiler' => 10,
                'stock_total' => 10,
                'activo' => 1,
                'tipo_collarin' => 'NORMAL',
                'color_collarin' => 'Azul',
                'codigo_color_collarin' => 'C-AZ',
            ])
            ->assertSessionHasErrors(['color_collarin' => 'El color azul corresponde únicamente a los collarines universitarios.']);
    }

    public function test_actualizar_borla_completa_el_codigo_a_partir_del_color(): void
    {
        $borla = $this->borla('Rojo');

        $this->put(route('productos.update', $borla->id), [
            'codigo' => $borla->codigo,
            'nombre' => $borla->nombre,
            'precio_alquiler' => 10,
            'stock_total' => 10,
            'activo' => 1,
            'borla_color' => 'Dorado',
            'borla_codigo_color' => '',
        ])->assertRedirect(route('productos.index'));

        $borla->refresh();
        $this->assertSame('Dorado', $borla->borla->color);
        $this->assertSame('B-DOR', $borla->borla->codigo_color);
    }

    public function test_desactivar_y_reactivar_producto(): void
    {
        $toga = $this->toga();

        $this->patch(route('productos.desactivar', $toga->id))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Producto desactivado correctamente.');
        $this->assertFalse($toga->fresh()->activo);

        $this->patch(route('productos.reactivar', $toga->id))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Producto reactivado correctamente.');
        $this->assertTrue($toga->fresh()->activo);
    }

    public function test_pantallas_de_administrar_productos(): void
    {
        $this->toga('ESTANDAR', ['nombre' => 'Toga Buscable']);
        $this->collarin();

        $this->get(route('productos.administrar'))->assertOk();

        foreach (['editar', 'entrada', 'ajuste', 'estado'] as $accion) {
            $this->get(route('productos.administrar.accion', $accion))
                ->assertOk()
                ->assertSee('Toga Buscable');
        }

        $this->get(route('productos.administrar.accion', ['accion' => 'editar', 'buscar' => 'Buscable', 'tipo' => 'TOGA']))
            ->assertOk()
            ->assertSee('Toga Buscable');

        $this->get(route('productos.administrar.accion', 'eliminar'))->assertNotFound();
    }

    // ------------------------------------------------ mejoras de la fase 1

    public function test_bajar_el_stock_total_por_debajo_de_lo_alquilado_muestra_error_en_el_formulario(): void
    {
        $toga = $this->toga('ESTANDAR', ['stock_total' => 10]);
        $toga->update(['stock_disponible' => 6, 'stock_alquilado' => 4]);

        $this->from(route('productos.edit', $toga->id))
            ->put(route('productos.update', $toga->id), [
                'codigo' => $toga->codigo,
                'nombre' => $toga->nombre,
                'precio_alquiler' => 10,
                'stock_total' => 3,
                'activo' => 1,
            ])
            ->assertRedirect(route('productos.edit', $toga->id))
            ->assertSessionHasErrors(['stock_total' => 'El stock total no puede ser menor que el stock actualmente alquilado.']);

        $this->assertSame(10, $toga->fresh()->stock_total);
    }

    public function test_textos_mas_largos_que_la_base_de_datos_se_avisan_en_el_formulario(): void
    {
        // Las columnas codigo_color tienen 20 caracteres; antes esto llegaba a
        // la base y terminaba en una pantalla de error del servidor.
        $this->from(route('productos.create'))
            ->post(route('productos.store'), $this->datosProducto('BORLA', [
                'borla_color' => 'Rojo',
                'borla_codigo_color' => str_repeat('B', 21),
            ]))
            ->assertSessionHasErrors('borla_codigo_color');

        $this->from(route('productos.create'))
            ->post(route('productos.store'), $this->datosProducto('TOGA', ['codigo' => str_repeat('X', 51)]))
            ->assertSessionHasErrors('codigo');

        $this->assertSame(0, Producto::count());
    }

    public function test_actualizar_producto_inexistente_da_404(): void
    {
        $this->put(route('productos.update', 9999), [])->assertNotFound();
    }
}
