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

        .page-1 {
            background-image: url("{{ asset('plantillas/carta-compromiso-p3.png') }}");
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

<div class="toolbar">
    <a href="{{ route('alquileres.show', $alquiler->id) }}">← Volver</a>
    <button type="button" onclick="window.print()">Imprimir documento</button>
</div>

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

    // Tabla de tallas: misma lógica que la página 1 de la carta de compromiso.
    $tallas = [
        '4' => 0, '6' => 0, '8' => 0, '10' => 0, '12' => 0,
        '14' => 0, '16' => 0, 'S' => 0, 'M' => 0, 'L' => 0,
    ];

    foreach ($alquiler->detalles as $detalle) {
        $producto = $detalle->producto;

        if (!$producto || $producto->tipo_producto !== 'TOGA') {
            continue;
        }

        $tallaOriginal = strtoupper(trim($producto->toga->talla ?? ''));

        $tallaNormalizada = match ($tallaOriginal) {
            '4' => '4', '6' => '6', '8' => '8', '10' => '10', '12' => '12',
            '14' => '14', '16', '16 (XS)', 'XS' => '16',
            'S' => 'S', 'M' => 'M', 'L' => 'L',
            default => null,
        };

        if ($tallaNormalizada && array_key_exists($tallaNormalizada, $tallas)) {
            $tallas[$tallaNormalizada] += (int) $detalle->cantidad;
        }
    }

    $totalTogas = array_sum($tallas);

    // "¿Incluyen birrete y collarín?" - se marca SI solo si TODAS las togas
    // del alquiler tienen al menos un birrete y un collarín entre sus accesorios.
    $incluyeBirreteYCollarin = $alquiler->detalles->isNotEmpty()
        && $alquiler->detalles->every(function ($detalle) {
            $accesorios = collect($detalle->accesorios ?? []);

            $tieneBirrete = $accesorios->contains(
                fn ($a) => ($a->producto->tipo_producto ?? null) === 'BIRRETE'
            );

            $tieneCollarin = $accesorios->contains(
                fn ($a) => ($a->producto->tipo_producto ?? null) === 'COLLARIN'
            );

            return $tieneBirrete && $tieneCollarin;
        });
@endphp

<div class="sheet page-1">

    {{-- Fecha del acta --}}
    <div class="campo campo-center" style="left: 2.20in; top: 1.48in; width: 0.30in;">
        {{ $fechaActa->format('d') }}
    </div>

    <div class="campo" style="left: 3.85in; top: 1.48in; width: 0.85in;">
        {{ $mesActa }}
    </div>

    <div class="campo campo-center" style="left: 5.08in; top: 1.48in; width: 0.45in;">
        {{ $fechaActa->format('Y') }}
    </div>

    <div class="campo campo-center" style="left: 6.22in; top: 1.48in; width: 0.58in;">
        {{ $fechaActa->format('H:i') }}
    </div>

    {{-- Tabla de tallas devueltas --}}
    <div class="campo campo-center campo-bold" style="left: 1.13in; top: 2.75in; width: 0.45in;">
        {{ $tallas['4'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 1.70in; top: 2.75in; width: 0.45in;">
        {{ $tallas['6'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 2.29in; top: 2.75in; width: 0.45in;">
        {{ $tallas['8'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 2.86in; top: 2.75in; width: 0.45in;">
        {{ $tallas['10'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 3.45in; top: 2.75in; width: 0.45in;">
        {{ $tallas['12'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.02in; top: 2.75in; width: 0.45in;">
        {{ $tallas['14'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.60in; top: 2.75in; width: 0.45in;">
        {{ $tallas['16'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.18in; top: 2.75in; width: 0.45in;">
        {{ $tallas['S'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.78in; top: 2.75in; width: 0.45in;">
        {{ $tallas['M'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 6.36in; top: 2.75in; width: 0.45in;">
        {{ $tallas['L'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 6.90in; top: 2.75in; width: 0.50in;">
        {{ $totalTogas ?: '' }}
    </div>

    {{-- ¿Incluyen birrete y collarín? --}}
    @if(!$incluyeBirreteYCollarin)
        <div class="campo-check" style="left: 1.36in; top: 3.10in; width: 0.14in;">
            ✓
        </div>
    @else
        <div class="campo-check" style="left: 1.63in; top: 3.10in; width: 0.14in;">
            ✓
        </div>
    @endif

</div>

</body>
</html>