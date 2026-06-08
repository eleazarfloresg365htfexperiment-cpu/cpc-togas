@extends('layouts.app')

@section('title', 'Editar producto')
@section('page_title', '✏️ Editar producto')
@section('page_subtitle', 'Modifica los datos generales y detalles específicos del producto')

@section('content')

@php
    $detalleToga = $producto->productoToga ?? $producto->toga ?? null;
    $detalleBirrete = $producto->productoBirrete ?? $producto->birrete ?? null;
    $detalleCollarin = $producto->productoCollarin ?? $producto->collarin ?? null;
    $detalleBorla = $producto->productoBorla ?? $producto->borla ?? null;
@endphp

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📦 Editar información del producto</div>
        <p class="text-muted mb-0">
            Actualiza los datos del producto seleccionado.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ url('/productos-web/administrar/editar') }}" class="btn btn-outline-secondary rounded-pill">
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

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text"
                               name="codigo"
                               id="codigo"
                               class="form-control"
                               value="{{ old('codigo', $producto->codigo) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre del producto</label>
                        <input type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control"
                               value="{{ old('nombre', $producto->nombre) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="precio_alquiler" class="form-label">Precio de alquiler</label>
                        <input type="number"
                               name="precio_alquiler"
                               id="precio_alquiler"
                               class="form-control"
                               value="{{ old('precio_alquiler', $producto->precio_alquiler) }}"
                               min="0"
                               step="0.01"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="tipo_producto" class="form-label">Tipo de producto</label>
                        <input type="text"
                               id="tipo_producto"
                               class="form-control"
                               value="{{ $producto->tipo_producto }}"
                               readonly>
                        <small class="text-muted">
                            El tipo de producto no se modifica desde esta pantalla.
                        </small>
                    </div>

                    <div class="col-md-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion"
                                  id="descripcion"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Descripción general del producto...">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    </div>

                </div>

                @if($producto->tipo_producto === 'TOGA')
                    <div class="alert alert-light border rounded-4 mt-4">
                        <div class="fw-bold mb-3">👗 Detalles de toga</div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="talla" class="form-label">Talla</label>
                                <input type="text"
                                       name="talla"
                                       id="talla"
                                       class="form-control"
                                       value="{{ old('talla', $detalleToga->talla ?? '') }}"
                                       placeholder="Ej: S, M, L, XL"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label for="color_toga" class="form-label">Color</label>
                                <input type="text"
                                    name="color_toga"
                                    id="color_toga"
                                    class="form-control"
                                    value="{{ old('color_toga', $detalleToga->color ?? '') }}"
                                    placeholder="Ej: Negro"
                                    required>
                            </div>

                            <div class="col-md-12">
                                <label for="observaciones_toga" class="form-label">Observaciones de la toga</label>
                                <textarea name="observaciones_toga"
                                        id="observaciones_toga"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Detalles adicionales de la toga...">{{ old('observaciones_toga', $detalleToga->observaciones ?? '') }}</textarea>
                            </div>

                        </div>
                    </div>
                @endif

                @if($producto->tipo_producto === 'BIRRETE')
                    <div class="alert alert-light border rounded-4 mt-4">
                        <div class="fw-bold mb-3">🎓 Detalles de birrete</div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="tipo_birrete" class="form-label">Tipo de birrete</label>
                                <select name="tipo_birrete" id="tipo_birrete" class="form-select" required>
                                    <option value="ESTANDAR" {{ old('tipo_birrete', $detalleBirrete->tipo_birrete ?? '') === 'ESTANDAR' ? 'selected' : '' }}>
                                        Estándar
                                    </option>
                                    <option value="NORMAL" {{ old('tipo_birrete', $detalleBirrete->tipo_birrete ?? '') === 'NORMAL' ? 'selected' : '' }}>
                                        Normal
                                    </option>
                                    <option value="UNIVERSITARIO" {{ old('tipo_birrete', $detalleBirrete->tipo_birrete ?? '') === 'UNIVERSITARIO' ? 'selected' : '' }}>
                                        Universitario
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="color_birrete" class="form-label">Color</label>
                                <input type="text"
                                    name="color_birrete"
                                    id="color_birrete"
                                    class="form-control"
                                    value="{{ old('color_birrete', $detalleBirrete->color ?? '') }}"
                                    placeholder="Ej: Negro"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label for="carrera" class="form-label">Carrera</label>
                                <select name="carrera" id="carrera" class="form-select">
                                    <option value="">No aplica</option>
                                    <option value="ADMINISTRACION" {{ old('carrera', $detalleBirrete->carrera ?? '') === 'ADMINISTRACION' ? 'selected' : '' }}>
                                        Administración
                                    </option>
                                    <option value="AGRONOMIA" {{ old('carrera', $detalleBirrete->carrera ?? '') === 'AGRONOMIA' ? 'selected' : '' }}>
                                        Agronomía
                                    </option>
                                    <option value="DERECHO" {{ old('carrera', $detalleBirrete->carrera ?? '') === 'DERECHO' ? 'selected' : '' }}>
                                        Derecho
                                    </option>
                                    <option value="PEDAGOGIA" {{ old('carrera', $detalleBirrete->carrera ?? '') === 'PEDAGOGIA' ? 'selected' : '' }}>
                                        Pedagogía
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="tiene_borlas_extra" class="form-label">Borlas extra</label>
                                <select name="tiene_borlas_extra" id="tiene_borlas_extra" class="form-select">
                                    <option value="0" {{ old('tiene_borlas_extra', $detalleBirrete->tiene_borlas_extra ?? 0) == 0 ? 'selected' : '' }}>
                                        No
                                    </option>
                                    <option value="1" {{ old('tiene_borlas_extra', $detalleBirrete->tiene_borlas_extra ?? 0) == 1 ? 'selected' : '' }}>
                                        Sí
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="descripcion_borlas_extra" class="form-label">Descripción de borlas extra</label>
                                <textarea name="descripcion_borlas_extra"
                                          id="descripcion_borlas_extra"
                                          rows="3"
                                          class="form-control"
                                          placeholder="Ej: Incluye borla adicional para Derecho...">{{ old('descripcion_borlas_extra', $detalleBirrete->descripcion_borlas_extra ?? '') }}</textarea>
                            </div>

                        </div>
                    </div>
                @endif

                @if($producto->tipo_producto === 'COLLARIN')
                    <div class="alert alert-light border rounded-4 mt-4">
                        <div class="fw-bold mb-3">🏅 Detalles de collarín</div>

                        <div class="row g-3">

                            <div class="col-md-4">
                                <label for="tipo_collarin" class="form-label">Tipo de collarín</label>
                                <select name="tipo_collarin" id="tipo_collarin" class="form-select" required>
                                    <option value="NORMAL" {{ old('tipo_collarin', $detalleCollarin->tipo_collarin ?? '') === 'NORMAL' ? 'selected' : '' }}>
                                        Normal
                                    </option>
                                    <option value="UNIVERSITARIO" {{ old('tipo_collarin', $detalleCollarin->tipo_collarin ?? '') === 'UNIVERSITARIO' ? 'selected' : '' }}>
                                        Universitario
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="color_collarin" class="form-label">Color</label>
                                <select name="color_collarin" id="color_collarin" class="form-select" required>
                                    <option value="DORADO" {{ old('color_collarin', $detalleCollarin->color ?? '') === 'DORADO' ? 'selected' : '' }}>
                                        Dorado
                                    </option>
                                    <option value="ROJO" {{ old('color_collarin', $detalleCollarin->color ?? '') === 'ROJO' ? 'selected' : '' }}>
                                        Rojo
                                    </option>
                                    <option value="VERDE" {{ old('color_collarin', $detalleCollarin->color ?? '') === 'VERDE' ? 'selected' : '' }}>
                                        Verde
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="tamano" class="form-label">Tamaño</label>
                                <select name="tamano" id="tamano" class="form-select" required>
                                    <option value="PEQUENO" {{ old('tamano', $detalleCollarin->tamano ?? '') === 'PEQUENO' ? 'selected' : '' }}>
                                        Pequeño
                                    </option>
                                    <option value="GRANDE" {{ old('tamano', $detalleCollarin->tamano ?? '') === 'GRANDE' ? 'selected' : '' }}>
                                        Grande
                                    </option>
                                </select>
                            </div>

                        </div>
                    </div>
                @endif

                @if($producto->tipo_producto === 'BORLA')

                    <div class="card mt-4">
                        <div class="card-header fw-bold">
                            Detalles de borla
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Color de borla</label>
                                    <input
                                        type="text"
                                        name="borla_color"
                                        class="form-control"
                                        value="{{ old('borla_color', $detalleBorla->color ?? '') }}"
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Carrera / área</label>
                                    <input
                                        type="text"
                                        name="borla_carrera"
                                        class="form-control"
                                        value="{{ old('borla_carrera', $detalleBorla->carrera ?? '') }}"
                                    >
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Observaciones de borla</label>
                                    <textarea
                                        name="borla_observaciones"
                                        class="form-control"
                                        rows="2"
                                    >{{ old('borla_observaciones', $detalleBorla->observaciones ?? '') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                @endif

                <div class="alert alert-light border rounded-4 mt-4">
                    <div class="fw-bold mb-1">Resumen de la acción</div>
                    <div class="text-muted">
                        Se guardarán los cambios generales y los detalles específicos del producto.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                    <a href="{{ url('/productos-web/administrar/editar') }}" class="btn btn-outline-secondary rounded-pill px-4">
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

@endsection