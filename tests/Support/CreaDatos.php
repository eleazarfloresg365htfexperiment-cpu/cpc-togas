<?php

namespace Tests\Support;

use App\Models\Cliente;
use App\Models\Producto;

/**
 * Constructores de datos para las pruebas: crean productos con su detalle
 * (toga, capa, birrete, collarín, borla) y clientes sin pasar por la web.
 */
trait CreaDatos
{
    private int $contadorDatos = 0;

    private function siguiente(): int
    {
        return ++$this->contadorDatos;
    }

    protected function cliente(array $atributos = []): Cliente
    {
        $n = $this->siguiente();

        return Cliente::create($atributos + [
            'nombres' => "Cliente{$n}",
            'apellidos' => "Prueba{$n}",
            'telefono' => '5555' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'dpi' => '2000' . str_pad((string) $n, 9, '0', STR_PAD_LEFT),
            'direccion' => 'Jalapa',
            'activo' => true,
        ]);
    }

    protected function producto(string $tipo, array $atributos = []): Producto
    {
        $n = $this->siguiente();
        $stock = $atributos['stock_total'] ?? 10;

        return Producto::create($atributos + [
            'codigo' => "{$tipo}-{$n}",
            'nombre' => ucfirst(strtolower($tipo)) . " {$n}",
            'tipo_producto' => $tipo,
            'descripcion' => null,
            'precio_alquiler' => 50,
            'stock_total' => $stock,
            'stock_disponible' => $stock,
            'stock_alquilado' => 0,
            'activo' => true,
        ]);
    }

    protected function toga(string $tipo = 'ESTANDAR', array $atributos = []): Producto
    {
        $producto = $this->producto('TOGA', $atributos);

        $producto->toga()->create([
            'tipo_toga' => $tipo,
            'talla' => 'M',
            'color' => 'Negro',
        ]);

        return $producto->load('toga');
    }

    protected function collarin(string $tipo = 'NORMAL', string $color = 'Rojo', array $atributos = []): Producto
    {
        $producto = $this->producto('COLLARIN', $atributos);

        $producto->collarin()->create([
            'tipo_collarin' => $tipo,
            'codigo_color' => 'C-' . strtoupper(substr($color, 0, 2)),
            'color' => $color,
        ]);

        return $producto->load('collarin');
    }

    protected function capa(string $carrera = 'DERECHO', string $color = 'Rojo', array $atributos = []): Producto
    {
        $producto = $this->producto('CAPA', $atributos);

        $producto->capa()->create([
            'talla' => 'M',
            'carrera' => $carrera,
            'codigo_color' => substr($carrera, 0, 3),
            'color' => $color,
        ]);

        return $producto->load('capa');
    }

    protected function birrete(string $tipo = 'ESTANDAR', array $atributos = []): Producto
    {
        $producto = $this->producto('BIRRETE', $atributos);

        $producto->birrete()->create([
            'tipo_birrete' => $tipo,
            'color' => 'Negro',
        ]);

        return $producto->load('birrete');
    }

    protected function borla(string $color = 'Rojo', array $atributos = [], string $tipo = 'NORMAL'): Producto
    {
        $producto = $this->producto('BORLA', $atributos);

        $producto->borla()->create([
            'tipo_borla' => $tipo,
            'codigo_color' => 'B-' . strtoupper(substr($color, 0, 2)),
            'color' => $color,
        ]);

        return $producto->load('borla');
    }

    /**
     * Datos del formulario "Nuevo alquiler" para una toga con su collarín.
     * $extra se mezcla encima de los datos de la toga (cantidad, capa_id...).
     */
    protected function formularioAlquiler(Cliente $cliente, Producto $toga, Producto $collarin, array $extraToga = [], array $extraForm = []): array
    {
        return array_merge([
            'cliente_id' => $cliente->id,
            'fecha_alquiler' => '2026-10-10',
            'fecha_entrega' => '2026-10-12',
            'fecha_devolucion_programada' => '2026-10-15',
            'productos' => [
                $toga->id => array_merge([
                    'seleccionado' => 1,
                    'producto_id' => $toga->id,
                    'cantidad' => 2,
                    'collarin_id' => $collarin->id,
                ], $extraToga),
            ],
        ], $extraForm);
    }
}
