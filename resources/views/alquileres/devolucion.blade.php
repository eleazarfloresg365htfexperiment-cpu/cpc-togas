<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta de devolución - {{ $alquiler->codigo_recibo }}</title>

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

        .devolucion-normal {
            background-image: url("{{ asset('plantillas/carta-devolucion-normal.png') }}");
        }

        .devolucion-universitaria {
            background-image: url("{{ asset('plantillas/carta-devolucion-universitaria.png') }}");
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
    // Fecha/hora del acta: si ya se registró la devolución real se usa esa,
    // si no, se usa el momento en que se abre/imprime la carta.
    $fechaActa = $alquiler->fecha_hora_devolucion_real ?? now();

    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    $mesActa = $meses[(int) $fechaActa->format('n')] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Tallas y detalles por tipo de toga
    |--------------------------------------------------------------------------
    | producto_togas.tipo_toga es enum('ESTANDAR','UNIVERSITARIA').
    | Cada carta se imprime solo con las togas de su tipo.
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

    $detallesNormal = collect();
    $detallesUniversitaria = collect();

    foreach ($alquiler->detalles as $detalle) {
        $producto = $detalle->producto;

        if (!$producto || $producto->tipo_producto !== 'TOGA') {
            continue;
        }

        $esUniversitaria = strtoupper($producto->toga->tipo_toga ?? '') === 'UNIVERSITARIA';

        if ($esUniversitaria) {
            $detallesUniversitaria->push($detalle);
        } else {
            $detallesNormal->push($detalle);
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
    |--------------------------------------------------------------------------
    | ¿Incluyen birrete y collarín?
    |--------------------------------------------------------------------------
    | Se marca SI solo si TODAS las togas de ese tipo tienen al menos un
    | birrete y un collarín entre sus accesorios.
    */
    $tieneAccesorio = fn ($detalle, $tipo) => collect($detalle->accesorios ?? [])->contains(
        fn ($a) => ($a->producto->tipo_producto ?? null) === $tipo
    );

    $verificarBirreteYCollarin = fn ($detalles) => $detalles->isNotEmpty()
        && $detalles->every(
            fn ($d) => $tieneAccesorio($d, 'BIRRETE') && $tieneAccesorio($d, 'COLLARIN')
        );

    $incluyeNormal = $verificarBirreteYCollarin($detallesNormal);
    $incluyeUniversitaria = $verificarBirreteYCollarin($detallesUniversitaria);

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
    <span class="badge-tipo">Devolución: {{ $etiquetaTipo }}</span>
    <a href="{{ route('alquileres.show', $alquiler->id) }}">← Volver</a>
    <button type="button" onclick="window.print()">Imprimir documento</button>
</div>

{{-- Carta de devolución para togas normales --}}
@if($imprimirNormal)
    @include('alquileres.cartas.devolucion-normal', [
        'tallas'                  => $tallasNormal,
        'totalTogas'              => $totalNormales,
        'incluyeBirreteYCollarin' => $incluyeNormal,
    ])
@endif

{{-- Carta de devolución para togas universitarias --}}
@if($imprimirUniversitaria)
    @include('alquileres.cartas.devolucion-universitaria', [
        'tallas'                  => $tallasUniversitaria,
        'totalTogas'              => $totalUniversitarias,
        'incluyeBirreteYCollarin' => $incluyeUniversitaria,
    ])
@endif

</body>
</html>