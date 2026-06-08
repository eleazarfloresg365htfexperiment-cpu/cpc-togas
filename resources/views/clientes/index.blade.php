@extends('layouts.app')

@section('title', 'Clientes')
@section('page_title', '👥 Clientes')
@section('page_subtitle', 'Administra los clientes registrados para alquileres de togas')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📋 Listado de clientes</div>
        <p class="text-muted mb-0">
            Consulta, registra, edita, desactiva o reactiva clientes del sistema.
        </p>
    </div>

    <a href="{{ route('clientes.create') }}" class="btn btn-primary rounded-pill">
        ➕ Registrar cliente
    </a>
</div>

<div class="page-card mb-4">
    <form method="GET" action="{{ route('clientes.web') }}">
        <div class="row g-3 align-items-end">

            <div class="col-md-6">
                <label class="form-label fw-semibold">Buscar cliente</label>
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Nombre, apellido, teléfono, DPI o dirección..."
                    value="{{ request('buscar') }}"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>
                        Activos
                    </option>
                    <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>
                        Inactivos
                    </option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary flex-fill">
                    Filtrar
                </button>

                <a href="{{ route('clientes.web') }}" class="btn btn-outline-secondary flex-fill">
                    Limpiar
                </a>
            </div>

        </div>
    </form>
</div>

<div class="stats-grid mb-4">

    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-label">Total clientes</div>
        <div class="stat-value">{{ $clientes->count() }}</div>
        <div class="stat-sub">Clientes registrados</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-label">Clientes activos</div>
        <div class="stat-value">{{ $clientes->where('activo', true)->count() }}</div>
        <div class="stat-sub">Disponibles para nuevos alquileres</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">⛔</div>
        <div class="stat-label">Clientes inactivos</div>
        <div class="stat-value">{{ $clientes->where('activo', false)->count() }}</div>
        <div class="stat-sub">No aparecen al crear alquileres</div>
    </div>

</div>

<div class="page-card p-3 p-md-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div>
            <div class="section-title mb-1">👤 Clientes registrados</div>
            <p class="text-muted mb-0">
                Vista general de los clientes guardados en el sistema.
            </p>
        </div>

        <span class="badge text-bg-light rounded-pill px-3 py-2">
            {{ $clientes->count() }} registros
        </span>
    </div>

    @if($clientes->count() > 0)
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>DPI</th>
                        <th>Dirección</th>
                        <th>Observaciones</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($clientes as $cliente)
                        <tr>
                            <td>
                                <div class="fw-bold">
                                    {{ $cliente->nombres }} {{ $cliente->apellidos }}
                                </div>
                                <small class="text-muted">
                                    ID: {{ $cliente->id }}
                                </small>
                            </td>

                            <td>
                                @if($cliente->telefono)
                                    <span class="badge text-bg-light rounded-pill px-3 py-2">
                                        {{ $cliente->telefono }}
                                    </span>
                                @else
                                    <span class="text-muted">Sin teléfono</span>
                                @endif
                            </td>

                            <td>
                                @if($cliente->dpi)
                                    {{ $cliente->dpi }}
                                @else
                                    <span class="text-muted">Sin DPI</span>
                                @endif
                            </td>

                            <td>
                                @if($cliente->direccion)
                                    {{ $cliente->direccion }}
                                @else
                                    <span class="text-muted">Sin dirección</span>
                                @endif
                            </td>

                            <td>
                                @if($cliente->observaciones)
                                    <span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $cliente->observaciones }}">
                                        {{ $cliente->observaciones }}
                                    </span>
                                @else
                                    <span class="text-muted">Sin observaciones</span>
                                @endif
                            </td>

                            <td>
                                @if($cliente->activo)
                                    <span class="badge-soft badge-entrada">Activo</span>
                                @else
                                    <span class="badge-soft badge-ajuste">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">

                                    <a href="{{ route('clientes.edit', $cliente->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill action-main-btn">
                                        ✏️ Editar
                                    </a>

                                    @if($cliente->activo)
                                        <form action="{{ route('clientes.desactivar', $cliente->id) }}"
                                              method="POST"
                                              class="d-inline confirm-action-form"
                                              data-title="¿Desactivar cliente?"
                                              data-text="Este cliente ya no aparecerá al crear nuevos alquileres."
                                              data-icon="warning"
                                              data-confirm="Sí, desactivar"
                                              data-cancel="Cancelar">
                                            @csrf

                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill action-main-btn">
                                                ⛔ Desactivar
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('clientes.reactivar', $cliente->id) }}"
                                              method="POST"
                                              class="d-inline confirm-action-form"
                                              data-title="¿Reactivar cliente?"
                                              data-text="Este cliente volverá a estar disponible para nuevos alquileres."
                                              data-icon="question"
                                              data-confirm="Sí, reactivar"
                                              data-cancel="Cancelar">
                                            @csrf

                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill action-main-btn">
                                                ✅ Reactivar
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
            No hay clientes registrados todavía.
        </div>
    @endif

</div>

@endsection