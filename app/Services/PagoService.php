<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Exception;

class PagoService
{
    public function registrarPago(
        int $alquilerId,
        float $monto,
        string $metodoPago = 'EFECTIVO',
        ?string $referencia = null,
        ?string $observaciones = null,
        ?int $usuarioId = null,
        float $descuentoAplicado = 0,
        ?string $observacionDescuento = null
    ): Pago {
        return DB::transaction(function () use (
            $alquilerId,
            $monto,
            $metodoPago,
            $referencia,
            $observaciones,
            $usuarioId,
            $descuentoAplicado,
            $observacionDescuento
        ) {
            $alquiler = Alquiler::lockForUpdate()->findOrFail($alquilerId);

            $monto = round((float) $monto, 2);
            $descuentoAplicado = round((float) $descuentoAplicado, 2);

            if ($monto < 0) {
                throw new Exception('El monto del pago no puede ser negativo.');
            }

            if ($descuentoAplicado < 0) {
                throw new Exception('El descuento aplicado no puede ser negativo.');
            }

            if (($monto + $descuentoAplicado) <= 0) {
                throw new Exception('Debe ingresar un monto de pago o un descuento mayor a cero.');
            }

            if ($alquiler->estado === 'CANCELADO') {
                throw new Exception('No se puede registrar pago a un alquiler cancelado.');
            }

            if ($alquiler->saldo_pendiente <= 0) {
                throw new Exception('Este alquiler ya no tiene saldo pendiente.');
            }

            $saldoPendiente = round((float) $alquiler->saldo_pendiente, 2);
            $totalAplicado = round($monto + $descuentoAplicado, 2);

            if ($monto > $saldoPendiente) {
                throw new Exception('El monto del pago no puede ser mayor al saldo pendiente.');
            }

            if ($totalAplicado > $saldoPendiente) {
                throw new Exception('La suma del pago y el descuento no puede ser mayor al saldo pendiente.');
            }

            if ($descuentoAplicado > 0 && !$observacionDescuento) {
                throw new Exception('Debe ingresar una observación para justificar el descuento aplicado.');
            }

            $pago = Pago::create([
                'alquiler_id' => $alquiler->id,
                'monto' => $monto,
                'descuento_aplicado' => $descuentoAplicado,
                'metodo_pago' => $metodoPago,
                'referencia' => $referencia,
                'observaciones' => $observaciones,
                'observacion_descuento' => $observacionDescuento,
                'usuario_id' => $usuarioId,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Aplicar descuento al alquiler
            |--------------------------------------------------------------------------
            | El descuento NO es dinero recibido.
            | Por eso:
            | - Se suma al descuento total del alquiler.
            | - Se resta del total del alquiler.
            | - Se resta del saldo pendiente junto con el pago real.
            */
            if ($descuentoAplicado > 0) {
                $alquiler->descuento = round((float) $alquiler->descuento + $descuentoAplicado, 2);
                $alquiler->total = round((float) $alquiler->total - $descuentoAplicado, 2);

                if ($alquiler->total < 0) {
                    $alquiler->total = 0;
                }
            }

            $alquiler->saldo_pendiente = round($saldoPendiente - $totalAplicado, 2);

            if ($alquiler->saldo_pendiente <= 0) {
                $alquiler->saldo_pendiente = 0;
                $alquiler->estado_pago = 'PAGADO';
            } elseif ($alquiler->saldo_pendiente < $alquiler->total) {
                $alquiler->estado_pago = 'PARCIAL';
            } else {
                $alquiler->estado_pago = 'PENDIENTE';
            }

            $alquiler->save();

            return $pago;
        });
    }
}