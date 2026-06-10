@extends('layouts.app')

@section('title', 'Alquileres')
@section('page_title', '🧾 Alquileres')
@section('page_subtitle', 'Administra reservas, entregas, devoluciones, pagos y recibos')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📋 Listado de alquileres</div>
        <p class="text-muted mb-0">
            Consulta el estado de los alquileres registrados y realiza acciones rápidas.
        </p>
    </div>

    <a href="{{ route('alquileres.create') }}" class="btn btn-primary rounded-pill">
        ➕ Nuevo alquiler
    </a>
</div>

<div class="d-flex gap-2 flex-wrap align-items-center">
    <span class="badge text-bg-light rounded-pill px-3 py-2">
        {{ $alquileres->count() }} registros
    </span>

    <a href="{{ route('exportaciones.alquileres.excel', request()->query()) }}"
        class="btn btn-outline-success rounded-pill">
        📊 Excel
    </a>

    <a href="{{ route('exportaciones.alquileres.pdf', request()->query()) }}"
       class="btn btn-outline-danger rounded-pill">
        📄 PDF
    </a>
</div>

<div class="page-card mb-4">
    <form method="GET" action="{{ route('alquileres.web') }}">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="form-label fw-semibold">Buscar alquiler</label>
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Recibo, cliente, teléfono o DPI..."
                    value="{{ request('buscar') }}"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Estado del alquiler</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="RESERVADO" {{ request('estado') == 'RESERVADO' ? 'selected' : '' }}>
                        Reservados
                    </option>
                    <option value="ENTREGADO" {{ request('estado') == 'ENTREGADO' ? 'selected' : '' }}>
                        Entregados
                    </option>
                    <option value="DEVUELTO" {{ request('estado') == 'DEVUELTO' ? 'selected' : '' }}>
                        Devueltos
                    </option>
                    <option value="CANCELADO" {{ request('estado') == 'CANCELADO' ? 'selected' : '' }}>
                        Cancelados
                    </option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Estado de pago</label>
                <select name="estado_pago" class="form-select">
                    <option value="">Todos</option>
                    <option value="PENDIENTE" {{ request('estado_pago') == 'PENDIENTE' ? 'selected' : '' }}>
                        Pendientes
                    </option>
                    <option value="PARCIAL" {{ request('estado_pago') == 'PARCIAL' ? 'selected' : '' }}>
                        Parciales
                    </option>
                    <option value="PAGADO" {{ request('estado_pago') == 'PAGADO' ? 'selected' : '' }}>
                        Pagados
                    </option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary flex-fill">
                    Filtrar
                </button>

                <a href="{{ route('alquileres.web') }}" class="btn btn-outline-secondary flex-fill">
                    Limpiar
                </a>
            </div>

        </div>
    </form>
</div>

<div class="stats-grid mb-4">

    <div class="stat-card">
        <div class="stat-icon">🧾</div>
        <div class="stat-label">Total alquileres</div>
        <div class="stat-value">{{ $alquileres->count() }}</div>
        <div class="stat-sub">Registros creados</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🕒</div>
        <div class="stat-label">Reservados</div>
        <div class="stat-value">{{ $alquileres->where('estado', 'RESERVADO')->count() }}</div>
        <div class="stat-sub">Pendientes de entrega</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🚚</div>
        <div class="stat-label">Entregados</div>
        <div class="stat-value">{{ $alquileres->where('estado', 'ENTREGADO')->count() }}</div>
        <div class="stat-sub">Actualmente fuera</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Por cobrar</div>
        <div class="stat-value">
            Q {{ number_format($alquileres->where('estado', '!=', 'CANCELADO')->sum('saldo_pendiente'), 2) }}
        </div>
        <div class="stat-sub">Excluye cancelados</div>
    </div>

</div>

<div class="page-card p-3 p-md-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div>
            <div class="section-title mb-1">🧾 Alquileres registrados</div>
            <p class="text-muted mb-0">
                Vista general de recibos, clientes, pagos y estados.
            </p>
        </div>

        <span class="badge text-bg-light rounded-pill px-3 py-2">
            {{ $alquileres->count() }} registros
        </span>
    </div>

    @if($alquileres->count() > 0)
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-nowrap">Recibo</th>
                        <th>Cliente</th>
                        <th class="text-nowrap">Fechas</th>
                        <th class="text-nowrap">Estado</th>
                        <th class="text-nowrap">Pago</th>
                        <th class="text-nowrap">Total</th>
                        <th class="text-nowrap">Saldo</th>
                        <th class="text-end text-nowrap">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($alquileres as $alquiler)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $alquiler->codigo_recibo }}</div>
                                <small class="text-muted">
                                    ID: {{ $alquiler->id }}
                                </small>
                            </td>

                            <td>
                                @if($alquiler->cliente)
                                    <div class="fw-bold">
                                        {{ $alquiler->cliente->nombres }} {{ $alquiler->cliente->apellidos }}
                                    </div>

                                    @if($alquiler->cliente->telefono)
                                        <small class="text-muted">{{ $alquiler->cliente->telefono }}</small>
                                    @else
                                        <small class="text-muted">Sin teléfono</small>
                                    @endif
                                @else
                                    <span class="text-muted">Cliente no encontrado</span>
                                @endif
                            </td>

                            <td>
                                <div>
                                    <strong>Reserva:</strong>
                                    {{ optional($alquiler->fecha_alquiler)->format('d/m/Y') ?? $alquiler->fecha_alquiler }}
                                </div>

                                <small class="text-muted">
                                    Entrega:
                                    {{ $alquiler->fecha_entrega ? \Carbon\Carbon::parse($alquiler->fecha_entrega)->format('d/m/Y') : 'Sin fecha' }}
                                </small>

                                <br>

                                <small class="text-muted">
                                    Devolución:
                                    {{ $alquiler->fecha_devolucion_programada ? \Carbon\Carbon::parse($alquiler->fecha_devolucion_programada)->format('d/m/Y') : 'Sin fecha' }}
                                </small>
                            </td>

                            <td>
                                @if($alquiler->estado === 'RESERVADO')
                                    <span class="badge-soft badge-ajuste">RESERVADO</span>
                                @elseif($alquiler->estado === 'ENTREGADO')
                                    <span class="badge-soft badge-alquiler">ENTREGADO</span>
                                @elseif($alquiler->estado === 'DEVUELTO')
                                    <span class="badge-soft badge-entrada">DEVUELTO</span>
                                @elseif($alquiler->estado === 'CANCELADO')
                                    <span class="badge-soft badge-danger-soft">CANCELADO</span>
                                @else
                                    <span class="badge bg-secondary">{{ $alquiler->estado }}</span>
                                @endif
                            </td>

                            <td>
                                @if ($alquiler->estado === 'CANCELADO')
                                    <span class="badge-soft badge-danger-soft">
                                        SIN COBRO
                                    </span>
                                @elseif ($alquiler->estado_pago === 'PAGADO')
                                    <span class="badge-soft" style="background:#dcfce7; color:#166534;">
                                        PAGADO
                                    </span>
                                @elseif ($alquiler->estado_pago === 'PARCIAL')
                                    <span class="badge-soft" style="background:#fef3c7; color:#92400e;">
                                        PARCIAL
                                    </span>
                                @else
                                    <span class="badge-soft badge-danger-soft">
                                        PENDIENTE
                                    </span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                <strong>Q {{ number_format($alquiler->total, 2) }}</strong>
                            </td>

                            <td class="text-nowrap">
                                @if ($alquiler->estado === 'CANCELADO')
                                    <span class="text-muted fw-semibold">
                                        Anulado
                                    </span>
                                @else
                                    <span class="{{ $alquiler->saldo_pendiente > 0 ? 'amount-negative' : 'amount-positive' }}">
                                        Q {{ number_format($alquiler->saldo_pendiente, 2) }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">

                                    <a href="{{ route('alquileres.show', $alquiler->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill action-main-btn">
                                        👁️ Ver
                                    </a>

                                    <a href="{{ route('alquileres.recibo', $alquiler->id) }}"
                                       class="btn btn-sm btn-outline-secondary rounded-pill action-main-btn"
                                       target="_blank">
                                        🖨️ Recibo
                                    </a>

                                    @if($alquiler->estado !== 'CANCELADO' && $alquiler->saldo_pendiente > 0)
                                        <a href="{{ route('pagos.create', $alquiler->id) }}"
                                           class="btn btn-sm btn-outline-success rounded-pill action-main-btn">
                                            💰 Pagar
                                        </a>
                                    @endif

                                    @if($alquiler->estado === 'RESERVADO')
                                        <form action="{{ route('alquileres.entregar', $alquiler->id) }}"
                                              method="POST"
                                              class="d-inline confirm-action-form"
                                              data-title="¿Entregar alquiler?"
                                              data-text="Se descontará el inventario disponible y el alquiler pasará a ENTREGADO."
                                              data-icon="question"
                                              data-confirm="Sí, entregar"
                                              data-cancel="Cancelar">
                                            @csrf

                                            <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill action-main-btn">
                                                🚚 Entregar
                                            </button>
                                        </form>

                                        @if($alquiler->saldo_pendiente <= 0)
                                            <form action="{{ route('alquileres.cancelar', $alquiler->id) }}"
                                                  method="POST"
                                                  class="d-inline confirm-action-form"
                                                  data-title="¿Cancelar alquiler?"
                                                  data-text="El alquiler será marcado como CANCELADO. Esta acción solo debe hacerse si no corresponde continuar con la reserva."
                                                  data-icon="warning"
                                                  data-confirm="Sí, cancelar"
                                                  data-cancel="Volver">
                                                @csrf

                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill action-main-btn">
                                                    ❌ Cancelar
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    @if($alquiler->estado === 'ENTREGADO')
                                        <form action="{{ route('alquileres.devolver', $alquiler->id) }}"
                                              method="POST"
                                              class="d-inline confirm-action-form"
                                              data-title="¿Registrar devolución?"
                                              data-text="Se restaurará el inventario disponible y el alquiler pasará a DEVUELTO."
                                              data-icon="question"
                                              data-confirm="Sí, devolver"
                                              data-cancel="Cancelar">
                                            @csrf

                                            <button type="submit" class="btn btn-sm btn-outline-info rounded-pill action-main-btn">
                                                🔁 Devolver
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    @else
        <div class="alert alert-light border rounded-4 mb-0">
            No hay alquileres registrados todavía.
        </div>
    @endif

</div>

@endsection