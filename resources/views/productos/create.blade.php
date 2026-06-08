@extends('layouts.app')

@section('title', 'Registrar producto')
@section('page_title', '➕ Registrar producto')
@section('page_subtitle', 'Agrega un nuevo producto al inventario de togas, birretes o collarines')

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
                            <option value="">Seleccione un tipo</option>

                            <option value="TOGA" {{ old('tipo_producto') == 'TOGA' ? 'selected' : '' }}>
                                TOGA
                            </option>

                            <option value="BIRRETE" {{ old('tipo_producto') == 'BIRRETE' ? 'selected' : '' }}>
                                BIRRETE
                            </option>

                            <option value="COLLARIN" {{ old('tipo_producto') == 'COLLARIN' ? 'selected' : '' }}>
                                COLLARÍN
                            </option>

                            <option value="BORLA" {{ old('tipo_producto') == 'BORLA' ? 'selected' : '' }}>
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

                <div id="mensaje-tipo" class="alert alert-light border rounded-4 mb-0">
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
                            <label class="form-label fw-semibold">Color de birrete</label>
                            <input 
                                type="text" 
                                name="color_birrete" 
                                class="form-control"
                                value="{{ old('color_birrete') }}"
                                placeholder="Ej. Negro"
                            >
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
                    <div class="row g-4">

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Color de borla</label>
                            <input
                                type="text"
                                name="borla_color"
                                class="form-control"
                                value="{{ old('borla_color') }}"
                                placeholder="Ej. Dorada, roja, azul..."
                            >
                        </div>

                        <div class="col-md-4">
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
                    class="form-control form-control-lg" 
                    value="{{ old('stock_total', 0) }}"
                    min="0"
                    required
                >

                <div class="alert alert-light border rounded-4 mt-3 mb-0">
                    <div class="small text-muted mb-1">Al guardar:</div>

                    <div class="d-flex justify-content-between">
                        <span>Stock total</span>
                        <strong id="preview_stock_total">{{ old('stock_total', 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Stock disponible</span>
                        <strong id="preview_stock_disponible">{{ old('stock_total', 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Stock alquilado</span>
                        <strong>0</strong>
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
        const tipoSelect = document.getElementById('tipo_producto');

        const mensajeTipo = document.getElementById('mensaje-tipo');
        const camposToga = document.getElementById('campos-toga');
        const camposBirrete = document.getElementById('campos-birrete');
        const camposCollarin = document.getElementById('campos-collarin');
        const camposBorla = document.getElementById('campos-borla');

        const stockInput = document.getElementById('stock_total');
        const previewStockTotal = document.getElementById('preview_stock_total');
        const previewStockDisponible = document.getElementById('preview_stock_disponible');

        function ocultarCampos() {
            camposToga.classList.add('d-none');
            camposBirrete.classList.add('d-none');
            camposCollarin.classList.add('d-none');
            camposBorla.classList.add('d-none');
            
        }

        function mostrarCampos() {
            const tipo = tipoSelect.value;

            ocultarCampos();

            if (!tipo) {
                mensajeTipo.classList.remove('d-none');
                return;
            }

            mensajeTipo.classList.add('d-none');

            if (tipo === 'TOGA') {
                camposToga.classList.remove('d-none');
            }

            if (tipo === 'BIRRETE') {
                camposBirrete.classList.remove('d-none');
            }

            if (tipo === 'COLLARIN') {
                camposCollarin.classList.remove('d-none');
            }

            if (tipo === 'BORLA') {
                camposBorla.classList.remove('d-none');
            }
            
        }

        function actualizarPreviewStock() {
            const valor = stockInput.value || 0;

            previewStockTotal.textContent = valor;
            previewStockDisponible.textContent = valor;
        }

        tipoSelect.addEventListener('change', mostrarCampos);
        stockInput.addEventListener('input', actualizarPreviewStock);

        mostrarCampos();
        actualizarPreviewStock();
    });
</script>

@endsection