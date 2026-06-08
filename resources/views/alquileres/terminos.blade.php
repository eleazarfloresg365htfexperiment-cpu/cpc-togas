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
            background-image: url("{{ asset('plantillas/carta-compromiso-p1.png') }}");
        }

        .page-2 {
            background-image: url("{{ asset('plantillas/carta-compromiso-p2.png') }}");
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
    $clienteNombre = trim(($alquiler->cliente->nombres ?? '') . ' ' . ($alquiler->cliente->apellidos ?? ''));

    $fechaActual = now();
    $fechaAlquiler = $alquiler->fecha_alquiler;
    $fechaEntrega = $alquiler->fecha_entrega;
    $fechaDevolucion = $alquiler->fecha_devolucion_programada;
    $fechaLimitePago = $alquiler->fecha_limite_pago_final;

    $saldo = $alquiler->estado === 'CANCELADO' ? 0 : $alquiler->saldo_pendiente;

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

    $tallas = [
        '4' => 0,
        '6' => 0,
        '8' => 0,
        '10' => 0,
        '12' => 0,
        '14' => 0,
        '16' => 0,
        'S' => 0,
        'M' => 0,
        'L' => 0,
    ];

    foreach ($alquiler->detalles as $detalle) {
        $producto = $detalle->producto;

        if (!$producto || $producto->tipo_producto !== 'TOGA') {
            continue;
        }

        $tallaOriginal = strtoupper(trim($producto->toga->talla ?? ''));

        $tallaNormalizada = match ($tallaOriginal) {
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

        if ($tallaNormalizada && array_key_exists($tallaNormalizada, $tallas)) {
            $tallas[$tallaNormalizada] += (int) $detalle->cantidad;
        }
    }

    $totalTogas = array_sum($tallas);
@endphp
<div class="sheet page-1">

    {{-- Fecha superior --}}
    <div class="campo campo-center" style="left: 1.35in; top: 1.48in; width: 1.10in;">
        Jalapa
    </div>

    <div class="campo campo-center" style="left: 2.48in; top: 1.48in; width: 0.42in;">
        {{ $fechaActual->format('d') }}
    </div>

    <div class="campo campo-center" style="left: 3.73in; top: 1.47in; width: 1.15in;">
        {{ $mesActual }}
    </div>

    <div class="campo campo-center" style="left: 5.07in; top: 1.48in; width: 0.58in;">
        {{ $fechaActual->format('Y') }}
    </div>

    {{-- Representante o encargado(a) del arrendador --}}
    <div class="campo campo-sm" style="left: 3.35in; top: 9.18in; width: 3.20in;">
        {{ $alquiler->representante_alquiler }}
    </div>

    {{-- Arrendatario --}}
    {{-- Institución representada --}}
    <div class="campo campo-sm" style="left: 2.73in; top: 3.38in; width: 3.45in;">
        {{ $alquiler->institucion_representada }}
    </div>

    <div class="campo campo-sm" style="left: 2.28in; top: 3.55in; width: 3.45in;">
        {{ $clienteNombre }}
    </div>

    <div class="campo campo-sm" style="left: 1.60in; top: 3.71in; width: 2.30in;">
        {{ $alquiler->cliente->dpi }}
    </div>

    <div class="campo campo-sm" style="left: 1.13in; top: 3.88in; width: 4.70in;">
        {{ $alquiler->cliente->direccion }}
    </div>

    <div class="campo campo-sm" style="left: 1.10in; top: 4.04in; width: 2.35in;">
        {{ $alquiler->cliente->telefono }}
    </div>

    {{-- Tabla de tallas --}}
    <div class="campo campo-center campo-bold" style="left: 1.13in; top: 6.12in; width: 0.45in;">
        {{ $tallas['4'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 1.70in; top: 6.12in; width: 0.45in;">
        {{ $tallas['6'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 2.29in; top: 6.12in; width: 0.45in;">
        {{ $tallas['8'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 2.86in; top: 6.12in; width: 0.45in;">
        {{ $tallas['10'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 3.45in; top: 6.12in; width: 0.45in;">
        {{ $tallas['12'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.02in; top: 6.12in; width: 0.45in;">
        {{ $tallas['14'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.60in; top: 6.12in; width: 0.45in;">
        {{ $tallas['16'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.18in; top: 6.12in; width: 0.45in;">
        {{ $tallas['S'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.78in; top: 6.12in; width: 0.45in;">
        {{ $tallas['M'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 6.36in; top: 6.12in; width: 0.45in;">
        {{ $tallas['L'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 6.90in; top: 6.12in; width: 0.50in;">
        {{ $totalTogas ?: '' }}
    </div>

    {{-- Fechas del alquiler --}}
    <div class="campo campo-center campo-sm" style="left: 2.65in; top: 7.36in; width: 0.35in;">
        {{ optional($fechaAlquiler)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 3.25in; top: 7.36in; width: 0.90in;">
        {{ $mesAlquiler }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 4.35in; top: 7.36in; width: 0.55in;">
        {{ optional($fechaAlquiler)->format('Y') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 5.52in; top: 7.36in; width: 0.35in;">
        {{ optional($fechaDevolucion)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 6.20in; top: 7.36in; width: 0.90in;">
        {{ $mesDevolucion }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 7.50in; top: 7.36in; width: 0.45in;">
        {{ optional($fechaDevolucion)->format('Y') }}
    </div>

    {{-- Recogerá --}}
    <div class="campo campo-center campo-sm" style="left: 2.78in; top: 7.52in; width: 0.35in;">
        {{ optional($fechaEntrega)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 3.97in; top: 7.52in; width: 0.95in;">
        {{ $mesEntrega }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 5.30in; top: 7.52in; width: 0.50in;">
        {{ optional($fechaEntrega)->format('Y') }}
    </div>

    {{-- Horario programado de entrega --}}
    <div class="campo campo-center campo-sm" style="left: 2.45in; top: 7.68in; width: 0.75in;">
        {{ $horaInicio }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 3.55in; top: 7.68in; width: 0.75in;">
        {{ $horaFin }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 6.35in; top: 7.68in; width: 1.20in;">
        {{ optional($fechaDevolucion)->format('d/m/Y') }}
    </div>

    {{-- Valores --}}
    <div class="campo campo-center campo-sm campo-bold" style="left: 3.68in; top: 8.16in; width: 1.05in;">
        {{ number_format($alquiler->total, 2) }}
    </div>

    <div class="campo campo-center campo-sm campo-bold" style="left: 0.58in; top: 8.64in; width: 1.05in;">
        {{ number_format($saldo, 2) }}
    </div>

    {{-- Fecha límite para pago final --}}
    <div class="campo campo-center campo-sm" style="left: 4.45in; top: 8.64in; width: 0.35in;">
        {{ optional($fechaLimitePago)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 5.15in; top: 8.64in; width: 0.95in;">
        {{ $mesLimitePago }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 6.35in; top: 8.64in; width: 0.55in;">
        {{ optional($fechaLimitePago)->format('Y') }}
    </div>

</div>

<div class="sheet page-2">
    {{-- Segunda página: solo plantilla de fondo por ahora --}}
</div>

</body>
</html>