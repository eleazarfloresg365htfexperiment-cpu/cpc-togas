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
        {{ $alquileres->total() }} registros
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
    <form method="GET" action="{{ route('alquileres.index') }}">
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
                    <option value="EN_FABRICACION" {{ request('estado') == 'EN_FABRICACION' ? 'selected' : '' }}>
                        En fabricación
                    </option>
                    <option value="LISTO_PARA_ENTREGA" {{ request('estado') == 'LISTO_PARA_ENTREGA' ? 'selected' : '' }}>
                        Listos para entrega
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

                <a href="{{ route('alquileres.index') }}" class="btn btn-outline-secondary flex-fill">
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
        <div class="stat-value">{{ $resumen['total'] }}</div>
        <div class="stat-sub">Registros creados</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🕒</div>
        <div class="stat-label">Por entregar</div>
        <div class="stat-value">{{ $resumen['porEntregar'] }}</div>
        <div class="stat-sub">
            Reservados y en fabricación
            @if($resumen['enFabricacion'] > 0)
                · {{ $resumen['enFabricacion'] }} en fabricación
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🚚</div>
        <div class="stat-label">Entregados</div>
        <div class="stat-value">{{ $resumen['entregados'] }}</div>
        <div class="stat-sub">Actualmente fuera</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Por cobrar</div>
        <div class="stat-value">
            Q {{ number_format($resumen['porCobrar'], 2) }}
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
            {{ $alquileres->total() }} registros
        </span>
    </div>

    @if($alquileres->isNotEmpty())
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
                                @elseif($alquiler->estado === 'EN_FABRICACION')
                                    <span class="badge-soft" style="background:#ede9fe; color:#5b21b6;">EN FABRICACIÓN</span>
                                @elseif($alquiler->estado === 'LISTO_PARA_ENTREGA')
                                    <span class="badge-soft" style="background:#ccfbf1; color:#115e59;">LISTO PARA ENTREGA</span>
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

                                    @if($alquiler->isEntregable())
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

                                    @endif

                                    @if($alquiler->puedeEditarse())
                                        <a href="{{ route('alquileres.edit', $alquiler->id) }}"
                                           class="btn btn-sm btn-outline-dark rounded-pill action-main-btn">
                                            ✏️ Editar
                                        </a>
                                    @endif

                                    @if($alquiler->puedeCancelarse())
                                        <form action="{{ route('alquileres.cancelar', $alquiler->id) }}"
                                              method="POST"
                                              class="d-inline form-cancelar-alquiler"
                                              data-recibo="{{ $alquiler->codigo_recibo }}"
                                              data-pagado="{{ number_format((float) $alquiler->pagos->sum('monto'), 2, '.', '') }}">
                                            @csrf
                                            <input type="hidden" name="motivo_cancelacion">
                                            <input type="hidden" name="responsable">

                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill action-main-btn">
                                                ❌ Cancelar
                                            </button>
                                        </form>
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

        <div class="mt-3">
            {{ $alquileres->links() }}
        </div>
    @else
        <div class="alert alert-light border rounded-4 mb-0">
            No hay alquileres registrados todavía.
        </div>
    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.form-cancelar-alquiler').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const pagado = Number(form.dataset.pagado || 0);
                const avisoPagos = pagado > 0
                    ? `<div class="alert alert-warning text-start small mb-3">
                           Tiene <strong>Q ${pagado.toFixed(2)}</strong> en pagos, que quedarán
                           <strong>retenidos, sin reembolso</strong>.
                       </div>`
                    : '';

                Swal.fire({
                    title: `¿Cancelar el alquiler ${form.dataset.recibo}?`,
                    html: `${avisoPagos}
                        <textarea id="swalMotivoIdx" class="swal2-textarea m-0 w-100" rows="3"
                                  placeholder="Motivo de la cancelación (obligatorio)"></textarea>
                        <input id="swalResponsableIdx" class="swal2-input m-0 mt-2 w-100"
                               placeholder="Quién cancela (opcional)">`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Volver',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    focusConfirm: false,
                    preConfirm: () => {
                        const motivo = document.getElementById('swalMotivoIdx').value.trim();

                        if (!motivo) {
                            Swal.showValidationMessage('Escribe el motivo de la cancelación.');
                            return false;
                        }

                        return {
                            motivo,
                            responsable: document.getElementById('swalResponsableIdx').value.trim(),
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.querySelector('[name="motivo_cancelacion"]').value = result.value.motivo;
                        form.querySelector('[name="responsable"]').value = result.value.responsable;
                        form.submit();
                    }
                });
            });
        });
    });
</script>

@endsection