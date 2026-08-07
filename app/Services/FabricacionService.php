<?php

namespace App\Services;

use App\Models\AlquilerFabricacion;
use App\Models\Alquiler;
use Illuminate\Support\Facades\DB;
use Exception;

class FabricacionService
{
    public function __construct(
        protected InventarioService $inventarioService
    ) {}

    public function registrarFabricacionPendiente(
        int $alquilerId,
        int $alquilerDetalleId,
        int $productoId,
        int $cantidadSolicitada,
        int $cantidadPendiente,
        ?string $responsable = null,
        ?string $motivo = null,
        ?string $observaciones = null,
        ?int $usuarioId = null,
        ?string $fecha = null
    ): AlquilerFabricacion {
        return DB::transaction(function () use (
            $alquilerId,
            $alquilerDetalleId,
            $productoId,
            $cantidadSolicitada,
            $cantidadPendiente,
            $responsable,
            $motivo,
            $observaciones,
            $usuarioId,
            $fecha
        ) {
            if ($cantidadSolicitada <= 0 || $cantidadPendiente <= 0) {
                throw new Exception('La cantidad de fabricación debe ser mayor a cero.');
            }

            return AlquilerFabricacion::create([
                'alquiler_id' => $alquilerId,
                'alquiler_detalle_id' => $alquilerDetalleId,
                'producto_id' => $productoId,
                'cantidad_solicitada' => $cantidadSolicitada,
                'cantidad_pendiente' => $cantidadPendiente,
                'responsable' => $responsable,
                'motivo' => $motivo,
                'observaciones' => $observaciones,
                'fecha' => $fecha ?? now()->toDateString(),
                'estado' => 'AUTORIZADO',
                'usuario_id' => $usuarioId,
            ]);
        });
    }

    public function completarFabricacion(
        int $fabricacionId,
        int $cantidadCompletada,
        ?string $observaciones = null,
        ?int $usuarioId = null
    ): AlquilerFabricacion {
        return DB::transaction(function () use ($fabricacionId, $cantidadCompletada, $observaciones, $usuarioId) {
            $fabricacion = AlquilerFabricacion::lockForUpdate()->findOrFail($fabricacionId);

            if ($fabricacion->estado === 'COMPLETADO') {
                throw new Exception('Esta fabricación ya se encuentra completada.');
            }

            if ($cantidadCompletada <= 0) {
                throw new Exception('La cantidad completada debe ser mayor a cero.');
            }

            if ($cantidadCompletada > $fabricacion->cantidad_pendiente) {
                throw new Exception('La cantidad completada no puede ser mayor a la cantidad pendiente.');
            }

            $this->inventarioService->registrarEntrada(
                $fabricacion->producto_id,
                $cantidadCompletada,
                'Fabricación completada',
                'FABRICACION',
                $usuarioId
            );

            $fabricacion->cantidad_pendiente -= $cantidadCompletada;
            $fabricacion->estado = $fabricacion->cantidad_pendiente > 0 ? 'EN_PROGRESO' : 'COMPLETADO';

            if ($observaciones) {
                $fabricacion->observaciones = trim(($fabricacion->observaciones ?? '') . ' ' . $observaciones);
            }

            $fabricacion->save();

            if ($fabricacion->cantidad_pendiente === 0) {
                $alquiler = $fabricacion->alquiler;

                if (!$alquiler->fabricaciones()->where('cantidad_pendiente', '>', 0)->exists()) {
                    $alquiler->estado = 'LISTO_PARA_ENTREGA';
                    $alquiler->save();
                }
            }

            return $fabricacion;
        });
    }
}
