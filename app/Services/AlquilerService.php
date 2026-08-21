<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\AlquilerDetalle;
use App\Models\AlquilerDetalleAccesorio;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Exception;

class AlquilerService
{
    public function __construct(
        protected InventarioService $inventarioService,
        protected ReciboService $reciboService,
        protected AlquilerRulesService $rulesService,
        protected DiscountCalculator $discountCalculator,
        protected FabricacionService $fabricacionService
    ) {}

    public function crearAlquiler(
        int $clienteId,
        array $productos,
        float $descuento = 0,
        float $descuentoPorToga = 0,
        ?string $fechaAlquiler = null,
        ?string $fechaEntrega = null,
        ?string $fechaDevolucionProgramada = null,
        ?string $observaciones = null,
        ?int $usuarioId = null,
        array $fabricacionData = []
    ): Alquiler {
        return DB::transaction(function () use (
            $clienteId,
            $productos,
            $descuento,
            $descuentoPorToga,
            $fechaAlquiler,
            $fechaEntrega,
            $fechaDevolucionProgramada,
            $observaciones,
            $usuarioId,
            $fabricacionData
        ) {
            $items = $this->rulesService->prepararItems($productos);

            if (!$fechaAlquiler) {
                $fechaAlquiler = now()->toDateString();
            }

            if ($fechaEntrega && $fechaAlquiler > $fechaEntrega) {
                throw new Exception('La fecha de reserva no puede ser posterior a la fecha de entrega.');
            }

            if ($fechaEntrega && $fechaDevolucionProgramada && $fechaDevolucionProgramada < $fechaEntrega) {
                throw new Exception('La fecha de devolución programada no puede ser anterior a la fecha de entrega.');
            }

            $subtotal = 0;

            foreach ($items as $item) {
                $subtotal += $item['subtotal'] + $item['subtotal_accesorios'];
            }

            $descuentoManual = max(0, (float) $descuento);
            $descuentoPorToga = max(0, $descuentoPorToga ?? 0);

            $descuentoToga = $this->discountCalculator->calcularDescuentoPorTogas(
                $items,
                $descuentoPorToga
            );
                        
            $descuentoTotal = round($descuentoManual + $descuentoToga, 2);

            $total = $this->discountCalculator->calcularTotal(
                $subtotal,
                $descuentoManual,
                $descuentoToga
            );

            $codigoRecibo = $this->reciboService->generarCodigoRecibo();

            $tieneFabricacionPendiente = $this->hasPendingFabricacion($items);
            $fabricacionAutorizada = !empty($fabricacionData);

            if ($tieneFabricacionPendiente && !$fabricacionAutorizada) {
                throw new Exception('Hay cantidades pendientes de fabricación. Activa la autorización de fabricación para crear este alquiler.');
            }

            $estado = $tieneFabricacionPendiente ? 'EN_FABRICACION' : 'RESERVADO';

            $alquiler = Alquiler::create([
                'cliente_id' => $clienteId,
                'codigo_recibo' => $codigoRecibo,
                'fecha_alquiler' => $fechaAlquiler,
                'fecha_entrega' => $fechaEntrega,
                'fecha_devolucion_programada' => $fechaDevolucionProgramada,
                'estado' => $estado,
                'estado_pago' => 'PENDIENTE',
                'subtotal' => $subtotal,
                'descuento' => $descuentoTotal,
                'descuento_por_toga' => $descuentoPorToga,
                'descuento_manual' => $descuentoManual,
                'descuento_toga' => $descuentoToga,
                'total' => $total,
                'saldo_pendiente' => $total,
                'observaciones' => $observaciones,
                'usuario_id' => $usuarioId,
            ]);

            foreach ($items as $item) {
                $detalleCreado = AlquilerDetalle::create([
                    'alquiler_id' => $alquiler->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'estado' => 'PENDIENTE',
                ]);

                foreach (($item['accesorios'] ?? []) as $accesorio) {
                    $detalleAccesorio = AlquilerDetalleAccesorio::create([
                        'alquiler_detalle_id' => $detalleCreado->id,
                        'producto_id' => $accesorio['producto']->id,
                        'tipo_accesorio' => $accesorio['tipo_accesorio'],
                        'tipo_cobro' => $accesorio['tipo_cobro'],
                        'cantidad' => $accesorio['cantidad'],
                        'precio_unitario' => $accesorio['precio_unitario'],
                        'total_linea' => $accesorio['total_linea'],
                    ]);

                    if (!empty($accesorio['cantidad_pendiente'])) {
                        $this->fabricacionService->registrarFabricacionPendiente(
                            $alquiler->id,
                            $detalleAccesorio->id,
                            $accesorio['producto']->id,
                            $accesorio['cantidad'],
                            $accesorio['cantidad_pendiente'],
                            $fabricacionData['responsable'] ?? null,
                            $fabricacionData['motivo'] ?? null,
                            $fabricacionData['observaciones'] ?? null,
                            $fabricacionData['usuario_id'] ?? $usuarioId,
                            $fabricacionData['fecha'] ?? null
                        );
                    }
                }

                if (!empty($item['cantidad_pendiente'])) {
                    $this->fabricacionService->registrarFabricacionPendiente(
                        $alquiler->id,
                        $detalleCreado->id,
                        $item['producto']->id,
                        $item['cantidad'],
                        $item['cantidad_pendiente'],
                        $fabricacionData['responsable'] ?? null,
                        $fabricacionData['motivo'] ?? null,
                        $fabricacionData['observaciones'] ?? null,
                        $fabricacionData['usuario_id'] ?? $usuarioId,
                        $fabricacionData['fecha'] ?? null
                    );
                }
            }

            return $alquiler->fresh(['cliente', 'detalles.producto', 'pagos']);
        });
    }

    protected function hasPendingFabricacion(array $items): bool
    {
        foreach ($items as $item) {
            if (!empty($item['cantidad_pendiente'])) {
                return true;
            }

            foreach ($item['accesorios'] as $accesorio) {
                if (!empty($accesorio['cantidad_pendiente'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function entregarAlquiler(
        int $alquilerId,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use ($alquilerId, $usuarioId) {
            $alquiler = Alquiler::with(['detalles.producto', 'detalles.accesorios.producto', 'fabricaciones'])
                ->lockForUpdate()
                ->findOrFail($alquilerId);

            if ($alquiler->fabricaciones()->where('cantidad_pendiente', '>', 0)->exists()) {
                throw new Exception('No se puede entregar el alquiler mientras haya cantidades pendientes de fabricación.');
            }

            if ($alquiler->estado === 'ENTREGADO') {
                throw new Exception('Este alquiler ya fue entregado.');
            }

            if ($alquiler->estado === 'DEVUELTO') {
                throw new Exception('No se puede entregar un alquiler que ya fue devuelto.');
            }

            if ($alquiler->estado === 'CANCELADO') {
                throw new Exception('No se puede entregar un alquiler cancelado.');
            }

            if ($alquiler->detalles->isEmpty()) {
                throw new Exception('El alquiler no tiene productos agregados.');
            }

            /*
            |--------------------------------------------------------------------------
            | Validación de pago para entregar / retirar togas
            |--------------------------------------------------------------------------
            | Regla:
            | - El cliente puede reservar con pago parcial.
            | - Para retirar las togas, debe haber pagado el 100% del alquiler.
            | - La mora no entra aquí, porque se genera hasta la devolución.
            */
            $totalAlquiler = (float) $alquiler->total;
            $saldoPendiente = (float) $alquiler->saldo_pendiente;
            $montoPagado = $totalAlquiler - $saldoPendiente;

            if ($montoPagado < $totalAlquiler) {
                $faltante = $totalAlquiler - $montoPagado;

                throw new Exception(
                    'No se puede entregar el alquiler. El cliente debe completar el pago antes de retirar las togas. ' .
                    'Faltan Q' . number_format($faltante, 2) . '.'
                );
            }

            foreach ($alquiler->detalles as $detalle) {
                if ($detalle->estado === 'ENTREGADO') {
                    continue;
                }

                $this->inventarioService->registrarAlquiler(
                    $detalle->producto_id,
                    $detalle->cantidad,
                    $alquiler->codigo_recibo,
                    $usuarioId,
                    'Entrega de alquiler ' . $alquiler->codigo_recibo
                );

                foreach ($detalle->accesorios as $accesorio) {
                    $this->inventarioService->registrarAlquiler(
                        $accesorio->producto_id,
                        $accesorio->cantidad,
                        $alquiler->codigo_recibo,
                        $usuarioId,
                        'Entrega de accesorio ' . $accesorio->tipo_accesorio . ' del alquiler ' . $alquiler->codigo_recibo
                    );
                }

                $detalle->estado = 'ENTREGADO';
                $detalle->save();
            }

            $alquiler->estado = 'ENTREGADO';
            $alquiler->fecha_entrega = now()->toDateString();
            $alquiler->save();

            return $alquiler->fresh(['cliente', 'detalles.producto', 'pagos']);
        });
    }

    public function devolverAlquiler(
        int $alquilerId,
        float $descuentoMora = 0,
        ?string $observacionMora = null,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use (
            $alquilerId,
            $descuentoMora,
            $observacionMora,
            $usuarioId
        ) {
            $alquiler = Alquiler::with([
                'detalles.producto',
                'detalles.accesorios.producto',
            ])
                ->lockForUpdate()
                ->findOrFail($alquilerId);

            if ($alquiler->estado !== 'ENTREGADO') {
                throw new Exception('Solo se pueden devolver alquileres que estén entregados.');
            }

            $fechaHoraReal = now();

            /*
            * Regla de mora:
            * La mora empieza a contar desde las 9:00 AM del día siguiente
            * a la fecha de devolución programada.
            *
            * Ejemplo:
            * Fecha devolución programada: 10/06/2026
            * Inicio de mora: 11/06/2026 09:00 AM
            */
            $diasMora = 0;
            $montoMoraCalculado = 0;

            if ($alquiler->fecha_devolucion_programada) {
                $inicioMora = $alquiler->fecha_devolucion_programada
                    ->copy()
                    ->addDay()
                    ->setTime(9, 0, 0);

                if ($fechaHoraReal->greaterThanOrEqualTo($inicioMora)) {
                    /*
                    * Regla:
                    * Si ya llegó o pasó el inicio de mora, ya cuenta como 1 día.
                    * El siguiente día de mora se suma hasta completar otras 24 horas.
                    *
                    * Ejemplo:
                    * Inicio mora: 11/06/2026 09:00 AM
                    * 11/06/2026 09:00 AM a 12/06/2026 08:59 AM = 1 día = Q50
                    * 12/06/2026 09:00 AM a 13/06/2026 08:59 AM = 2 días = Q100
                    */
                    $segundosRetraso = (int) floor($inicioMora->diffInSeconds($fechaHoraReal, true));

                    $diasMora = intdiv($segundosRetraso, 86400) + 1;
                    $montoMoraCalculado = $diasMora * 50;
                }
            }

            $descuentoMora = max($descuentoMora, 0);

            if ($descuentoMora > $montoMoraCalculado) {
                $descuentoMora = $montoMoraCalculado;
            }

            $montoMoraFinal = max($montoMoraCalculado - $descuentoMora, 0);

            /*
            * Devolver inventario alquilado: toga principal Y accesorios
            * (collarín, birrete, borla, capa), igual que entregarAlquiler()
            * hace con registrarAlquiler() para ambos.
            */
            foreach ($alquiler->detalles as $detalle) {
                if (!$detalle->producto) {
                    throw new Exception('Uno de los productos del alquiler no existe.');
                }

                $this->inventarioService->registrarDevolucion(
                    $detalle->producto_id,
                    $detalle->cantidad,
                    $alquiler->codigo_recibo,
                    $usuarioId,
                    'Devolución de alquiler ' . $alquiler->codigo_recibo
                );

                foreach ($detalle->accesorios as $accesorio) {
                    if (!$accesorio->producto) {
                        throw new Exception('Uno de los accesorios del alquiler no existe.');
                    }

                    $this->inventarioService->registrarDevolucion(
                        $accesorio->producto_id,
                        $accesorio->cantidad,
                        $alquiler->codigo_recibo,
                        $usuarioId,
                        'Devolución de accesorio ' . $accesorio->tipo_accesorio . ' del alquiler ' . $alquiler->codigo_recibo
                    );
                }
            }

            /*
            * Guardar devolución y mora.
            */
            $alquiler->estado = 'DEVUELTO';
            $alquiler->fecha_devolucion_real = $fechaHoraReal->toDateString();
            $alquiler->fecha_hora_devolucion_real = $fechaHoraReal;

            $alquiler->dias_mora = $diasMora;
            $alquiler->monto_mora_calculado = $montoMoraCalculado;
            $alquiler->descuento_mora = $descuentoMora;
            $alquiler->monto_mora = $montoMoraFinal;
            $alquiler->observacion_mora = $observacionMora;

            /*
            * La mora final se suma como cargo adicional.
            * Después puede pagarse desde el flujo normal de pagos.
            */
            if ($montoMoraFinal > 0) {
                $alquiler->total = $alquiler->total + $montoMoraFinal;
                $alquiler->saldo_pendiente = $alquiler->saldo_pendiente + $montoMoraFinal;

                if ($alquiler->saldo_pendiente > 0 && $alquiler->estado_pago === 'PAGADO') {
                    $alquiler->estado_pago = 'PARCIAL';
                }
            }

            $alquiler->save();

            return $alquiler->fresh();
        });
    }
}