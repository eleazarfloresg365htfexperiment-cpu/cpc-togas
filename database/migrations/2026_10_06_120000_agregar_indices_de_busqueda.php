<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices para que los listados y filtros sigan siendo rápidos cuando haya
 * muchos registros (estado del alquiler, estado de pago, fechas, tipo de
 * movimiento, tipo de producto, activo).
 *
 * Solo AGREGA índices: no cambia, mueve ni borra ningún dato. Si un índice
 * ya existe (por ejemplo, creado a mano), se salta.
 */
return new class extends Migration
{
    /** tabla => [columnas] (un índice por columna) */
    private const INDICES = [
        'alquileres' => ['estado', 'estado_pago', 'fecha_entrega', 'fecha_devolucion_programada'],
        'movimientos_inventario' => ['tipo_movimiento'],
        'productos' => ['tipo_producto', 'activo'],
        'clientes' => ['activo'],
    ];

    public function up(): void
    {
        foreach (self::INDICES as $tabla => $columnas) {
            foreach ($columnas as $columna) {
                if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla, $columna)) {
                    continue;
                }

                if (Schema::hasIndex($tabla, $this->nombre($tabla, $columna))) {
                    continue;
                }

                Schema::table($tabla, function (Blueprint $table) use ($tabla, $columna) {
                    $table->index($columna, $this->nombre($tabla, $columna));
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDICES as $tabla => $columnas) {
            foreach ($columnas as $columna) {
                if (Schema::hasTable($tabla) && Schema::hasIndex($tabla, $this->nombre($tabla, $columna))) {
                    Schema::table($tabla, function (Blueprint $table) use ($tabla, $columna) {
                        $table->dropIndex($this->nombre($tabla, $columna));
                    });
                }
            }
        }
    }

    private function nombre(string $tabla, string $columna): string
    {
        return "{$tabla}_{$columna}_index";
    }
};
