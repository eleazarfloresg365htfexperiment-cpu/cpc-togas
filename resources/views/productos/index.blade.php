@extends('layouts.app')

@section('title', 'Productos')
@section('page_title', '👗 Productos')
@section('page_subtitle', 'Consulta general de togas, birretes, collarines y su inventario')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📦 Inventario de productos</div>
        <p class="text-muted mb-0">
            Consulta el stock, estado y datos principales de los productos registrados.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('productos.create') }}" class="btn btn-primary rounded-pill">
            ➕ Registrar producto
        </a>

        <a href="{{ url('/productos-web/administrar') }}" class="btn btn-outline-primary rounded-pill">
            🛠️ Administrar productos
        </a>

        <a href="{{ url('/inventario/movimientos') }}" class="btn btn-outline-primary rounded-pill">
            📋 Ver movimientos
        </a>
    </div>
</div>

<div class="page-card mb-4">
    <form method="GET" action="{{ route('productos.index') }}">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="form-label fw-semibold">Buscar producto</label>
                <input 
                    type="text" 
                    name="buscar" 
                    class="form-control" 
                    placeholder="Código, nombre o descripción..."
                    value="{{ request('buscar') }}"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Tipo de producto</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="TOGA" {{ request('tipo') == 'TOGA' ? 'selected' : '' }}>
                        TOGA
                    </option>
                    <option value="BIRRETE" {{ request('tipo') == 'BIRRETE' ? 'selected' : '' }}>
                        BIRRETE
                    </option>
                    <option value="COLLARIN" {{ request('tipo') == 'COLLARIN' ? 'selected' : '' }}>
                        COLLARÍN
                    </option>
                </select>
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

            <div class="col-md-2 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary flex-fill">
                    Filtrar
                </button>

                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary flex-fill">
                    Limpiar
                </a>
            </div>

        </div>
    </form>
</div>

<div class="stats-grid mb-4">

    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-label">Total productos</div>
        <div class="stat-value">{{ $productos->count() }}</div>
        <div class="stat-sub">Productos registrados</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-label">Activos</div>
        <div class="stat-value">{{ $productos->where('activo', true)->count() }}</div>
        <div class="stat-sub">Disponibles para alquiler</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">⛔</div>
        <div class="stat-label">Inactivos</div>
        <div class="stat-value">{{ $productos->where('activo', false)->count() }}</div>
        <div class="stat-sub">Ocultos en nuevos alquileres</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🟢</div>
        <div class="stat-label">Stock disponible</div>
        <div class="stat-value">{{ $productos->sum('stock_disponible') }}</div>
        <div class="stat-sub">Unidades listas para alquilar</div>
    </div>

</div>

<div class="page-card p-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div>
            <div class="section-title mb-1">📋 Listado de productos</div>
            <p class="text-muted mb-0">
                Vista general del inventario actual.
            </p>
        </div>

        <span class="badge text-bg-light rounded-pill px-3 py-2">
            {{ $productos->count() }} registros
        </span>
    </div>

    @if($productos->count() > 0)
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Precio alquiler</th>
                        <th>Stock total</th>
                        <th>Disponible</th>
                        <th>Alquilado</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($productos as $producto)
                        <tr>
                            <td>
                                <span class="badge text-bg-light rounded-pill px-3 py-2">
                                    {{ $producto->codigo }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-bold">{{ $producto->nombre }}</div>

                                @if($producto->descripcion)
                                    <small class="text-muted">{{ $producto->descripcion }}</small>
                                @else
                                    <small class="text-muted">Sin descripción</small>
                                @endif
                            </td>

                            <td>
                                @if($producto->tipo_producto === 'TOGA')
                                    <span class="badge-soft badge-toga">TOGA</span>
                                @elseif($producto->tipo_producto === 'BIRRETE')
                                    <span class="badge-soft badge-birrete">BIRRETE</span>
                                @elseif($producto->tipo_producto === 'COLLARIN')
                                    <span class="badge-soft badge-collarin">COLLARÍN</span>
                                @else
                                    <span class="badge bg-secondary">{{ $producto->tipo_producto }}</span>
                                @endif
                            </td>

                            <td>
                                <strong>Q {{ number_format($producto->precio_alquiler, 2) }}</strong>
                            </td>

                            <td>
                                <strong>{{ $producto->stock_total }}</strong>
                            </td>

                            <td>
                                @if($producto->stock_disponible > 0)
                                    <span class="amount-positive">{{ $producto->stock_disponible }}</span>
                                @else
                                    <span class="amount-negative">0</span>
                                @endif
                            </td>

                            <td>
                                @if($producto->stock_alquilado > 0)
                                    <span class="amount-negative">{{ $producto->stock_alquilado }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>

                            <td>
                                @if($producto->activo)
                                    <span class="badge-soft badge-entrada">Activo</span>
                                @else
                                    <span class="badge-soft badge-ajuste">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-light border rounded-4 mb-0">
            No hay productos registrados todavía.
        </div>
    @endif

</div>

@endsection