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

<form method="POST" action="{{ route('productos.store') }}">
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

                <div class="row g-4">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Código</label>
                        <input 
                            type="text" 
                            name="codigo" 
                            class="form-control" 
                            value="{{ old('codigo') }}"
                            placeholder="Ej. TOGA-S-NEGRA"
                            required
                        >
                        <small class="text-muted">Debe ser único.</small>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nombre del producto</label>
                        <input 
                            type="text" 
                            name="nombre" 
                            class="form-control" 
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Toga negra talla S"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo de producto</label>
                        <select name="tipo_producto" id="tipo_producto" class="form-select" required>
                            <option value="">Selecciona un tipo</option>

                            <option value="TOGA" {{ old('tipo_producto') === 'TOGA' ? 'selected' : '' }}>
                                TOGA
                            </option>

                            <option value="BIRRETE" {{ old('tipo_producto') === 'BIRRETE' ? 'selected' : '' }}>
                                BIRRETE
                            </option>

                            <option value="COLLARIN" {{ old('tipo_producto') === 'COLLARIN' ? 'selected' : '' }}>
                                COLLARÍN
                            </option>

                            <option value="BORLA" {{ old('tipo_producto') === 'BORLA' ? 'selected' : '' }}>
                                BORLA
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Precio de alquiler</label>
                        <div class="input-group">
                            <span class="input-group-text">Q</span>
                            <input 
                                type="number" 
                                name="precio_alquiler" 
                                class="form-control" 
                                value="{{ old('precio_alquiler', 0) }}"
                                step="0.01"
                                min="0"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="activo" class="form-select" required>
                            <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>
                                Activo
                            </option>

                            <option value="0" {{ old('activo') == '0' ? 'selected' : '' }}>
                                Inactivo
                            </option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea 
                            name="descripcion" 
                            class="form-control" 
                            rows="3"
                            placeholder="Descripción general del producto..."
                        >{{ old('descripcion') }}</textarea>
                    </div>

                </div>
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

                <div id="campos-toga" class="tipo-extra d-none">
                    <div class="row g-4">

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Talla de toga</label>
                            <input 
                                type="text" 
                                name="talla_toga" 
                                class="form-control"
                                value="{{ old('talla_toga') }}"
                                placeholder="Ej. S, M, L, XL"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Color de toga</label>
                            <input 
                                type="text" 
                                name="color_toga" 
                                class="form-control"
                                value="{{ old('color_toga') }}"
                                placeholder="Ej. Negro, azul marino"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea 
                                name="observaciones_toga" 
                                class="form-control" 
                                rows="2"
                                placeholder="Ej. Modelo, tela, zipper..."
                            >{{ old('observaciones_toga') }}</textarea>
                        </div>

                    </div>
                </div>

                <div id="campos-birrete" class="tipo-extra d-none">
                    <div class="row g-4">

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo de birrete</label>
                            <select name="tipo_birrete" class="form-select">
                                <option value="">Selecciona tipo...</option>

                                <option value="ESTANDAR" {{ old('tipo_birrete') === 'ESTANDAR' ? 'selected' : '' }}>
                                    Estándar
                                </option>

                                <option value="NORMAL" {{ old('tipo_birrete') === 'NORMAL' ? 'selected' : '' }}>
                                    Normal
                                </option>

                                <option value="UNIVERSITARIO" {{ old('tipo_birrete') === 'UNIVERSITARIO' ? 'selected' : '' }}>
                                    Universitario
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Color de birrete</label>
                            <input 
                                type="text" 
                                name="color_birrete" 
                                class="form-control"
                                value="{{ old('color_birrete') }}"
                                placeholder="Ej. Negro"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Carrera / área</label>
                            <select name="carrera_birrete" class="form-select">
                                <option value="">Sin carrera específica</option>

                                <option value="ADMINISTRACION" {{ old('carrera_birrete') === 'ADMINISTRACION' ? 'selected' : '' }}>
                                    Administración
                                </option>

                                <option value="AGRONOMIA" {{ old('carrera_birrete') === 'AGRONOMIA' ? 'selected' : '' }}>
                                    Agronomía
                                </option>

                                <option value="DERECHO" {{ old('carrera_birrete') === 'DERECHO' ? 'selected' : '' }}>
                                    Derecho
                                </option>

                                <option value="PEDAGOGIA" {{ old('carrera_birrete') === 'PEDAGOGIA' ? 'selected' : '' }}>
                                    Pedagogía
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="campos-collarin" class="tipo-extra d-none">
                    <div class="row g-4">

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Color de collarín</label>
                            <input 
                                type="text" 
                                name="color_collarin" 
                                class="form-control"
                                value="{{ old('color_collarin') }}"
                                placeholder="Ej. Dorado, azul, rojo..."
                            >
                        </div>

                    </div>
                </div>

                <div id="campos-borla" class="tipo-extra d-none">
                    <div class="alert alert-light border rounded-4 mt-4">
                        <div class="fw-bold mb-3">Detalles de borla</div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Color de borla</label>
                                <input
                                    type="text"
                                    name="borla_color"
                                    class="form-control"
                                    value="{{ old('borla_color') }}"
                                    placeholder="Ej. Roja, dorada, azul..."
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Carrera / área</label>
                                <input
                                    type="text"
                                    name="borla_carrera"
                                    class="form-control"
                                    value="{{ old('borla_carrera') }}"
                                    placeholder="Ej. Derecho, Administración, General..."
                                >
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Observaciones de borla</label>
                                <textarea
                                    name="borla_observaciones"
                                    class="form-control"
                                    rows="2"
                                    placeholder="Detalles adicionales de la borla..."
                                >{{ old('borla_observaciones') }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoProducto = document.getElementById('tipo_producto');

        const camposToga = document.getElementById('campos-toga');
        const camposBirrete = document.getElementById('campos-birrete');
        const camposCollarin = document.getElementById('campos-collarin');
        const camposBorla = document.getElementById('campos-borla');

        const mensajeDetalles = document.getElementById('mensaje-detalles');

        const stockInput = document.getElementById('stock_total');

        const previewStockTotal = document.getElementById('preview_stock_total');
        const previewStockDisponible = document.getElementById('preview_stock_disponible');
        const previewStockAlquilado = document.getElementById('preview_stock_alquilado');

        function ocultarCampos() {
            if (camposToga) camposToga.classList.add('d-none');
            if (camposBirrete) camposBirrete.classList.add('d-none');
            if (camposCollarin) camposCollarin.classList.add('d-none');
            if (camposBorla) camposBorla.classList.add('d-none');
        }

        function mostrarCamposSegunTipo() {
            if (!tipoProducto) return;

            const tipo = tipoProducto.value;

            ocultarCampos();

            if (tipo && mensajeDetalles) {
                mensajeDetalles.classList.add('d-none');
            }

            if (tipo === 'TOGA' && camposToga) {
                camposToga.classList.remove('d-none');
            }

            if (tipo === 'BIRRETE' && camposBirrete) {
                camposBirrete.classList.remove('d-none');
            }

            if (tipo === 'COLLARIN' && camposCollarin) {
                camposCollarin.classList.remove('d-none');
            }

            if (tipo === 'BORLA' && camposBorla) {
                camposBorla.classList.remove('d-none');
            }
        }

        if (tipoProducto) {
            tipoProducto.addEventListener('change', mostrarCamposSegunTipo);
            mostrarCamposSegunTipo();
        }

        function actualizarPreviewStock() {
            if (!stockInput) return;

            let stock = parseInt(stockInput.value, 10);

            if (isNaN(stock) || stock < 0) {
                stock = 0;
            }

            if (previewStockTotal) {
                previewStockTotal.textContent = stock;
            }

            if (previewStockDisponible) {
                previewStockDisponible.textContent = stock;
            }

            if (previewStockAlquilado) {
                previewStockAlquilado.textContent = 0;
            }
        }

        if (stockInput) {
            stockInput.addEventListener('input', actualizarPreviewStock);
            stockInput.addEventListener('change', actualizarPreviewStock);
            actualizarPreviewStock();
        }
    });
</script>

@endsection