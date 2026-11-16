<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            margin: 24px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            color: #4b5563;
            margin-top: 4px;
        }

        .badge {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 5px 10px;
            border-radius: 999px;
            font-weight: bold;
            margin-top: 8px;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .grid td {
            width: 25%;
            border: 1px solid #e5e7eb;
            padding: 10px;
            vertical-align: top;
        }

        .label {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .value {
            font-size: 15px;
            font-weight: bold;
        }

        h2 {
            font-size: 15px;
            margin-top: 20px;
            margin-bottom: 8px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th {
            background: #f3f4f6;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #e5e7eb;
            padding: 7px;
            text-align: left;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 7px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .note {
            margin-top: 18px;
            color: #6b7280;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        .chart-section {
            margin-top: 18px;
            margin-bottom: 26px;
            page-break-inside: avoid;
        }

        .chart-title {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .chart-description {
            font-size: 10.5px;
            color: #6b7280;
            margin-bottom: 10px;
            line-height: 1.45;
        }

        .barras { width: 100%; border-collapse: collapse; }
        .barras td { border: none; border-bottom: 1px solid #eef0f4; padding: 5px 4px; vertical-align: top; }
        .barras-etiqueta { width: 27%; font-weight: bold; font-size: 10px; padding-top: 6px !important; }
        .barras-detalle { font-weight: normal; color: #6b7280; font-size: 9px; }
        .barras-linea { width: 100%; border-collapse: collapse; }
        .barras-linea td { border: none; padding: 1px 0; }
        .barra { height: 11px; line-height: 11px; font-size: 1px; }
        .barras-valor { padding-left: 5px !important; font-size: 9.5px; font-weight: bold; white-space: nowrap; }
        .barras-leyenda { font-size: 9.5px; margin-bottom: 6px; }
        .barras-leyenda-item { margin-right: 14px; }
        .barras-cuadro { font-size: 7px; }
        .barras-vacio { color: #6b7280; font-size: 10px; padding: 8px 0; }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <p class="title">Estadísticas - Togas</p>
        <div class="subtitle">Centro Profesional de Cómputo CPC</div>
        <div class="badge">
            {{ $tituloPeriodo }} · {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}
        </div>
    </div>

    <table class="grid">
        <tr>
            <td>
                <div class="label">Alquileres registrados</div>
                <div class="value">{{ number_format($alquileresRegistrados) }}</div>
            </td>
            <td>
                <div class="label">Pagos registrados</div>
                <div class="value">{{ number_format($pagosRegistrados) }}</div>
            </td>
            <td>
                <div class="label">Ingresos recibidos</div>
                <div class="value">Q{{ number_format($ingresosRecibidos, 2) }}</div>
            </td>
            <td>
                <div class="label">Descuentos aplicados</div>
                <div class="value">Q{{ number_format($descuentosAplicados, 2) }}</div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="label">Mora generada</div>
                <div class="value">Q{{ number_format($moraGenerada, 2) }}</div>
            </td>
            <td>
                <div class="label">Saldo pendiente actual</div>
                <div class="value">Q{{ number_format($saldoPendienteActual, 2) }}</div>
            </td>
            <td>
                <div class="label">Producto más alquilado</div>
                <div class="value">{{ $productoMasAlquilado->nombre ?? 'Sin datos' }}</div>
            </td>
            <td>
                <div class="label">Talla más alquilada</div>
                <div class="value">{{ $tallaTogaMasAlquilada->talla ?? 'Sin datos' }}</div>
            </td>
        </tr>
    </table>

    <h2>Tabla resumen</h2>

    <table>
        <thead>
            <tr>
                <th>Periodo</th>
                <th class="text-center">Alquileres</th>
                <th class="text-center">Pagos</th>
                <th class="text-end">Ingresos</th>
                <th class="text-end">Descuentos</th>
                <th class="text-end">Mora</th>
                <th class="text-end">Total aplicado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tablaResumen as $fila)
                <tr>
                    <td>{{ $fila['periodo'] }}</td>
                    <td class="text-center">{{ number_format($fila['alquileres']) }}</td>
                    <td class="text-center">{{ number_format($fila['pagos']) }}</td>
                    <td class="text-end">Q{{ number_format($fila['ingresos'], 2) }}</td>
                    <td class="text-end">Q{{ number_format($fila['descuentos'], 2) }}</td>
                    <td class="text-end">Q{{ number_format($fila['mora'], 2) }}</td>
                    <td class="text-end">Q{{ number_format($fila['total_aplicado'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No hay datos para el periodo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rankings</h2>

    <table>
        <thead>
            <tr>
                <th>Indicador</th>
                <th>Resultado</th>
                <th class="text-end">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Día con más alquileres</td>
                <td>{{ $diaMasAlquileres->periodo ?? 'Sin datos' }}</td>
                <td class="text-end">{{ $diaMasAlquileres->total ?? 0 }}</td>
            </tr>

            <tr>
                <td>Día con más ingresos</td>
                <td>{{ $diaMasIngresos->periodo ?? 'Sin datos' }}</td>
                <td class="text-end">Q{{ number_format($diaMasIngresos->total ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Día con más descuentos</td>
                <td>{{ $diaMasDescuentos->periodo ?? 'Sin datos' }}</td>
                <td class="text-end">Q{{ number_format($diaMasDescuentos->total ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Día con más mora</td>
                <td>{{ $diaMasMora->periodo ?? 'Sin datos' }}</td>
                <td class="text-end">Q{{ number_format($diaMasMora->total ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Método de pago más usado</td>
                <td>{{ $metodoPagoMasUsado->metodo_pago ?? 'Sin datos' }}</td>
                <td class="text-end">{{ $metodoPagoMasUsado->total ?? 0 }} pago(s)</td>
            </tr>

            <tr>
                <td>Institución con más alquileres</td>
                <td>{{ $institucionMasAlquileres->institucion_representada ?? 'Sin datos' }}</td>
                <td class="text-end">{{ $institucionMasAlquileres->total ?? 0 }} alquiler(es)</td>
            </tr>
        </tbody>
    </table>

    @php
        $colorPrincipal = '#4f46e5';
        $conMovimiento = collect($tablaResumen)->filter(
            fn ($f) => $f['alquileres'] > 0 || $f['ingresos'] > 0 || $f['descuentos'] > 0 || $f['mora'] > 0
        );
    @endphp

    <div class="page-break"></div>

    <h2>Gráficas del periodo</h2>

    @include('estadisticas.partials.pdf-barras', [
        'titulo' => 'Alquileres por periodo',
        'descripcion' => 'Cantidad de alquileres registrados. Solo se muestran los periodos con movimiento.',
        'filas' => $conMovimiento->where('alquileres', '>', 0)->map(fn ($f) => [
            'etiqueta' => $f['periodo'],
            'series' => [['valor' => $f['alquileres'], 'texto' => number_format($f['alquileres']), 'color' => $colorPrincipal]],
        ])->values()->all(),
        'vacio' => 'No hay alquileres registrados en este periodo.',
    ])

    @include('estadisticas.partials.pdf-barras', [
        'titulo' => 'Resumen financiero por periodo',
        'descripcion' => 'Ingresos reales recibidos, descuentos aplicados y mora generada. Los descuentos no son ingreso.',
        'leyenda' => [
            ['nombre' => 'Ingresos recibidos', 'color' => '#059669'],
            ['nombre' => 'Descuentos aplicados', 'color' => '#d97706'],
            ['nombre' => 'Mora generada', 'color' => '#dc2626'],
        ],
        'filas' => $conMovimiento->filter(fn ($f) => $f['ingresos'] > 0 || $f['descuentos'] > 0 || $f['mora'] > 0)->map(fn ($f) => [
            'etiqueta' => $f['periodo'],
            'series' => collect([
                ['valor' => $f['ingresos'], 'texto' => 'Q' . number_format($f['ingresos'], 2), 'color' => '#059669'],
                ['valor' => $f['descuentos'], 'texto' => 'Q' . number_format($f['descuentos'], 2), 'color' => '#d97706'],
                ['valor' => $f['mora'], 'texto' => 'Q' . number_format($f['mora'], 2), 'color' => '#dc2626'],
            ])->filter(fn ($serie) => $serie['valor'] > 0)->values()->all(),
        ])->values()->all(),
        'vacio' => 'No hay movimientos financieros en este periodo.',
    ])

    @include('estadisticas.partials.pdf-barras', [
        'titulo' => 'Productos más alquilados',
        'filas' => collect($topProductos)->map(fn ($p) => [
            'etiqueta' => $p->nombre,
            'detalle' => $p->tipo_producto,
            'series' => [['valor' => $p->total, 'texto' => number_format($p->total), 'color' => $colorPrincipal]],
        ])->all(),
        'vacio' => 'No hay productos alquilados en este periodo.',
    ])

    @include('estadisticas.partials.pdf-barras', [
        'titulo' => 'Tallas de toga más alquiladas',
        'filas' => collect($topTallasToga)->map(fn ($t) => [
            'etiqueta' => 'Talla ' . $t->talla,
            'series' => [['valor' => $t->total, 'texto' => number_format($t->total) . ' togas', 'color' => $colorPrincipal]],
        ])->all(),
        'vacio' => 'No hay togas alquiladas en este periodo.',
    ])

    @include('estadisticas.partials.pdf-barras', [
        'titulo' => 'Clientes que más togas alquilaron',
        'filas' => collect($topClientes)->map(fn ($c) => [
            'etiqueta' => trim($c->nombres . ' ' . $c->apellidos),
            'detalle' => ($c->institucion_representada ?: 'Individual') . ' · ' . $c->alquileres . ' alquiler(es)',
            'series' => [['valor' => $c->total, 'texto' => number_format($c->total) . ' togas', 'color' => $colorPrincipal]],
        ])->all(),
        'vacio' => 'No hay togas alquiladas en este periodo.',
    ])

    <div class="note">
        Los ingresos recibidos se calculan únicamente con pagos reales registrados.
        Los descuentos no se consideran ingreso.
        Reporte generado el {{ now()->format('d/m/Y H:i') }}.
    </div>

</body>
</html>