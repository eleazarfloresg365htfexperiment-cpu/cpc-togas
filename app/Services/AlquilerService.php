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
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use ($alquilerId, $usuarioId) {
            $alquiler = Alquiler::with(['detalles.producto', 'detalles.accesorios.producto'])
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

                foreach ($detalle->accesorios as $accesorio) {
                    $this->inventarioService->registrarDevolucion(
                        $accesorio->producto_id,
                        $accesorio->cantidad,
                        $alquiler->codigo_recibo,
                        $usuarioId,
                        'Devolución de accesorio ' . $accesorio->tipo_accesorio . ' del alquiler ' . $alquiler->codigo_recibo
                    );
                }

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