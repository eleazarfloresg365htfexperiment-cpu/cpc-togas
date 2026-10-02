<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta de compromiso - {{ $alquiler->codigo_recibo }}</title>

    <style>
        @page {
            size: letter;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #e5e7eb;
        }

        .toolbar {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 99999;
            display: flex;
            gap: 8px;
            font-family: Arial, sans-serif;
        }

        .toolbar a,
        .toolbar button {
            border: 1px solid #0d6efd;
            background: #0d6efd;
            color: #fff;
            padding: 8px 14px;
            border-radius: 999px;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        .toolbar a {
            background: #fff;
            color: #0d6efd;
        }

        .toolbar .badge-tipo {
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
        }

        .sheet {
            width: 8.5in;
            height: 11in;
            margin: 0 auto;
            position: relative;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center;
            page-break-after: always;
        }

        /* Carta de compromiso: togas normales (ESTANDAR) */
        .normal-p1 {
            background-image: url("{{ asset('plantillas/carta-compromiso-normal-p1.png') }}");
        }

        .normal-p2 {
            background-image: url("{{ asset('plantillas/carta-compromiso-normal-p2.png') }}");
        }

        /* Carta de compromiso: togas universitarias */
        .universitaria-p1 {
            background-image: url("{{ asset('plantillas/carta-compromiso-universitaria-p1.png') }}");
        }

        .universitaria-p2 {
            background-image: url("{{ asset('plantillas/carta-compromiso-universitaria-p2.png') }}");
        }

        .campo {
            position: absolute;
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 13px;
            color: #000;
            white-space: nowrap;
            overflow: hidden;
        }

        .campo-center {
            text-align: center;
        }

        .campo-bold {
            font-weight: bold;
        }

        .campo-sm {
            font-size: 11px;
            line-height: 12px;
        }

        .campo-check {
            position: absolute;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 13px;
            color: #000;
            text-align: center;
        }

        @media print {
            html, body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .sheet {
                margin: 0;
                box-shadow: none;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

@php
    $clienteNombre = trim(($alquiler->cliente->nombres ?? '') . ' ' . ($alquiler->cliente->apellidos ?? ''));

    $fechaActual = now();
    $fechaAlquiler = $alquiler->fecha_alquiler;
    $fechaEntrega = $alquiler->fecha_entrega;
    $fechaDevolucion = $alquiler->fecha_devolucion_programada;
    $fechaLimitePago = $alquiler->fecha_limite_pago_final;

    $saldo = $alquiler->estado === 'CANCELADO' ? 0 : $alquiler->saldo_pendiente;

    $institucionRepresentada = $alquiler->institucion_representada
        ?: 'Cliente individual';

    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre',
    ];

    $mesActual = $meses[(int) $fechaActual->format('n')] ?? '';

    $mesAlquiler = $fechaAlquiler
        ? ($meses[(int) $fechaAlquiler->format('n')] ?? '')
        : '';

    $mesEntrega = $fechaEntrega
        ? ($meses[(int) $fechaEntrega->format('n')] ?? '')
        : '';

    $mesDevolucion = $fechaDevolucion
        ? ($meses[(int) $fechaDevolucion->format('n')] ?? '')
        : '';

    $mesLimitePago = $fechaLimitePago
        ? ($meses[(int) $fechaLimitePago->format('n')] ?? '')
        : '';

    $horaInicio = $alquiler->hora_entrega_inicio
        ? \Carbon\Carbon::parse($alquiler->hora_entrega_inicio)->format('H:i')
        : '';

    $horaFin = $alquiler->hora_entrega_fin
        ? \Carbon\Carbon::parse($alquiler->hora_entrega_fin)->format('H:i')
        : '';

    $horaEntregaExacta = $alquiler->hora_entrega
        ? \Carbon\Carbon::parse($alquiler->hora_entrega)->format('H:i')
        : '';

    $horaDevolucionProgramada = $alquiler->hora_devolucion_programada
        ? \Carbon\Carbon::parse($alquiler->hora_devolucion_programada)->format('H:i')
        : '';

    $horaEntregaMostrarInicio = $horaInicio ?: $horaEntregaExacta;
    $horaEntregaMostrarFin = $horaFin ?: $horaEntregaExacta;

    /*
    |--------------------------------------------------------------------------
    | Tallas por tipo de toga
    |--------------------------------------------------------------------------
    | producto_togas.tipo_toga es enum('ESTANDAR','UNIVERSITARIA').
    | Se separan las tallas para imprimir cada carta con sus propias cantidades.
    */
    $normalizarTalla = fn ($t) => match (strtoupper(trim((string) $t))) {
        '4' => '4',
        '6' => '6',
        '8' => '8',
        '10' => '10',
        '12' => '12',
        '14' => '14',
        '16', '16 (XS)', 'XS' => '16',
        'S' => 'S',
        'M' => 'M',
        'L' => 'L',
        default => null,
    };

    $tallasBase = array_fill_keys(['4', '6', '8', '10', '12', '14', '16', 'S', 'M', 'L'], 0);

    $tallasNormal = $tallasBase;
    $tallasUniversitaria = $tallasBase;
    $detallesUniversitaria = collect();

    foreach ($alquiler->detalles as $detalle) {
        $producto = $detalle->producto;

        if (!$producto || $producto->tipo_producto !== 'TOGA') {
            continue;
        }

        $esUniversitaria = strtoupper($producto->toga->tipo_toga ?? '') === 'UNIVERSITARIA';

        if ($esUniversitaria) {
            $detallesUniversitaria->push($detalle);
        }

        $talla = $normalizarTalla($producto->toga->talla ?? '');

        if (!$talla) {
            continue;
        }

        $cantidad = (int) $detalle->cantidad;

        if ($esUniversitaria) {
            $tallasUniversitaria[$talla] += $cantidad;
        } else {
            $tallasNormal[$talla] += $cantidad;
        }
    }

    $totalNormales = array_sum($tallasNormal);
    $totalUniversitarias = array_sum($tallasUniversitaria);

    /*
    | "(Estas togas no [ ] si [ ] incluyen capelo, borla, capa y collarín)":
    | SI solo si TODAS las togas universitarias tienen birrete, borla, capa y collarín.
    */
    $tieneAccesorio = fn ($detalle, $tipo) => collect($detalle->accesorios ?? [])->contains(
        fn ($a) => ($a->producto->tipo_producto ?? null) === $tipo
    );

    $incluyeAccesoriosUniversitaria = $detallesUniversitaria->isNotEmpty()
        && $detallesUniversitaria->every(
            fn ($d) => $tieneAccesorio($d, 'BIRRETE')
                && $tieneAccesorio($d, 'BORLA')
                && $tieneAccesorio($d, 'CAPA')
                && $tieneAccesorio($d, 'COLLARIN')
        );

    // Si no hay ninguna toga, se imprime la carta normal para no dejar el documento vacío.
    $imprimirNormal = $totalNormales > 0 || $totalUniversitarias === 0;
    $imprimirUniversitaria = $totalUniversitarias > 0;

    $etiquetaTipo = match (true) {
        $imprimirNormal && $imprimirUniversitaria => 'Normal + Universitaria',
        $imprimirUniversitaria => 'Universitaria',
        default => 'Normal',
    };
@endphp

<div class="toolbar">
    <span class="badge-tipo">Carta: {{ $etiquetaTipo }}</span>
    <a href="{{ route('alquileres.show', $alquiler->id) }}">← Volver</a>
    <button type="button" onclick="window.print()">Imprimir documento</button>
</div>

{{-- Carta de compromiso para togas normales --}}
@if($imprimirNormal)
    @include('alquileres.cartas.normal', [
        'tallas'     => $tallasNormal,
        'totalTogas' => $totalNormales,
    ])
@endif

{{-- Carta de compromiso para togas universitarias --}}
@if($imprimirUniversitaria)
    @include('alquileres.cartas.universitaria', [
        'tallas'            => $tallasUniversitaria,
        'totalTogas'        => $totalUniversitarias,
        'incluyeAccesorios' => $incluyeAccesoriosUniversitaria,
    ])
@endif

</body>
</html>