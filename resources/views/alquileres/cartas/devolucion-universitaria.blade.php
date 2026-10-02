{{--
    Carta de devolución - TOGAS UNIVERSITARIAS
    Coordenadas medidas sobre la plantilla (carta de 8.5 x 11 in, 300 dpi).
    Regla usada: top = (y del subrayado) - 0.13in, left/width = extremos del subrayado.

    Variables heredadas de devolucion.blade.php: $alquiler, $fechaActa, $mesActa
    Variables pasadas por @include: $tallas, $totalTogas, $incluyeBirreteYCollarin
--}}

<div class="sheet devolucion-universitaria" style="background-image: url('{{ asset('plantillas/carta-devolucion-universitaria.png') }}');">

    {{-- "a los ___ días del mes de ___ del año ___ siendo las ___ horas" --}}
    <div class="campo campo-center" style="left: 2.15in; top: 1.86in; width: 0.35in;">
        {{ $fechaActa->format('d') }}
    </div>

    <div class="campo campo-center" style="left: 3.46in; top: 1.86in; width: 1.03in;">
        {{ $mesActa }}
    </div>

    <div class="campo campo-center" style="left: 4.99in; top: 1.86in; width: 0.56in;">
        {{ $fechaActa->format('Y') }}
    </div>

    <div class="campo campo-center" style="left: 6.19in; top: 1.86in; width: 0.63in;">
        {{ $fechaActa->format('H:i') }}
    </div>

    {{-- Tabla de tallas devueltas: solo 16 (XS), S, M, L y TOTAL (fila de datos centrada en y = 3.62in) --}}
    <div class="campo campo-center campo-bold" style="left: 2.83in; top: 3.55in; width: 0.50in;">
        {{ $tallas['16'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 3.42in; top: 3.55in; width: 0.50in;">
        {{ $tallas['S'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.00in; top: 3.55in; width: 0.50in;">
        {{ $tallas['M'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.58in; top: 3.55in; width: 0.50in;">
        {{ $tallas['L'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.16in; top: 3.55in; width: 0.50in;">
        {{ $totalTogas ?: '' }}
    </div>

    {{-- "(Estas togas no [ ] si [ ] incluyen birrete y collarín.)" --}}
    @if(!$incluyeBirreteYCollarin)
        <div class="campo-check" style="left: 1.353in; top: 4.343in; width: 0.15in; height: 0.15in; line-height: 0.15in;">✓</div>
    @else
        <div class="campo-check" style="left: 1.623in; top: 4.357in; width: 0.15in; height: 0.15in; line-height: 0.15in;">✓</div>
    @endif

</div>