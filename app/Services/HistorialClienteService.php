<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\Cliente;
use Illuminate\Support\Collection;

/**
 * Historial de un cliente: sus datos, un resumen y todo lo que ha alquilado,
 * agrupado por año. Lo usan la pantalla del cliente y sus exportaciones
 * (PDF y Excel, individual o de todos los clientes).
 *
 * Solo LEE datos.
 */
class HistorialClienteService
{
    private const NOMBRES_ACCESORIO = [
        'COLLARIN' => 'Collarín',
        'CAPA' => 'Capa',
        'BIRRETE' => 'Birrete',
        'BORLA' => 'Borla',
    ];

    /**
     * @return array{
     *   cliente: Cliente,
     *   resumen: array<string, mixed>,
     *   anios: array<int, array{anio: int|string, alquileres: array, togas: int, total: float, pagado: float, saldo: float}>
     * }
     */
    public function de(Cliente $cliente): array
    {
        $alquileres = $cliente->relationLoaded('alquileres')
            ? $cliente->alquileres
            : $cliente->alquileres()->with($this->relaciones())->get();

        return $this->armar($cliente, $alquileres);
    }

    /**
     * Historiales de varios clientes con pocas consultas.
     *
     * @param  Collection<int, Cliente>  $clientes
     */
    public function deVarios(Collection $clientes): array
    {
        $clientes->load(['alquileres' => fn ($q) => $q->with($this->relaciones())]);

        return $clientes->map(fn (Cliente $cliente) => $this->armar($cliente, $cliente->alquileres))->all();
    }

    private function relaciones(): array
    {
        return ['detalles.producto.toga', 'detalles.accesorios.producto', 'pagos', 'danos'];
    }

    private function armar(Cliente $cliente, Collection $alquileres): array
    {
        $filas = $alquileres
            ->sortByDesc(fn (Alquiler $a) => [$this->fecha($a)?->toDateString() ?? '', $a->id])
            ->map(fn (Alquiler $a) => $this->fila($a))
            ->values();

        $validos = $filas->where('cancelado', false);

        $anios = $filas
            ->groupBy('anio')
            ->map(function (Collection $delAnio, $anio) {
                $validos = $delAnio->where('cancelado', false);

                return [
                    'anio' => $anio,
                    'alquileres' => $delAnio->all(),
                    'cantidad' => $validos->count(),
                    'togas' => (int) $validos->sum('togas'),
                    'total' => (float) $validos->sum('total'),
                    'pagado' => (float) $delAnio->sum('pagado'),
                    'saldo' => (float) $validos->sum('saldo'),
                ];
            })
            ->sortKeysDesc()
            ->values()
            ->all();

        $fechas = $validos->pluck('fecha')->filter();

        return [
            'cliente' => $cliente,
            'resumen' => [
                'alquileres' => $validos->count(),
                'cancelados' => $filas->where('cancelado', true)->count(),
                'togas' => (int) $validos->sum('togas'),
                'total' => (float) $validos->sum('total'),
                'pagado' => (float) $filas->sum('pagado'),
                'saldo' => (float) $validos->sum('saldo'),
                'danos' => (float) $validos->sum('danos_monto'),
                'primer_alquiler' => $fechas->min(),
                'ultimo_alquiler' => $fechas->max(),
                'anios_activos' => $validos->pluck('anio')->unique()->count(),
            ],
            'anios' => $anios,
        ];
    }

    /** Un alquiler dentro del historial. */
    private function fila(Alquiler $alquiler): array
    {
        $cancelado = $alquiler->estado === 'CANCELADO';
        $fecha = $this->fecha($alquiler);

        return [
            'id' => $alquiler->id,
            'recibo' => $alquiler->codigo_recibo,
            'anio' => $fecha?->year ?? 'Sin fecha',
            'fecha' => $fecha,
            'fecha_entrega' => $alquiler->fecha_entrega,
            'fecha_devolucion_programada' => $alquiler->fecha_devolucion_programada,
            'fecha_devolucion_real' => $alquiler->fecha_devolucion_real,
            'estado' => $alquiler->estado,
            'estado_pago' => $cancelado ? 'SIN COBRO' : $alquiler->estado_pago,
            'cancelado' => $cancelado,
            'institucion' => $alquiler->institucion_representada,
            'togas' => $alquiler->totalTogas(),
            'tallas' => $alquiler->tallasTexto(),
            'accesorios' => $this->accesorios($alquiler),
            'total' => (float) $alquiler->total,
            'pagado' => $alquiler->totalPagado(),
            'saldo' => $cancelado ? 0.0 : (float) $alquiler->saldo_pendiente,
            'danos' => $alquiler->danos->count(),
            'danos_monto' => (float) $alquiler->danos->sum('monto'),
            'observaciones' => $alquiler->observaciones,
        ];
    }

    private function fecha(Alquiler $alquiler)
    {
        return $alquiler->fecha_alquiler ?? $alquiler->created_at;
    }

    /** "Collarín×3, Birrete×3, Borla×2" */
    private function accesorios(Alquiler $alquiler): string
    {
        $cantidades = [];

        foreach ($alquiler->detalles as $detalle) {
            foreach ($detalle->accesorios as $accesorio) {
                $tipo = $accesorio->producto->tipo_producto ?? null;

                if (isset(self::NOMBRES_ACCESORIO[$tipo])) {
                    $cantidades[$tipo] = ($cantidades[$tipo] ?? 0) + (int) $accesorio->cantidad;
                }
            }
        }

        return collect(self::NOMBRES_ACCESORIO)
            ->filter(fn ($nombre, $tipo) => isset($cantidades[$tipo]))
            ->map(fn ($nombre, $tipo) => $nombre . '×' . $cantidades[$tipo])
            ->implode(', ');
    }
}
