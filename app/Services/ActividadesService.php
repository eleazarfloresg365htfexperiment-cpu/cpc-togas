<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\AlquilerFabricacion;
use App\Models\Producto;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Cálculos del módulo Actividades: agenda de entregas y devoluciones,
 * disponibilidad de togas por talla (y de accesorios) y alquileres con
 * saldo pendiente.
 *
 * Solo LEE datos: no cambia nada en la base.
 */
class ActividadesService
{
    /** Estados que ocupan inventario: apartados (antes de entregar) y entregados. */
    private const ESTADOS_QUE_OCUPAN = ['RESERVADO', 'EN_FABRICACION', 'LISTO_PARA_ENTREGA', 'ENTREGADO'];

    // ------------------------------------------------------------------
    // Agenda
    // ------------------------------------------------------------------

    /**
     * Entregas y devoluciones de cada día del rango, más lo atrasado.
     *
     * @return array{
     *   dias: array<string, array{fecha: CarbonImmutable, entregas: array, devoluciones: array}>,
     *   atrasados: array,
     *   fabricaciones: array,
     *   totales: array{entregas: int, devoluciones: int, togas_entrega: int, togas_devolucion: int}
     * }
     */
    public function agenda(CarbonImmutable $desde, CarbonImmutable $hasta, ?CarbonImmutable $hoy = null): array
    {
        $hoy = ($hoy ?? CarbonImmutable::today())->startOfDay();
        $desde = $desde->startOfDay();
        $hasta = $hasta->startOfDay();

        $alquileres = $this->consultaConDetalle()
            ->where('estado', '!=', 'CANCELADO')
            ->where(function ($q) use ($desde, $hasta) {
                $q->whereBetween('fecha_entrega', [$desde->toDateString(), $hasta->toDateString()])
                    ->orWhereBetween('fecha_devolucion_programada', [$desde->toDateString(), $hasta->toDateString()]);
            })
            ->get();

        $dias = [];

        for ($dia = $desde; $dia->lte($hasta); $dia = $dia->addDay()) {
            $dias[$dia->toDateString()] = ['fecha' => $dia, 'entregas' => [], 'devoluciones' => []];
        }

        $totales = ['entregas' => 0, 'devoluciones' => 0, 'togas_entrega' => 0, 'togas_devolucion' => 0];

        foreach ($alquileres as $alquiler) {
            $entrega = $alquiler->fecha_entrega?->toDateString();
            $devolucion = $alquiler->fecha_devolucion_programada?->toDateString();

            if ($entrega && isset($dias[$entrega])) {
                $item = $this->resumen($alquiler, $hoy, 'entrega');
                $dias[$entrega]['entregas'][] = $item;
                $totales['entregas']++;
                $totales['togas_entrega'] += $item['togas'];
            }

            if ($devolucion && isset($dias[$devolucion])) {
                $item = $this->resumen($alquiler, $hoy, 'devolucion');
                $dias[$devolucion]['devoluciones'][] = $item;
                $totales['devoluciones']++;
                $totales['togas_devolucion'] += $item['togas'];
            }
        }

        foreach ($dias as &$dia) {
            usort($dia['entregas'], fn ($a, $b) => strcmp($a['hora'] ?? '99', $b['hora'] ?? '99'));
            usort($dia['devoluciones'], fn ($a, $b) => strcmp($a['hora'] ?? '99', $b['hora'] ?? '99'));
        }
        unset($dia);

        return [
            'dias' => $dias,
            'atrasados' => $this->atrasados($hoy),
            'fabricaciones' => $this->fabricacionesPendientes(),
            'totales' => $totales,
        ];
    }

    /**
     * Lo que ya debió pasar y no pasó: entregas sin hacer y devoluciones
     * vencidas, sin importar el rango elegido.
     */
    public function atrasados(?CarbonImmutable $hoy = null): array
    {
        $hoy = ($hoy ?? CarbonImmutable::today())->startOfDay();

        $entregas = $this->consultaConDetalle()
            ->whereIn('estado', Alquiler::ESTADOS_ANTES_DE_ENTREGA)
            ->whereDate('fecha_entrega', '<', $hoy->toDateString())
            ->orderBy('fecha_entrega')
            ->get()
            ->map(fn ($a) => $this->resumen($a, $hoy, 'entrega'));

        $devoluciones = $this->consultaConDetalle()
            ->where('estado', 'ENTREGADO')
            ->whereDate('fecha_devolucion_programada', '<', $hoy->toDateString())
            ->orderBy('fecha_devolucion_programada')
            ->get()
            ->map(fn ($a) => $this->resumen($a, $hoy, 'devolucion'));

        return ['entregas' => $entregas->all(), 'devoluciones' => $devoluciones->all()];
    }

    /** Fabricaciones con unidades pendientes, la más urgente primero. */
    public function fabricacionesPendientes(): array
    {
        return AlquilerFabricacion::query()
            ->with(['alquiler.cliente', 'producto.toga', 'producto.collarin', 'producto.borla', 'producto.capa', 'producto.birrete'])
            ->where('cantidad_pendiente', '>', 0)
            ->where('estado', '!=', 'COMPLETADO')
            ->whereHas('alquiler', fn ($q) => $q->where('estado', '!=', 'CANCELADO'))
            ->get()
            ->sortBy(fn ($f) => $f->alquiler?->fecha_entrega?->toDateString() ?? '9999')
            ->map(fn ($f) => [
                'alquiler_id' => $f->alquiler_id,
                'recibo' => $f->alquiler?->codigo_recibo,
                'cliente' => $this->nombreCliente($f->alquiler),
                'fecha_entrega' => $f->alquiler?->fecha_entrega,
                'producto' => $this->nombreProducto($f->producto),
                'pendiente' => (int) $f->cantidad_pendiente,
                'responsable' => $f->responsable,
            ])
            ->values()
            ->all();
    }

    // ------------------------------------------------------------------
    // Togas por talla
    // ------------------------------------------------------------------

    /**
     * Una fila por tipo de toga y talla, con lo que hay, lo que está fuera,
     * lo apartado y lo libre para la fecha consultada.
     *
     * - Total, en bodega y fuera salen del inventario de cada producto.
     * - Apartadas: alquileres reservados, en fabricación o listos (aún no entregados).
     * - En fabricación: unidades pendientes que se sumarán al inventario.
     * - Libres para la fecha: total + en fabricación − lo ocupado ese día.
     *   Ocupado = alquileres cuyo periodo (entrega → devolución) incluye la
     *   fecha. Los entregados con devolución vencida cuentan como ocupados
     *   hasta que regresen.
     */
    public function togasPorTalla(CarbonImmutable $fecha, ?CarbonImmutable $hoy = null): array
    {
        $hoy = ($hoy ?? CarbonImmutable::today())->startOfDay();
        $fecha = $fecha->startOfDay();

        $togas = Producto::query()
            ->with('toga')
            ->where('tipo_producto', 'TOGA')
            ->where('activo', true)
            ->get();

        $filas = [];

        foreach ($togas as $toga) {
            $clave = $this->claveTalla($toga);
            $filas[$clave] ??= $this->filaVacia($toga);
            $filas[$clave]['total'] += (int) $toga->stock_total;
            $filas[$clave]['bodega'] += (int) $toga->stock_disponible;
            $filas[$clave]['fuera'] += (int) $toga->stock_alquilado;
        }

        $alquileres = $this->consultaConDetalle()
            ->whereIn('estado', self::ESTADOS_QUE_OCUPAN)
            ->get();

        foreach ($alquileres as $alquiler) {
            foreach ($alquiler->detalles as $detalle) {
                $producto = $detalle->producto;

                if (($producto->tipo_producto ?? null) !== 'TOGA') {
                    continue;
                }

                $clave = $this->claveTalla($producto);
                $filas[$clave] ??= $this->filaVacia($producto);
                $cantidad = (int) $detalle->cantidad;

                if ($alquiler->estado !== 'ENTREGADO') {
                    $filas[$clave]['apartadas'] += $cantidad;
                    $filas[$clave]['detalle_apartadas'][] = [
                        'alquiler_id' => $alquiler->id,
                        'recibo' => $alquiler->codigo_recibo,
                        'cliente' => $this->nombreCliente($alquiler),
                        'fecha_entrega' => $alquiler->fecha_entrega,
                        'cantidad' => $cantidad,
                        'estado' => $alquiler->estado,
                    ];
                } elseif ($alquiler->fecha_devolucion_programada) {
                    $vuelve = $alquiler->fecha_devolucion_programada->toDateString();
                    $filas[$clave]['devoluciones'][$vuelve] = ($filas[$clave]['devoluciones'][$vuelve] ?? 0) + $cantidad;
                }

                if ($this->ocupaEnFecha($alquiler, $fecha, $hoy)) {
                    $filas[$clave]['ocupadas_en_fecha'] += $cantidad;
                }
            }
        }

        foreach (AlquilerFabricacion::with('producto.toga')
            ->where('cantidad_pendiente', '>', 0)
            ->where('estado', '!=', 'COMPLETADO')
            ->whereHas('alquiler', fn ($q) => $q->where('estado', '!=', 'CANCELADO'))
            ->get() as $fabricacion) {
            if (($fabricacion->producto->tipo_producto ?? null) !== 'TOGA') {
                continue;
            }

            $clave = $this->claveTalla($fabricacion->producto);
            $filas[$clave] ??= $this->filaVacia($fabricacion->producto);
            $filas[$clave]['en_fabricacion'] += (int) $fabricacion->cantidad_pendiente;
        }

        foreach ($filas as &$fila) {
            $fila['libres_en_fecha'] = $fila['total'] + $fila['en_fabricacion'] - $fila['ocupadas_en_fecha'];

            ksort($fila['devoluciones']);
            $proxima = collect($fila['devoluciones'])
                ->filter(fn ($cantidad, $dia) => $dia >= $hoy->toDateString())
                ->take(1);
            $fila['proxima_devolucion'] = $proxima->isNotEmpty()
                ? ['fecha' => CarbonImmutable::parse($proxima->keys()->first()), 'cantidad' => $proxima->first()]
                : null;
            $fila['devoluciones_vencidas'] = collect($fila['devoluciones'])
                ->filter(fn ($cantidad, $dia) => $dia < $hoy->toDateString())
                ->sum();

            usort($fila['detalle_apartadas'], fn ($a, $b) => strcmp(
                $a['fecha_entrega']?->toDateString() ?? '9999',
                $b['fecha_entrega']?->toDateString() ?? '9999'
            ));
        }
        unset($fila);

        // Estándar primero; dentro de cada tipo, tallas en orden natural.
        uasort($filas, function ($a, $b) {
            return [$a['tipo'] === 'UNIVERSITARIA', $this->ordenTalla($a['talla'])]
                <=> [$b['tipo'] === 'UNIVERSITARIA', $this->ordenTalla($b['talla'])];
        });

        return array_values($filas);
    }

    /**
     * Accesorios (collarines, capas, birretes, borlas) con lo que hay, lo que
     * está fuera, lo apartado y lo libre hoy.
     */
    public function accesorios(): array
    {
        $productos = Producto::query()
            ->with(['collarin', 'capa', 'birrete', 'borla'])
            ->whereIn('tipo_producto', ['COLLARIN', 'CAPA', 'BIRRETE', 'BORLA'])
            ->where('activo', true)
            ->get()
            ->keyBy('id');

        $apartados = [];

        foreach ($this->consultaConDetalle()->whereIn('estado', Alquiler::ESTADOS_ANTES_DE_ENTREGA)->get() as $alquiler) {
            foreach ($alquiler->detalles as $detalle) {
                foreach ($detalle->accesorios as $accesorio) {
                    $apartados[$accesorio->producto_id] = ($apartados[$accesorio->producto_id] ?? 0) + (int) $accesorio->cantidad;
                }
            }
        }

        $orden = ['COLLARIN' => 1, 'CAPA' => 2, 'BIRRETE' => 3, 'BORLA' => 4];

        return $productos
            ->map(fn (Producto $p) => [
                'tipo' => $p->tipo_producto,
                'nombre' => $this->nombreProducto($p),
                'codigo' => $p->codigo,
                'total' => (int) $p->stock_total,
                'bodega' => (int) $p->stock_disponible,
                'fuera' => (int) $p->stock_alquilado,
                'apartados' => $apartados[$p->id] ?? 0,
                'libres' => (int) $p->stock_disponible - ($apartados[$p->id] ?? 0),
            ])
            ->sortBy(fn ($fila) => [$orden[$fila['tipo']] ?? 9, $fila['nombre']])
            ->groupBy('tipo')
            ->all();
    }

    // ------------------------------------------------------------------
    // Cobros
    // ------------------------------------------------------------------

    /**
     * Alquileres (no cancelados) con saldo pendiente, ordenados por fecha
     * de entrega. Recuerda: no se puede entregar sin el pago completo.
     */
    public function porCobrar(?CarbonImmutable $hoy = null): array
    {
        $hoy = ($hoy ?? CarbonImmutable::today())->startOfDay();

        return $this->consultaConDetalle()
            ->where('estado', '!=', 'CANCELADO')
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('fecha_entrega')
            ->get()
            ->map(function (Alquiler $alquiler) use ($hoy) {
                $item = $this->resumen($alquiler, $hoy, 'entrega');
                $dias = $alquiler->fecha_entrega ? $hoy->diffInDays($alquiler->fecha_entrega->copy()->startOfDay(), false) : null;

                $item['total'] = (float) $alquiler->total;
                $item['pagado'] = (float) $alquiler->total - (float) $alquiler->saldo_pendiente;
                $item['dias_para_entrega'] = $dias !== null ? (int) $dias : null;
                $item['fecha_limite_pago'] = $alquiler->fecha_limite_pago_final;
                $item['limite_vencido'] = $alquiler->fecha_limite_pago_final
                    && $alquiler->fecha_limite_pago_final->copy()->startOfDay()->lt($hoy);
                $item['urgente'] = in_array($alquiler->estado, Alquiler::ESTADOS_ANTES_DE_ENTREGA, true)
                    && $dias !== null && $dias <= 7;

                return $item;
            })
            ->all();
    }

    // ------------------------------------------------------------------
    // Ayudantes
    // ------------------------------------------------------------------

    private function consultaConDetalle()
    {
        return Alquiler::query()->with([
            'cliente',
            'detalles.producto.toga',
            'detalles.accesorios',
        ]);
    }

    /** Datos de un alquiler para listar en la agenda o en cobros. */
    private function resumen(Alquiler $alquiler, CarbonImmutable $hoy, string $momento): array
    {
        $porTalla = [];

        foreach ($alquiler->detalles as $detalle) {
            $producto = $detalle->producto;

            if (($producto->tipo_producto ?? null) !== 'TOGA') {
                continue;
            }

            $tipo = ($producto->toga->tipo_toga ?? 'ESTANDAR') === 'UNIVERSITARIA' ? 'Universitaria' : 'Estándar';
            $talla = $producto->toga->talla ?? '¿?';
            $porTalla[$tipo][$talla] = ($porTalla[$tipo][$talla] ?? 0) + (int) $detalle->cantidad;
        }

        $textoTallas = collect($porTalla)
            ->map(fn ($tallas, $tipo) => $tipo . ': ' . collect($tallas)
                ->sortKeysUsing(fn ($a, $b) => $this->ordenTalla($a) <=> $this->ordenTalla($b))
                ->map(fn ($cantidad, $talla) => "{$talla}×{$cantidad}")
                ->implode(', '))
            ->implode(' · ');

        $hora = $momento === 'entrega'
            ? ($alquiler->hora_entrega_inicio?->format('H:i') ?? $this->hora($alquiler->hora_entrega))
            : $this->hora($alquiler->hora_devolucion_programada);

        $horaFin = $momento === 'entrega' ? $alquiler->hora_entrega_fin?->format('H:i') : null;

        $hecho = $momento === 'entrega'
            ? in_array($alquiler->estado, ['ENTREGADO', 'DEVUELTO'], true)
            : $alquiler->estado === 'DEVUELTO';

        $fechaMomento = $momento === 'entrega' ? $alquiler->fecha_entrega : $alquiler->fecha_devolucion_programada;

        return [
            'id' => $alquiler->id,
            'recibo' => $alquiler->codigo_recibo,
            'cliente' => $this->nombreCliente($alquiler),
            'telefono' => $alquiler->cliente->telefono ?? null,
            'institucion' => $alquiler->institucion_representada ?: ($alquiler->cliente->institucion_representada ?? null),
            'estado' => $alquiler->estado,
            'estado_pago' => $alquiler->estado_pago,
            'saldo' => (float) $alquiler->saldo_pendiente,
            'togas' => array_sum(array_map('array_sum', $porTalla)),
            'tallas' => $textoTallas,
            'hora' => $hora,
            'hora_fin' => $horaFin,
            'fecha' => $fechaMomento,
            'hecho' => $hecho,
            'atrasado' => !$hecho && $fechaMomento && $fechaMomento->copy()->startOfDay()->lt($hoy),
        ];
    }

    private function hora($valor): ?string
    {
        if (!$valor) {
            return null;
        }

        return substr((string) ($valor instanceof \DateTimeInterface ? $valor->format('H:i') : $valor), 0, 5);
    }

    private function ocupaEnFecha(Alquiler $alquiler, CarbonImmutable $fecha, CarbonImmutable $hoy): bool
    {
        if (!$alquiler->fecha_entrega) {
            return false;
        }

        $inicio = $alquiler->fecha_entrega->copy()->startOfDay();
        $fin = $alquiler->fecha_devolucion_programada?->copy()->startOfDay() ?? $inicio;

        // Entregado y vencido: sigue ocupado hasta que regrese.
        if ($alquiler->estado === 'ENTREGADO' && $fin->lt($hoy)) {
            return true;
        }

        return $fecha->gte($inicio) && $fecha->lte($fin);
    }

    private function claveTalla(Producto $toga): string
    {
        return ($toga->toga->tipo_toga ?? 'ESTANDAR') . '|' . mb_strtoupper(trim((string) ($toga->toga->talla ?? '¿?')));
    }

    private function filaVacia(Producto $toga): array
    {
        return [
            'tipo' => ($toga->toga->tipo_toga ?? 'ESTANDAR') === 'UNIVERSITARIA' ? 'UNIVERSITARIA' : 'ESTANDAR',
            'talla' => mb_strtoupper(trim((string) ($toga->toga->talla ?? '¿?'))),
            'total' => 0,
            'bodega' => 0,
            'fuera' => 0,
            'apartadas' => 0,
            'en_fabricacion' => 0,
            'ocupadas_en_fecha' => 0,
            'libres_en_fecha' => 0,
            'devoluciones' => [],
            'detalle_apartadas' => [],
        ];
    }

    /** Orden natural de tallas: números (4, 6, 8…) y luego XS, S, M, L, XL… */
    private function ordenTalla(string $talla): array
    {
        $talla = mb_strtoupper(trim($talla));

        if (is_numeric($talla)) {
            return [0, (float) $talla];
        }

        $letras = ['XXS' => 1, 'XS' => 2, 'S' => 3, 'M' => 4, 'L' => 5, 'XL' => 6, 'XXL' => 7, 'XXXL' => 8];

        return [1, $letras[$talla] ?? 99, $talla];
    }

    private function nombreCliente(?Alquiler $alquiler): string
    {
        return trim(($alquiler->cliente->nombres ?? '') . ' ' . ($alquiler->cliente->apellidos ?? '')) ?: 'Sin cliente';
    }

    private function nombreProducto(?Producto $producto): string
    {
        if (!$producto) {
            return 'Producto';
        }

        $extra = match ($producto->tipo_producto) {
            'COLLARIN' => trim(($producto->collarin->color ?? '') . ' · ' . (($producto->collarin->tipo_collarin ?? '') === 'UNIVERSITARIO' ? 'Universitario' : 'Normal'), ' ·'),
            'BORLA' => trim(($producto->borla->color ?? '') . ' · ' . (($producto->borla->tipo_borla ?? 'NORMAL') === 'UNIVERSITARIA' ? 'Universitaria' : 'Normal'), ' ·'),
            'CAPA' => $producto->capa?->carrera ? (config("alquiler.carreras_capa.{$producto->capa->carrera}.nombre") ?? $producto->capa->carrera) : '',
            'BIRRETE' => ($producto->birrete->tipo_birrete ?? '') === 'UNIVERSITARIO' ? 'Universitario' : 'Normal',
            'TOGA' => trim(($producto->toga->talla ?? '') . ' · ' . (($producto->toga->tipo_toga ?? '') === 'UNIVERSITARIA' ? 'Universitaria' : 'Estándar'), ' ·'),
            default => '',
        };

        return $producto->nombre . ($extra !== '' ? " ({$extra})" : '');
    }
}
