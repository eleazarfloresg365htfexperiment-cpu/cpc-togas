{{--
    Carta de compromiso - TOGAS UNIVERSITARIAS
    Coordenadas medidas sobre la plantilla (carta de 8.5 x 11 in, 300 dpi).
    Regla usada: top = (y del subrayado) - 0.13in, left/width = extremos del subrayado.

    Variables heredadas de terminos.blade.php:
    $alquiler, $clienteNombre, $fechaActual, $mesActual, $fechaAlquiler, $mesAlquiler,
    $fechaDevolucion, $mesDevolucion, $fechaEntrega, $mesEntrega,
    $horaEntregaMostrarInicio, $horaEntregaMostrarFin, $horaDevolucionProgramada
    Variables pasadas por @include: $tallas, $totalTogas, $incluyeAccesorios
--}}

<div class="sheet universitaria-p1">

    {{-- Fecha superior: "En el municipio de ___, a los ___ días del mes de ___ de ___" --}}
    <div class="campo campo-center" style="left: 1.57in; top: 1.47in; width: 0.62in;">
        Jalapa
    </div>

    <div class="campo campo-center" style="left: 2.55in; top: 1.47in; width: 0.27in;">
        {{ $fechaActual->format('d') }}
    </div>

    <div class="campo campo-center" style="left: 3.72in; top: 1.47in; width: 1.25in;">
        {{ $mesActual }}
    </div>

    <div class="campo campo-center" style="left: 5.17in; top: 1.47in; width: 0.41in;">
        {{ $fechaActual->format('Y') }}
    </div>

    {{-- Representante o encargado(a) del arrendador --}}
    <div class="campo campo-sm" style="left: 2.32in; top: 2.27in; width: 3.00in;">
        {{ $alquiler->representante_alquiler }}
    </div>

    {{-- Arrendatario(a) --}}
    <div class="campo campo-sm" style="left: 1.54in; top: 3.39in; width: 3.70in;">
        {{ $clienteNombre }}
    </div>

    {{-- Carrera y Universidad (se toma de "Institución representada" del alquiler) --}}
    <div class="campo campo-sm" style="left: 1.81in; top: 3.55in; width: 3.40in;">
        {{ $alquiler->institucion_representada }}
    </div>

    <div class="campo campo-sm" style="left: 1.57in; top: 3.71in; width: 2.60in;">
        {{ $alquiler->cliente->dpi }}
    </div>

    <div class="campo campo-sm" style="left: 1.09in; top: 3.87in; width: 4.20in;">
        {{ $alquiler->cliente->direccion }}
    </div>

    <div class="campo campo-sm" style="left: 1.08in; top: 4.03in; width: 2.70in;">
        {{ $alquiler->cliente->telefono }}
    </div>

    {{-- Tabla de tallas: solo 16 (XS), S, M, L y TOTAL (fila de datos centrada en y = 6.14in) --}}
    <div class="campo campo-center campo-bold" style="left: 2.83in; top: 6.07in; width: 0.50in;">
        {{ $tallas['16'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 3.42in; top: 6.07in; width: 0.50in;">
        {{ $tallas['S'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.00in; top: 6.07in; width: 0.50in;">
        {{ $tallas['M'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.58in; top: 6.07in; width: 0.50in;">
        {{ $tallas['L'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.16in; top: 6.07in; width: 0.50in;">
        {{ $totalTogas ?: '' }}
    </div>

    {{-- "(Estas togas no [ ] si [ ] incluyen capelo, borla, capa y collarín.)" --}}
    @if(!$incluyeAccesorios)
        <div class="campo-check" style="position: absolute; left: 1.353in; top: 6.543in; width: 0.15in; height: 0.15in; line-height: 0.15in;">✓</div>
    @else
        <div class="campo-check" style="position: absolute; left: 1.623in; top: 6.557in; width: 0.15in; height: 0.15in; line-height: 0.15in;">✓</div>
    @endif

    {{-- SEGUNDA: período del alquiler (desde el día ___ de ___ de ___ hasta el día ___ de ___ de ___) --}}
    <div class="campo campo-center campo-sm" style="left: 2.70in; top: 7.36in; width: 0.27in;">
        {{ optional($fechaAlquiler)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 3.17in; top: 7.36in; width: 1.04in;">
        {{ $mesAlquiler }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 4.40in; top: 7.36in; width: 0.42in;">
        {{ optional($fechaAlquiler)->format('Y') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 5.50in; top: 7.36in; width: 0.34in;">
        {{ optional($fechaDevolucion)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 6.04in; top: 7.36in; width: 1.18in;">
        {{ $mesDevolucion }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 7.41in; top: 7.36in; width: 0.56in;">
        {{ optional($fechaDevolucion)->format('Y') }}
    </div>

    {{-- Recogerá el día ___ del mes de ___ de ___. En un horario de ___ a ___ --}}
    <div class="campo campo-center campo-sm" style="left: 2.72in; top: 7.52in; width: 0.49in;">
        {{ optional($fechaEntrega)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 3.82in; top: 7.52in; width: 1.24in;">
        {{ $mesEntrega }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 5.25in; top: 7.52in; width: 0.63in;">
        {{ optional($fechaEntrega)->format('Y') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 6.88in; top: 7.52in; width: 0.55in;">
        {{ $horaEntregaMostrarInicio }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 7.55in; top: 7.52in; width: 0.41in;">
        {{ $horaEntregaMostrarFin }}
    </div>

    {{-- (fecha de entrega: ___) = fecha y hora de devolución programada --}}
    <div class="campo campo-center campo-sm" style="left: 5.91in; top: 7.68in; width: 2.01in;">
        {{ optional($fechaDevolucion)->format('d/m/Y') }}
        {{ $horaDevolucionProgramada ? ' ' . $horaDevolucionProgramada : '' }}
    </div>

</div>

<div class="sheet universitaria-p2">
    {{-- Página 2 (cláusulas y firmas): no tiene campos que llenar, solo la plantilla de fondo --}}
</div>