@extends('layouts.app')

@section('title', 'Actividades · Bitácora')
@section('page_title', '📋 Actividades')
@section('page_subtitle', 'Quién hizo qué y cuándo')

@section('content')
    @include('actividades.partials.pestanas')

    <div class="page-card p-3 p-md-4 mb-4">
        <form method="GET" action="{{ route('actividades.bitacora') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="usuario" class="form-label fw-semibold">Usuario</label>
                <select name="usuario" id="usuario" class="form-select">
                    <option value="">Todos</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" @selected((string) request('usuario') === (string) $u->id)>{{ $u->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="categoria" class="form-label fw-semibold">Tipo</label>
                <select name="categoria" id="categoria" class="form-select">
                    <option value="">Todos</option>
                    @foreach($categorias as $valor => $texto)
                        <option value="{{ $valor }}" @selected(request('categoria') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="desde" class="form-label fw-semibold">Desde</label>
                <input type="date" name="desde" id="desde" class="form-control" value="{{ request('desde') }}">
            </div>
            <div class="col-md-2">
                <label for="hasta" class="form-label fw-semibold">Hasta</label>
                <input type="date" name="hasta" id="hasta" class="form-control" value="{{ request('hasta') }}">
            </div>
            <div class="col-md-3">
                <label for="buscar" class="form-label fw-semibold">Buscar</label>
                <input type="text" name="buscar" id="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Recibo, cliente, producto…">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill">Filtrar</button>
                <a href="{{ route('actividades.bitacora') }}" class="btn btn-outline-secondary rounded-pill">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="page-card p-3 p-md-4">
        @if($registros->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha y hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registros as $r)
                            <tr>
                                <td class="text-nowrap small">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $r->usuario?->nombre_corto ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $categorias[$r->categoria] ?? $r->categoria }}</span>
                                    <div class="fw-semibold">{{ $r->accion }}</div>
                                </td>
                                <td class="small">
                                    {{ $r->descripcion }}
                                    @if($r->alquiler)
                                        <a href="{{ route('alquileres.show', $r->alquiler_id) }}" class="ms-1">Ver alquiler</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $registros->links() }}</div>
        @else
            <div class="alert alert-light border rounded-4 mb-0">
                No hay registros con esos filtros. La bitácora empieza a llenarse desde esta versión.
            </div>
        @endif
    </div>
@endsection
