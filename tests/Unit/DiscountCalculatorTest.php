<?php

namespace Tests\Unit;

use App\Services\DiscountCalculator;
use Tests\TestCase;

class DiscountCalculatorTest extends TestCase
{
    public function test_calcular_total_con_descuento_valido(): void
    {
        $calculator = new DiscountCalculator();

        $total = $calculator->calcularTotal(100.00, 20.00);

        $this->assertSame(80.00, $total);
    }

    public function test_calcular_total_con_descuento_manual_y_descuento_por_toga(): void
    {
        $calculator = new DiscountCalculator();

        $total = $calculator->calcularTotal(100.00, 10.00, 15.00);

        $this->assertSame(75.00, $total);
    }

    public function test_calcular_descuento_por_togas(): void
    {
        $calculator = new DiscountCalculator();

        $items = [
            ['producto' => (object) ['tipo_producto' => 'TOGA'], 'cantidad' => 2],
            ['producto' => (object) ['tipo_producto' => 'TOGA'], 'cantidad' => 1],
            ['producto' => (object) ['tipo_producto' => 'BIRRETE'], 'cantidad' => 5],
        ];

        $this->assertSame(15.00, $calculator->calcularDescuentoPorTogas($items));
    }

    public function test_descuento_no_puede_ser_mayor_al_subtotal(): void
    {
        $calculator = new DiscountCalculator();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El descuento no puede ser mayor al subtotal.');

        $calculator->calcularTotal(50.00, 60.00);
    }
}
