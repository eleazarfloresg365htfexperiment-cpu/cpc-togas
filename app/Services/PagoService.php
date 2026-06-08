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
        ?int $usuarioId = null
    ): Pago {
        return DB::transaction(function () use (
            $alquilerId,
            $monto,
            $metodoPago,
            $referencia,
            $observaciones,
            $usuarioId
        ) {
            $alquiler = Alquiler::lockForUpdate()->findOrFail($alquilerId);

            if ($monto <= 0) {
                throw new Exception('El monto del pago debe ser mayor a cero.');
            }

            if ($alquiler->estado === 'CANCELADO') {
                throw new Exception('No se puede registrar pago a un alquiler cancelado.');
            }

            if ($alquiler->saldo_pendiente <= 0) {
                throw new Exception('Este alquiler ya no tiene saldo pendiente.');
            }

            if ($monto > $alquiler->saldo_pendiente) {
                throw new Exception('El monto del pago no puede ser mayor al saldo pendiente.');
            }

            $pago = Pago::create([
                'alquiler_id' => $alquiler->id,
                'monto' => $monto,
                'metodo_pago' => $metodoPago,
                'referencia' => $referencia,
                'observaciones' => $observaciones,
                'usuario_id' => $usuarioId,
            ]);

            $alquiler->saldo_pendiente = $alquiler->saldo_pendiente - $monto;

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