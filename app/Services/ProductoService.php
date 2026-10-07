<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Alta y edición de productos junto con su detalle según el tipo
 * (toga, capa, birrete, collarín o borla).
 */
class ProductoService
{
    /**
     * Crea el producto, su detalle y el movimiento de entrada inicial.
     *
     * @param array $datos Datos ya validados del formulario.
     */
    public function crear(array $datos, ?int $usuarioId = null): Producto
    {
        return DB::transaction(function () use ($datos, $usuarioId) {
            $producto = Producto::create([
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'tipo_producto' => $datos['tipo_producto'],
                'descripcion' => $datos['descripcion'] ?? null,
                'precio_alquiler' => $datos['precio_alquiler'],
                'stock_total' => $datos['stock_total'],
                'stock_disponible' => $datos['stock_total'],
                'stock_alquilado' => 0,
                'activo' => $datos['activo'],
            ]);

            [$relacion, $detalle] = $this->detalleNuevo($producto->tipo_producto, $datos);

            $this->guardarDetalle($producto, $relacion, $detalle);

            if ($producto->stock_total > 0) {
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $producto->stock_total,
                    'stock_anterior_disponible' => 0,
                    'stock_nuevo_disponible' => $producto->stock_disponible,
                    'stock_anterior_alquilado' => 0,
                    'stock_nuevo_alquilado' => 0,
                    'motivo' => 'Registro inicial de producto',
                    'referencia' => 'Producto nuevo',
                    'usuario_id' => $usuarioId,
                ]);
            }

            return $producto;
        });
    }

    /**
     * Actualiza datos generales, stock y detalle de un producto.
     *
     * Los campos que no llegan en $datos conservan su valor actual.
     *
     * @throws DomainException si el nuevo stock contradice lo ya alquilado.
     */
    public function actualizar(Producto $producto, array $datos): Producto
    {
        $producto->loadMissing(['toga', 'capa', 'birrete', 'collarin', 'borla']);

        return DB::transaction(function () use ($producto, $datos) {
            $nuevoStockTotal = (int) $datos['stock_total'];

            if ($nuevoStockTotal < $producto->stock_alquilado) {
                throw new DomainException(
                    'El stock total no puede ser menor que el stock actualmente alquilado.'
                );
            }

            $nuevoStockDisponible = $producto->stock_disponible + ($nuevoStockTotal - $producto->stock_total);

            if ($nuevoStockDisponible < 0) {
                throw new DomainException('El stock disponible no puede quedar negativo.');
            }

            $producto->update([
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'precio_alquiler' => $datos['precio_alquiler'],
                'stock_total' => $nuevoStockTotal,
                'stock_disponible' => $nuevoStockDisponible,
                'activo' => $datos['activo'],
            ]);

            $detalle = $this->detalleActualizado($producto, $datos);

            if ($detalle !== null) {
                $this->guardarDetalle($producto, ...$detalle);
            }

            return $producto;
        });
    }

    /**
     * Tipo de una borla (NORMAL o UNIVERSITARIA). Si no se indica, se deduce
     * del color cuando solo existe en un tipo (Dorado → NORMAL, Celeste →
     * UNIVERSITARIA); si el color existe en los dos (Rojo, Verde), NORMAL.
     */
    public function tipoBorla(?string $tipo, ?string $color): string
    {
        $porTipo = config('alquiler.colores_borla_por_tipo', []);

        if ($tipo !== null && isset($porTipo[$tipo])) {
            return $tipo;
        }

        $tipos = array_keys(array_filter($porTipo, fn (array $colores) => isset($colores[(string) $color])));

        return count($tipos) === 1 ? $tipos[0] : 'NORMAL';
    }

    /**
     * Código de color de una borla según su tipo y color
     * (config/alquiler.php → colores_borla_por_tipo), o null si no hay.
     */
    public function codigoBorla(?string $tipo, ?string $color): ?string
    {
        if ($color === null || $color === '') {
            return null;
        }

        return config('alquiler.colores_borla_por_tipo', [])[$this->tipoBorla($tipo, $color)][$color] ?? null;
    }

    /**
     * Detalle de un producto nuevo: [relación, datos].
     */
    private function detalleNuevo(string $tipo, array $d): array
    {
        return match ($tipo) {
            'TOGA' => ['toga', [
                'tipo_toga' => $d['tipo_toga'] ?? 'ESTANDAR',
                'talla' => $d['talla_toga'] ?? 'No especificado',
                'color' => $d['color_toga'] ?? 'No especificado',
                'observaciones' => $d['observaciones_toga'] ?? null,
            ]],
            'CAPA' => ['capa', [
                'talla' => $d['talla_capa'] ?? 'No especificado',
                'carrera' => $d['carrera_capa'] ?? null,
                'codigo_color' => $d['codigo_color_capa'] ?? null,
                'color' => $d['color_capa'] ?? 'No especificado',
                'observaciones' => $d['observaciones_capa'] ?? null,
            ]],
            'BIRRETE' => ['birrete', [
                'tipo_birrete' => $d['tipo_birrete'] ?? 'NORMAL',
                'color' => $d['color_birrete'] ?? null,
                'observaciones' => $d['observaciones_birrete'] ?? null,
            ]],
            'COLLARIN' => ['collarin', [
                'tipo_collarin' => $d['tipo_collarin'] ?? null,
                'codigo_color' => $d['codigo_color_collarin'] ?? null,
                'color' => $d['color_collarin'] ?? null,
                'tamano' => null,
            ]],
            'BORLA' => ['borla', [
                'tipo_borla' => $this->tipoBorla($d['tipo_borla'] ?? null, $d['borla_color'] ?? null),
                'codigo_color' => $d['borla_codigo_color'] ?? null,
                'color' => $d['borla_color'] ?? null,
                'observaciones' => $d['borla_observaciones'] ?? null,
            ]],
        };
    }

    /**
     * Detalle de un producto existente: [relación, datos] o null.
     * Lo que no viene en $d se conserva tal como está guardado.
     */
    private function detalleActualizado(Producto $producto, array $d): ?array
    {
        $valor = fn (string $clave, $actual) => array_key_exists($clave, $d) ? $d[$clave] : $actual;

        switch ($producto->tipo_producto) {
            case 'TOGA':
                $t = $producto->toga;

                return ['toga', [
                    'tipo_toga' => $valor('tipo_toga', $t->tipo_toga ?? 'ESTANDAR'),
                    'talla' => $valor('talla_toga', $t->talla ?? null),
                    'color' => $valor('color_toga', $t->color ?? null),
                    'observaciones' => $valor('observaciones_toga', $t->observaciones ?? null),
                ]];

            case 'CAPA':
                $c = $producto->capa;

                return ['capa', [
                    'talla' => $valor('talla_capa', $c->talla ?? null),
                    'carrera' => $valor('carrera_capa', $c->carrera ?? null),
                    'codigo_color' => $valor('codigo_color_capa', $c->codigo_color ?? null),
                    'color' => $valor('color_capa', $c->color ?? null),
                    'observaciones' => $valor('observaciones_capa', $c->observaciones ?? null),
                ]];

            case 'BIRRETE':
                $b = $producto->birrete;

                return ['birrete', [
                    'tipo_birrete' => $valor('tipo_birrete', $b->tipo_birrete ?? 'NORMAL'),
                    'color' => $valor('color_birrete', $b->color ?? null),
                    'observaciones' => $valor('observaciones_birrete', $b->observaciones ?? null),
                ]];

            case 'COLLARIN':
                $c = $producto->collarin;

                return ['collarin', [
                    'tipo_collarin' => $valor('tipo_collarin', $c->tipo_collarin ?? 'NORMAL'),
                    'codigo_color' => $valor('codigo_color_collarin', $c->codigo_color ?? null),
                    'color' => $valor('color_collarin', $c->color ?? null),
                    'tamano' => null,
                ]];

            case 'BORLA':
                $b = $producto->borla;

                $color = $valor('borla_color', $b->color ?? null);
                $tipo = $this->tipoBorla($valor('tipo_borla', $b->tipo_borla ?? null), $color);
                $codigo = $valor('borla_codigo_color', $b->codigo_color ?? null);

                // Si el código llega vacío, se completa con el del tipo y color.
                if (blank($codigo)) {
                    $codigo = $this->codigoBorla($tipo, $color);
                }

                return ['borla', [
                    'tipo_borla' => $tipo,
                    'codigo_color' => $codigo,
                    'color' => $color,
                    'observaciones' => $valor('borla_observaciones', $b->observaciones ?? null),
                ]];
        }

        return null;
    }

    private function guardarDetalle(Producto $producto, string $relacion, array $datos): void
    {
        $producto->{$relacion}()->updateOrCreate(
            ['producto_id' => $producto->id],
            $datos
        );
    }
}
