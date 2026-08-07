<?php

namespace App\Services;

use Exception;

class DiscountCalculator
{
    public function validarDescuento(float $subtotal, float $descuentoTotal): void
    {
        if ($descuentoTotal < 0) {
            throw new Exception('El descuento no puede ser negativo.');
        }

        if ($descuentoTotal > $subtotal) {
            throw new Exception('El descuento no puede ser mayor al subtotal.');
        }
    }

    public function calcularDescuentoPorTogas(array $items): float
    {
        if (!config('alquiler.discounts.enabled', false)) {
            return 0.0;
        }

        $valorPorToga = (float) config('alquiler.discounts.per_toga', 0);

        if ($valorPorToga <= 0) {
            return 0.0;
        }

        $cantidadTogas = 0;

        foreach ($items as $item) {
            if ($item['producto']->tipo_producto === 'TOGA') {
                $cantidadTogas += $item['cantidad'];
            }
        }

        return round($cantidadTogas * $valorPorToga, 2);
    }

    public function calcularTotal(float $subtotal, float $descuentoManual, float $descuentoAutomatic = 0): float
    {
        $descuentoTotal = round($descuentoManual + $descuentoAutomatic, 2);

        $this->validarDescuento($subtotal, $descuentoTotal);

        return round($subtotal - $descuentoTotal, 2);
    }
}
