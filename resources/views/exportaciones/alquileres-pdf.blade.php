<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de alquileres</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 4px;
        }

        .sub {
            text-align: center;
            font-size: 11px;
            margin-bottom: 18px;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #e5e7eb;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #9ca3af;
            padding: 5px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Reporte de alquileres</h1>

<div class="sub">
    Centro Profesional de Cómputo CPC Jalapa<br>
    Generado el {{ $fechaGeneracion->format('d/m/Y H:i') }}
</div>

<table>
    <thead>
        <tr>
            <th>Recibo</th>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Alquiler</th>
            <th>Entrega</th>
            <th>Devolución</th>
            <th>Estado</th>
            <th>Pago</th>
            <th>Total</th>
            <th>Pagado</th>
            <th>Saldo</th>
        </tr>
    </thead>

    <tbody>
        @foreach($alquileres as $alquiler)
            <tr>
                <td>{{ $alquiler->codigo_recibo }}</td>
                <td>{{ $alquiler->cliente->nombres ?? '' }} {{ $alquiler->cliente->apellidos ?? '' }}</td>
                <td>{{ $alquiler->cliente->telefono ?? '' }}</td>
                <td>{{ optional($alquiler->fecha_alquiler)->format('d/m/Y') }}</td>
                <td>{{ optional($alquiler->fecha_entrega)->format('d/m/Y') }}</td>
                <td>{{ optional($alquiler->fecha_devolucion_programada)->format('d/m/Y') }}</td>
                <td class="fw-bold">{{ $alquiler->estado }}</td>
                <td>
                    {{ $alquiler->estado === 'CANCELADO' ? 'SIN COBRO' : $alquiler->estado_pago }}
                </td>
                <td class="text-right">Q {{ number_format($alquiler->total, 2) }}</td>
                <td class="text-right">Q {{ number_format($alquiler->pagos->sum('monto'), 2) }}</td>
                <td class="text-right">
                    @if($alquiler->estado === 'CANCELADO')
                        Q 0.00
                    @else
                        Q {{ number_format($alquiler->saldo_pendiente, 2) }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>