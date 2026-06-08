<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de movimientos</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111827;
        }

        h1 {
            text-align: center;
            font-size: 17px;
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
            padding: 4px;
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

<h1>Reporte de movimientos de inventario</h1>

<div class="sub">
    Centro Profesional de Cómputo CPC Jalapa<br>
    Generado el {{ $fechaGeneracion->format('d/m/Y H:i') }}
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Producto</th>
            <th>Movimiento</th>
            <th>Cant.</th>
            <th>Disp. ant.</th>
            <th>Disp. nuevo</th>
            <th>Alq. ant.</th>
            <th>Alq. nuevo</th>
            <th>Motivo</th>
            <th>Referencia</th>
        </tr>
    </thead>

    <tbody>
        @foreach($movimientos as $movimiento)
            <tr>
                <td>{{ $movimiento->id }}</td>
                <td>{{ optional($movimiento->created_at)->format('d/m/Y H:i') }}</td>
                <td>
                    <span class="fw-bold">{{ $movimiento->producto->codigo ?? '' }}</span><br>
                    {{ $movimiento->producto->nombre ?? '' }}
                </td>
                <td>{{ $movimiento->tipo_movimiento }}</td>
                <td class="text-right">{{ $movimiento->cantidad }}</td>
                <td class="text-right">{{ $movimiento->stock_anterior_disponible }}</td>
                <td class="text-right">{{ $movimiento->stock_nuevo_disponible }}</td>
                <td class="text-right">{{ $movimiento->stock_anterior_alquilado }}</td>
                <td class="text-right">{{ $movimiento->stock_nuevo_alquilado }}</td>
                <td>{{ $movimiento->motivo }}</td>
                <td>{{ $movimiento->referencia }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>