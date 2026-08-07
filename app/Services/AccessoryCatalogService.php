<?php

namespace App\Services;

use App\Models\Producto;

class AccessoryCatalogService
{
    public function getAccessoryConfig(string $tipoAccesorio): array
    {
        return config('alquiler.accessory_types.' . strtoupper($tipoAccesorio), []);
    }

    public function normalizeAccessoryType(string $tipoAccesorio): string
    {
        return strtoupper(trim($tipoAccesorio));
    }

    public function isAllowedFor(string $tipoAccesorio, string $tipoPrincipal): bool
    {
        $config = $this->getAccessoryConfig($tipoAccesorio);

        return !empty($config) && in_array($tipoPrincipal, $config['allowed_for'] ?? [], true);
    }

    public function requiredAccessoriesFor(string $tipoPrincipal): array
    {
        return config('alquiler.required_accessories.' . $tipoPrincipal, []);
    }

    public function resolveAccessoryPrice(Producto $producto, string $tipoCobro, string $tipoAccesorio): float
    {
        $tipoCobro = strtoupper(trim($tipoCobro));
        $tipoAccesorio = $this->normalizeAccessoryType($tipoAccesorio);
        $config = $this->getAccessoryConfig($tipoAccesorio);

        if ($tipoCobro !== 'EXTRA' || empty($config)) {
            return 0.0;
        }

        $defaultPrice = (float) ($config['default_price'] ?? 0.0);

        if ($producto->tipo_producto === 'BIRRETE') {
            $tipoBirrete = optional($producto->birrete)->tipo_birrete;
            if ($tipoBirrete && isset($config['price_by_tipo'][$tipoBirrete])) {
                return (float) $config['price_by_tipo'][$tipoBirrete];
            }
        }

        return $defaultPrice;
    }

    public function getAllowedAccessoryTypesFor(string $tipoPrincipal): array
    {
        return array_keys(array_filter(config('alquiler.accessory_types', []), function ($config) use ($tipoPrincipal) {
            return in_array($tipoPrincipal, $config['allowed_for'] ?? [], true);
        }));
    }
}
