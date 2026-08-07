@extends('layouts.app')

@section('title', 'Editar producto')
@section('page_title', '✏️ Editar producto')
@section('page_subtitle', 'Modifica los datos generales y detalles específicos del producto')

@section('content')

@php
    $detalleToga = $producto->productoToga ?? $producto->toga ?? null;
    $detalleCapa = $producto->productoCapa ?? $producto->capa ?? null;
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
                                <label for="talla_toga" class="form-label">Talla</label>
                                <input
                                    type="text"
                                    name="talla_toga"
                                    id="talla_toga"
                                    class="form-control"
                                    value="{{ old('talla_toga', $detalleToga->talla ?? '') }}"
                                    placeholder="Ej: S, M, L, XL"
                                    required
                                >
                            </div>

                            <div class="col-md-4">
                                <label for="tipo_toga" class="form-label">Tipo de toga</label>
                                <select name="tipo_toga" id="tipo_toga" class="form-select" required>
                                    <option value="ESTANDAR" {{ old('tipo_toga', $detalleToga->tipo_toga ?? 'ESTANDAR') === 'ESTANDAR' ? 'selected' : '' }}>
                                        Estándar
                                    </option>
                                    <option value="UNIVERSITARIA" {{ old('tipo_toga', $detalleToga->tipo_toga ?? '') === 'UNIVERSITARIA' ? 'selected' : '' }}>
                                        Universitaria
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="color_toga" class="form-label">Color</label>
                                <input type="text"
                                    name="color_toga"
                                    id="color_toga"
                                    class="form-control"
                                    value="{{ old('color_toga', $detalleToga->color ?? '') }}"
                                    placeholder="Ej: Negro"
                                    required>
                            </div>

                            <div class="col-md-4">
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

                @if($producto->tipo_producto === 'CAPA')

                    <div class="alert alert-light border rounded-4 mt-4">

                        <div class="fw-bold mb-3">
                            🧥 Detalles de capa
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label">
                                    Talla
                                </label>

                                <input
                                    type="text"
                                    name="talla_capa"
                                    class="form-control"
                                    value="{{ old('talla_capa', $detalleCapa->talla ?? '') }}"
                                    placeholder="Ej. S, M, L, XL"
                                >

                            </div>

                            <div class="col-md-6" id="grupo-codigo-color-capa">
                                <label class="form-label">Código de color</label>

                                <input
                                    type="text"
                                    name="codigo_color_capa"
                                    class="form-control"
                                    value="{{ old('codigo_color_capa', $detalleCapa->codigo_color ?? '') }}"
                                >
                            </div>

                            <div class="col-md-6" id="grupo-color-capa">

                                <label class="form-label">
                                    Color
                                </label>

                                <select
                                    id="color_capa"
                                    name="color_capa"
                                    class="form-select"
                                >
                                    <option value="">Seleccione...</option>

                                    <option value="Celeste"
                                        {{ old('color_capa', $detalleCapa->color ?? '') === 'Celeste' ? 'selected' : '' }}>
                                        Celeste
                                    </option>

                                    <option value="Rojo"
                                        {{ old('color_capa', $detalleCapa->color ?? '') === 'Rojo' ? 'selected' : '' }}>
                                        Rojo
                                    </option>

                                    <option value="Verde"
                                        {{ old('color_capa', $detalleCapa->color ?? '') === 'Verde' ? 'selected' : '' }}>
                                        Verde
                                    </option>

                                    <option value="Amarillo"
                                        {{ old('color_capa', $detalleCapa->color ?? '') === 'Amarillo' ? 'selected' : '' }}>
                                        Amarillo
                                    </option>

                                    <option value="Naranja"
                                        {{ old('color_capa', $detalleCapa->color ?? '') === 'Naranja' ? 'selected' : '' }}>
                                        Naranja
                                    </option>
                                </select>

                            </div>

                            <div class="col-md-6" id="grupo-carrera-capa">
                                <label class="form-label">Carrera</label>

                                <select
                                    name="carrera_capa"
                                    class="form-select">

                                    <option value="">Seleccione...</option>

                                    @foreach([
                                        'AGRONOMIA'=>'Agronomía',
                                        'DERECHO'=>'Derecho',
                                        'PEDAGOGIA'=>'Pedagogía',
                                        'MEDICINA'=>'Medicina',
                                        'CIENCIAS_ECONOMICAS'=>'Ciencias Económicas'
                                    ] as $valor=>$texto)

                                        <option
                                            value="{{ $valor }}"
                                            {{ old('carrera_capa', $detalleCapa->carrera ?? '') == $valor ? 'selected' : '' }}>
                                            {{ $texto }}
                                        </option>

                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-12">

                                <label class="form-label">
                                    Observaciones
                                </label>

                                <textarea
                                    name="observaciones_capa"
                                    rows="3"
                                    class="form-control"
                                >{{ old('observaciones_capa', $detalleCapa->observaciones ?? '') }}</textarea>

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

                            <div class="col-md-4" id="grupo-codigo-color-collarin">
                                <label class="form-label">Código de color</label>

                                <input
                                    type="text"
                                    id="codigo_color_collarin"
                                    name="codigo_color_collarin"
                                    class="form-control"
                                    value="{{ old('codigo_color_collarin', $detalleCollarin->codigo_color ?? '') }}">
                            </div>

                            <div class="col-md-4" id="grupo-color-collarin">
                                <label for="color_collarin" class="form-label">Color</label>

                                @php
                                    $tipoCollarinActual = old('tipo_collarin', $detalleCollarin->tipo_collarin ?? 'NORMAL');
                                    $colorCollarinActual = old('color_collarin', $detalleCollarin->color ?? '');
                                @endphp

                                <select
                                    id="color_collarin"
                                    name="color_collarin"
                                    class="form-select"
                                    required
                                >
                                    <option value="">Seleccione...</option>

                                    @if($tipoCollarinActual === 'UNIVERSITARIO')
                                        <option value="Azul" {{ $colorCollarinActual === 'Azul' ? 'selected' : '' }}>
                                            Azul
                                        </option>
                                    @else
                                        <option value="Dorado" {{ $colorCollarinActual === 'Dorado' ? 'selected' : '' }}>
                                            Dorado
                                        </option>
                                        <option value="Rojo" {{ $colorCollarinActual === 'Rojo' ? 'selected' : '' }}>
                                            Rojo
                                        </option>
                                        <option value="Verde" {{ $colorCollarinActual === 'Verde' ? 'selected' : '' }}>
                                            Verde
                                        </option>
                                    @endif
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

                                <div class="col-md-6" id="grupo-color-borla">
                                    <label class="form-label">Color de borla</label>
                                    <select
                                        id="borla_color"
                                        name="borla_color"
                                        class="form-select"
                                    >
                                        <option value="">Seleccione...</option>
                                        <option value="Celeste" {{ old('borla_color', $detalleBorla->color ?? '') === 'Celeste' ? 'selected' : '' }}>
                                            Celeste
                                        </option>
                                        <option value="Rojo" {{ old('borla_color', $detalleBorla->color ?? '') === 'Rojo' ? 'selected' : '' }}>
                                            Rojo
                                        </option>
                                        <option value="Verde" {{ old('borla_color', $detalleBorla->color ?? '') === 'Verde' ? 'selected' : '' }}>
                                            Verde
                                        </option>
                                        <option value="Amarillo" {{ old('borla_color', $detalleBorla->color ?? '') === 'Amarillo' ? 'selected' : '' }}>
                                            Amarillo
                                        </option>
                                        <option value="Naranja" {{ old('borla_color', $detalleBorla->color ?? '') === 'Naranja' ? 'selected' : '' }}>
                                            Naranja
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6" id="grupo-codigo-color-borla">
                                    <label class="form-label">
                                        Código de color
                                    </label>

                                    <input
                                        type="text"
                                        name="borla_codigo_color"
                                        class="form-control"
                                        value="{{ old('borla_codigo_color', $detalleBorla->codigo_color ?? '') }}">
                                </div>

                                <div class="col-md-12" id="grupo-observaciones-borla">
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

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {

    // ============================================
    // CONFIGURACIÓN AUTOMÁTICA POR CARRERA
    // ============================================

    const carreras = {
        AGRONOMIA: {
            color: "Verde",
            codigo: "AGR"
        },
        DERECHO: {
            color: "Rojo",
            codigo: "DER"
        },
        PEDAGOGIA: {
            color: "Celeste",
            codigo: "PED"
        },
        MEDICINA: {
            color: "Amarillo",
            codigo: "MED"
        },
        CIENCIAS_ECONOMICAS: {
            color: "Naranja",
            codigo: "ECO"
        }
    };

    function actualizarCampos(config) {

        const carrera = document.querySelector(config.carrera);

        if (!carrera) return;

        const codigo = document.querySelector(config.codigo);
        const color = document.querySelector(config.color);

        function actualizar() {

            const datos = carreras[carrera.value];

            if (!datos) return;

            if (codigo) {
                codigo.value = datos.codigo;
            }

            if (color) {
                color.value = datos.color;
            }
        }

        carrera.addEventListener('change', actualizar);
        actualizar();
    }

    actualizarCampos({
        carrera: 'select[name="carrera_capa"]',
        codigo: 'input[name="codigo_color_capa"]',
        color: 'select[name="color_capa"]'
    });

    // ============================================
    // COLLARÍN NORMAL / UNIVERSITARIO
    // ============================================

    const tipo = document.getElementById('tipo_collarin');
    const colorCollarinSelectCollarin = document.getElementById('color_collarin');

    const opcionesColorPorTipoCollarin = {
        NORMAL: ['Dorado', 'Rojo', 'Verde'],
        UNIVERSITARIO: ['Azul']
    };

    function actualizarOpcionesColorCollarin(tipoValor, valorPrevio) {
        if (!colorCollarinSelectCollarin) return;

        const opciones = opcionesColorPorTipoCollarin[tipoValor] || [];

        colorCollarinSelectCollarin.innerHTML = '<option value="">Seleccione...</option>';

        opciones.forEach(color => {
            const opt = document.createElement('option');
            opt.value = color;
            opt.textContent = color;
            if (color === valorPrevio) {
                opt.selected = true;
            }
            colorCollarinSelectCollarin.appendChild(opt);
        });
    }

    if (tipo) {

        function actualizarVistaCollarin() {
            const valorPrevio = colorCollarinSelectCollarin ? colorCollarinSelectCollarin.value : null;
            actualizarOpcionesColorCollarin(tipo.value, valorPrevio);
            actualizarCodigoCollarin();
        }

        tipo.addEventListener('change', actualizarVistaCollarin);
    }

    const coloresCollarin = {
        'Azul': 'C-AZ',
        'Rojo': 'C-RO',
        'Verde': 'C-VE',
        'Dorado': 'C-DO',
    };

    const coloresBorla = {
        'Celeste': 'B-CE',
        'Rojo': 'B-RO',
        'Verde': 'B-VE',
        'Amarillo': 'B-AM',
        'Naranja': 'B-NA'
    };

    const colorCollarinInput = document.getElementById('color_collarin');
    const codigoCollarinInput = document.getElementById('codigo_color_collarin');
    const borlaColorInput = document.getElementById('borla_color');
    const borlaCodigoInput = document.getElementById('borla_codigo_color');

    function actualizarCodigoCollarin() {
        if (!colorCollarinInput || !codigoCollarinInput) {
            return;
        }

        const codigo = coloresCollarin[colorCollarinInput.value];

        if (codigo) {
            codigoCollarinInput.value = codigo;
        }
    }

    function actualizarCodigoBorla() {
        if (!borlaColorInput || !borlaCodigoInput) {
            return;
        }

        const codigo = coloresBorla[borlaColorInput.value];

        if (codigo) {
            borlaCodigoInput.value = codigo;
        }
    }

    if (colorCollarinInput) {
        colorCollarinInput.addEventListener('change', actualizarCodigoCollarin);
        colorCollarinInput.addEventListener('input', actualizarCodigoCollarin);
        actualizarCodigoCollarin();
    }

    if (borlaColorInput) {
        borlaColorInput.addEventListener('change', actualizarCodigoBorla);
        borlaColorInput.addEventListener('input', actualizarCodigoBorla);
        actualizarCodigoBorla();
    }

});
</script>

@endpush

@endsection