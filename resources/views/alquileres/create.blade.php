@extends('layouts.app')

@section('title', 'Nuevo alquiler')
@section('page_title', '➕ Nuevo alquiler')
@section('page_subtitle', 'Registra una reserva o alquiler de togas y accesorios')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">🧾 Crear nuevo alquiler</div>
        <p class="text-muted mb-0">
            Selecciona el cliente, las fechas y los productos que formarán parte del alquiler.
        </p>
    </div>

    <a href="{{ route('alquileres.web') }}" class="btn btn-outline-secondary rounded-pill">
        ← Volver a alquileres
    </a>
</div>

<div class="row g-4">

    <div class="col-lg-4">
        <div class="page-card p-4 h-100">

            <div class="mb-3">
                <div class="stat-icon">🧾</div>
                <h4 class="fw-bold mb-1">Registro de alquiler</h4>
                <p class="text-muted mb-0">
                    Este formulario permite crear una reserva con productos, fechas y saldo pendiente.
                </p>
            </div>

            <hr>

            <div class="mb-3">
                <div class="fw-bold mb-1">Flujo recomendado</div>
                <ol class="text-muted mb-0 ps-3">
                    <li>Selecciona el cliente.</li>
                    <li>Define fecha de entrega y devolución.</li>
                    <li>Marca los productos y cantidades.</li>
                    <li>Aplica descuento si corresponde.</li>
                    <li>Guarda el alquiler.</li>
                </ol>
            </div>

            <div class="alert alert-light border rounded-4 mb-3">
                <strong>Nota:</strong><br>
                Al crear el alquiler todavía no se descuenta inventario. El stock se descuenta cuando marcas el alquiler como <strong>ENTREGADO</strong>.
            </div>

            <div class="alert alert-warning rounded-4 mb-0">
                <strong>Importante:</strong><br>
                Solo aparecerán clientes y productos activos.
            </div>

        </div>
    </div>

    <div class="col-lg-8">
        <div class="page-card p-4">

            <div class="section-title mb-3">📝 Datos del alquiler</div>

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

            <form action="{{ route('alquileres.store') }}"
                  method="POST"
                  class="confirm-action-form"
                  data-title="¿Crear alquiler?"
                  data-text="Se registrará un nuevo alquiler con los productos seleccionados."
                  data-icon="question"
                  data-confirm="Sí, crear alquiler"
                  data-cancel="Cancelar">

                @csrf

                <div class="row g-3">

                    <div class="col-md-12">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select name="cliente_id" id="cliente_id" class="form-select" required>
                            <option value="">Selecciona un cliente...</option>

                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombres }} {{ $cliente->apellidos }}
                                    @if($cliente->telefono)
                                        - {{ $cliente->telefono }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        <small class="text-muted">
                            Solo aparecen clientes activos.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="fecha_entrega" class="form-label">Fecha de entrega</label>
                        <input type="date"
                               name="fecha_entrega"
                               id="fecha_entrega"
                               class="form-control"
                               value="{{ old('fecha_entrega') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="fecha_devolucion_programada" class="form-label">Fecha de devolución programada</label>
                        <input type="date"
                               name="fecha_devolucion_programada"
                               id="fecha_devolucion_programada"
                               class="form-control"
                               value="{{ old('fecha_devolucion_programada') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="descuento" class="form-label">Descuento</label>
                        <input type="number"
                               name="descuento"
                               id="descuento"
                               class="form-control"
                               value="{{ old('descuento', 0) }}"
                               min="0"
                               step="0.01">
                        <small class="text-muted">
                            Coloca 0 si no aplica descuento.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Estado inicial</label>
                        <input type="text"
                               class="form-control"
                               value="RESERVADO"
                               readonly>
                        <small class="text-muted">
                            El alquiler inicia como reservado.
                        </small>
                    </div>

                    <div class="col-md-12">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones"
                                  id="observaciones"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Ej: Entrega para graduación, cliente pagará al retirar, observaciones especiales...">{{ old('observaciones') }}</textarea>
                    </div>

                </div>

                <div class="card mb-4">
                    <div class="card-header fw-bold">
                        Datos para carta de compromiso
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="institucion_representada" class="form-label">
                                    Institución representada
                                </label>
                                <input
                                    type="text"
                                    name="institucion_representada"
                                    id="institucion_representada"
                                    class="form-control"
                                    value="{{ old('institucion_representada') }}"
                                    placeholder="Ej. Centro Profesional de Cómputo CPC"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="representante_alquiler" class="form-label">
                                    Representante o encargado del alquiler
                                </label>
                                <input
                                    type="text"
                                    name="representante_alquiler"
                                    id="representante_alquiler"
                                    class="form-control"
                                    value="{{ old('representante_alquiler') }}"
                                    placeholder="Ej. Nombre de quien atendió el alquiler"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="hora_entrega_inicio" class="form-label">
                                    Hora de entrega inicio
                                </label>
                                <input
                                    type="time"
                                    name="hora_entrega_inicio"
                                    id="hora_entrega_inicio"
                                    class="form-control"
                                    value="{{ old('hora_entrega_inicio') }}"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="hora_entrega_fin" class="form-label">
                                    Hora de entrega fin
                                </label>
                                <input
                                    type="time"
                                    name="hora_entrega_fin"
                                    id="hora_entrega_fin"
                                    class="form-control"
                                    value="{{ old('hora_entrega_fin') }}"
                                >
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0">
                        <div class="fw-bold">🎓 Togas del alquiler</div>
                        <small class="text-muted">
                            Selecciona las togas y configura sus accesorios solo cuando sea necesario.
                        </small>
                    </div>

                    <div class="card-body">

                        @if($togas->count() > 0)

                            @foreach($togas as $toga)
                                <div class="border rounded-4 p-3 mb-3 toga-item" id="toga_item_{{ $toga->id }}">

                                    <div class="row g-3 align-items-center">

                                        <div class="col-md-1 col-2">
                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input producto-check"
                                                    id="producto_{{ $toga->id }}"
                                                    name="productos[{{ $toga->id }}][seleccionado]"
                                                    value="1"
                                                    data-producto="{{ $toga->id }}"
                                                    {{ old("productos.$toga->id.seleccionado") ? 'checked' : '' }}
                                                >
                                            </div>
                                        </div>

                                        <div class="col-md-5 col-10">
                                            <input
                                                type="hidden"
                                                name="productos[{{ $toga->id }}][producto_id]"
                                                value="{{ $toga->id }}"
                                            >

                                            <div class="fw-bold">
                                                {{ $toga->nombre }}
                                            </div>

                                            <div class="text-muted small">
                                                Código: {{ $toga->codigo }}
                                                |
                                                Talla: {{ $toga->toga->talla ?? 'N/A' }}
                                                |
                                                Disponible: {{ $toga->stock_disponible }}
                                            </div>

                                            <div class="small">
                                                Precio: Q {{ number_format($toga->precio_alquiler, 2) }}
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label small fw-semibold mb-1">Cantidad</label>
                                            <input
                                                type="number"
                                                class="form-control cantidad-input"
                                                name="productos[{{ $toga->id }}][cantidad]"
                                                id="cantidad_{{ $toga->id }}"
                                                value="{{ old("productos.$toga->id.cantidad") }}"
                                                min="1"
                                                max="{{ $toga->stock_disponible }}"
                                                placeholder="0"
                                                disabled
                                            >
                                        </div>

                                        <div class="col-md-4">
                                            <div
                                                class="resumen-configuracion small text-muted d-none mb-2"
                                                id="resumen_{{ $toga->id }}"
                                            >
                                                Togas: 0 | Birrete: No | Extras: 0
                                            </div>

                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm rounded-pill btn-toggle-config d-none"
                                                data-producto="{{ $toga->id }}"
                                                id="btn_config_{{ $toga->id }}"
                                            >
                                                Mostrar configuración
                                            </button>
                                        </div>

                                    </div>

                                    <div
                                        class="panel-configuracion mt-3 d-none"
                                        id="panel_config_{{ $toga->id }}"
                                    >
                                        <hr>

                                        <div class="row g-3">

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">
                                                    Collarín obligatorio
                                                </label>

                                                <select
                                                    name="productos[{{ $toga->id }}][collarin_id]"
                                                    id="collarin_{{ $toga->id }}"
                                                    class="form-select accesorio-input"
                                                    disabled
                                                >
                                                    <option value="">Selecciona collarín...</option>

                                                    @foreach($collarines as $collarin)
                                                        <option
                                                            value="{{ $collarin->id }}"
                                                            {{ old("productos.$toga->id.collarin_id") == $collarin->id ? 'selected' : '' }}
                                                        >
                                                            {{ $collarin->nombre }}
                                                            - Disp: {{ $collarin->stock_disponible }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <small class="text-muted">
                                                    Incluido en el precio de la toga.
                                                </small>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-check mb-2">
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input accesorio-check"
                                                        id="birrete_incluido_{{ $toga->id }}"
                                                        name="productos[{{ $toga->id }}][birrete_incluido]"
                                                        value="1"
                                                        data-target="birrete_{{ $toga->id }}"
                                                        data-producto="{{ $toga->id }}"
                                                        disabled
                                                        {{ old("productos.$toga->id.birrete_incluido") ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label fw-semibold" for="birrete_incluido_{{ $toga->id }}">
                                                        Birrete incluido
                                                    </label>
                                                </div>

                                                <select
                                                    name="productos[{{ $toga->id }}][birrete_id]"
                                                    id="birrete_{{ $toga->id }}"
                                                    class="form-select accesorio-select"
                                                    disabled
                                                >
                                                    <option value="">Selecciona birrete...</option>

                                                    @foreach($birretes as $birrete)
                                                        <option
                                                            value="{{ $birrete->id }}"
                                                            {{ old("productos.$toga->id.birrete_id") == $birrete->id ? 'selected' : '' }}
                                                        >
                                                            {{ $birrete->nombre }}
                                                            - Disp: {{ $birrete->stock_disponible }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-check mb-2">
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input accesorio-check"
                                                        id="borla_incluida_{{ $toga->id }}"
                                                        name="productos[{{ $toga->id }}][borla_incluida]"
                                                        value="1"
                                                        data-target="borla_{{ $toga->id }}"
                                                        data-producto="{{ $toga->id }}"
                                                        disabled
                                                        {{ old("productos.$toga->id.borla_incluida") ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label fw-semibold" for="borla_incluida_{{ $toga->id }}">
                                                        Borla incluida
                                                    </label>
                                                </div>

                                                <select
                                                    name="productos[{{ $toga->id }}][borla_id]"
                                                    id="borla_{{ $toga->id }}"
                                                    class="form-select accesorio-select"
                                                    disabled
                                                >
                                                    <option value="">Selecciona borla...</option>

                                                    @foreach($borlas as $borla)
                                                        <option
                                                            value="{{ $borla->id }}"
                                                            {{ old("productos.$toga->id.borla_id") == $borla->id ? 'selected' : '' }}
                                                        >
                                                            {{ $borla->nombre }}
                                                            - Disp: {{ $borla->stock_disponible }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>

                                        <div class="alert alert-light border rounded-4 mt-3 mb-0">
                                            <div class="fw-bold mb-2">Extras cobrables</div>

                                            <div class="row g-3">

                                                <div class="col-md-6">
                                                    <label class="form-label">Birrete extra</label>
                                                    <select
                                                        name="productos[{{ $toga->id }}][birrete_extra_id]"
                                                        id="birrete_extra_{{ $toga->id }}"
                                                        class="form-select accesorio-input extra-input"
                                                        data-producto="{{ $toga->id }}"
                                                        disabled
                                                    >
                                                        <option value="">Sin birrete extra</option>

                                                        @foreach($birretes as $birrete)
                                                            <option
                                                                value="{{ $birrete->id }}"
                                                                {{ old("productos.$toga->id.birrete_extra_id") == $birrete->id ? 'selected' : '' }}
                                                            >
                                                                {{ $birrete->nombre }}
                                                                - Disp: {{ $birrete->stock_disponible }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <input
                                                        type="number"
                                                        name="productos[{{ $toga->id }}][birrete_extra_cantidad]"
                                                        id="birrete_extra_cantidad_{{ $toga->id }}"
                                                        class="form-control mt-2 accesorio-input extra-cantidad"
                                                        data-producto="{{ $toga->id }}"
                                                        value="{{ old("productos.$toga->id.birrete_extra_cantidad") }}"
                                                        min="1"
                                                        placeholder="Cantidad extra"
                                                        disabled
                                                    >

                                                    <small class="text-muted">
                                                        Normal: Q25 | Universitario: Q50
                                                    </small>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Borla extra</label>
                                                    <select
                                                        name="productos[{{ $toga->id }}][borla_extra_id]"
                                                        id="borla_extra_{{ $toga->id }}"
                                                        class="form-select accesorio-input extra-input"
                                                        data-producto="{{ $toga->id }}"
                                                        disabled
                                                    >
                                                        <option value="">Sin borla extra</option>

                                                        @foreach($borlas as $borla)
                                                            <option
                                                                value="{{ $borla->id }}"
                                                                {{ old("productos.$toga->id.borla_extra_id") == $borla->id ? 'selected' : '' }}
                                                            >
                                                                {{ $borla->nombre }}
                                                                - Disp: {{ $borla->stock_disponible }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <input
                                                        type="number"
                                                        name="productos[{{ $toga->id }}][borla_extra_cantidad]"
                                                        id="borla_extra_cantidad_{{ $toga->id }}"
                                                        class="form-control mt-2 accesorio-input extra-cantidad"
                                                        data-producto="{{ $toga->id }}"
                                                        value="{{ old("productos.$toga->id.borla_extra_cantidad") }}"
                                                        min="1"
                                                        placeholder="Cantidad extra"
                                                        disabled
                                                    >

                                                    <small class="text-muted">
                                                        Borla extra: Q5
                                                    </small>
                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                </div>
                            @endforeach

                        @else
                            <div class="alert alert-warning rounded-4">
                                No hay togas activas disponibles para crear alquileres.
                            </div>
                        @endif

                    </div>
                </div>
                <div class="alert alert-light border rounded-4 mt-4">
                    <div class="fw-bold mb-1">Resumen de la acción</div>
                    <div class="text-muted">
                        El sistema calculará el subtotal, descuento, total y saldo pendiente según los productos seleccionados.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                    <a href="{{ route('alquileres.web') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        💾 Crear alquiler
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checks = document.querySelectorAll('.producto-check');

        checks.forEach(function (check) {
            const productoId = check.dataset.productoId;
            const cantidadInput = document.querySelector('.cantidad-input[data-producto-id="' + productoId + '"]');

            function actualizarEstado() {
                if (check.checked) {
                    cantidadInput.disabled = false;

                    if (!cantidadInput.value) {
                        cantidadInput.value = 1;
                    }
                } else {
                    cantidadInput.disabled = true;
                    cantidadInput.value = '';
                }
            }

            check.addEventListener('change', actualizarEstado);
            actualizarEstado();
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checks = document.querySelectorAll('.producto-check');

        function actualizarResumen(productoId) {
            const resumen = document.getElementById('resumen_' + productoId);
            const cantidad = document.getElementById('cantidad_' + productoId);

            const birreteCheck = document.getElementById('birrete_incluido_' + productoId);
            const birreteSelect = document.getElementById('birrete_' + productoId);

            const birreteExtra = document.getElementById('birrete_extra_' + productoId);
            const birreteExtraCantidad = document.getElementById('birrete_extra_cantidad_' + productoId);

            const borlaExtra = document.getElementById('borla_extra_' + productoId);
            const borlaExtraCantidad = document.getElementById('borla_extra_cantidad_' + productoId);

            if (!resumen) return;

            const cantidadTogas = parseInt(cantidad?.value || 0, 10) || 0;

            let birrete = 'No';

            if (
                birreteCheck &&
                birreteCheck.checked &&
                birreteSelect &&
                birreteSelect.value
            ) {
                birrete = 'Sí';
            }

            let totalExtras = 0;

            if (birreteExtra && birreteExtra.value) {
                totalExtras += parseInt(birreteExtraCantidad?.value || 1, 10) || 1;
            }

            if (borlaExtra && borlaExtra.value) {
                totalExtras += parseInt(borlaExtraCantidad?.value || 1, 10) || 1;
            }

            resumen.textContent = 'Togas: ' + cantidadTogas + ' | Birrete: ' + birrete + ' | Extras: ' + totalExtras;
        }

        function actualizarAccesoriosIncluidos(productoId) {
            const birreteCheck = document.getElementById('birrete_incluido_' + productoId);
            const borlaCheck = document.getElementById('borla_incluida_' + productoId);

            [birreteCheck, borlaCheck].forEach(function (check) {
                if (!check) return;

                const target = document.getElementById(check.dataset.target);

                if (target) {
                    target.disabled = !check.checked || check.disabled;
                    target.required = check.checked && !check.disabled;

                    if (!check.checked || check.disabled) {
                        target.value = '';
                    }
                }
            });

            actualizarResumen(productoId);
        }

        function actualizarFila(check) {
            const productoId = check.dataset.producto;

            const activo = check.checked;

            const cantidad = document.getElementById('cantidad_' + productoId);
            const collarin = document.getElementById('collarin_' + productoId);

            const panel = document.getElementById('panel_config_' + productoId);
            const botonConfig = document.getElementById('btn_config_' + productoId);
            const resumen = document.getElementById('resumen_' + productoId);

            const inputsConfiguracion = document.querySelectorAll(
                '#panel_config_' + productoId + ' select, ' +
                '#panel_config_' + productoId + ' input'
            );

            if (cantidad) {
                cantidad.disabled = !activo;
                cantidad.required = activo;

                if (!activo) {
                    cantidad.value = '';
                } else if (!cantidad.value || cantidad.value === '0') {
                    cantidad.value = 1;
                }
            }

            if (collarin) {
                collarin.disabled = !activo;
                collarin.required = activo;

                if (!activo) {
                    collarin.value = '';
                }
            }

            inputsConfiguracion.forEach(function (input) {
                if (input.id === 'collarin_' + productoId) return;

                input.disabled = !activo;

                if (!activo) {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                }
            });

            if (botonConfig) {
                botonConfig.classList.toggle('d-none', !activo);
                botonConfig.textContent = 'Mostrar configuración';
            }

            if (resumen) {
                resumen.classList.toggle('d-none', !activo);
            }

            if (panel) {
                panel.classList.add('d-none');
            }

            actualizarAccesoriosIncluidos(productoId);
            actualizarResumen(productoId);
        }

        checks.forEach(function (check) {
            check.addEventListener('change', function () {
                actualizarFila(check);
            });

            actualizarFila(check);
        });

        const botonesConfig = document.querySelectorAll('.btn-toggle-config');

        botonesConfig.forEach(function (boton) {
            boton.addEventListener('click', function () {
                const productoId = boton.dataset.producto;
                const panel = document.getElementById('panel_config_' + productoId);

                if (!panel) return;

                const oculto = panel.classList.contains('d-none');

                if (oculto) {
                    panel.classList.remove('d-none');
                    boton.textContent = 'Ocultar configuración';
                } else {
                    panel.classList.add('d-none');
                    boton.textContent = 'Mostrar configuración';
                }

                actualizarResumen(productoId);
            });
        });

        const checksAccesorios = document.querySelectorAll('.accesorio-check');

        checksAccesorios.forEach(function (check) {
            check.addEventListener('change', function () {
                const productoId = check.dataset.producto;
                actualizarAccesoriosIncluidos(productoId);
            });
        });

        const camposResumen = document.querySelectorAll('.cantidad-input, .extra-input, .extra-cantidad, .accesorio-select');

        camposResumen.forEach(function (campo) {
            campo.addEventListener('input', function () {
                const productoId = campo.dataset.producto || obtenerProductoIdDesdeId(campo.id);
                actualizarResumen(productoId);
            });

            campo.addEventListener('change', function () {
                const productoId = campo.dataset.producto || obtenerProductoIdDesdeId(campo.id);
                actualizarResumen(productoId);
            });
        });

        function obtenerProductoIdDesdeId(id) {
            if (!id) return null;

            const partes = id.split('_');

            return partes[partes.length - 1];
        }
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form.confirm-action-form');

        function validarCampoExtra(productoId, tipo) {
            const select = document.getElementById(tipo + '_extra_' + productoId);
            const cantidad = document.getElementById(tipo + '_extra_cantidad_' + productoId);

            if (!select || !cantidad) return true;

            const tieneProducto = select.value !== '';
            const tieneCantidad = cantidad.value !== '';

            select.setCustomValidity('');
            cantidad.setCustomValidity('');

            if (!tieneProducto && tieneCantidad) {
                const mensaje = tipo === 'birrete'
                    ? 'Selecciona qué birrete extra será cobrado.'
                    : 'Selecciona qué borla extra será cobrada.';

                select.setCustomValidity(mensaje);
                return false;
            }

            if (tieneProducto && !tieneCantidad) {
                const mensaje = tipo === 'birrete'
                    ? 'Coloca la cantidad de birretes extra.'
                    : 'Coloca la cantidad de borlas extra.';

                cantidad.setCustomValidity(mensaje);
                return false;
            }

            return true;
        }

        function validarExtras() {
            let valido = true;

            document.querySelectorAll('.producto-check:checked').forEach(function (check) {
                const productoId = check.dataset.producto || check.dataset.productoId;

                if (!productoId) return;

                if (!validarCampoExtra(productoId, 'birrete')) {
                    valido = false;
                }

                if (!validarCampoExtra(productoId, 'borla')) {
                    valido = false;
                }
            });

            return valido;
        }

        document.querySelectorAll('.extra-input, .extra-cantidad').forEach(function (campo) {
            campo.addEventListener('input', validarExtras);
            campo.addEventListener('change', validarExtras);
        });

        if (form) {
            form.addEventListener('submit', function (event) {
                if (!validarExtras()) {
                    event.preventDefault();
                    event.stopImmediatePropagation();

                    const primerInvalido = form.querySelector(':invalid');

                    if (primerInvalido) {
                        primerInvalido.reportValidity();
                    }
                }
            }, true);
        }
    });
</script>

@endsection