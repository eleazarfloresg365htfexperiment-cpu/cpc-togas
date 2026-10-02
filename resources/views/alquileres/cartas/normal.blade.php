{{--
    Carta de compromiso - TOGAS NORMALES (ESTANDAR)
    Variables heredadas de terminos.blade.php:
    $alquiler, $clienteNombre, $fechaActual, $mesActual, $institucionRepresentada,
    $fechaAlquiler, $mesAlquiler, $fechaEntrega, $mesEntrega, $fechaDevolucion, $mesDevolucion,
    $fechaLimitePago, $mesLimitePago, $horaEntregaMostrarInicio, $horaEntregaMostrarFin,
    $horaDevolucionProgramada, $saldo
    Variables pasadas por @include: $tallas, $totalTogas
--}}

<div class="sheet normal-p1">

    {{-- Fecha superior --}}
    <div class="campo campo-center" style="left: 1.35in; top: 1.48in; width: 1.10in;">
        Jalapa
    </div>

    <div class="campo campo-center" style="left: 2.48in; top: 1.48in; width: 0.42in;">
        {{ $fechaActual->format('d') }}
    </div>

    <div class="campo campo-center" style="left: 3.73in; top: 1.47in; width: 1.15in;">
        {{ $mesActual }}
    </div>

    <div class="campo campo-center" style="left: 5.07in; top: 1.48in; width: 0.58in;">
        {{ $fechaActual->format('Y') }}
    </div>

    {{-- Representante o encargado(a) del arrendador --}}
    <div class="campo campo-sm" style="left: 2.35in; top: 2.28in; width: 3.20in;">
        {{ $alquiler->representante_alquiler }}
    </div>

    {{-- Institución representada --}}
    <div class="campo campo-sm" style="left: 2.73in; top: 3.38in; width: 3.45in;">
        {{ $institucionRepresentada }}
    </div>

    {{-- Arrendatario --}}
    <div class="campo campo-sm" style="left: 2.28in; top: 3.55in; width: 3.45in;">
        {{ $clienteNombre }}
    </div>

    <div class="campo campo-sm" style="left: 1.60in; top: 3.71in; width: 2.30in;">
        {{ $alquiler->cliente->dpi }}
    </div>

    <div class="campo campo-sm" style="left: 1.13in; top: 3.88in; width: 4.70in;">
        {{ $alquiler->cliente->direccion }}
    </div>

    <div class="campo campo-sm" style="left: 1.10in; top: 4.04in; width: 2.35in;">
        {{ $alquiler->cliente->telefono }}
    </div>

    {{-- Tabla de tallas --}}
    <div class="campo campo-center campo-bold" style="left: 1.13in; top: 6.12in; width: 0.45in;">
        {{ $tallas['4'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 1.70in; top: 6.12in; width: 0.45in;">
        {{ $tallas['6'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 2.29in; top: 6.12in; width: 0.45in;">
        {{ $tallas['8'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 2.86in; top: 6.12in; width: 0.45in;">
        {{ $tallas['10'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 3.45in; top: 6.12in; width: 0.45in;">
        {{ $tallas['12'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.02in; top: 6.12in; width: 0.45in;">
        {{ $tallas['14'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 4.60in; top: 6.12in; width: 0.45in;">
        {{ $tallas['16'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.18in; top: 6.12in; width: 0.45in;">
        {{ $tallas['S'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 5.78in; top: 6.12in; width: 0.45in;">
        {{ $tallas['M'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 6.36in; top: 6.12in; width: 0.45in;">
        {{ $tallas['L'] ?: '' }}
    </div>

    <div class="campo campo-center campo-bold" style="left: 6.90in; top: 6.12in; width: 0.50in;">
        {{ $totalTogas ?: '' }}
    </div>

    {{-- Fechas del alquiler --}}
    <div class="campo campo-center campo-sm" style="left: 2.65in; top: 7.36in; width: 0.35in;">
        {{ optional($fechaAlquiler)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 3.25in; top: 7.36in; width: 0.90in;">
        {{ $mesAlquiler }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 4.35in; top: 7.36in; width: 0.55in;">
        {{ optional($fechaAlquiler)->format('Y') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 5.52in; top: 7.36in; width: 0.35in;">
        {{ optional($fechaDevolucion)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 6.20in; top: 7.36in; width: 0.90in;">
        {{ $mesDevolucion }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 7.50in; top: 7.36in; width: 0.45in;">
        {{ optional($fechaDevolucion)->format('Y') }}
    </div>

    {{-- Recogerá --}}
    <div class="campo campo-center campo-sm" style="left: 2.78in; top: 7.52in; width: 0.35in;">
        {{ optional($fechaEntrega)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 3.97in; top: 7.52in; width: 0.95in;">
        {{ $mesEntrega }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 5.30in; top: 7.52in; width: 0.50in;">
        {{ optional($fechaEntrega)->format('Y') }}
    </div>

    {{-- Horario programado de entrega --}}
    <div class="campo campo-center campo-sm" style="left: 6.75in; top: 7.52in; width: 0.75in;">
        {{ $horaEntregaMostrarInicio }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 7.40in; top: 7.52in; width: 0.75in;">
        {{ $horaEntregaMostrarFin }}
    </div>

    {{-- Fecha y hora de devolución programada --}}
    <div class="campo campo-center campo-sm" style="left: 6.20in; top: 7.68in; width: 1.65in;">
        {{ optional($fechaDevolucion)->format('d/m/Y') }}
        {{ $horaDevolucionProgramada ? ' ' . $horaDevolucionProgramada : '' }}
    </div>

    {{-- Valores --}}
    <div class="campo campo-center campo-sm campo-bold" style="left: 3.68in; top: 8.16in; width: 1.05in;">
        {{ number_format($alquiler->total, 2) }}
    </div>

    <div class="campo campo-center campo-sm campo-bold" style="left: 0.58in; top: 8.64in; width: 1.05in;">
        {{ number_format($saldo, 2) }}
    </div>

    {{-- Fecha límite para pago final --}}
    <div class="campo campo-center campo-sm" style="left: 1.90in; top: 8.64in; width: 0.35in;">
        {{ optional($fechaLimitePago)->format('d') }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 2.95in; top: 8.64in; width: 0.95in;">
        {{ $mesLimitePago }}
    </div>

    <div class="campo campo-center campo-sm" style="left: 4.60in; top: 8.64in; width: 0.55in;">
        {{ optional($fechaLimitePago)->format('Y') }}
    </div>

</div>

<div class="sheet normal-p2">
    {{-- Segunda página: solo plantilla de fondo por ahora --}}
</div>