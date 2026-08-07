<?php

namespace Tests\Unit;

use App\Models\Producto;
use App\Models\ProductoToga;
use App\Services\AlquilerRulesService;
use Tests\TestCase;

class AlquilerRulesServiceTest extends TestCase
{
    public function test_prepara_items_con_accesorios_incluidos_y_extra(): void
    {
        $principal = new Producto([
            'id' => 1,
            'tipo_producto' => 'TOGA',
            'precio_alquiler' => 100.00,
            'activo' => true,
            'stock_disponible' => 2,
        ]);

        $collarin = new Producto([
            'id' => 2,
            'tipo_producto' => 'COLLARIN',
            'precio_alquiler' => 0.0,
            'activo' => true,
            'stock_disponible' => 2,
        ]);

        $birreteExtra = new Producto([
            'id' => 3,
            'tipo_producto' => 'BIRRETE',
            'precio_alquiler' => 25.00,
            'activo' => true,
            'stock_disponible' => 5,
        ]);

        $service = $this->getMockBuilder(AlquilerRulesService::class)
            ->onlyMethods(['obtenerProducto'])
            ->getMock();

        $service->expects($this->any())
            ->method('obtenerProducto')
            ->willReturnMap([
                [1, $principal],
                [2, $collarin],
                [3, $birreteExtra],
            ]);

        $items = $service->prepararItems([
            [
                'producto_id' => 1,
                'cantidad' => 1,
                'accesorios' => [
                    [
                        'producto_id' => 2,
                        'tipo_accesorio' => 'COLLARIN',
                        'tipo_cobro' => 'INCLUIDO',
                        'cantidad' => 1,
                    ],
                    [
                        'producto_id' => 3,
                        'tipo_accesorio' => 'BIRRETE',
                        'tipo_cobro' => 'EXTRA',
                        'cantidad' => 1,
                        'precio_unitario' => null,
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $items);
        $this->assertSame(100.0, $items[0]['subtotal']);
        $this->assertEquals(1, $items[0]['cantidad']);
        $this->assertSame('COLLARIN', $items[0]['accesorios'][0]['tipo_accesorio']);
        $this->assertSame('BIRRETE', $items[0]['accesorios'][1]['tipo_accesorio']);
        $this->assertGreaterThan(0, $items[0]['accesorios'][1]['precio_unitario']);
    }

    public function test_rechaza_accesorio_invalido_para_producto_principal(): void
    {
        $principal = new Producto([
            'id' => 1,
            'tipo_producto' => 'TOGA',
            'precio_alquiler' => 100.00,
            'activo' => true,
            'stock_disponible' => 2,
        ]);

        $collarin = new Producto([
            'id' => 2,
            'tipo_producto' => 'COLLARIN',
            'precio_alquiler' => 0.0,
            'activo' => true,
            'stock_disponible' => 2,
        ]);

        $service = $this->getMockBuilder(AlquilerRulesService::class)
            ->onlyMethods(['obtenerProducto'])
            ->getMock();

        $service->expects($this->any())
            ->method('obtenerProducto')
            ->willReturnMap([
                [1, $principal],
                [2, $collarin],
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El accesorio "' . $collarin->nombre . '" no es válido para el producto principal "' . $principal->nombre . '".');

        $service->prepararItems([
            [
                'producto_id' => 1,
                'cantidad' => 1,
                'accesorios' => [
                    [
                        'producto_id' => 2,
                        'tipo_accesorio' => 'CAPA',
                        'tipo_cobro' => 'INCLUIDO',
                        'cantidad' => 1,
                    ],
                ],
            ],
        ]);
    }

    public function test_requiere_capa_para_toga_universitaria(): void
    {
        $principal = new Producto([
            'id' => 1,
            'tipo_producto' => 'TOGA',
            'precio_alquiler' => 100.00,
            'activo' => true,
            'stock_disponible' => 2,
        ]);
        $togaDetails = new \stdClass();
        $togaDetails->tipo_toga = 'UNIVERSITARIA';
        $principal->setRelation('toga', $togaDetails);

        $collarin = new Producto([
            'id' => 2,
            'tipo_producto' => 'COLLARIN',
            'precio_alquiler' => 0.0,
            'activo' => true,
            'stock_disponible' => 2,
        ]);

        $service = $this->getMockBuilder(AlquilerRulesService::class)
            ->onlyMethods(['obtenerProducto'])
            ->getMock();

        $service->expects($this->any())
            ->method('obtenerProducto')
            ->willReturnMap([
                [1, $principal],
                [2, $collarin],
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El producto principal #1 requiere un accesorio del tipo "CAPA".');

        $service->prepararItems([
            [
                'producto_id' => 1,
                'cantidad' => 1,
                'accesorios' => [
                    [
                        'producto_id' => 2,
                        'tipo_accesorio' => 'COLLARIN',
                        'tipo_cobro' => 'INCLUIDO',
                        'cantidad' => 1,
                    ],
                ],
            ],
        ]);
    }
}
