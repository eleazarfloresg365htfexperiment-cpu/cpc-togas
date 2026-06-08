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

    .resumen-cobro-table td {
        padding: 0.7rem 0;
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

        {{-- TARJETAS PRINCIPALES --}}
        <div class="col-lg-8">
            <div class="row g-4">

                <div class="col-md-4">
                    <div class="detalle-card p-4 h-100">
                        <div class="detalle-icon">🗓️</div>

                        <div class="text-muted small">Fecha de alquiler</div>
                        <div class="fw-bold fs-4">
                            {{ optional($alquiler->fecha_alquiler)->format('d/m/Y') }}
                        </div>
                        <div class="text-muted small">
                            Fecha de creación del alquiler
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detalle-card p-4 h-100">
                        <div class="detalle-icon">🚚</div>

                        <div class="text-muted small">Fecha de entrega</div>
                        <div class="fw-bold fs-4">
                            {{ optional($alquiler->fecha_entrega)->format('d/m/Y') }}
                        </div>
                        <div class="text-muted small">
                            Fecha programada de entrega
                        </div>

                        @if($alquiler->hora_entrega_inicio || $alquiler->hora_entrega_fin)
                            <div class="mt-2 text-muted small">
                                Horario:
                                {{ $alquiler->hora_entrega_inicio ? \Carbon\Carbon::parse($alquiler->hora_entrega_inicio)->format('H:i') : '--:--' }}
                                a
                                {{ $alquiler->hora_entrega_fin ? \Carbon\Carbon::parse($alquiler->hora_entrega_fin)->format('H:i') : '--:--' }}
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
                        <div class="text-muted small">
                            Fecha esperada de devolución
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
                    <div class="detalle-card p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            💵 Resumen de cobro
                        </h5>

                        <table class="table resumen-cobro-table mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold">Subtotal togas</td>
                                    <td class="text-end fw-bold">
                                        Q {{ number_format($subtotalTogas, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-muted fw-semibold">Extras cobrables</td>
                                    <td class="text-end fw-bold">
                                        Q {{ number_format($subtotalExtras, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-muted fw-semibold">Descuento</td>
                                    <td class="text-end fw-bold">
                                        Q {{ number_format($alquiler->descuento, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">Total final</td>
                                    <td class="text-end fw-bold fs-5">
                                        Q {{ number_format($alquiler->total, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>

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

                                                <div class="d-flex justify-content-between small">
                                                    <span class="text-muted">Subtotal toga</span>
                                                    <strong>Q{{ number_format($detalle->subtotal, 2) }}</strong>
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
                        <form action="{{ route('alquileres.devolver', $alquiler->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success rounded-pill">
                                ↩️ Registrar devolución
                            </button>
                        </form>
                    @endif

                    @if(in_array($alquiler->estado, ['RESERVADO', 'ENTREGADO']))
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