<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Exception;

class InventarioService
{
    public function registrarAlquiler(
        int $productoId,
        int $cantidad,
        string $referencia,
        ?int $usuarioId = null,
        ?string $motivo = null
    ): MovimientoInventario {
        return DB::transaction(function () use ($productoId, $cantidad, $referencia, $usuarioId, $motivo) {
            $producto = Producto::lockForUpdate()->findOrFail($productoId);

            if ($cantidad <= 0) {
                throw new Exception('La cantidad debe ser mayor a cero.');
            }

            if (!$producto->activo) {
                throw new Exception('El producto está inactivo y no puede alquilarse.');
            }

            if ($producto->stock_disponible < $cantidad) {
                throw new Exception('No hay suficiente stock disponible para alquilar.');
            }

            $stockAnteriorDisponible = $producto->stock_disponible;
            $stockAnteriorAlquilado = $producto->stock_alquilado;

            $producto->stock_disponible -= $cantidad;
            $producto->stock_alquilado += $cantidad;
            $producto->save();

            return MovimientoInventario::create([
                'producto_id' => $producto->id,
                'tipo_movimiento' => 'ALQUILER',
                'cantidad' => $cantidad,
                'stock_anterior_disponible' => $stockAnteriorDisponible,
                'stock_nuevo_disponible' => $producto->stock_disponible,
                'stock_anterior_alquilado' => $stockAnteriorAlquilado,
                'stock_nuevo_alquilado' => $producto->stock_alquilado,
                'motivo' => $motivo ?? 'Registro de alquiler',
                'referencia' => $referencia,
                'usuario_id' => $usuarioId,
            ]);
        });
    }

    public function registrarDevolucion(
        int $productoId,
        int $cantidad,
        string $referencia,
        ?int $usuarioId = null,
        ?string $motivo = null
    ): MovimientoInventario {
        return DB::transaction(function () use ($productoId, $cantidad, $referencia, $usuarioId, $motivo) {
            $producto = Producto::lockForUpdate()->findOrFail($productoId);

            if ($cantidad <= 0) {
                throw new Exception('La cantidad debe ser mayor a cero.');
            }

            if ($producto->stock_alquilado < $cantidad) {
                throw new Exception('No se puede devolver más cantidad de la que está alquilada.');
            }

            $stockAnteriorDisponible = $producto->stock_disponible;
            $stockAnteriorAlquilado = $producto->stock_alquilado;

            $producto->stock_disponible += $cantidad;
            $producto->stock_alquilado -= $cantidad;
            $producto->save();

            return MovimientoInventario::create([
                'producto_id' => $producto->id,
                'tipo_movimiento' => 'DEVOLUCION',
                'cantidad' => $cantidad,
                'stock_anterior_disponible' => $stockAnteriorDisponible,
                'stock_nuevo_disponible' => $producto->stock_disponible,
                'stock_anterior_alquilado' => $stockAnteriorAlquilado,
                'stock_nuevo_alquilado' => $producto->stock_alquilado,
                'motivo' => $motivo ?? 'Registro de devolución',
                'referencia' => $referencia,
                'usuario_id' => $usuarioId,
            ]);
        });
    }

    public function registrarEntrada(
        int $productoId,
        int $cantidad,
        ?string $motivo = null,
        ?string $referencia = null,
        ?int $usuarioId = null
    ) {
        return DB::transaction(function () use ($productoId, $cantidad, $motivo, $referencia, $usuarioId) {
            $producto = Producto::lockForUpdate()->findOrFail($productoId);

            if ($cantidad <= 0) {
                throw new \Exception('La cantidad de entrada debe ser mayor a cero.');
            }

            $stockAnteriorDisponible = $producto->stock_disponible;
            $stockAnteriorAlquilado = $producto->stock_alquilado;

            $producto->stock_total += $cantidad;
            $producto->stock_disponible += $cantidad;
            $producto->save();

            MovimientoInventario::create([
                'producto_id' => $producto->id,
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => $cantidad,
                'stock_anterior_disponible' => $stockAnteriorDisponible,
                'stock_nuevo_disponible' => $producto->stock_disponible,
                'stock_anterior_alquilado' => $stockAnteriorAlquilado,
                'stock_nuevo_alquilado' => $producto->stock_alquilado,
                'motivo' => $motivo,
                'referencia' => $referencia,
                'usuario_id' => $usuarioId,
            ]);

            return $producto;
        });
    }

    public function registrarAjuste(
        int $productoId,
        int $nuevoStockDisponible,
        ?string $motivo = null,
        ?string $referencia = null,
        ?int $usuarioId = null
    ) {
        return DB::transaction(function () use ($productoId, $nuevoStockDisponible, $motivo, $referencia, $usuarioId) {
            $producto = Producto::lockForUpdate()->findOrFail($productoId);

            if ($nuevoStockDisponible < 0) {
                throw new \Exception('El nuevo stock disponible no puede ser negativo.');
            }

            $stockAnteriorDisponible = $producto->stock_disponible;
            $stockAnteriorAlquilado = $producto->stock_alquilado;

            $nuevoStockTotal = $nuevoStockDisponible + $producto->stock_alquilado;

            $diferencia = $nuevoStockDisponible - $stockAnteriorDisponible;

            $producto->stock_disponible = $nuevoStockDisponible;
            $producto->stock_total = $nuevoStockTotal;
            $producto->save();

            MovimientoInventario::create([
                'producto_id' => $producto->id,
                'tipo_movimiento' => 'AJUSTE',
                'cantidad' => $diferencia,
                'stock_anterior_disponible' => $stockAnteriorDisponible,
                'stock_nuevo_disponible' => $producto->stock_disponible,
                'stock_anterior_alquilado' => $stockAnteriorAlquilado,
                'stock_nuevo_alquilado' => $producto->stock_alquilado,
                'motivo' => $motivo,
                'referencia' => $referencia,
                'usuario_id' => $usuarioId,
            ]);

            return $producto;
        });
    }
}