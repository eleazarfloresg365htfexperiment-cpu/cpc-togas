@extends('layouts.app')

@section('title', 'Registrar producto')
@section('page_title', '➕ Registrar producto')
@section('page_subtitle', 'Agrega un nuevo producto al inventario de togas, birretes, collarines o borlas')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📦 Nuevo producto</div>
        <p class="text-muted mb-0">
            Registra el producto, su tipo, precio, stock inicial y detalles específicos.
        </p>
    </div>

    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary rounded-pill">
        ← Volver a productos
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
        <div class="fw-bold mb-2">⚠️ Revisa los datos ingresados</div>

        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('productos.store') }}" id="formProducto" data-modo="crear">
    @csrf

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="page-card p-3 p-md-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon">🧾</div>
                    <div>
                        <div class="section-title mb-1">Información principal</div>
                        <p class="text-muted mb-0">
                            Datos generales que identificarán el producto dentro del sistema.
                        </p>
                    </div>
                </div>

                @include('productos.partials.campos-generales')
            </div>

            <div class="page-card p-3 p-md-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon">🎓</div>
                    <div>
                        <div class="section-title mb-1">Detalles específicos</div>
                        <p class="text-muted mb-0">
                            Estos campos cambian automáticamente según el tipo de producto.
                        </p>
                    </div>
                </div>

                <div id="mensaje-detalles" class="alert alert-light border rounded-4">
                    Selecciona un tipo de producto para mostrar sus detalles específicos.
                </div>

                @include('productos.partials.detalles')

            </div>

        </div>

        <div class="col-lg-4">

            <div class="page-card p-3 p-md-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon">📊</div>
                    <div>
                        <div class="section-title mb-1">Stock inicial</div>
                        <p class="text-muted mb-0">
                            Cantidad con la que iniciará el producto.
                        </p>
                    </div>
                </div>

                <label class="form-label fw-semibold">Unidades iniciales</label>
                <input
                    type="number"
                    name="stock_total"
                    id="stock_total"
                    class="form-control"
                    value="{{ old('stock_total', 0) }}"
                    min="0"
                    required
                >

                <div class="alert alert-light border rounded-4 mt-3 mb-0">
                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span>Stock total</span>
                            <strong id="preview_stock_total">0</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Stock disponible</span>
                            <strong id="preview_stock_disponible">0</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Stock alquilado</span>
                            <strong id="preview_stock_alquilado">0</strong>
                        </div>
                    </div>
                </div>

                <small class="text-muted d-block mt-3">
                    Si el stock inicial es mayor a 0, se creará automáticamente un movimiento de inventario tipo ENTRADA.
                </small>
            </div>

            <div class="page-card p-3 p-md-4">
                <div class="section-title mb-2">✅ Confirmación</div>
                <p class="text-muted">
                    Revisa que el código, tipo, precio y stock inicial estén correctos antes de guardar.
                </p>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill">
                        Guardar producto
                    </button>

                    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary rounded-pill">
                        Cancelar
                    </a>
                </div>
            </div>

        </div>

    </div>
</form>

@push('scripts')
    @include('productos.partials.scripts')
@endpush

@endsection
