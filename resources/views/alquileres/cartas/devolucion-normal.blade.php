{{--
    Carta de devolución - TOGAS NORMALES (ESTANDAR)
    Variables heredadas de devolucion.blade.php: $alquiler, $fechaActa, $mesActa
    Variables pasadas por @include: $tallas, $totalTogas, $incluyeBirreteYCollarin
--}}

<div class="sheet devolucion-normal" style="background-image: url('{{ asset('plantillas/carta-devolucion-normal.png') }}');">

    {{-- Fecha del acta --}}
    <div class="campo campo-center" style="left: 2.20in; top: 1.48in; width: 0.30in;">
        {{ $fechaActa->format('d') }}
    </div>

    <div class="campo" style="left: 3.85in; top: 1.48in; width: 0.85in;">
        {{ $mesActa }}
    </div>

    <div class="campo campo-center" style="left: 5.08in; top: 1.48in; width: 0.45in;">
        {{ $fechaActa->format('Y') }}
    </div>

    <div class="campo campo-center" style="left: 6.22in; top: 1.48in; width: 0.58in;">
        {{ $fechaActa->format('H:i') }}
    </div>

    {{-- Tabla de tallas devueltas --}}
    <div class="campo campo-center campo-bold" style="left: 1.13in; top: 2.75in; width: 0.45in;">
        {{ $tallas['4'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 1.70in; top: 2.75in; width: 0.45in;">
        {{ $tallas['6'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 2.29in; top: 2.75in; width: 0.45in;">
        {{ $tallas['8'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 2.86in; top: 2.75in; width: 0.45in;">
        {{ $tallas['10'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 3.45in; top: 2.75in; width: 0.45in;">
        {{ $tallas['12'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.02in; top: 2.75in; width: 0.45in;">
        {{ $tallas['14'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.60in; top: 2.75in; width: 0.45in;">
        {{ $tallas['16'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.18in; top: 2.75in; width: 0.45in;">
        {{ $tallas['S'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.78in; top: 2.75in; width: 0.45in;">
        {{ $tallas['M'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 6.36in; top: 2.75in; width: 0.45in;">
        {{ $tallas['L'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 6.90in; top: 2.75in; width: 0.50in;">
        {{ $totalTogas ?: '' }}
    </div>

    {{-- ¿Incluyen birrete y collarín? --}}
    @if(!$incluyeBirreteYCollarin)
        <div class="campo-check" style="left: 1.36in; top: 3.10in; width: 0.14in;">
            ✓
        </div>
    @else
        <div class="campo-check" style="left: 1.63in; top: 3.10in; width: 0.14in;">
            ✓
        </div>
    @endif

</div>