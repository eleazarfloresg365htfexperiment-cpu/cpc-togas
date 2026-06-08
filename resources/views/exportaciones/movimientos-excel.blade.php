<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        th {
            background-color: #1d4ed8;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #1e3a8a;
            padding: 8px;
            text-align: center;
        }

        td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            vertical-align: top;
        }

        .titulo {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding: 12px;
        }

        .subtitulo {
            background-color: #e2e8f0;
            color: #334155;
            text-align: center;
            font-weight: bold;
            padding: 8px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .movimiento {
            font-weight: bold;
            text-align: center;
        }

        .cantidad-negativa {
            color: #b91c1c;
            font-weight: bold;
        }

        .cantidad-positiva {
            color: #166534;
            font-weight: bold;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="13" class="titulo">
            REPORTE DE MOVIMIENTOS DE INVENTARIO - CENTRO PROFESIONAL DE CÓMPUTO CPC
        </td>
    </tr>

    <tr>
        <td colspan="13" class="subtitulo">
            Generado el {{ $fechaGeneracion->format('d/m/Y H:i') }}
        </td>
    </tr>

    <tr>
        <th>ID</th>
        <th>Fecha</th>
        <th>Producto</th>
        <th>Código</th>
        <th>Tipo producto</th>
        <th>Movimiento</th>
        <th>Cantidad</th>
        <th>Stock disp. anterior</th>
        <th>Stock disp. nuevo</th>
        <th>Stock alq. anterior</th>
        <th>Stock alq. nuevo</th>
        <th>Motivo</th>
        <th>Referencia</th>
    </tr>

    @foreach($movimientos as $movimiento)
        <tr>
            <td class="text-center">{{ $movimiento->id }}</td>
            <td class="text-center">{{ optional($movimiento->created_at)->format('d/m/Y H:i') }}</td>
            <td>{{ $movimiento->producto->nombre ?? '' }}</td>
            <td>{{ $movimiento->producto->codigo ?? '' }}</td>
            <td class="text-center">{{ $movimiento->producto->tipo_producto ?? '' }}</td>
            <td class="movimiento">{{ $movimiento->tipo_movimiento }}</td>
            <td class="text-right {{ $movimiento->cantidad < 0 ? 'cantidad-negativa' : 'cantidad-positiva' }}">
                {{ $movimiento->cantidad > 0 ? '+' : '' }}{{ $movimiento->cantidad }}
            </td>
            <td class="text-right">{{ $movimiento->stock_anterior_disponible }}</td>
            <td class="text-right">{{ $movimiento->stock_nuevo_disponible }}</td>
            <td class="text-right">{{ $movimiento->stock_anterior_alquilado }}</td>
            <td class="text-right">{{ $movimiento->stock_nuevo_alquilado }}</td>
            <td>{{ $movimiento->motivo }}</td>
            <td>{{ $movimiento->referencia }}</td>
        </tr>
    @endforeach
</table>

</body>
</html>