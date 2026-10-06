<?php

namespace App\Services;

use App\Models\AlquilerFabricacion;
use App\Models\Alquiler;
use App\Models\AlquilerHistorial;
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

    /**
     * Registra lo que ya se fabricó de un pendiente.
     * Lo fabricado entra al inventario (stock total y disponible) y, cuando
     * ya no queda nada pendiente en el alquiler, este pasa a LISTO_PARA_ENTREGA.
     */
    public function completarFabricacion(
        int $fabricacionId,
        int $cantidadCompletada,
        ?string $observaciones = null,
        ?int $usuarioId = null,
        ?int $alquilerId = null,
        ?string $responsable = null
    ): AlquilerFabricacion {
        return DB::transaction(function () use ($fabricacionId, $cantidadCompletada, $observaciones, $usuarioId, $alquilerId, $responsable) {
            $fabricacion = AlquilerFabricacion::with('producto')->lockForUpdate()->findOrFail($fabricacionId);
            $alquiler = Alquiler::lockForUpdate()->findOrFail($fabricacion->alquiler_id);

            if ($alquilerId !== null && $fabricacion->alquiler_id !== $alquilerId) {
                throw new Exception('Esta fabricación no pertenece al alquiler indicado.');
            }

            if ($fabricacion->estado === 'COMPLETADO' || $fabricacion->cantidad_pendiente <= 0) {
                throw new Exception('Esta fabricación ya se encuentra completada.');
            }

            if ($fabricacion->estado === 'CANCELADO' || $alquiler->estado === 'CANCELADO') {
                throw new Exception('No se puede completar la fabricación de un alquiler cancelado.');
            }

            if ($alquiler->estado !== 'EN_FABRICACION') {
                throw new Exception('Solo se puede registrar fabricación en alquileres EN FABRICACIÓN (estado actual: ' . $alquiler->estado . ').');
            }

            if ($cantidadCompletada <= 0) {
                throw new Exception('La cantidad completada debe ser mayor a cero.');
            }

            if ($cantidadCompletada > $fabricacion->cantidad_pendiente) {
                throw new Exception('La cantidad completada no puede ser mayor a la cantidad pendiente (' . $fabricacion->cantidad_pendiente . ').');
            }

            $this->inventarioService->registrarEntrada(
                $fabricacion->producto_id,
                $cantidadCompletada,
                'Fabricación completada para alquiler ' . $alquiler->codigo_recibo,
                $alquiler->codigo_recibo,
                $usuarioId
            );

            $fabricacion->cantidad_pendiente -= $cantidadCompletada;
            $fabricacion->estado = $fabricacion->cantidad_pendiente > 0 ? 'EN_PROGRESO' : 'COMPLETADO';

            if ($observaciones) {
                $fabricacion->observaciones = trim(($fabricacion->observaciones ?? '') . ' ' . trim($observaciones));
            }

            $fabricacion->save();

            $quedaPendiente = $alquiler->fabricaciones()
                ->where('cantidad_pendiente', '>', 0)
                ->where('estado', '!=', 'CANCELADO')
                ->exists();

            if (!$quedaPendiente) {
                $alquiler->estado = 'LISTO_PARA_ENTREGA';
                $alquiler->save();
            }

            AlquilerHistorial::create([
                'alquiler_id' => $alquiler->id,
                'accion' => 'FABRICACION',
                'campo' => null,
                'valor_anterior' => null,
                'valor_nuevo' => 'Fabricado: ' . $cantidadCompletada . ' x ' . ($fabricacion->producto->nombre ?? 'Producto') .
                    ($fabricacion->cantidad_pendiente > 0
                        ? ' (faltan ' . $fabricacion->cantidad_pendiente . ')'
                        : ' (completo)') .
                    (!$quedaPendiente ? ' — alquiler listo para entrega' : ''),
                'motivo' => $observaciones ? trim($observaciones) : null,
                'responsable' => $responsable ? trim($responsable) : null,
                'usuario_id' => $usuarioId,
            ]);

            return $fabricacion;
        });
    }
}
