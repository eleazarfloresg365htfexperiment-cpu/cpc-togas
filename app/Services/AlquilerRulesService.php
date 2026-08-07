<?php

namespace App\Services;

use App\Models\Producto;
use Exception;

class AlquilerRulesService
{
    public function __construct(protected ?AccessoryCatalogService $accessoryCatalogService = null)
    {
        if (!$this->accessoryCatalogService) {
            $this->accessoryCatalogService = new AccessoryCatalogService();
        }
    }

    public function prepararItems(array $productos): array
    {
        if (empty($productos)) {
            throw new Exception('Debe agregar al menos un producto al alquiler.');
        }

        $items = [];

        foreach ($productos as $index => $item) {
            $producto = $this->obtenerProducto($item['producto_id'] ?? null);
            $cantidad = (int) ($item['cantidad'] ?? 0);

            $this->validarProductoPrincipal($producto, $cantidad, $index + 1);

            $cantidadDisponible = max(0, $producto->stock_disponible);
            $cantidadPendiente = max(0, $cantidad - $cantidadDisponible);

            $accesorios = $this->prepararAccesorios(
                $item['accesorios'] ?? [],
                $producto,
                $cantidad,
                $index + 1
            );

            $this->validarAccesoriosRequeridos($producto, $accesorios, $index + 1);

            $subtotal = (float) $producto->precio_alquiler * $cantidad;
            $subtotalAccesorios = array_sum(array_column($accesorios, 'total_linea'));

            $items[] = [
                'producto' => $producto,
                'cantidad' => $cantidad,
                'cantidad_disponible' => $cantidadDisponible,
                'cantidad_pendiente' => $cantidadPendiente,
                'precio_unitario' => (float) $producto->precio_alquiler,
                'subtotal' => $subtotal,
                'accesorios' => $accesorios,
                'subtotal_accesorios' => $subtotalAccesorios,
            ];
        }

        return $items;
    }

    protected function validarProductoPrincipal(Producto $producto, int $cantidad, int $numeroProducto): void
    {
        if ($cantidad <= 0) {
            throw new Exception("La cantidad del producto principal #{$numeroProducto} debe ser mayor a cero.");
        }

        if (!$producto->activo) {
            throw new Exception("El producto \"{$producto->nombre}\" está inactivo.");
        }
    }

    protected function prepararAccesorios(array $accesorios, Producto $productoPrincipal, int $cantidadPrincipal, int $numeroProducto): array
    {
        $resultado = [];
        $birreteIncluido = $this->hasIncludedBirrete($accesorios);
        $tipoPrincipal = $this->tipoProductoPrincipal($productoPrincipal);

        foreach ($accesorios as $accesorioIndex => $accesorio) {
            $productoAccesorio = $this->obtenerProducto($accesorio['producto_id'] ?? null);
            $cantidadAccesorio = (int) ($accesorio['cantidad'] ?? 0);

            $this->validarAccesorio($productoAccesorio, $cantidadAccesorio, $numeroProducto, $accesorioIndex + 1);

            $tipoCobro = strtoupper($accesorio['tipo_cobro'] ?? 'INCLUIDO');
            $tipoAccesorio = $this->accessoryCatalogService->normalizeAccessoryType($accesorio['tipo_accesorio'] ?? $productoAccesorio->tipo_producto);

            if (!$this->permiteAccesorio($productoPrincipal, $tipoAccesorio)) {
                throw new Exception("El accesorio \"{$productoAccesorio->nombre}\" no es válido para el producto principal \"{$productoPrincipal->nombre}\".");
            }

            if ($tipoAccesorio === 'BORLA' && $tipoCobro === 'INCLUIDO' && !$birreteIncluido) {
                throw new Exception("La borla incluida solo puede agregarse cuando el birrete también está incluido.");
            }

            if (
                $tipoAccesorio === 'BIRRETE' &&
                $tipoCobro === 'INCLUIDO' &&
                $productoPrincipal->tipo_producto === 'TOGA' &&
                optional($productoPrincipal->toga)->tipo_toga === 'UNIVERSITARIA' &&
                optional($productoAccesorio->birrete)->tipo_birrete !== 'UNIVERSITARIO'
            ) {
                throw new Exception("Para una toga universitaria, el birrete incluido debe ser de tipo UNIVERSITARIO.");
            }

            if (
                $tipoPrincipal === 'TOGA' &&
                $tipoAccesorio === 'BIRRETE' &&
                $tipoCobro === 'INCLUIDO' &&
                optional($productoAccesorio->birrete)->tipo_birrete === 'UNIVERSITARIO'
            ) {
                throw new Exception("La toga estándar no admite birrete universitario incluido.");
            }

            if (
                $tipoPrincipal === 'TOGA' &&
                $tipoAccesorio === 'COLLARIN' &&
                optional($productoPrincipal->toga)->tipo_toga === 'ESTANDAR' &&
                optional($productoAccesorio->collarin)->tipo_collarin !== 'NORMAL'
            ) {
                throw new Exception("La toga estándar requiere un collarín normal.");
            }

            if (
                $tipoPrincipal === 'TOGA' &&
                $tipoAccesorio === 'BORLA' &&
                $tipoCobro === 'INCLUIDO' &&
                optional($productoPrincipal->toga)->tipo_toga === 'ESTANDAR' &&
                $birreteIncluido &&
                $this->hasIncludedCollarin($accesorios) &&
                $this->getIncludedCollarinColor($accesorios) !== null &&
                optional($productoAccesorio->borla)->color !== $this->getIncludedCollarinColor($accesorios)
            ) {
                throw new Exception("La borla incluida para una toga estándar debe coincidir con el color del collarín incluido.");
            }

            $cantidadDisponible = max(0, $productoAccesorio->stock_disponible);
            $cantidadPendiente = max(0, $cantidadAccesorio - $cantidadDisponible);

            $precioUnitario = isset($accesorio['precio_unitario'])
                ? (float) $accesorio['precio_unitario']
                : $this->accessoryCatalogService->resolveAccessoryPrice($productoAccesorio, $tipoCobro, $tipoAccesorio);

            $totalLinea = $precioUnitario * $cantidadAccesorio;

            $resultado[] = [
                'producto' => $productoAccesorio,
                'tipo_accesorio' => $tipoAccesorio,
                'tipo_cobro' => $tipoCobro,
                'cantidad' => $cantidadAccesorio,
                'cantidad_disponible' => $cantidadDisponible,
                'cantidad_pendiente' => $cantidadPendiente,
                'precio_unitario' => $precioUnitario,
                'total_linea' => $totalLinea,
            ];
        }

        return $resultado;
    }

    protected function hasIncludedBirrete(array $accesorios): bool
    {
        foreach ($accesorios as $accesorio) {
            $tipoAccesorio = strtoupper($accesorio['tipo_accesorio'] ?? '');
            $tipoCobro = strtoupper($accesorio['tipo_cobro'] ?? 'INCLUIDO');

            if ($tipoAccesorio === 'BIRRETE' && $tipoCobro === 'INCLUIDO') {
                return true;
            }
        }

        return false;
    }

    protected function validarAccesorio(Producto $producto, int $cantidad, int $numeroProducto, int $numeroAccesorio): void
    {
        if ($cantidad <= 0) {
            throw new Exception("La cantidad del accesorio #{$numeroAccesorio} en el producto #{$numeroProducto} debe ser mayor a cero.");
        }

        if (!$producto->activo) {
            throw new Exception("El accesorio \"{$producto->nombre}\" está inactivo.");
        }
    }

    protected function permiteAccesorio(Producto $productoPrincipal, string $tipoAccesorio): bool
    {
        $tipoPrincipal = $this->tipoProductoPrincipal($productoPrincipal);

        return $this->accessoryCatalogService->isAllowedFor($tipoAccesorio, $tipoPrincipal);
    }

    protected function tipoProductoPrincipal(Producto $producto): string
    {
        if ($producto->tipo_producto === 'TOGA' && optional($producto->toga)->tipo_toga === 'UNIVERSITARIA') {
            return 'TOGA_UNIVERSITARIA';
        }

        return $producto->tipo_producto;
    }

    protected function getIncludedCollarinColor(array $accesorios): ?string
    {
        foreach ($accesorios as $accesorio) {
            $tipoAccesorio = strtoupper($accesorio['tipo_accesorio'] ?? '');
            $tipoCobro = strtoupper($accesorio['tipo_cobro'] ?? 'INCLUIDO');

            if ($tipoAccesorio === 'COLLARIN' && $tipoCobro === 'INCLUIDO') {
                $producto = $this->obtenerProducto($accesorio['producto_id'] ?? null);
                return optional($producto->collarin)->color;
            }
        }

        return null;
    }

    protected function hasIncludedCollarin(array $accesorios): bool
    {
        foreach ($accesorios as $accesorio) {
            $tipoAccesorio = strtoupper($accesorio['tipo_accesorio'] ?? '');
            $tipoCobro = strtoupper($accesorio['tipo_cobro'] ?? 'INCLUIDO');

            if ($tipoAccesorio === 'COLLARIN' && $tipoCobro === 'INCLUIDO') {
                return true;
            }
        }

        return false;
    }

    protected function validarAccesoriosRequeridos(Producto $productoPrincipal, array $accesorios, int $numeroProducto): void
    {
        $tipoPrincipal = $this->tipoProductoPrincipal($productoPrincipal);
        $requeridos = $this->accessoryCatalogService->requiredAccessoriesFor($tipoPrincipal);

        if (empty($requeridos)) {
            return;
        }

        $tiposExistentes = array_column($accesorios, 'tipo_accesorio');

        foreach ($requeridos as $tipoRequerido) {
            if (!in_array($tipoRequerido, $tiposExistentes, true)) {
                throw new Exception("El producto principal #{$numeroProducto} requiere un accesorio del tipo \"{$tipoRequerido}\".");
            }
        }
    }

    protected function obtenerProducto(?int $productoId): Producto
    {
        return Producto::lockForUpdate()->findOrFail($productoId);
    }
}
