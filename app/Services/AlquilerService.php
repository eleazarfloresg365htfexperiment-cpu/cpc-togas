<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\AlquilerDetalle;
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
        ?string $fechaEntrega = null,
        ?string $fechaDevolucionProgramada = null,
        ?string $observaciones = null,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use (
            $clienteId,
            $productos,
            $descuento,
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

            $subtotal = 0;
            $productosPreparados = [];

            foreach ($productos as $item) {
                if (!isset($item['producto_id']) || !isset($item['cantidad'])) {
                    throw new Exception('Cada producto debe incluir producto_id y cantidad.');
                }

                $producto = Producto::findOrFail($item['producto_id']);
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

                $subtotal += $subtotalDetalle;

                $productosPreparados[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalDetalle,
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
                'fecha_alquiler' => now()->toDateString(),
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
                AlquilerDetalle::create([
                    'alquiler_id' => $alquiler->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'estado' => 'PENDIENTE',
                ]);
            }

            return $alquiler->fresh(['cliente', 'detalles.producto', 'pagos']);
        });
    }

    public function entregarAlquiler(
        int $alquilerId,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use ($alquilerId, $usuarioId) {
            $alquiler = Alquiler::with(['detalles.producto'])
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
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use ($alquilerId, $usuarioId) {
            $alquiler = Alquiler::with(['detalles.producto'])
                ->lockForUpdate()
                ->findOrFail($alquilerId);

            if ($alquiler->estado === 'DEVUELTO') {
                throw new Exception('Este alquiler ya fue devuelto.');
            }

            if ($alquiler->estado === 'CANCELADO') {
                throw new Exception('No se puede devolver un alquiler cancelado.');
            }

            if ($alquiler->estado !== 'ENTREGADO') {
                throw new Exception('Solo se puede devolver un alquiler que ya fue entregado.');
            }

            foreach ($alquiler->detalles as $detalle) {
                if ($detalle->estado === 'DEVUELTO') {
                    continue;
                }

                $this->inventarioService->registrarDevolucion(
                    $detalle->producto_id,
                    $detalle->cantidad,
                    $alquiler->codigo_recibo,
                    $usuarioId,
                    'Devolución de alquiler ' . $alquiler->codigo_recibo
                );

                $detalle->estado = 'DEVUELTO';
                $detalle->save();
            }

            $alquiler->estado = 'DEVUELTO';
            $alquiler->fecha_devolucion_real = now()->toDateString();
            $alquiler->save();

            return $alquiler->fresh(['cliente', 'detalles.producto', 'pagos']);
        });
    }
}