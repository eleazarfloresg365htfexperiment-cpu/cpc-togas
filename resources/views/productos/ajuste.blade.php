@extends('layouts.app')

@section('title', 'Ajuste de inventario')
@section('page_title', '⚙️ Ajuste manual')
@section('page_subtitle', 'Corrige el stock disponible según conteo físico o revisión interna')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📦 Ajustar inventario</div>
        <p class="text-muted mb-0">
            Registra una corrección manual para el producto seleccionado.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ url('/productos-web/administrar/ajuste') }}" class="btn btn-outline-secondary rounded-pill">
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
                <div class="stat-icon">👗</div>
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

            <div class="alert alert-warning rounded-4 mt-4 mb-0">
                <strong>Importante:</strong><br>
                Aquí debes escribir el <strong>nuevo stock disponible real</strong>, no la cantidad que quieres sumar o restar.
            </div>

        </div>
    </div>

    <div class="col-lg-8">
        <div class="page-card p-4">

            <div class="section-title mb-3">📝 Datos del ajuste</div>

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

            <form action="{{ url('/productos-web/' . $producto->id . '/ajuste') }}"
                  method="POST"
                  class="confirm-action-form"
                  data-title="¿Registrar ajuste de inventario?"
                  data-text="Este ajuste modificará el stock disponible del producto."
                  data-icon="warning"
                  data-confirm="Sí, registrar ajuste"
                  data-cancel="Cancelar">

                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="nuevo_stock_disponible" class="form-label">Nuevo stock disponible</label>
                        <input type="number"
                               name="nuevo_stock_disponible"
                               id="nuevo_stock_disponible"
                               class="form-control"
                               value="{{ old('nuevo_stock_disponible', $producto->stock_disponible) }}"
                               min="0"
                               required>

                        <small class="text-muted">
                            Stock disponible actual: <strong>{{ $producto->stock_disponible }}</strong>
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="motivo" class="form-label">Motivo</label>
                        <input type="text"
                               name="motivo"
                               id="motivo"
                               class="form-control"
                               value="{{ old('motivo', 'Conteo físico') }}"
                               placeholder="Ej: Conteo físico"
                               required>
                    </div>

                    <div class="col-md-12">
                        <label for="referencia" class="form-label">Referencia u observación</label>
                        <input type="text"
                               name="referencia"
                               id="referencia"
                               class="form-control"
                               value="{{ old('referencia') }}"
                               placeholder="Ej: Revisión junio, conteo de bodega, corrección de registro...">
                    </div>

                </div>

                <div class="alert alert-light border rounded-4 mt-4">
                    <div class="fw-bold mb-1">Vista previa del movimiento</div>
                    <div class="text-muted">
                        El sistema comparará el stock actual con el nuevo stock disponible y registrará la diferencia como movimiento tipo <strong>AJUSTE</strong>.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                    <a href="{{ url('/productos-web/administrar/ajuste') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-warning rounded-pill px-4">
                        ⚙️ Registrar ajuste
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection