{{--
    Gráfica de barras horizontales para el PDF (se dibuja con tablas, sin
    imágenes ni JavaScript, así siempre sale impresa y con su valor escrito).

    Variables:
      $titulo, $descripcion (opcional)
      $filas: [['etiqueta' => 'M', 'detalle' => 'opcional', 'series' => [['valor' => 12, 'texto' => '12', 'color' => '#4f46e5'], ...]], ...]
      $leyenda (opcional): [['nombre' => 'Ingresos', 'color' => '#059669'], ...]
      $vacio: texto cuando no hay filas
--}}
@php
    $maximo = 0;
    foreach ($filas as $fila) {
        foreach ($fila['series'] as $serie) {
            $maximo = max($maximo, (float) $serie['valor']);
        }
    }
@endphp

<div class="chart-section">
    <div class="chart-title">{{ $titulo }}</div>

    @if(!empty($descripcion))
        <div class="chart-description">{{ $descripcion }}</div>
    @endif

    @if(!empty($leyenda))
        <div class="barras-leyenda">
            @foreach($leyenda as $item)
                <span class="barras-leyenda-item">
                    <span class="barras-cuadro" style="background: {{ $item['color'] }};">&nbsp;&nbsp;&nbsp;</span>
                    {{ $item['nombre'] }}
                </span>
            @endforeach
        </div>
    @endif

    @if(count($filas) && $maximo > 0)
        <table class="barras">
            @foreach($filas as $fila)
                <tr>
                    <td class="barras-etiqueta">
                        {{ $fila['etiqueta'] }}
                        @if(!empty($fila['detalle']))
                            <div class="barras-detalle">{{ $fila['detalle'] }}</div>
                        @endif
                    </td>
                    <td class="barras-zona">
                        @foreach($fila['series'] as $serie)
                            @php $ancho = max(0.5, round(((float) $serie['valor'] / $maximo) * 78, 1)); @endphp
                            <table class="barras-linea">
                                <tr>
                                    <td style="width: {{ $ancho }}%;">
                                        <div class="barra" style="background: {{ $serie['color'] }};">&nbsp;</div>
                                    </td>
                                    <td class="barras-valor">{{ $serie['texto'] }}</td>
                                </tr>
                            </table>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="barras-vacio">{{ $vacio ?? 'No hay datos para graficar en este periodo.' }}</div>
    @endif
</div>
