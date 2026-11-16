<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
        th { background-color: #1d4ed8; color: #ffffff; font-weight: bold; border: 1px solid #1e3a8a; padding: 8px; text-align: center; }
        td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: top; }
        .titulo { background-color: #0f172a; color: #ffffff; font-size: 18px; font-weight: bold; text-align: center; padding: 12px; }
        .subtitulo { background-color: #e2e8f0; color: #334155; text-align: center; font-weight: bold; padding: 8px; }
        .total-row { background-color: #f8fafc; font-weight: bold; }
        .num { text-align: right; }
        .centro { text-align: center; }
        .texto { mso-number-format: "\@"; }
    </style>
</head>
<body>
@php $varios = count($historiales) !== 1; @endphp

{{-- Hoja 1: un renglón por cliente --}}
<table>
    <tr><td colspan="13" class="titulo">{{ $varios ? 'HISTORIAL DE CLIENTES' : 'HISTORIAL DEL CLIENTE' }} - CENTRO PROFESIONAL DE CÓMPUTO CPC</td></tr>
    <tr><td colspan="13" class="subtitulo">Generado el {{ $fechaGeneracion->format('d/m/Y H:i') }} · Los totales no incluyen alquileres cancelados</td></tr>
    <tr>
        <th>Cliente</th>
        <th>Teléfono</th>
        <th>DPI</th>
        <th>Institución</th>
        <th>Dirección</th>
        <th>Estado</th>
        <th>Alquileres</th>
        <th>Togas alquiladas</th>
        <th>Primer alquiler</th>
        <th>Último alquiler</th>
        <th>Total alquilado</th>
        <th>Total pagado</th>
        <th>Saldo pendiente</th>
    </tr>
    @foreach($historiales as $h)
        <tr>
            <td>{{ $h['cliente']->nombres }} {{ $h['cliente']->apellidos }}</td>
            <td class="texto">{{ $h['cliente']->telefono }}</td>
            <td class="texto">{{ $h['cliente']->dpi }}</td>
            <td>{{ $h['cliente']->institucion_representada ?: 'Individual' }}</td>
            <td>{{ $h['cliente']->direccion }}</td>
            <td class="centro">{{ $h['cliente']->activo ? 'Activo' : 'Inactivo' }}</td>
            <td class="centro">{{ $h['resumen']['alquileres'] }}</td>
            <td class="centro">{{ $h['resumen']['togas'] }}</td>
            <td class="centro">{{ $h['resumen']['primer_alquiler']?->format('d/m/Y') }}</td>
            <td class="centro">{{ $h['resumen']['ultimo_alquiler']?->format('d/m/Y') }}</td>
            <td class="num">Q {{ number_format($h['resumen']['total'], 2) }}</td>
            <td class="num">Q {{ number_format($h['resumen']['pagado'], 2) }}</td>
            <td class="num">Q {{ number_format($h['resumen']['saldo'], 2) }}</td>
        </tr>
    @endforeach
    @if($varios)
        <tr class="total-row">
            <td colspan="6" class="num">TOTALES ({{ count($historiales) }} clientes)</td>
            <td class="centro">{{ collect($historiales)->sum('resumen.alquileres') }}</td>
            <td class="centro">{{ collect($historiales)->sum('resumen.togas') }}</td>
            <td colspan="2"></td>
            <td class="num">Q {{ number_format(collect($historiales)->sum('resumen.total'), 2) }}</td>
            <td class="num">Q {{ number_format(collect($historiales)->sum('resumen.pagado'), 2) }}</td>
            <td class="num">Q {{ number_format(collect($historiales)->sum('resumen.saldo'), 2) }}</td>
        </tr>
    @endif

    <tr><td colspan="13" style="border: none;"></td></tr>

    {{-- Detalle: un renglón por alquiler --}}
    <tr><td colspan="13" class="titulo">DETALLE DE ALQUILERES</td></tr>
    <tr>
        <th>Cliente</th>
        <th>Año</th>
        <th>Recibo</th>
        <th>Fecha</th>
        <th>Entrega</th>
        <th>Devolución</th>
        <th>Estado</th>
        <th>Togas</th>
        <th>Tallas</th>
        <th>Accesorios</th>
        <th>Total</th>
        <th>Pagado</th>
        <th>Saldo</th>
    </tr>
    @foreach($historiales as $h)
        @foreach($h['anios'] as $anio)
            @foreach($anio['alquileres'] as $a)
                <tr>
                    <td>{{ $h['cliente']->nombres }} {{ $h['cliente']->apellidos }}</td>
                    <td class="centro">{{ $anio['anio'] }}</td>
                    <td>{{ $a['recibo'] }}</td>
                    <td class="centro">{{ $a['fecha']?->format('d/m/Y') }}</td>
                    <td class="centro">{{ $a['fecha_entrega']?->format('d/m/Y') }}</td>
                    <td class="centro">{{ $a['fecha_devolucion_real']?->format('d/m/Y') ?? ($a['cancelado'] ? '' : 'Pendiente') }}</td>
                    <td class="centro">{{ str_replace('_', ' ', $a['estado']) }}</td>
                    <td class="centro">{{ $a['togas'] }}</td>
                    <td>{{ $a['tallas'] }}</td>
                    <td>{{ $a['accesorios'] }}</td>
                    <td class="num">Q {{ number_format($a['total'], 2) }}</td>
                    <td class="num">Q {{ number_format($a['pagado'], 2) }}</td>
                    <td class="num">Q {{ number_format($a['saldo'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="7" class="num">{{ $h['cliente']->nombres }} {{ $h['cliente']->apellidos }} — total {{ $anio['anio'] }} (sin cancelados)</td>
                <td class="centro">{{ $anio['togas'] }}</td>
                <td colspan="2"></td>
                <td class="num">Q {{ number_format($anio['total'], 2) }}</td>
                <td class="num">Q {{ number_format($anio['pagado'], 2) }}</td>
                <td class="num">Q {{ number_format($anio['saldo'], 2) }}</td>
            </tr>
        @endforeach
    @endforeach
</table>
</body>
</html>
