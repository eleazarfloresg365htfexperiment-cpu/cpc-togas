@extends('layouts.app')

@section('title', 'Editar producto')
@section('page_title', '✏️ Editar producto')
@section('page_subtitle', 'Modifica los datos generales y detalles específicos del producto')

@section('content')

@php
    // Título de la caja de detalles según el tipo de producto.
    $titulosDetalle = [
        'TOGA' => '👗 Detalles de toga',
        'CAPA' => '🧥 Detalles de capa',
        'BIRRETE' => '🎓 Detalles de birrete',
        'COLLARIN' => '🏅 Detalles de collarín',
        'BORLA' => '🎀 Detalles de borla',
    ];
@endphp

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📦 Editar información del producto</div>
        <p class="text-muted mb-0">
            Actualiza los datos del producto seleccionado.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('productos.administrar.accion', 'editar') }}" class="btn btn-outline-secondary rounded-pill">
            ← Volver a selección
        </a>

        <a href="{{ route('productos.index') }}" class="btn btn-outline-primary rounded-pill">
            👗 Ver productos
        </a>
    </div>
</div>

<div class="row g-4">

    <div class="col-lg-4">
        <div class="page-card p-4 h-100">

            <div class="mb-3">
                <div class="stat-icon">✏️</div>
                <h4 class="fw-bold mb-1">{{ $producto->nombre }}</h4>
                <p class="text-muted mb-0">{{ $producto->descripcion ?? 'Sin descripción' }}</p>
            </div>

            <hr>

            <div class="mb-3">
                <div class="text-muted small">Código actual</div>
                <div class="fw-bold">{{ $producto->codigo }}</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Tipo de producto</div>

                @if($producto->tipo_producto === 'TOGA')
                    <span class="badge-soft badge-toga">TOGA</span>
                @elseif($producto->tipo_producto === 'CAPA')
                    <span class="badge-soft badge-capa">CAPA</span>
                @elseif($producto->tipo_producto === 'BIRRETE')
                    <span class="badge-soft badge-birrete">BIRRETE</span>
                @elseif($producto->tipo_producto === 'COLLARIN')
                    <span class="badge-soft badge-collarin">COLLARÍN</span>
                @elseif($producto->tipo_producto === 'BORLA')
                    <span class="badge-soft badge-borla">BORLA</span>
                @else
                    <span class="badge bg-secondary">{{ $producto->tipo_producto }}</span>
                @endif
            </div>

            <div class="row g-3 mt-2">
                <div class="col-6">
                    <div class="p-3 rounded-4 bg-light">
                        <div class="text-muted small">Stock total</div>
                        <div class="h4 fw-bold mb-0">{{ $producto->stock_total }}</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-4 bg-light">
                        <div class="text-muted small">Disponible</div>
                        <div class="h4 fw-bold mb-0 amount-positive">{{ $producto->stock_disponible }}</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-4 bg-light">
                        <div class="text-muted small">Alquilado</div>
                        <div class="h4 fw-bold mb-0 amount-negative">{{ $producto->stock_alquilado }}</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-4 bg-light">
                        <div class="text-muted small">Estado</div>
                        <div class="mt-1">
                            @if($producto->activo)
                                <span class="badge-soft badge-entrada">Activo</span>
                            @else
                                <span class="badge-soft badge-ajuste">Inactivo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border rounded-4 mt-4 mb-0">
                <strong>Nota:</strong><br>
                Esta pantalla modifica datos del producto, pero no debe usarse para cambiar stock. Para eso usa Entrada o Ajuste.
            </div>

        </div>
    </div>
    <div class="col-lg-8">
        <div class="page-card p-4">

            <div class="section-title mb-3">📝 Datos del producto</div>

            @if ($errors->any())
                <div class="alert alert-danger rounded-4">
                    <strong>Revisa los datos ingresados:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('productos.update', $producto->id) }}"
                method="POST"
                id="formProducto"
                data-modo="editar"
                class="confirm-action-form"
                data-title="¿Guardar cambios?"
                data-text="Se actualizará la información del producto seleccionado."
                data-icon="question"
                data-confirm="Sí, guardar cambios"
                data-cancel="Cancelar">

                @csrf
                @method('PUT')

                <input type="hidden" name="stock_total" value="{{ old('stock_total', $producto->stock_total) }}">
                <input type="hidden" name="activo" value="{{ old('activo', $producto->activo ? 1 : 0) }}">

                @include('productos.partials.campos-generales', ['producto' => $producto])

                @if(isset($titulosDetalle[$producto->tipo_producto]))
                    <div class="alert alert-light border rounded-4 mt-4">
                        <div class="fw-bold mb-3">{{ $titulosDetalle[$producto->tipo_producto] }}</div>

                        @include('productos.partials.detalles', ['producto' => $producto])
                    </div>
                @endif

                <div class="alert alert-light border rounded-4 mt-4">
                    <div class="fw-bold mb-1">Resumen de la acción</div>
                    <div class="text-muted">
                        Se guardarán los cambios generales y los detalles específicos del producto.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                    <a href="{{ route('productos.administrar.accion', 'editar') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        💾 Guardar cambios
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@push('scripts')
    @include('productos.partials.scripts')
@endpush

@endsection
