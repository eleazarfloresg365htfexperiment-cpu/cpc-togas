@extends('layouts.app')

@section('title', 'Movimientos de inventario')
@section('page_title', '📦 Movimientos')
@section('page_subtitle', 'Consulta entradas, alquileres, devoluciones y ajustes del inventario')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📋 Historial de movimientos</div>
        <p class="text-muted mb-0">
            Revisa todos los cambios realizados sobre el inventario de productos.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ url('/productos-web/administrar') }}" class="btn btn-primary rounded-pill">
            🛠️ Administrar productos
        </a>

        <a href="{{ route('productos.index') }}" class="btn btn-outline-primary rounded-pill">
            👗 Ver productos
        </a>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap align-items-center">
    <span class="badge text-bg-light rounded-pill px-3 py-2">
        {{ $movimientos->total() }} registros
    </span>

    <a href="{{ route('exportaciones.movimientos.excel', request()->query()) }}"
        class="btn btn-outline-success rounded-pill">
        📊 Excel
    </a>

    <a href="{{ route('exportaciones.movimientos.pdf', request()->query()) }}"
       class="btn btn-outline-danger rounded-pill">
        📄 PDF
    </a>
</div>

<div class="page-card mb-4">
    <form method="GET" action="{{ route('inventario.movimientos') }}">
        <div class="row g-3 align-items-end">

            <div class="col-md-5">
                <label class="form-label fw-semibold">Buscar movimiento</label>
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Producto, código, motivo o referencia..."
                    value="{{ request('buscar') }}"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Tipo de movimiento</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos los movimientos</option>

                    <option value="ENTRADA" {{ request('tipo') == 'ENTRADA' ? 'selected' : '' }}>
                        Entrada
                    </option>

                    <option value="ALQUILER" {{ request('tipo') == 'ALQUILER' ? 'selected' : '' }}>
                        Alquiler
                    </option>

                    <option value="DEVOLUCION" {{ request('tipo') == 'DEVOLUCION' ? 'selected' : '' }}>
                        Devolución
                    </option>

                    <option value="AJUSTE" {{ request('tipo') == 'AJUSTE' ? 'selected' : '' }}>
                        Ajuste
                    </option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary flex-fill">
                    Filtrar
                </button>

                <a href="{{ route('inventario.movimientos') }}" class="btn btn-outline-secondary flex-fill">
                    Limpiar
                </a>
            </div>

        </div>
    </form>
</div>

<div class="stats-grid mb-4">

    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-label">Total movimientos</div>
        <div class="stat-value">{{ $movimientos->count() }}</div>
        <div class="stat-sub">Registros de inventario</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">➕</div>
        <div class="stat-label">Entradas</div>
        <div class="stat-value">{{ $movimientos->where('tipo_movimiento', 'ENTRADA')->count() }}</div>
        <div class="stat-sub">Aumentos de inventario</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🧾</div>
        <div class="stat-label">Alquileres</div>
        <div class="stat-value">{{ $movimientos->where('tipo_movimiento', 'ALQUILER')->count() }}</div>
        <div class="stat-sub">Salidas por alquiler</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🔁</div>
        <div class="stat-label">Devoluciones</div>
        <div class="stat-value">{{ $movimientos->where('tipo_movimiento', 'DEVOLUCION')->count() }}</div>
        <div class="stat-sub">Retornos al inventario</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">⚙️</div>
        <div class="stat-label">Ajustes</div>
        <div class="stat-value">{{ $movimientos->where('tipo_movimiento', 'AJUSTE')->count() }}</div>
        <div class="stat-sub">Correcciones manuales</div>
    </div>

</div>

<div class="page-card p-3 p-md-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div>
            <div class="section-title mb-1">📦 Movimientos registrados</div>
            <p class="text-muted mb-0">
                Listado ordenado del movimiento más reciente al más antiguo.
            </p>
        </div>

        <span class="badge text-bg-light rounded-pill px-3 py-2">
            {{ $movimientos->count() }} registros
        </span>
    </div>

    @if($movimientos->count() > 0)
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-nowrap">Fecha</th>
                        <th>Producto</th>
                        <th class="text-nowrap">Movimiento</th>
                        <th class="text-nowrap">Cantidad</th>
                        <th class="text-nowrap">Stock disponible</th>
                        <th class="text-nowrap">Stock alquilado</th>
                        <th>Motivo</th>
                        <th class="text-nowrap">Referencia</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($movimientos as $movimiento)
                        <tr>
                            <td class="text-nowrap">
                                <div class="fw-bold">
                                    {{ $movimiento->created_at->format('d/m/Y') }}
                                </div>
                                <small class="text-muted">
                                    {{ $movimiento->created_at->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                @if($movimiento->producto)
                                    <div class="fw-bold">
                                        {{ $movimiento->producto->codigo }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $movimiento->producto->nombre }}
                                    </small>
                                @else
                                    <span class="text-muted">Producto no encontrado</span>
                                @endif
                            </td>

                            <td>
                                @if($movimiento->tipo_movimiento === 'ENTRADA')
                                    <span class="badge-soft badge-entrada">ENTRADA</span>
                                @elseif($movimiento->tipo_movimiento === 'AJUSTE')
                                    <span class="badge-soft badge-ajuste">AJUSTE</span>
                                @elseif($movimiento->tipo_movimiento === 'ALQUILER')
                                    <span class="badge-soft badge-alquiler">ALQUILER</span>
                                @elseif($movimiento->tipo_movimiento === 'DEVOLUCION')
                                    <span class="badge-soft badge-devolucion">DEVOLUCIÓN</span>
                                @elseif($movimiento->tipo_movimiento === 'SALIDA')
                                    <span class="badge-soft badge-danger-soft">SALIDA</span>
                                @else
                                    <span class="badge bg-secondary">
                                        {{ $movimiento->tipo_movimiento }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @if($movimiento->cantidad < 0)
                                    <span class="amount-negative">
                                        {{ $movimiento->cantidad }}
                                    </span>
                                @else
                                    <span class="amount-positive">
                                        +{{ $movimiento->cantidad }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="fw-bold">
                                    {{ $movimiento->stock_anterior_disponible }}
                                    →
                                    {{ $movimiento->stock_nuevo_disponible }}
                                </div>

                                @php
                                    $diferenciaDisponible = $movimiento->stock_nuevo_disponible - $movimiento->stock_anterior_disponible;
                                @endphp

                                @if($diferenciaDisponible > 0)
                                    <small class="amount-positive">
                                        +{{ $diferenciaDisponible }}
                                    </small>
                                @elseif($diferenciaDisponible < 0)
                                    <small class="amount-negative">
                                        {{ $diferenciaDisponible }}
                                    </small>
                                @else
                                    <small class="text-muted">
                                        Sin cambio
                                    </small>
                                @endif
                            </td>

                            <td>
                                <div class="fw-bold">
                                    {{ $movimiento->stock_anterior_alquilado }}
                                    →
                                    {{ $movimiento->stock_nuevo_alquilado }}
                                </div>

                                @php
                                    $diferenciaAlquilado = $movimiento->stock_nuevo_alquilado - $movimiento->stock_anterior_alquilado;
                                @endphp

                                @if($diferenciaAlquilado > 0)
                                    <small class="amount-negative">
                                        +{{ $diferenciaAlquilado }}
                                    </small>
                                @elseif($diferenciaAlquilado < 0)
                                    <small class="amount-positive">
                                        {{ $diferenciaAlquilado }}
                                    </small>
                                @else
                                    <small class="text-muted">
                                        Sin cambio
                                    </small>
                                @endif
                            </td>

                            <td>
                                @if($movimiento->motivo)
                                    {{ $movimiento->motivo }}
                                @else
                                    <span class="text-muted">Sin motivo</span>
                                @endif
                            </td>

                            <td>
                                @if($movimiento->referencia)
                                    <span class="badge text-bg-light rounded-pill px-3 py-2">
                                        {{ $movimiento->referencia }}
                                    </span>
                                @else
                                    <span class="text-muted">Sin referencia</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    @else
        <div class="alert alert-light border rounded-4 mb-0">
            No hay movimientos de inventario registrados todavía.
        </div>
    @endif

</div>

@endsection