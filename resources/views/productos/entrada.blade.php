@extends('layouts.app')

@section('title', 'Entrada de inventario')
@section('page_title', '➕ Entrada de inventario')
@section('page_subtitle', 'Agrega nuevas unidades al stock disponible del producto seleccionado')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📦 Registrar entrada</div>
        <p class="text-muted mb-0">
            Aumenta el inventario disponible de un producto existente.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ url('/productos-web/administrar/entrada') }}" class="btn btn-outline-secondary rounded-pill">
            ← Volver a selección
        </a>

        <a href="{{ url('/productos-web') }}" class="btn btn-outline-primary rounded-pill">
            👗 Ver productos
        </a>
    </div>
</div>

<div class="row g-4">

    <div class="col-lg-4">
        <div class="page-card p-4 h-100">

            <div class="mb-3">
                <div class="stat-icon">➕</div>
                <h4 class="fw-bold mb-1">{{ $producto->nombre }}</h4>
                <p class="text-muted mb-0">{{ $producto->descripcion ?? 'Sin descripción' }}</p>
            </div>

            <hr>

            <div class="mb-3">
                <div class="text-muted small">Código</div>
                <div class="fw-bold">{{ $producto->codigo }}</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Tipo de producto</div>

                @if($producto->tipo_producto === 'TOGA')
                    <span class="badge-soft badge-toga">TOGA</span>
                @elseif($producto->tipo_producto === 'BIRRETE')
                    <span class="badge-soft badge-birrete">BIRRETE</span>
                @elseif($producto->tipo_producto === 'COLLARIN')
                    <span class="badge-soft badge-collarin">COLLARÍN</span>
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

            <div class="alert alert-success rounded-4 mt-4 mb-0">
                <strong>Nota:</strong><br>
                Esta acción aumentará el <strong>stock total</strong> y el <strong>stock disponible</strong> del producto.
            </div>

        </div>
    </div>

    <div class="col-lg-8">
        <div class="page-card p-4">

            <div class="section-title mb-3">📝 Datos de la entrada</div>

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

            <form action="{{ url('/productos-web/' . $producto->id . '/entrada') }}"
                  method="POST"
                  class="confirm-action-form"
                  data-title="¿Registrar entrada de inventario?"
                  data-text="Esta acción aumentará el stock total y disponible del producto."
                  data-icon="question"
                  data-confirm="Sí, registrar entrada"
                  data-cancel="Cancelar">

                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="cantidad" class="form-label">Cantidad a ingresar</label>
                        <input type="number"
                               name="cantidad"
                               id="cantidad"
                               class="form-control"
                               value="{{ old('cantidad') }}"
                               min="1"
                               placeholder="Ej: 5"
                               required>

                        <small class="text-muted">
                            Solo se permiten cantidades positivas.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="motivo" class="form-label">Motivo</label>
                        <input type="text"
                               name="motivo"
                               id="motivo"
                               class="form-control"
                               value="{{ old('motivo', 'Entrada de inventario') }}"
                               placeholder="Ej: Compra, ingreso inicial, devolución encontrada..."
                               required>
                    </div>

                    <div class="col-md-12">
                        <label for="referencia" class="form-label">Referencia u observación</label>
                        <input type="text"
                               name="referencia"
                               id="referencia"
                               class="form-control"
                               value="{{ old('referencia') }}"
                               placeholder="Ej: Compra junio, ingreso de bodega, lote nuevo...">
                    </div>

                </div>

                <div class="alert alert-light border rounded-4 mt-4">
                    <div class="fw-bold mb-1">Vista previa del movimiento</div>
                    <div class="text-muted">
                        El sistema registrará un movimiento tipo <strong>ENTRADA</strong> y aumentará el inventario disponible.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                    <a href="{{ url('/productos-web/administrar/entrada') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        ➕ Registrar entrada
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection