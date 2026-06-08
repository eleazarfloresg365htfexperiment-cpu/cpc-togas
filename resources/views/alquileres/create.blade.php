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

                <div class="alert alert-light border rounded-4 mt-4">
                    <div class="fw-bold mb-1">📦 Productos disponibles</div>
                    <div class="text-muted">
                        Marca los productos que incluirás en el alquiler y escribe la cantidad correspondiente.
                    </div>
                </div>

                @if($productos->count() > 0)
                    <div class="table-responsive mt-3">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Usar</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Disponible</th>
                                    <th>Precio</th>
                                    <th style="width: 140px;">Cantidad</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($productos as $producto)
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   class="form-check-input producto-check"
                                                   data-producto-id="{{ $producto->id }}"
                                                   id="producto_{{ $producto->id }}"
                                                   {{ old("productos.{$producto->id}.seleccionado") ? 'checked' : '' }}>
                                        </td>

                                        <td>
                                            <label for="producto_{{ $producto->id }}" class="mb-0">
                                                <div class="fw-bold">{{ $producto->codigo }}</div>
                                                <small class="text-muted">{{ $producto->nombre }}</small>
                                            </label>

                                            <input type="hidden"
                                                   name="productos[{{ $producto->id }}][producto_id]"
                                                   value="{{ $producto->id }}">
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
                                            @if($producto->stock_disponible > 0)
                                                <span class="amount-positive">{{ $producto->stock_disponible }}</span>
                                            @else
                                                <span class="amount-negative">0</span>
                                            @endif
                                        </td>

                                        <td>
                                            <strong>Q {{ number_format($producto->precio_alquiler, 2) }}</strong>
                                        </td>

                                        <td>
                                            <input type="number"
                                                   name="productos[{{ $producto->id }}][cantidad]"
                                                   class="form-control cantidad-input"
                                                   data-producto-id="{{ $producto->id }}"
                                                   value="{{ old("productos.{$producto->id}.cantidad") }}"
                                                   min="1"
                                                   max="{{ $producto->stock_disponible }}"
                                                   placeholder="0"
                                                   disabled>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning rounded-4 mb-0">
                        No hay productos activos disponibles para crear alquileres.
                    </div>
                @endif

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

@endsection