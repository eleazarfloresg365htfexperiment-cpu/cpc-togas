<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de clientes</title>
    <style>
        @page { margin: 24px 28px 34px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111827; }
        .salto { page-break-before: always; }
        .encabezado { border-bottom: 2px solid #4f46e5; padding-bottom: 6px; margin-bottom: 10px; }
        .encabezado .institucion { font-size: 9px; color: #4b5563; text-transform: uppercase; letter-spacing: .5px; }
        h1 { font-size: 17px; margin: 2px 0 0; }
        .sub { color: #4b5563; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        .datos td { padding: 3px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .datos .etq { background: #f3f4f6; font-weight: bold; width: 80px; }
        .resumen { margin: 10px 0; }
        .resumen td { border: 1px solid #c7d2fe; background: #eef2ff; text-align: center; padding: 6px 4px; width: 20%; }
        .resumen .num { font-size: 14px; font-weight: bold; display: block; }
        .resumen .txt { font-size: 8px; color: #4b5563; text-transform: uppercase; }
        h2 { font-size: 12px; margin: 14px 0 4px; padding: 4px 6px; background: #4f46e5; color: #fff; }
        h2 .der { float: right; font-weight: normal; font-size: 9px; }
        .hist th { background: #f3f4f6; border: 1px solid #d1d5db; padding: 3px 4px; font-size: 8.5px; text-align: left; }
        .hist td { border: 1px solid #d1d5db; padding: 3px 4px; vertical-align: top; }
        .num-d { text-align: right; white-space: nowrap; }
        .cancelado td { color: #9ca3af; }
        .rojo { color: #b91c1c; font-weight: bold; }
        .gris { color: #6b7280; }
        .total-anio td { background: #f9fafb; font-weight: bold; }
        .pie { position: fixed; bottom: -22px; left: 0; right: 0; font-size: 8px; color: #6b7280; text-align: center; }
        .firma { margin-top: 26px; font-size: 9px; color: #4b5563; }
    </style>
</head>
<body>
    <div class="pie">
        Centro Profesional de Cómputo CPC · Generado el {{ $fechaGeneracion->format('d/m/Y H:i') }}{{ $generadoPor ? ' por ' . $generadoPor : '' }}
    </div>

    @forelse($historiales as $i => $h)
        @php $cliente = $h['cliente']; $resumen = $h['resumen']; @endphp

        <div class="{{ $i > 0 ? 'salto' : '' }}">
            <div class="encabezado">
                <div class="institucion">Centro Profesional de Cómputo CPC · Historial de alquiler de togas</div>
                <h1>{{ $cliente->nombres }} {{ $cliente->apellidos }}</h1>
                <div class="sub">
                    @if($resumen['primer_alquiler'])
                        Cliente desde el {{ $resumen['primer_alquiler']->format('d/m/Y') }}
                        · último alquiler el {{ $resumen['ultimo_alquiler']->format('d/m/Y') }}
                    @else
                        Sin alquileres registrados
                    @endif
                </div>
            </div>

            <table class="datos">
                <tr>
                    <td class="etq">Teléfono</td><td>{{ $cliente->telefono ?: '—' }}</td>
                    <td class="etq">DPI</td><td>{{ $cliente->dpi ?: '—' }}</td>
                    <td class="etq">Estado</td><td>{{ $cliente->activo ? 'Activo' : 'Inactivo' }}</td>
                </tr>
                <tr>
                    <td class="etq">Institución</td><td colspan="2">{{ $cliente->institucion_representada ?: 'Individual' }}</td>
                    <td class="etq">Dirección</td><td colspan="2">{{ $cliente->direccion ?: '—' }}</td>
                </tr>
                @if($cliente->observaciones)
                    <tr><td class="etq">Observaciones</td><td colspan="5">{{ $cliente->observaciones }}</td></tr>
                @endif
            </table>

            <table class="resumen">
                <tr>
                    <td><span class="num">{{ $resumen['alquileres'] }}</span><span class="txt">Alquileres</span></td>
                    <td><span class="num">{{ $resumen['togas'] }}</span><span class="txt">Togas alquiladas</span></td>
                    <td><span class="num">Q {{ number_format($resumen['total'], 2) }}</span><span class="txt">Total alquilado</span></td>
                    <td><span class="num">Q {{ number_format($resumen['pagado'], 2) }}</span><span class="txt">Total pagado</span></td>
                    <td><span class="num {{ $resumen['saldo'] > 0 ? 'rojo' : '' }}">Q {{ number_format($resumen['saldo'], 2) }}</span><span class="txt">Saldo pendiente</span></td>
                </tr>
            </table>

            @foreach($h['anios'] as $anio)
                <h2>
                    <span class="der">{{ $anio['cantidad'] }} alquiler(es) · {{ $anio['togas'] }} toga(s)</span>
                    {{ $anio['anio'] }}
                </h2>

                <table class="hist">
                    <tr>
                        <th style="width: 98px;">Recibo</th>
                        <th style="width: 52px;">Fecha</th>
                        <th style="width: 52px;">Entrega</th>
                        <th style="width: 58px;">Devolución</th>
                        <th>Qué alquiló</th>
                        <th style="width: 62px;">Estado</th>
                        <th style="width: 54px;" class="num-d">Total</th>
                        <th style="width: 54px;" class="num-d">Pagado</th>
                        <th style="width: 50px;" class="num-d">Saldo</th>
                    </tr>
                    @foreach($anio['alquileres'] as $a)
                        <tr class="{{ $a['cancelado'] ? 'cancelado' : '' }}">
                            <td style="white-space: nowrap;">{{ $a['recibo'] }}</td>
                            <td>{{ $a['fecha']?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $a['fecha_entrega']?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($a['fecha_devolucion_real'])
                                    {{ $a['fecha_devolucion_real']->format('d/m/Y') }}
                                @elseif($a['cancelado'])
                                    —
                                @else
                                    <span class="gris">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $a['togas'] }} toga(s)</strong>{{ $a['tallas'] ? ' — ' . $a['tallas'] : '' }}
                                @if($a['accesorios']) <br><span class="gris">{{ $a['accesorios'] }}</span> @endif
                                @if($a['institucion']) <br><span class="gris">{{ $a['institucion'] }}</span> @endif
                                @if($a['danos']) <br><span class="rojo">{{ $a['danos'] }} daño(s)/extravío(s): Q {{ number_format($a['danos_monto'], 2) }}</span> @endif
                            </td>
                            <td>{{ ucfirst(mb_strtolower(str_replace('_', ' ', $a['estado']))) }}</td>
                            <td class="num-d">Q {{ number_format($a['total'], 2) }}</td>
                            <td class="num-d">Q {{ number_format($a['pagado'], 2) }}</td>
                            <td class="num-d {{ $a['saldo'] > 0 ? 'rojo' : '' }}">Q {{ number_format($a['saldo'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-anio">
                        <td colspan="6" class="num-d">Total {{ $anio['anio'] }} (sin cancelados)</td>
                        <td class="num-d">Q {{ number_format($anio['total'], 2) }}</td>
                        <td class="num-d">Q {{ number_format($anio['pagado'], 2) }}</td>
                        <td class="num-d">Q {{ number_format($anio['saldo'], 2) }}</td>
                    </tr>
                </table>
            @endforeach

            @if(count($historiales) === 1)
                <div class="firma">
                    Documento informativo generado por el sistema de control de togas a solicitud del cliente.
                </div>
            @endif
        </div>
    @empty
        <p>No hay clientes con esos filtros.</p>
    @endforelse
</body>
</html>
