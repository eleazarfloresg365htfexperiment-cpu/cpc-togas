@extends('layouts.app')

@section('title', 'Detalle del alquiler')

@section('content')

<style>
    .detalle-card {
        border: 0;
        border-radius: 1.25rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
        background: #ffffff;
    }

    .detalle-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
    }

    .badge-soft-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-soft-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-soft-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-soft-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .producto-toggle {
        cursor: pointer;
        transition: background 0.2s ease-in-out;
    }

    .producto-toggle:hover {
        background: #f8fafc !important;
    }

    .producto-toggle .badge {
        white-space: nowrap;
    }

    .producto-resumen-badges {
        min-width: fit-content;
    }

    .producto-detalle-box {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1rem;
        height: 100%;
    }

    .accesorio-linea {
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 0.65rem 0.75rem;
        background: #ffffff;
    }

    .resumen-cobro-card {
        min-width: 0;
    }

    .resumen-cobro-linea {
        padding: 10px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .resumen-cobro-linea span {
        display: block;
        font-size: 0.92rem;
        line-height: 1.25;
        word-break: normal;
        overflow-wrap: normal;
        hyphens: none;
    }

    .resumen-cobro-linea strong {
        display: block;
        margin-top: 4px;
        white-space: nowrap;
        font-weight: 800;
        color: #000;
        text-align: right;
    }

    .resumen-cobro-total span {
        font-size: 1rem;
        font-weight: 900;
    }

    .resumen-cobro-total strong {
        font-size: 1.08rem;
        font-weight: 900;
    }


    .quick-rental-table thead th {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .quick-rental-table tbody td {
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .quick-rental-table tbody tr:last-child td {
        border-bottom: none;
    }

    .quick-rental-table .badge {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 999px;
    }

    @media (max-width: 768px) {
        .producto-toggle {
            text-align: left;
        }

        .producto-toggle .d-flex {
            align-items: flex-start !important;
        }

        .producto-resumen-badges {
            width: 100%;
        }
    }
</style>

@php
    $estadoAlquiler = $alquiler->estado ?? 'N/A';
    $estadoPago = $alquiler->estado_pago ?? 'N/A';

    $saldoPendiente = $alquiler->estado === 'CANCELADO'
        ? 0
        : $alquiler->saldo_pendiente;

    $subtotalTogas = $alquiler->detalles->sum('subtotal');

    $subtotalExtras = $alquiler->detalles->sum(function ($detalle) {
        return $detalle->accesorios
            ? $detalle->accesorios->where('tipo_cobro', 'EXTRA')->sum('total_linea')
            : 0;
    });

    $badgeEstadoAlquiler = match ($estadoAlquiler) {
        'RESERVADO' => 'badge-soft-warning',
        'ENTREGADO' => 'badge-soft-success',
        'DEVUELTO' => 'badge-soft-secondary',
        'CANCELADO' => 'badge-soft-danger',
        default => 'badge-soft-secondary',
    };

    $badgeEstadoPago = match ($estadoPago) {
        'PENDIENTE' => 'badge-soft-danger',
        'PARCIAL' => 'badge-soft-warning',
        'PAGADO' => 'badge-soft-success',
        default => 'badge-soft-secondary',
    };
    $horaEntrega = $alquiler->hora_entrega
        ? \Carbon\Carbon::parse($alquiler->hora_entrega)->format('h:i A')
        : null;

    $horaDevolucionProgramada = $alquiler->hora_devolucion_programada
        ? \Carbon\Carbon::parse($alquiler->hora_devolucion_programada)->format('h:i A')
        : null;
    
    $fechaHoraRealReferencia = now();

    $inicioMora = null;
    $diasMoraSugeridos = 0;
    $montoMoraSugerido = 0;

    if ($alquiler->fecha_devolucion_programada) {
        $inicioMora = $alquiler->fecha_devolucion_programada
            ->copy()
            ->addDay()
            ->setTime(9, 0, 0);

        if ($fechaHoraRealReferencia->greaterThanOrEqualTo($inicioMora)) {
            $segundosRetraso = max(0, $fechaHoraRealReferencia->timestamp - $inicioMora->timestamp);
            $diasCompletos = intdiv($segundosRetraso, 86400);

            $diasMoraSugeridos = $diasCompletos + 1;
            $montoMoraSugerido = $diasMoraSugeridos * 50;
        }
    }
@endphp

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                📋 Alquiler {{ $alquiler->codigo_recibo }}
            </h1>
            <p class="text-muted mb-0">
                Información completa del alquiler registrado.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('alquileres.web') }}" class="btn btn-outline-secondary rounded-pill">
                ← Volver a alquileres
            </a>

            <a href="{{ route('alquileres.recibo', $alquiler->id) }}" class="btn btn-outline-primary rounded-pill">
                🖨️ Ver recibo
            </a>

            <a href="{{ route('alquileres.terminos', $alquiler->id) }}" class="btn btn-outline-primary rounded-pill">
                📄 Carta de compromiso
            </a>

            @if($alquiler->estado !== 'CANCELADO' && $alquiler->saldo_pendiente > 0)
                <a href="{{ route('pagos.create', $alquiler->id) }}" class="btn btn-success rounded-pill">
                    💰 Registrar pago
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger rounded-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- INFORMACIÓN GENERAL --}}
    <div class="row g-4">

        {{-- CLIENTE --}}
        <div class="col-lg-4">
            <div class="detalle-card p-4 h-100">

                <div class="detalle-icon">
                    👤
                </div>

                <h2 class="h5 fw-bold mb-2">
                    {{ trim(($alquiler->cliente->nombres ?? '') . ' ' . ($alquiler->cliente->apellidos ?? '')) }}
                </h2>

                <div class="text-muted mb-3">
                    {{ $alquiler->cliente->telefono ?? 'Sin teléfono' }}
                </div>

                <hr>

                <div class="mb-3">
                    <div class="text-muted small">Código de recibo</div>
                    <div class="fw-bold">
                        {{ $alquiler->codigo_recibo }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Estado del alquiler</div>
                    <span class="badge {{ $badgeEstadoAlquiler }} rounded-pill px-3 py-2">
                        {{ $estadoAlquiler }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Estado de pago</div>

                    @if($alquiler->estado === 'CANCELADO')
                        <span class="badge badge-soft-secondary rounded-pill px-3 py-2">
                            SIN COBRO
                        </span>
                    @else
                        <span class="badge {{ $badgeEstadoPago }} rounded-pill px-3 py-2">
                            {{ $estadoPago }}
                        </span>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-muted small">DPI</div>
                    <div class="fw-semibold">
                        {{ $alquiler->cliente->dpi ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Dirección</div>
                    <div class="fw-semibold">
                        {{ $alquiler->cliente->direccion ?? 'N/A' }}
                    </div>
                </div>

            </div>
        </div>


        {{-- DETALLES RÁPIDOS DEL ALQUILER --}}
        <div class="col-lg-8">
            <div class="detalle-card p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="detalle-icon mb-0" style="width: 44px; height: 44px; font-size: 20px;">
                        📋
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Detalles rápidos del alquiler</h5>
                        <small class="text-muted">Resumen para consulta rápida de tallas, birretes, borlas y carrera.</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 quick-rental-table">
                        <thead>
                            <tr>
                                <th>Talla</th>
                                <th class="text-center">Cantidad</th>
                                <th>Birrete(s)</th>
                                <th>Borla(s)</th>
                                <th>Carrera</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($alquiler->detalles as $detalle)
                                @php
                                    $producto = $detalle->producto;
                                    $talla = $producto?->toga?->talla ?? 'N/A';

                                    $accesoriosDetalle = collect($detalle->accesorios ?? []);

                                    $birretes = $accesoriosDetalle->filter(function ($accesorio) {
                                        return ($accesorio->producto->tipo_producto ?? null) === 'BIRRETE';
                                    });

                                    $birretesTexto = $birretes->map(function ($accesorio) {
                                        $productoBirrete = $accesorio->producto;
                                        $tipo = $productoBirrete?->birrete?->tipo ?? null;

                                        if ($tipo === 'UNIVERSITARIO') {
                                            return 'Universitario x' . $accesorio->cantidad;
                                        }

                                        return ($productoBirrete->nombre ?? 'Birrete') . ' x' . $accesorio->cantidad;
                                    })->implode(', ');

                                    $birreteUniversitario = $birretes->first(function ($accesorio) {
                                        return ($accesorio->producto->birrete->tipo ?? null) === 'UNIVERSITARIO';
                                    });

                                    $tieneBirreteUniversitario = $birreteUniversitario !== null;

                                    $carrera = $tieneBirreteUniversitario
                                        ? ($birreteUniversitario->producto->birrete->carrera ?? null)
                                        : null;

                                    $borlas = $accesoriosDetalle->filter(function ($accesorio) {
                                        return ($accesorio->producto->tipo_producto ?? null) === 'BORLA'
                                            || str_contains(strtoupper($accesorio->producto->nombre ?? ''), 'BORLA');
                                    });

                                    $borlasTexto = $borlas->map(function ($accesorio) {
                                        return ($accesorio->producto->nombre ?? 'Borla') . ' x' . $accesorio->cantidad;
                                    })->implode(', ');
                                @endphp

                                <tr>
                                    <td>
                                        <strong>{{ $talla }}</strong>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">
                                            {{ $detalle->cantidad }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $birretesTexto ?: 'Sin birrete' }}
                                    </td>

                                    <td>
                                        @if($tieneBirreteUniversitario)
                                            {{ $borlasTexto ?: 'Incluida / no detallada' }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($tieneBirreteUniversitario)
                                            {{ $carrera ?: 'No registrada' }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No hay productos registrados en este alquiler.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TARJETAS PRINCIPALES --}}
        <div class="col-12">
            <div class="row g-4">

                <div class="col-md-4">
                    <div class="detalle-card p-4 h-100">
                        <div class="detalle-icon">🗓️</div>

                        <div class="text-muted small">Fecha de reserva</div>
                        <div class="fw-bold fs-4">
                            {{ optional($alquiler->fecha_alquiler)->format('d/m/Y') }}
                        </div>
                        <div class="text-muted small">
                            Fecha registrada para la reserva
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detalle-card p-4 h-100">
                        <div class="detalle-icon">🚚</div>

                        <div class="text-muted small">Entrega programada</div>

                        <div class="fw-bold fs-4">
                            {{ optional($alquiler->fecha_entrega)->format('d/m/Y') }}
                        </div>

                        <div class="fw-semibold text-primary mt-1">
                            @if($horaEntrega)
                                {{ $horaEntrega }}
                            @else
                                <span class="text-muted">Hora no registrada</span>
                            @endif
                        </div>

                        <div class="text-muted small mt-1">
                            Día y hora en que el cliente recibe las togas
                        </div>

                        @if($alquiler->hora_entrega_inicio || $alquiler->hora_entrega_fin)
                            <hr>

                            <div class="text-muted small">Rango de atención / recogida</div>
                            <div class="fw-semibold">
                                {{ $alquiler->hora_entrega_inicio ? \Carbon\Carbon::parse($alquiler->hora_entrega_inicio)->format('h:i A') : '--:--' }}
                                a
                                {{ $alquiler->hora_entrega_fin ? \Carbon\Carbon::parse($alquiler->hora_entrega_fin)->format('h:i A') : '--:--' }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detalle-card p-4 h-100">
                        <div class="detalle-icon">↩️</div>

                        <div class="text-muted small">Devolución programada</div>

                        <div class="fw-bold fs-4">
                            {{ optional($alquiler->fecha_devolucion_programada)->format('d/m/Y') }}
                        </div>

                        <div class="fw-semibold text-warning mt-1">
                            @if($horaDevolucionProgramada)
                                {{ $horaDevolucionProgramada }}
                            @else
                                <span class="text-muted">Hora no registrada</span>
                            @endif
                        </div>

                        <div class="text-muted small mt-1">
                            Día y hora en que el cliente debe devolver las togas
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detalle-card p-4 h-100">
                        <div class="detalle-icon">💰</div>

                        <div class="text-muted small">Total</div>
                        <div class="fw-bold fs-4">
                            Q {{ number_format($alquiler->total, 2) }}
                        </div>
                        <div class="text-muted small">
                            Total del alquiler
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detalle-card p-4 h-100">
                        <div class="detalle-icon">🏢</div>

                        <div class="text-muted small">Saldo pendiente</div>

                        <div class="fw-bold fs-4 text-danger">
                            Q {{ number_format($saldoPendiente, 2) }}
                        </div>

                        <div class="text-muted small">
                            Monto pendiente de pago
                        </div>

                        <hr>

                        <div class="text-muted small">Descuento aplicado</div>

                        <div class="fw-bold fs-5">
                            Q {{ number_format($alquiler->descuento, 2) }}
                        </div>

                        <div class="text-muted small">
                            Rebaja aplicada al alquiler
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detalle-card p-4 h-100 resumen-cobro-card">
                        <h5 class="fw-bold mb-3">💵 Resumen de cobro</h5>

                        <div class="resumen-cobro-linea">
                            <span>Subtotal togas</span>
                            <strong>Q {{ number_format((float) $subtotalTogas, 2) }}</strong>
                        </div>

                        <div class="resumen-cobro-linea">
                            <span>Extras cobrables</span>
                            <strong>Q {{ number_format((float) $subtotalExtras, 2) }}</strong>
                        </div>

                        @if(($alquiler->monto_mora ?? 0) > 0)
                            <div class="resumen-cobro-linea">
                                <span>Mora por devolución tardía</span>
                                <strong>Q {{ number_format((float) $alquiler->monto_mora, 2) }}</strong>
                            </div>
                        @endif

                        <div class="resumen-cobro-linea">
                            <span>Descuento</span>
                            <strong>Q {{ number_format((float) $alquiler->descuento, 2) }}</strong>
                        </div>

                        @if(($alquiler->descuento_mora ?? 0) > 0)
                            <div class="resumen-cobro-linea">
                                <span>Descuento de mora</span>
                                <strong class="text-success">- Q {{ number_format((float) $alquiler->descuento_mora, 2) }}</strong>
                            </div>
                        @endif

                        <div class="resumen-cobro-linea resumen-cobro-total">
                            <span>Total final</span>
                            <strong>Q {{ number_format((float) $alquiler->total, 2) }}</strong>
                        </div>
                    </div>
                </div>

                @if(($alquiler->dias_mora ?? 0) > 0 || ($alquiler->monto_mora ?? 0) > 0)
                    <div class="page-card p-4 mt-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="stat-icon">⏰</div>
                            <div>
                                <h5 class="fw-bold mb-0">Mora por devolución tardía</h5>
                                <div class="text-muted small">
                                    Cargo generado al registrar la devolución del alquiler.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light">
                                    <div class="text-muted small">Días de mora</div>
                                    <div class="fw-bold fs-5">
                                        {{ (int) ($alquiler->dias_mora ?? 0) }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light">
                                    <div class="text-muted small">Mora calculada</div>
                                    <div class="fw-bold fs-5">
                                        Q {{ number_format((float) ($alquiler->monto_mora_calculado ?? 0), 2) }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light">
                                    <div class="text-muted small">Descuento de mora</div>
                                    <div class="fw-bold fs-5">
                                        Q {{ number_format((float) ($alquiler->descuento_mora ?? 0), 2) }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light">
                                    <div class="text-muted small">Mora final cargada</div>
                                    <div class="fw-bold fs-5 text-danger">
                                        Q {{ number_format((float) ($alquiler->monto_mora ?? 0), 2) }}
                                    </div>
                                </div>
                            </div>

                            @if(!empty($alquiler->observacion_mora))
                                <div class="col-12">
                                    <div class="border rounded-4 p-3 bg-light">
                                        <div class="text-muted small mb-1">Observación de mora</div>
                                        <div>
                                            {{ $alquiler->observacion_mora }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- DATOS EXTRA PARA CARTA --}}
    @if($alquiler->institucion_representada || $alquiler->representante_alquiler || $alquiler->fecha_limite_pago_final)
        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="detalle-card p-4">

                    <h5 class="fw-bold mb-3">
                        📄 Datos para carta de compromiso
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Institución representada</div>
                            <div class="fw-semibold">
                                {{ $alquiler->institucion_representada ?? 'No especificada' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="text-muted small">Representante del alquiler</div>
                            <div class="fw-semibold">
                                {{ $alquiler->representante_alquiler ?? 'No especificado' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="text-muted small">Fecha límite de pago final</div>
                            <div class="fw-semibold">
                                {{ optional($alquiler->fecha_limite_pago_final)->format('d/m/Y') ?? 'No definida' }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- PRODUCTOS Y ACCESORIOS EN ANCHO COMPLETO --}}
    <div class="row g-4 mt-1">
        <div class="col-12">

            <div class="detalle-card p-0 overflow-hidden">

                <div class="p-4 border-bottom bg-white">
                    <h5 class="mb-0 fw-bold">
                        🎓 Productos y accesorios del alquiler
                    </h5>
                    <small class="text-muted">
                        Despliega cada toga para ver accesorios incluidos y extras cobrables.
                    </small>
                </div>

                <div class="p-4">

                    @forelse($alquiler->detalles as $detalle)
                        @php
                            $producto = $detalle->producto;

                            $accesoriosIncluidos = $detalle->accesorios
                                ? $detalle->accesorios->where('tipo_cobro', 'INCLUIDO')
                                : collect();

                            $accesoriosExtras = $detalle->accesorios
                                ? $detalle->accesorios->where('tipo_cobro', 'EXTRA')
                                : collect();

                            $totalExtrasDetalle = $accesoriosExtras->sum('total_linea');
                            $cantidadExtrasDetalle = $accesoriosExtras->sum('cantidad');
                            $collapseId = 'detalle-producto-' . $detalle->id;
                        @endphp

                        <div class="border rounded-4 mb-3 overflow-hidden">

                            {{-- Barra horizontal --}}
                            <button
                                class="w-100 border-0 bg-white p-3 text-start producto-toggle"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="false"
                                aria-controls="{{ $collapseId }}"
                            >
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                                    <div>
                                        <div class="fw-bold">
                                            {{ $producto->nombre ?? 'Producto no disponible' }}
                                        </div>

                                        <div class="text-muted small">
                                            Código: {{ $producto->codigo ?? 'N/A' }}
                                            |
                                            Tipo: {{ $producto->tipo_producto ?? 'N/A' }}

                                            @if(($producto->tipo_producto ?? '') === 'TOGA')
                                                |
                                                Talla: {{ $producto->toga->talla ?? 'N/A' }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 align-items-center producto-resumen-badges">

                                        <span class="badge bg-light text-dark border">
                                            Togas: {{ $detalle->cantidad }}
                                        </span>

                                        <span class="badge bg-primary-subtle text-primary border">
                                            Toga Q{{ number_format($detalle->subtotal, 2) }}
                                        </span>

                                        <span class="badge bg-success-subtle text-success border">
                                            Incluidos: {{ $accesoriosIncluidos->count() }}
                                        </span>

                                        <span class="badge bg-warning-subtle text-warning border">
                                            Extras: {{ $cantidadExtrasDetalle }}
                                        </span>

                                        <span class="badge bg-danger-subtle text-danger border">
                                            + Q{{ number_format($totalExtrasDetalle, 2) }}
                                        </span>

                                        <span class="text-muted small">
                                            Ver detalle ▼
                                        </span>

                                    </div>

                                </div>
                            </button>

                            {{-- Contenido desplegable --}}
                            <div class="collapse" id="{{ $collapseId }}">
                                <div class="p-3 border-top bg-light">

                                    <div class="row g-3">

                                        {{-- Datos de toga --}}
                                        <div class="col-lg-4">
                                            <div class="producto-detalle-box">
                                                <div class="fw-bold mb-2">
                                                    📌 Toga principal
                                                </div>

                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted">Cantidad</span>
                                                    <strong>{{ $detalle->cantidad }}</strong>
                                                </div>

                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted">Precio unitario</span>
                                                    <strong>Q{{ number_format($detalle->precio_unitario, 2) }}</strong>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="text-muted small">Subtotal</div>
                                                    <div class="fw-bold">
                                                        Q {{ number_format($alquiler->subtotal, 2) }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="text-muted small">Descuento</div>
                                                    <div class="fw-bold text-danger">
                                                        Q {{ number_format($alquiler->descuento, 2) }}
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="text-muted small">Total final</div>
                                                    <div class="fw-bold text-success">
                                                        Q {{ number_format($alquiler->total, 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Accesorios incluidos --}}
                                        <div class="col-lg-4">
                                            <div class="producto-detalle-box">
                                                <div class="fw-bold mb-2">
                                                    ✅ Accesorios incluidos
                                                </div>

                                                @if($accesoriosIncluidos->count() > 0)
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach($accesoriosIncluidos as $accesorio)
                                                            <div class="accesorio-linea small">
                                                                <div class="fw-semibold">
                                                                    {{ $accesorio->producto->nombre ?? 'Accesorio no disponible' }}
                                                                </div>

                                                                <div class="text-muted">
                                                                    {{ $accesorio->tipo_accesorio }}
                                                                    |
                                                                    Cantidad: {{ $accesorio->cantidad }}
                                                                </div>

                                                                <span class="badge bg-success-subtle text-success border mt-1">
                                                                    Incluido Q0.00
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted small">
                                                        Sin accesorios incluidos.
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Extras cobrables --}}
                                        <div class="col-lg-4">
                                            <div class="producto-detalle-box">
                                                <div class="fw-bold mb-2">
                                                    💰 Extras cobrables
                                                </div>

                                                @if($accesoriosExtras->count() > 0)
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach($accesoriosExtras as $accesorio)
                                                            <div class="accesorio-linea small">
                                                                <div class="fw-semibold">
                                                                    {{ $accesorio->producto->nombre ?? 'Extra no disponible' }}
                                                                </div>

                                                                <div class="text-muted">
                                                                    {{ $accesorio->cantidad }}
                                                                    x
                                                                    Q{{ number_format($accesorio->precio_unitario, 2) }}
                                                                </div>

                                                                <div class="fw-bold mt-1">
                                                                    Q{{ number_format($accesorio->total_linea, 2) }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="border-top pt-2 mt-3 d-flex justify-content-between">
                                                        <span class="fw-semibold">Total extras</span>
                                                        <strong>Q{{ number_format($totalExtrasDetalle, 2) }}</strong>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">
                                                        Sin extras cobrables.
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="alert alert-warning rounded-4 mb-0">
                            Este alquiler no tiene productos registrados.
                        </div>
                    @endforelse

                </div>

            </div>

        </div>
    </div>

    {{-- PAGOS --}}
    <div class="row g-4 mt-1">
        <div class="col-12">

            <div class="detalle-card p-0 overflow-hidden">

                <div class="p-4 border-bottom bg-white">
                    <h5 class="mb-0 fw-bold">
                        💳 Pagos registrados
                    </h5>
                    <small class="text-muted">
                        Historial de pagos aplicados a este alquiler.
                    </small>
                </div>

                <div class="p-4">

                    @if($alquiler->pagos && $alquiler->pagos->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Método</th>
                                        <th>Referencia</th>
                                        <th class="text-end">Monto</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($alquiler->pagos as $pago)
                                        <tr>
                                            <td>
                                                {{ optional($pago->created_at)->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                {{ $pago->metodo_pago }}
                                            </td>
                                            <td>
                                                {{ $pago->referencia ?? 'N/A' }}
                                            </td>
                                            <td class="text-end fw-bold">
                                                Q {{ number_format($pago->monto, 2) }}
                                            </td>
                                            <td>
                                                {{ $pago->observaciones ?? 'Sin observaciones' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-light border rounded-4 mb-0">
                            No hay pagos registrados para este alquiler.
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>

    {{-- OBSERVACIONES --}}
    @if($alquiler->observaciones)
        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="detalle-card p-4">
                    <h5 class="fw-bold mb-2">
                        📝 Observaciones
                    </h5>
                    <p class="mb-0">
                        {{ $alquiler->observaciones }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ACCIONES --}}
    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            <div class="detalle-card p-4 text-center">

                <h5 class="fw-bold mb-3">
                    ⚙️ Acciones del alquiler
                </h5>

                <div class="d-flex flex-wrap justify-content-center gap-2">

                    @if($alquiler->estado === 'RESERVADO')
                        <form action="{{ route('alquileres.entregar', $alquiler->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary rounded-pill">
                                🚚 Entregar alquiler
                            </button>
                        </form>
                    @endif

                    @if($alquiler->estado === 'ENTREGADO')
                        <div class="w-100 mt-3">
                            <div class="alert {{ $diasMoraSugeridos > 0 ? 'alert-warning' : 'alert-success' }} rounded-4 text-start">
                                <div class="fw-bold mb-1">
                                    ↩️ Revisión de devolución
                                </div>

                                @if($diasMoraSugeridos > 0)
                                    <div>
                                        Este alquiler tiene <strong>{{ (int) $diasMoraSugeridos }}</strong> día(s) de mora por devolución tardía.
                                    </div>
                                    <div>
                                        Mora sugerida: <strong>Q {{ number_format($montoMoraSugerido, 2) }}</strong>
                                        <span class="text-muted">(Q50.00 por día)</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        La mora empieza a contar desde las 9:00 AM del día siguiente a la devolución programada.
                                    </div>
                                @else
                                    <div>
                                        Este alquiler no tiene mora por devolución tardía.
                                    </div>
                                @endif
                            </div>

                            <form
                                id="formDevolverAlquiler"
                                action="{{ route('alquileres.devolver', $alquiler->id) }}"
                                method="POST"
                                class="border rounded-4 p-4 text-start bg-light"
                            >
                                @csrf

                                <input type="hidden" name="dias_mora_sugeridos" value="{{ (int) $diasMoraSugeridos }}">
                                <input type="hidden" name="monto_mora_calculado" value="{{ $montoMoraSugerido }}">

                                <div class="row g-3 align-items-start">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Días de mora</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            value="{{ (int) $diasMoraSugeridos }}"
                                            readonly
                                        >
                                        <div class="form-text invisible">
                                            Espacio reservado.
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Mora calculada</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            value="Q {{ number_format($montoMoraSugerido, 2) }}"
                                            readonly
                                        >
                                        <div class="form-text invisible">
                                            Espacio reservado.
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Descuento de mora</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="{{ $montoMoraSugerido }}"
                                            name="descuento_mora"
                                            id="descuento_mora"
                                            class="form-control"
                                            value="{{ old('descuento_mora', 0) }}"
                                        >
                                        <div class="form-text">
                                            Descuento aplicado solo a la mora.
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Mora final a cargar</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            id="monto_mora"
                                            class="form-control"
                                            value="{{ number_format($montoMoraSugerido, 2, '.', '') }}"
                                            readonly
                                        >
                                        <div class="form-text">
                                            Este monto se agregará al total y saldo pendiente del alquiler.
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Observación de mora</label>
                                        <textarea
                                            name="observacion_mora"
                                            class="form-control"
                                            rows="2"
                                            placeholder="Ejemplo: Se aplicó descuento autorizado por administración."
                                        >{{ old('observacion_mora') }}</textarea>
                                    </div>

                                    <div class="col-12 d-flex flex-wrap justify-content-center gap-2">
                                        <button type="submit" class="btn btn-success rounded-pill">
                                            ↩️ Confirmar devolución
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const montoCalculado = Number(@json($montoMoraSugerido));
                                const descuentoInput = document.getElementById('descuento_mora');
                                const montoMoraInput = document.getElementById('monto_mora');
                                const formDevolver = document.getElementById('formDevolverAlquiler');

                                function recalcularMora() {
                                    if (!descuentoInput || !montoMoraInput) {
                                        return;
                                    }

                                    let descuento = Number(descuentoInput.value || 0);

                                    if (descuento < 0) {
                                        descuento = 0;
                                    }

                                    if (descuento > montoCalculado) {
                                        descuento = montoCalculado;
                                        descuentoInput.value = descuento.toFixed(2);
                                    }

                                    const montoFinal = Math.max(montoCalculado - descuento, 0);
                                    montoMoraInput.value = montoFinal.toFixed(2);
                                }

                                if (descuentoInput && montoMoraInput) {
                                    descuentoInput.addEventListener('input', recalcularMora);
                                }

                                if (formDevolver) {
                                    formDevolver.addEventListener('submit', function (event) {
                                        event.preventDefault();

                                        const descuento = Number(descuentoInput?.value || 0);
                                        const moraFinal = Number(montoMoraInput?.value || 0);

                                        Swal.fire({
                                            title: '¿Confirmar devolución?',
                                            html: `
                                                <div class="text-start">
                                                    <p class="mb-2">Se registrará la devolución de este alquiler.</p>
                                                    <p class="mb-1"><strong>Mora calculada:</strong> Q ${montoCalculado.toFixed(2)}</p>
                                                    <p class="mb-1"><strong>Descuento de mora:</strong> Q ${descuento.toFixed(2)}</p>
                                                    <p class="mb-0"><strong>Mora final a cargar:</strong> Q ${moraFinal.toFixed(2)}</p>
                                                </div>
                                            `,
                                            icon: 'question',
                                            showCancelButton: true,
                                            confirmButtonText: 'Sí, confirmar devolución',
                                            cancelButtonText: 'Cancelar',
                                            confirmButtonColor: '#198754',
                                            cancelButtonColor: '#6c757d',
                                            reverseButtons: true
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                formDevolver.submit();
                                            }
                                        });
                                    });
                                }
                            });
                        </script>
                    @endif

                    @if($alquiler->estado === 'RESERVADO')
                        <form
                            action="{{ route('alquileres.cancelar', $alquiler->id) }}"
                            method="POST"
                            onsubmit="return confirm('¿Seguro que deseas cancelar este alquiler?');"
                        >
                            @csrf
                            <button type="submit" class="btn btn-outline-danger rounded-pill">
                                ❌ Cancelar alquiler
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('alquileres.web') }}" class="btn btn-outline-secondary rounded-pill">
                        ← Volver
                    </a>

                </div>

            </div>
        </div>
    </div>

</div>

@endsection