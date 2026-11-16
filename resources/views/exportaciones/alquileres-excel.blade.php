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

        .estado {
            font-weight: bold;
            text-align: center;
        }

        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="19" class="titulo">
            REPORTE DE ALQUILERES - CENTRO PROFESIONAL DE CÓMPUTO CPC
        </td>
    </tr>

    <tr>
        <td colspan="19" class="subtitulo">
            Generado el {{ $fechaGeneracion->format('d/m/Y H:i') }}
        </td>
    </tr>

    <tr>
        <th>ID</th>
        <th>Recibo</th>
        <th>Cliente</th>
        <th>Togas alquiladas</th>
        <th>Tallas</th>
        <th>Teléfono</th>
        <th>DPI</th>
        <th>Fecha alquiler</th>
        <th>Fecha entrega</th>
        <th>Devolución programada</th>
        <th>Devolución real</th>
        <th>Estado</th>
        <th>Estado de pago</th>
        <th>Subtotal</th>
        <th>Descuento</th>
        <th>Total</th>
        <th>Pagado</th>
        <th>Saldo pendiente</th>
        <th>Observaciones</th>
    </tr>

    @php
        $totalGeneral = 0;
        $totalPagado = 0;
        $totalSaldo = 0;
        $totalTogas = 0;
    @endphp

    @foreach($alquileres as $alquiler)
        @php
            $pagado = $alquiler->pagos->sum('monto');
            $saldo = $alquiler->estado === 'CANCELADO' ? 0 : $alquiler->saldo_pendiente;

            $totalGeneral += $alquiler->total;
            $totalPagado += $pagado;
            $totalSaldo += $saldo;

            // Los cancelados muestran sus togas, pero no suman al total.
            $togas = $alquiler->totalTogas();
            $totalTogas += $alquiler->estado === 'CANCELADO' ? 0 : $togas;
        @endphp

        <tr>
            <td class="text-center">{{ $alquiler->id }}</td>
            <td>{{ $alquiler->codigo_recibo }}</td>
            <td>{{ $alquiler->cliente->nombres ?? '' }} {{ $alquiler->cliente->apellidos ?? '' }}</td>
            <td class="text-center">{{ $togas }}</td>
            <td>{{ $alquiler->tallasTexto() }}</td>
            <td>{{ $alquiler->cliente->telefono ?? '' }}</td>
            <td>{{ $alquiler->cliente->dpi ?? '' }}</td>
            <td class="text-center">{{ optional($alquiler->fecha_alquiler)->format('d/m/Y') }}</td>
            <td class="text-center">{{ optional($alquiler->fecha_entrega)->format('d/m/Y') }}</td>
            <td class="text-center">{{ optional($alquiler->fecha_devolucion_programada)->format('d/m/Y') }}</td>
            <td class="text-center">{{ optional($alquiler->fecha_devolucion_real)->format('d/m/Y') }}</td>
            <td class="estado">{{ $alquiler->estado }}</td>
            <td class="estado">
                {{ $alquiler->estado === 'CANCELADO' ? 'SIN COBRO' : $alquiler->estado_pago }}
            </td>
            <td class="text-right">Q {{ number_format($alquiler->subtotal, 2) }}</td>
            <td class="text-right">Q {{ number_format($alquiler->descuento, 2) }}</td>
            <td class="text-right">Q {{ number_format($alquiler->total, 2) }}</td>
            <td class="text-right">Q {{ number_format($pagado, 2) }}</td>
            <td class="text-right">Q {{ number_format($saldo, 2) }}</td>
            <td>{{ $alquiler->observaciones }}</td>
        </tr>
    @endforeach

    <tr class="total-row">
        <td colspan="3" class="text-right">TOTALES</td>
        <td class="text-center">{{ $totalTogas }}</td>
        <td colspan="11"></td>
        <td class="text-right">Q {{ number_format($totalGeneral, 2) }}</td>
        <td class="text-right">Q {{ number_format($totalPagado, 2) }}</td>
        <td class="text-right">Q {{ number_format($totalSaldo, 2) }}</td>
        <td></td>
    </tr>

    {{-- Resumen: togas alquiladas por talla --}}
    <tr><td colspan="19" style="border: none;"></td></tr>
    <tr>
        <td colspan="4" class="titulo">TOGAS ALQUILADAS POR TALLA</td>
    </tr>
    <tr>
        <td colspan="4" class="subtitulo">No incluye alquileres cancelados</td>
    </tr>
    <tr>
        <th>Talla</th>
        <th>Estándar</th>
        <th>Universitaria</th>
        <th>Total</th>
    </tr>
    @forelse($togasPorTalla as $talla => $cantidades)
        <tr>
            <td class="text-center"><strong>Talla {{ $talla }}</strong></td>
            <td class="text-center">{{ $cantidades['ESTANDAR'] }}</td>
            <td class="text-center">{{ $cantidades['UNIVERSITARIA'] }}</td>
            <td class="text-center"><strong>{{ $cantidades['total'] }}</strong></td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-center">Sin togas</td></tr>
    @endforelse
    <tr class="total-row">
        <td class="text-center">Total</td>
        <td class="text-center">{{ collect($togasPorTalla)->sum('ESTANDAR') }}</td>
        <td class="text-center">{{ collect($togasPorTalla)->sum('UNIVERSITARIA') }}</td>
        <td class="text-center">{{ collect($togasPorTalla)->sum('total') }}</td>
    </tr>
</table>

</body>
</html>