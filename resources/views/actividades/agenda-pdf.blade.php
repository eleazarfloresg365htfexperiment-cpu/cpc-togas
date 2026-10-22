<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda {{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}</title>
    <style>
        @page { margin: 22px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .sub { color: #4b5563; margin-bottom: 10px; }
        .resumen { margin-bottom: 10px; }
        .resumen span { display: inline-block; margin-right: 14px; }
        h2 { font-size: 11.5px; margin: 12px 0 4px; padding: 4px 6px; background: #eef2ff; border-left: 3px solid #4f46e5; }
        h2.alerta { background: #fef2f2; border-left-color: #dc2626; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { border: 1px solid #d1d5db; padding: 3px 5px; vertical-align: top; text-align: left; }
        th { background: #f3f4f6; font-size: 8.5px; }
        .tipo { width: 70px; font-weight: bold; }
        .hora { width: 62px; }
        .recibo { width: 92px; }
        .saldo { width: 70px; text-align: right; }
        .estado { width: 70px; }
        .hecho td { color: #6b7280; }
        .rojo { color: #b91c1c; font-weight: bold; }
        .pie { margin-top: 12px; color: #6b7280; font-size: 8px; }
    </style>
</head>
<body>
    <h1>Agenda de entregas y devoluciones</h1>
    <div class="sub">
        Centro Profesional de Cómputo CPC ·
        del {{ $desde->translatedFormat('l j \d\e F') }} al {{ $hasta->translatedFormat('l j \d\e F \d\e Y') }}
    </div>

    <div class="resumen">
        <span><strong>Entregas:</strong> {{ $agenda['totales']['entregas'] }} ({{ $agenda['totales']['togas_entrega'] }} togas)</span>
        <span><strong>Devoluciones:</strong> {{ $agenda['totales']['devoluciones'] }} ({{ $agenda['totales']['togas_devolucion'] }} togas)</span>
        <span><strong>Atrasados:</strong> {{ count($agenda['atrasados']['entregas']) + count($agenda['atrasados']['devoluciones']) }}</span>
    </div>

    @if(count($agenda['atrasados']['entregas']) || count($agenda['atrasados']['devoluciones']))
        <h2 class="alerta">Atrasados</h2>
        <table>
            <tr><th class="tipo">Tipo</th><th class="hora">Debía</th><th class="recibo">Recibo</th><th>Cliente / togas</th><th class="saldo">Saldo</th></tr>
            @foreach($agenda['atrasados']['devoluciones'] as $item)
                <tr>
                    <td class="tipo rojo">No devuelto</td>
                    <td class="hora">{{ $item['fecha']?->format('d/m/Y') }}</td>
                    <td class="recibo">{{ $item['recibo'] }}</td>
                    <td>{{ $item['cliente'] }}{{ $item['telefono'] ? ' · ' . $item['telefono'] : '' }}<br>{{ $item['togas'] }} toga(s) — {{ $item['tallas'] }}</td>
                    <td class="saldo">{{ $item['saldo'] > 0 ? 'Q ' . number_format($item['saldo'], 2) : '—' }}</td>
                </tr>
            @endforeach
            @foreach($agenda['atrasados']['entregas'] as $item)
                <tr>
                    <td class="tipo rojo">Sin entregar</td>
                    <td class="hora">{{ $item['fecha']?->format('d/m/Y') }}</td>
                    <td class="recibo">{{ $item['recibo'] }}</td>
                    <td>{{ $item['cliente'] }}{{ $item['telefono'] ? ' · ' . $item['telefono'] : '' }}<br>{{ $item['togas'] }} toga(s) — {{ $item['tallas'] }}</td>
                    <td class="saldo">{{ $item['saldo'] > 0 ? 'Q ' . number_format($item['saldo'], 2) : '—' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @php $hayAlgo = false; @endphp

    @foreach($agenda['dias'] as $dia)
        @continue(!count($dia['entregas']) && !count($dia['devoluciones']))
        @php $hayAlgo = true; @endphp

        <h2>{{ ucfirst($dia['fecha']->translatedFormat('l j \d\e F')) }}</h2>
        <table>
            <tr><th class="tipo">Tipo</th><th class="hora">Hora</th><th class="recibo">Recibo</th><th>Cliente / togas</th><th class="estado">Estado</th><th class="saldo">Saldo</th></tr>

            @foreach(['entregas' => 'Entrega', 'devoluciones' => 'Devolución'] as $clave => $texto)
                @foreach($dia[$clave] as $item)
                    <tr class="{{ $item['hecho'] ? 'hecho' : '' }}">
                        <td class="tipo">{{ $texto }}</td>
                        <td class="hora">{{ $item['hora'] ?? '—' }}{{ $item['hora_fin'] ? '–' . $item['hora_fin'] : '' }}</td>
                        <td class="recibo">{{ $item['recibo'] }}</td>
                        <td>
                            {{ $item['cliente'] }}{{ $item['telefono'] ? ' · ' . $item['telefono'] : '' }}
                            @if($item['institucion']) <br><em>{{ $item['institucion'] }}</em> @endif
                            <br>{{ $item['togas'] }} toga(s){{ $item['tallas'] ? ' — ' . $item['tallas'] : '' }}
                        </td>
                        <td class="estado">
                            @if($item['hecho'])
                                {{ $clave === 'entregas' ? 'Entregado' : 'Devuelto' }}
                            @elseif($item['atrasado'])
                                <span class="rojo">Atrasado</span>
                            @elseif($clave === 'devoluciones' && $item['estado'] === 'ENTREGADO')
                                Por recibir
                            @else
                                {{ ucfirst(mb_strtolower(str_replace('_', ' ', $item['estado']))) }}
                            @endif
                        </td>
                        <td class="saldo {{ $item['saldo'] > 0 ? 'rojo' : '' }}">{{ $item['saldo'] > 0 ? 'Q ' . number_format($item['saldo'], 2) : 'Pagado' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    @endforeach

    @unless($hayAlgo)
        <p>No hay entregas ni devoluciones en este periodo.</p>
    @endunless

    @if(count($agenda['fabricaciones']))
        <h2>Fabricaciones pendientes</h2>
        <table>
            <tr><th class="hora">Entrega</th><th class="recibo">Recibo</th><th>Producto</th><th class="saldo">Faltan</th></tr>
            @foreach($agenda['fabricaciones'] as $fab)
                <tr>
                    <td class="hora">{{ $fab['fecha_entrega']?->format('d/m/Y') ?? '—' }}</td>
                    <td class="recibo">{{ $fab['recibo'] }}</td>
                    <td>{{ $fab['producto'] }} — {{ $fab['cliente'] }}</td>
                    <td class="saldo">{{ $fab['pendiente'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="pie">
        Generado el {{ now()->format('d/m/Y H:i') }}{{ $generadoPor ? ' por ' . $generadoPor : '' }} · Sistema de control de togas CPC
    </div>
</body>
</html>
