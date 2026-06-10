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
        protected ReciboService $reciboService
    ) {}

        public function crearAlquiler(
        int $clienteId,
        array $productos,
        float $descuento = 0,
        ?string $fechaAlquiler = null,
        ?string $fechaEntrega = null,
        ?string $fechaDevolucionProgramada = null,
        ?string $observaciones = null,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use (
            $clienteId,
            $productos,
            $descuento,
            $fechaAlquiler,
            $fechaEntrega,
            $fechaDevolucionProgramada,
            $observaciones,
            $usuarioId
        ) {
            if (empty($productos)) {
                throw new Exception('Debe agregar al menos un producto al alquiler.');
            }

            if ($descuento < 0) {
                throw new Exception('El descuento no puede ser negativo.');
            }

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
            $productosPreparados = [];

            foreach ($productos as $item) {
                if (!isset($item['producto_id']) || !isset($item['cantidad'])) {
                    throw new Exception('Cada producto debe incluir producto_id y cantidad.');
                }

                $producto = Producto::lockForUpdate()->findOrFail($item['producto_id']);
                $cantidad = (int) $item['cantidad'];

                if ($cantidad <= 0) {
                    throw new Exception('La cantidad de cada producto debe ser mayor a cero.');
                }

                if (!$producto->activo) {
                    throw new Exception("El producto {$producto->nombre} está inactivo.");
                }

                if ($producto->stock_disponible < $cantidad) {
                    throw new Exception("No hay suficiente stock disponible para {$producto->nombre}.");
                }

                $precioUnitario = (float) $producto->precio_alquiler;
                $subtotalDetalle = $precioUnitario * $cantidad;

                $accesoriosPreparados = [];
                $subtotalAccesorios = 0;

                foreach (($item['accesorios'] ?? []) as $accesorio) {
                    if (!isset($accesorio['producto_id']) || !isset($accesorio['cantidad'])) {
                        throw new Exception('Cada accesorio debe incluir producto_id y cantidad.');
                    }

                    $productoAccesorio = Producto::lockForUpdate()->findOrFail($accesorio['producto_id']);
                    $cantidadAccesorio = (int) $accesorio['cantidad'];

                    if ($cantidadAccesorio <= 0) {
                        throw new Exception('La cantidad de cada accesorio debe ser mayor a cero.');
                    }

                    if (!$productoAccesorio->activo) {
                        throw new Exception("El accesorio {$productoAccesorio->nombre} está inactivo.");
                    }

                    if ($productoAccesorio->stock_disponible < $cantidadAccesorio) {
                        throw new Exception("No hay suficiente stock disponible para {$productoAccesorio->nombre}.");
                    }

                    $tipoCobro = $accesorio['tipo_cobro'] ?? 'INCLUIDO';
                    $precioAccesorio = (float) ($accesorio['precio_unitario'] ?? 0);
                    $totalAccesorio = $precioAccesorio * $cantidadAccesorio;

                    if ($tipoCobro === 'EXTRA') {
                        $subtotalAccesorios += $totalAccesorio;
                    }

                    $accesoriosPreparados[] = [
                        'producto' => $productoAccesorio,
                        'tipo_accesorio' => $accesorio['tipo_accesorio'],
                        'tipo_cobro' => $tipoCobro,
                        'cantidad' => $cantidadAccesorio,
                        'precio_unitario' => $precioAccesorio,
                        'total_linea' => $totalAccesorio,
                    ];
                }

                $subtotal += $subtotalDetalle + $subtotalAccesorios;

                $productosPreparados[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalDetalle,
                    'accesorios' => $accesoriosPreparados,
                ];
            }

            if ($descuento > $subtotal) {
                throw new Exception('El descuento no puede ser mayor al subtotal.');
            }

            $total = $subtotal - $descuento;

            $codigoRecibo = $this->reciboService->generarCodigoRecibo();

            $alquiler = Alquiler::create([
                'cliente_id' => $clienteId,
                'codigo_recibo' => $codigoRecibo,
                'fecha_alquiler' => $fechaAlquiler,
                'fecha_entrega' => $fechaEntrega,
                'fecha_devolucion_programada' => $fechaDevolucionProgramada,
                'estado' => 'RESERVADO',
                'estado_pago' => 'PENDIENTE',
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'saldo_pendiente' => $total,
                'observaciones' => $observaciones,
                'usuario_id' => $usuarioId,
            ]);

            foreach ($productosPreparados as $item) {
                $detalleCreado = AlquilerDetalle::create([
                    'alquiler_id' => $alquiler->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'estado' => 'PENDIENTE',
                ]);

                foreach (($item['accesorios'] ?? []) as $accesorio) {
                    AlquilerDetalleAccesorio::create([
                        'alquiler_detalle_id' => $detalleCreado->id,
                        'producto_id' => $accesorio['producto']->id,
                        'tipo_accesorio' => $accesorio['tipo_accesorio'],
                        'tipo_cobro' => $accesorio['tipo_cobro'],
                        'cantidad' => $accesorio['cantidad'],
                        'precio_unitario' => $accesorio['precio_unitario'],
                        'total_linea' => $accesorio['total_linea'],
                    ]);
                }
            }

            return $alquiler->fresh(['cliente', 'detalles.producto', 'pagos']);
        });
    }

    public function entregarAlquiler(
        int $alquilerId,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use ($alquilerId, $usuarioId) {
            $alquiler = Alquiler::with(['detalles.producto', 'detalles.accesorios.producto'])
                ->lockForUpdate()
                ->findOrFail($alquilerId);

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
            $alquiler = Alquiler::with('detalles.producto')
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
            * Devolver inventario alquilado.
            */
            foreach ($alquiler->detalles as $detalle) {
                $producto = $detalle->producto;

                if (!$producto) {
                    throw new Exception('Uno de los productos del alquiler no existe.');
                }

                $stockAnteriorDisponible = $producto->stock_disponible;
                $stockAnteriorAlquilado = $producto->stock_alquilado;

                $producto->stock_disponible += $detalle->cantidad;
                $producto->stock_alquilado -= $detalle->cantidad;

                if ($producto->stock_alquilado < 0) {
                    throw new Exception('El stock alquilado no puede quedar negativo.');
                }

                $producto->save();

                $producto->movimientos()->create([
                    'tipo' => 'ENTRADA',
                    'cantidad' => $detalle->cantidad,
                    'stock_anterior_disponible' => $stockAnteriorDisponible,
                    'stock_nuevo_disponible' => $producto->stock_disponible,
                    'stock_anterior_alquilado' => $stockAnteriorAlquilado,
                    'stock_nuevo_alquilado' => $producto->stock_alquilado,
                    'motivo' => 'Devolución de alquiler',
                    'observaciones' => 'Devolución del alquiler ' . $alquiler->codigo_recibo,
                    'usuario_id' => $usuarioId,
                ]);
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

            return $alquiler;
        });
    }
}