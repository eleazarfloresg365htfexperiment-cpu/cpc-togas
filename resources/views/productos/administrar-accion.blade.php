@extends('layouts.app')

@php
    $titulos = [
        'editar' => '✏️ Editar producto',
        'entrada' => '➕ Entrada de inventario',
        'ajuste' => '⚙️ Ajuste manual',
        'estado' => '⛔ Activar / desactivar producto',
    ];

    $subtitulos = [
        'editar' => 'Selecciona el producto que deseas modificar',
        'entrada' => 'Selecciona el producto al que deseas agregar stock',
        'ajuste' => 'Selecciona el producto al que deseas corregir stock',
        'estado' => 'Selecciona el producto que deseas activar o desactivar',
    ];
@endphp

@section('title', $titulos[$accion])
@section('page_title', $titulos[$accion])
@section('page_subtitle', $subtitulos[$accion])

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📋 Seleccionar producto</div>
        <p class="text-muted mb-0">
            Puedes buscar por código, nombre o descripción.
        </p>
    </div>

    <a href="{{ url('/productos-web/administrar') }}" class="btn btn-outline-secondary rounded-pill">
        ← Volver al panel
    </a>
</div>

<div class="page-card p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">

        <div class="col-md-6">
            <label class="form-label">Buscar producto</label>
            <input type="text"
                   name="buscar"
                   value="{{ request('buscar') }}"
                   class="form-control rounded-pill"
                   placeholder="Ej: TOGA-M, birrete, collarín...">
        </div>

        <div class="col-md-4">
            <label class="form-label">Tipo de producto</label>
            <select name="tipo" class="form-select rounded-pill">
                <option value="TODOS" {{ request('tipo') === 'TODOS' ? 'selected' : '' }}>Todos</option>
                <option value="TOGA" {{ request('tipo') === 'TOGA' ? 'selected' : '' }}>👗 Togas</option>
                <option value="BIRRETE" {{ request('tipo') === 'BIRRETE' ? 'selected' : '' }}>🎓 Birretes</option>
                <option value="COLLARIN" {{ request('tipo') === 'COLLARIN' ? 'selected' : '' }}>🏅 Collarines</option>
            </select>
        </div>

        <div class="col-md-2 d-grid">
            <button class="btn btn-primary rounded-pill">
                🔎 Buscar
            </button>
        </div>

    </form>
</div>

<div class="page-card p-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
        <div class="section-title mb-0">📦 Productos encontrados</div>

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
                        <th>Stock</th>
                        <th>Estado</th>
                        <th class="text-end">Acción</th>
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
                                <small class="text-muted">
                                    {{ $producto->descripcion ?? 'Sin descripción' }}
                                </small>
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
                                <div>
                                    <strong>Total:</strong> {{ $producto->stock_total }}
                                </div>
                                <small class="text-muted">
                                    Disponible: {{ $producto->stock_disponible }} |
                                    Alquilado: {{ $producto->stock_alquilado }}
                                </small>
                            </td>

                            <td>
                                @if($producto->activo)
                                    <span class="badge-soft badge-entrada">Activo</span>
                                @else
                                    <span class="badge-soft badge-ajuste">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-end">

                                @if($accion === 'editar')
                                    <a href="{{ url('/productos-web/' . $producto->id . '/editar') }}"
                                       class="btn btn-sm btn-primary rounded-pill action-main-btn">
                                        ✏️ Editar
                                    </a>
                                @elseif($accion === 'entrada')
                                    <a href="{{ url('/productos-web/' . $producto->id . '/entrada') }}"
                                       class="btn btn-sm btn-success rounded-pill action-main-btn">
                                        ➕ Entrada
                                    </a>
                                @elseif($accion === 'ajuste')
                                    <a href="{{ url('/productos-web/' . $producto->id . '/ajuste') }}"
                                       class="btn btn-sm btn-warning rounded-pill action-main-btn">
                                        ⚙️ Ajuste
                                    </a>
                                @elseif($accion === 'estado')
                                    @if($producto->activo)
                                        <form action="{{ url('/productos-web/' . $producto->id . '/desactivar') }}"
                                            method="POST"
                                            class="d-inline confirm-action-form"
                                            data-title="¿Desactivar producto?"
                                            data-text="Este producto ya no aparecerá al crear nuevos alquileres."
                                            data-icon="warning"
                                            data-confirm="Sí, desactivar"
                                            data-cancel="Cancelar">
                                            @csrf
                                            @method('PATCH')

                                            <button class="btn btn-sm btn-danger rounded-pill action-main-btn">
                                                ⛔ Desactivar
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ url('/productos-web/' . $producto->id . '/reactivar') }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Deseas reactivar este producto?');"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            <button class="btn btn-sm btn-success rounded-pill action-main-btn">
                                                ✅ Reactivar
                                            </button>
                                        </form>
                                    @endif
                                @endif

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-light border rounded-4 mb-0">
            No se encontraron productos con esos filtros.
        </div>
    @endif

</div>

@endsection