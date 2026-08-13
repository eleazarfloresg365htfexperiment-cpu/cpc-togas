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
                    Solo aparecerán clientes y productos activos.<br>
                    Si algún producto falta en stock, activa la fabricación para registrar cantidades pendientes.
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
                    <input type="hidden" name="fabricacion_autorizada" id="fabricacion_autorizada_hidden" value="{{ old('fabricacion_autorizada', 0) }}">

                    <div class="row g-3">

                        <div class="col-md-12">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="form-select" required>
                                <option value="">Seleccione un cliente</option>

                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}"
                                            data-institucion="{{ $cliente->institucion_representada }}"
                                            {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombres }} {{ $cliente->apellidos }}
                                        @if($cliente->institucion_representada)
                                            - {{ $cliente->institucion_representada }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            <small class="text-muted">
                                Solo aparecen clientes activos.
                            </small>
                        </div>

                        <div class="row g-3 align-items-start">
                            <div class="col-md-4">
                                <label class="form-label d-flex align-items-end" style="min-height: 48px;">
                                    Fecha de reserva
                                </label>
                                <input type="date"
                                    name="fecha_alquiler"
                                    id="fecha_alquiler"
                                    class="form-control"
                                    value="{{ old('fecha_alquiler', now()->toDateString()) }}"
                                    required>
                                <small class="text-muted">
                                    Día en que se registra o acuerda la reserva.
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-flex align-items-end" style="min-height: 48px;">
                                    Fecha de entrega
                                </label>
                                <input type="date"
                                    name="fecha_entrega"
                                    id="fecha_entrega"
                                    class="form-control"
                                    value="{{ old('fecha_entrega') }}"
                                    required>
                                <small class="text-muted">
                                    Día programado para retirar las togas.
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-flex align-items-end" style="min-height: 48px;">
                                    Hora de entrega
                                </label>
                                <input type="time"
                                    name="hora_entrega"
                                    class="form-control"
                                    value="{{ old('hora_entrega') }}">
                                <small class="text-muted">
                                    Opcional.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-end" style="min-height: 48px;">
                                    Fecha de devolución programada
                                </label>
                                <input type="date"
                                    name="fecha_devolucion_programada"
                                    id="fecha_devolucion_programada"
                                    class="form-control"
                                    value="{{ old('fecha_devolucion_programada') }}"
                                    required>
                                <small class="text-muted">
                                    Día límite para devolver sin mora.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-end" style="min-height: 48px;">
                                    Hora de devolución programada
                                </label>
                                <input type="time"
                                    name="hora_devolucion_programada"
                                    class="form-control"
                                    value="{{ old('hora_devolucion_programada') }}">
                                <small class="text-muted">
                                    Opcional.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="descuento" class="form-label">
                                Descuento general
                            </label>

                            <input
                                type="number"
                                name="descuento"
                                id="descuento"
                                class="form-control"
                                value="{{ old('descuento', 0) }}"
                                min="0"
                                step="0.01"
                            >

                            <small class="text-muted">
                                Descuento adicional aplicado al alquiler.
                            </small>
                        </div>

                        <div class="col-md-4">
                            <label for="descuento_por_toga" class="form-label">
                                Descuento por toga
                            </label>

                            <input
                                type="number"
                                name="descuento_por_toga"
                                id="descuento_por_toga"
                                class="form-control"
                                value="{{ old('descuento_por_toga', 0) }}"
                                min="0"
                                step="0.01"
                            >

                            <small class="text-muted">
                                Monto de descuento aplicado a cada toga.
                            </small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Descuento por togas
                            </label>

                            <input
                                type="text"
                                id="descuento_toga_preview"
                                class="form-control"
                                value="Q 0.00"
                                readonly
                            >

                            <small class="text-muted">
                                Se calcula según la cantidad de togas.
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
                            <div class="card border-0 shadow-sm rounded-4 mb-3 d-none" id="fabricacion_meta_panel">
                                <div class="card-body p-3">
                                    <div class="fw-semibold mb-3">Autorizar fabricación</div>
                                    <p class="text-muted mb-3">
                                        Completa esta información solo si alguna toga requiere fabricación porque la cantidad solicitada excede el stock disponible.
                                    </p>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="fabricacion_responsable" class="form-label">Responsable</label>
                                            <input
                                                type="text"
                                                name="fabricacion_responsable"
                                                id="fabricacion_responsable"
                                                class="form-control"
                                                value="{{ old('fabricacion_responsable') }}"
                                                placeholder="Nombre del responsable">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="fabricacion_motivo" class="form-label">Motivo</label>
                                            <input
                                                type="text"
                                                name="fabricacion_motivo"
                                                id="fabricacion_motivo"
                                                class="form-control"
                                                value="{{ old('fabricacion_motivo') }}"
                                                placeholder="Ej. falta de stock">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="fabricacion_observaciones" class="form-label">Observaciones</label>
                                            <input
                                                type="text"
                                                name="fabricacion_observaciones"
                                                id="fabricacion_observaciones"
                                                class="form-control"
                                                value="{{ old('fabricacion_observaciones') }}"
                                                placeholder="Opcional">
                                        </div>
                                    </div>

                                    <div class="alert alert-info rounded-4 mt-3 mb-0">
                                        Si alguna toga falta en stock, habilita la autorización en el panel de esa toga para registrar la diferencia como fabricación pendiente.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="institucion_representada" class="form-label">Institución representada</label>
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
                                    <div
                                        class="border rounded-4 p-3 mb-3 toga-item"
                                        id="toga_item_{{ $toga->id }}"
                                        data-tipo="{{ $toga->toga->tipo_toga ?? 'ESTANDAR' }}"
                                        data-stock="{{ $toga->stock_disponible }}"
                                        data-precio="{{ $toga->precio_alquiler }}"
                                    >

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
                                                        data-producto-id="{{ $toga->id }}"
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
                                                    data-producto-id="{{ $toga->id }}"
                                                    value="{{ old("productos.$toga->id.cantidad") }}"
                                                    min="1"
                                                    placeholder="0"
                                                    disabled
                                                >
                                            </div>

                                            <div class="col-md-4 col-10">
                                                <div class="form-check mb-3 d-none" id="fabricacion_notice_{{ $toga->id }}">
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input producto-fabricacion-checkbox"
                                                        id="fabricacion_producto_{{ $toga->id }}"
                                                        name="productos[{{ $toga->id }}][fabricacion_autorizada]"
                                                        value="1"
                                                        data-producto="{{ $toga->id }}"
                                                        {{ old("productos.$toga->id.fabricacion_autorizada") ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label fw-semibold" for="fabricacion_producto_{{ $toga->id }}">
                                                        Autorizar fabricación para esta toga si el stock no alcanza
                                                    </label>
                                                </div>

                                                <div class="alert alert-warning rounded-4 mb-3 d-none" id="stock_alert_{{ $toga->id }}">
                                                    <div class="fw-semibold mb-1">
                                                        La cantidad solicitada excede el stock disponible.
                                                    </div>
                                                    <div>
                                                        Solicitaste más de {{ $toga->stock_disponible }} unidades. Si deseas continuar, activa la autorización de fabricación.
                                                    </div>
                                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary btn-reset-stock"
                                                                data-producto="{{ $toga->id }}"
                                                                data-stock="{{ $toga->stock_disponible }}">
                                                            Cancelar
                                                        </button>

                                                        <button type="button"
                                                                class="btn btn-sm btn-primary btn-authorize-fabricacion"
                                                                data-producto="{{ $toga->id }}">
                                                            Ignorar límite y autorizar fabricación
                                                        </button>
                                                    </div>
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
                                                                data-tipo="{{ $collarin->collarin->tipo_collarin }}"
                                                                data-color="{{ $collarin->collarin->color }}"
                                                                {{ old("productos.$toga->id.collarin_id") == $collarin->id ? 'selected' : '' }}
                                                            >
                                                                {{ $collarin->nombre }}
                                                                ({{ $collarin->collarin->tipo_collarin }} - {{ $collarin->collarin->color ?? 'Sin color' }})
                                                                - Disp: {{ $collarin->stock_disponible }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    {{-- CAPA (solo para togas universitarias) --}}
                                                    <div class="col-md-4 capa-container d-none">

                                                        <label class="form-label fw-semibold">
                                                            Capa
                                                        </label>

                                                        <select
                                                            name="productos[{{ $toga->id }}][capa_id]"
                                                            id="capa_{{ $toga->id }}"
                                                            class="form-select accesorio-input"
                                                            disabled
                                                        >
                                                            <option value="">Selecciona una capa...</option>

                                                            @foreach($capas as $capa)
                                                                <option
                                                                    value="{{ $capa->id }}"
                                                                    data-carrera="{{ $capa->capa->carrera }}"
                                                                    {{ old("productos.$toga->id.capa_id") == $capa->id ? 'selected' : '' }}
                                                                >
                                                                    {{ $capa->nombre }}
                                                                    - Disp: {{ $capa->stock_disponible }}
                                                                </option>
                                                            @endforeach

                                                        </select>

                                                    </div>

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
                                                                data-tipo="{{ $birrete->birrete->tipo_birrete }}"
                                                                {{ old("productos.$toga->id.birrete_id") == $birrete->id ? 'selected' : '' }}
                                                            >
                                                                {{ $birrete->nombre }}
                                                                ({{ $birrete->birrete->tipo_birrete }})
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
                                                                data-tipo="{{ $borla->borla->tipo_borla }}"
                                                                data-color="{{ $borla->borla->color ?? '' }}"
                                                                {{ old("productos.$toga->id.borla_id") == $borla->id ? 'selected' : '' }}
                                                            >
                                                                {{ $borla->nombre }}
                                                                ({{ $borla->borla->tipo_borla }} - {{ $borla->borla->color ?? 'Sin color' }})
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
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-white border-0">
                            <div class="fw-bold">💰 Resumen del alquiler</div>
                                <small class="text-muted">
                                    El resumen se actualiza automáticamente según los productos, cantidades y descuento ingresados.
                                </small>
                            </div>
                            <div class="card-body">

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="text-muted small">
                                            Subtotal
                                        </div>

                                        <div class="fs-4 fw-bold">
                                            Q <span id="resumen_subtotal">0.00</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="text-muted small">
                                            Descuento
                                        </div>

                                        <div class="fs-4 fw-bold text-danger">
                                            - Q <span id="resumen_descuento">0.00</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="text-muted small">
                                            Total
                                        </div>

                                        <div class="fs-4 fw-bold text-primary">
                                            Q <span id="resumen_total">0.00</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">
                                        Saldo pendiente inicial
                                    </div>

                                    <small class="text-muted">
                                        El alquiler se crea inicialmente sin pagos registrados.
                                    </small>
                                </div>

                                <div class="fs-4 fw-bold">
                                    Q <span id="resumen_saldo">0.00</span>
                                </div>
                            </div>

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

        const hiddenFabricacionCheckbox =
            document.getElementById('fabricacion_autorizada_hidden');

        const fabricacionMetaPanel =
            document.getElementById('fabricacion_meta_panel');


        /*
        |--------------------------------------------------------------------------
        | Fabricación global
        |--------------------------------------------------------------------------
        |
        | Se activa únicamente si alguna toga tiene autorizado fabricar
        | el excedente.
        |
        */
        function actualizarFabricacionGlobal() {

            const anyFabricacion =
                !!document.querySelector('.producto-fabricacion-checkbox:checked');

            if (hiddenFabricacionCheckbox) {
                hiddenFabricacionCheckbox.value = anyFabricacion ? '1' : '0';
            }

            if (fabricacionMetaPanel) {
                fabricacionMetaPanel.classList.toggle(
                    'd-none',
                    !anyFabricacion
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Actualizar alerta de stock
        |--------------------------------------------------------------------------
        */
        function actualizarStockAlert(productoId) {

            const cantidadInput =
                document.getElementById('cantidad_' + productoId);

            const togaItem =
                document.getElementById('toga_item_' + productoId);

            const stockAlert =
                document.getElementById('stock_alert_' + productoId);

            const fabricacionNotice =
                document.getElementById('fabricacion_notice_' + productoId);

            const cantidad =
                parseInt(cantidadInput?.value || '0', 10) || 0;

            const stock =
                parseInt(togaItem?.dataset.stock || '0', 10) || 0;

            const exceso = cantidad > stock;


            /*
            |--------------------------------------------------------------------------
            | Alerta de exceso
            |--------------------------------------------------------------------------
            */
            if (stockAlert) {
                stockAlert.classList.toggle(
                    'd-none',
                    !exceso ||
                    !cantidadInput ||
                    cantidadInput.disabled
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Panel de fabricación
            |--------------------------------------------------------------------------
            */
            if (fabricacionNotice) {

                fabricacionNotice.classList.toggle(
                    'd-none',
                    !exceso
                );


                /*
                | Si ya no existe exceso, se cancela automáticamente
                | la autorización de fabricación de ese producto.
                |
                | IMPORTANTE:
                | Esto NO toca el checkbox .producto-check.
                */
                if (!exceso) {

                    const checkbox =
                        fabricacionNotice.querySelector(
                            '.producto-fabricacion-checkbox'
                        );

                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            }


            actualizarFabricacionGlobal();
        }


        /*
        |--------------------------------------------------------------------------
        | Inicializar controles de stock
        |--------------------------------------------------------------------------
        */
        function inicializarStockAlerts() {

            document
                .querySelectorAll('.cantidad-input')
                .forEach(function (input) {

                    const productoId =
                        input.dataset.productoId ||
                        obtenerProductoIdDesdeId(input.id);


                    input.addEventListener('input', function () {
                        actualizarStockAlert(productoId);
                    });


                    input.addEventListener('change', function () {
                        actualizarStockAlert(productoId);
                    });


                    actualizarStockAlert(productoId);
                });
        }


        /*
        |--------------------------------------------------------------------------
        | Obtener ID del producto
        |--------------------------------------------------------------------------
        */
        function obtenerProductoIdDesdeId(id) {

            if (!id) {
                return null;
            }

            const partes = id.split('_');

            return partes[partes.length - 1];
        }


        /*
        |--------------------------------------------------------------------------
        | OMITIR EXCEDENTE
        |--------------------------------------------------------------------------
        |
        | Reduce la cantidad hasta el stock disponible.
        |
        | MUY IMPORTANTE:
        | NO deselecciona la toga.
        |
        */
        document
            .querySelectorAll('.btn-reset-stock')
            .forEach(function (button) {

                button.addEventListener('click', function () {

                    const productoId =
                        button.dataset.producto;

                    const stock =
                        parseInt(
                            button.dataset.stock || '0',
                            10
                        ) || 0;

                    const cantidadInput =
                        document.getElementById(
                            'cantidad_' + productoId
                        );


                    if (!cantidadInput) {
                        return;
                    }


                    /*
                    | Si existe stock, usamos todo el disponible.
                    | Si no existe stock, dejamos vacío.
                    */
                    cantidadInput.value =
                        stock > 0 ? stock : '';


                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANTE
                    |--------------------------------------------------------------------------
                    | No hacemos:
                    |
                    | checkbox.checked = false;
                    |
                    | La toga sigue seleccionada.
                    |--------------------------------------------------------------------------
                    */


                    cantidadInput.dispatchEvent(
                        new Event('input', {
                            bubbles: true
                        })
                    );

                    cantidadInput.dispatchEvent(
                        new Event('change', {
                            bubbles: true
                        })
                    );
                });
            });


        /*
        |--------------------------------------------------------------------------
        | AUTORIZAR FABRICACIÓN
        |--------------------------------------------------------------------------
        */
        document
            .querySelectorAll('.btn-authorize-fabricacion')
            .forEach(function (button) {

                button.addEventListener('click', function () {

                    const productoId =
                        button.dataset.producto;

                    const notice =
                        document.getElementById(
                            'fabricacion_notice_' + productoId
                        );

                    const checkbox =
                        document.getElementById(
                            'fabricacion_producto_' + productoId
                        );


                    if (!notice || !checkbox) {
                        return;
                    }


                    notice.classList.remove('d-none');

                    checkbox.checked = true;

                    actualizarFabricacionGlobal();
                });
            });


        /*
        |--------------------------------------------------------------------------
        | Cambio manual de autorización de fabricación
        |--------------------------------------------------------------------------
        */
        document
            .querySelectorAll('.producto-fabricacion-checkbox')
            .forEach(function (checkbox) {

                checkbox.addEventListener('change', function () {

                    actualizarFabricacionGlobal();

                });
            });


        /*
        |--------------------------------------------------------------------------
        | Cambio de selección de toga
        |--------------------------------------------------------------------------
        */
        document
            .querySelectorAll('.producto-check')
            .forEach(function (check) {

                check.addEventListener('change', function () {

                    const productoId =
                        check.dataset.producto ||
                        check.dataset.productoId;

                    actualizarStockAlert(productoId);

                });
            });


        /*
        |--------------------------------------------------------------------------
        | Inicialización
        |--------------------------------------------------------------------------
        */
        inicializarStockAlerts();

        actualizarFabricacionGlobal();

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

                if (birreteCheck) {
                    const borlaContainer = document.getElementById('borla_incluida_' + productoId)?.closest('.form-check');

                    if (borlaContainer) {
                        borlaContainer.style.opacity = birreteCheck.checked ? '' : '0.5';
                    }

                    if (!birreteCheck.checked) {
                        if (borlaCheck) {
                            borlaCheck.checked = false;
                            borlaCheck.disabled = true;
                        }
                    } else if (borlaCheck) {
                        borlaCheck.disabled = false;
                    }
                }

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

                const togaItem = document.getElementById('toga_item_' + productoId);

                const tipoToga = togaItem?.dataset.tipo || '';

                const capaContainer = togaItem?.querySelector('.capa-container');
                const capaSelect = document.getElementById('capa_' + productoId);

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

                // Mostrar la capa únicamente para togas universitarias.
                if (capaContainer && capaSelect) {

                    const esUniversitaria = tipoToga === 'UNIVERSITARIA';

                    capaContainer.classList.toggle('d-none', !esUniversitaria);

                    capaSelect.disabled = !activo || !esUniversitaria;

                    capaSelect.required = activo && esUniversitaria;

                    if (!activo || !esUniversitaria) {
                        capaSelect.value = '';
                    }
                }

                if (collarin) {
                    const opciones = Array.from(collarin.options);

                    opciones.forEach(function (opcion) {
                        if (!opcion.value) {
                            return;
                        }

                        const tipoCollarin = opcion.dataset.tipo;

                        if (tipoToga === 'ESTANDAR') {
                            opcion.hidden = tipoCollarin !== 'NORMAL';
                        } else {
                            opcion.hidden = false;
                        }
                    });

                    if (collarin.value) {
                        const selectedOption = collarin.selectedOptions[0];
                        if (selectedOption && selectedOption.hidden) {
                            collarin.value = '';
                        }
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

                actualizarBorlaIncluida(productoId);
                actualizarAccesoriosIncluidos(productoId);
                actualizarResumen(productoId);
            }

            function obtenerColorDeCapa(productoId) {
                const capaSelect = document.getElementById('capa_' + productoId);
                const opcion = capaSelect?.selectedOptions?.[0];
                const carrera = opcion?.dataset?.carrera;

                const coloresPorCarrera = {
                    'DERECHO': 'Rojo',
                    'AGRONOMIA': 'Verde',
                    'PEDAGOGIA': 'Celeste',
                    'MEDICINA': 'Amarillo',
                    'CIENCIAS_ECONOMICAS': 'Naranja'
                };

                return coloresPorCarrera[carrera] || null;
            }

            function seleccionarBorlaPorColor(productoId, color) {
                const borlaSelect = document.getElementById('borla_' + productoId);
                if (!borlaSelect || !color) {
                    return false;
                }

                const opcionCoincidente = Array.from(borlaSelect.options).find(function (opcion) {
                    return opcion.dataset.color === color;
                });

                if (opcionCoincidente) {
                    borlaSelect.value = opcionCoincidente.value;
                    return true;
                }

                return false;
            }

            checks.forEach(function (check) {
                check.addEventListener('change', function () {
                    actualizarFila(check);
                });

                actualizarFila(check);
            });

            /*
            |--------------------------------------------------------------------------
            | Cambio de collarín
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll('.accesorio-select[id^="collarin_"]')
                .forEach(function (select) {

                    select.addEventListener('change', function () {

                        const productoId =
                            select.dataset.producto ||
                            select.id.replace('collarin_', '');

                        actualizarBorlaIncluida(productoId);
                    });

                });


            /*
            |--------------------------------------------------------------------------
            | Cambio de capa
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll('.accesorio-select[id^="capa_"]')
                .forEach(function (select) {

                    select.addEventListener('change', function () {

                        const productoId =
                            select.dataset.producto ||
                            select.id.replace('capa_', '');

                        actualizarBorlaIncluida(productoId);
                    });

                });


            /*
            |--------------------------------------------------------------------------
            | Cambio de borla incluida
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll('.accesorio-check[id^="borla_incluida_"]')
                .forEach(function (check) {

                    check.addEventListener('change', function () {

                        const productoId =
                            check.dataset.producto ||
                            check.id.replace('borla_incluida_', '');

                        actualizarBorlaIncluida(productoId);
                    });
                });

            function obtenerColorCollarin(productoId) {
                const select = document.getElementById('collarin_' + productoId);

                if (!select || !select.value) {
                    return null;
                }

                const opcion = select.options[select.selectedIndex];

                return opcion?.dataset.color || null;
            }

            function actualizarBorlaIncluida(productoId) {

                const togaItem =
                    document.getElementById('toga_item_' + productoId);

                const borlaSelect =
                    document.getElementById('borla_' + productoId);

                const collarinSelect =
                    document.getElementById('collarin_' + productoId);

                const borlaCheck =
                    document.getElementById('borla_incluida_' + productoId);

                if (!togaItem || !borlaSelect || !borlaCheck) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | La lógica solamente aplica a la BORLA INCLUIDA
                |--------------------------------------------------------------------------
                */

                if (!borlaCheck.checked) {
                    return;
                }


                const tipoToga =
                    (togaItem.dataset.tipo || '').toUpperCase();


                /*
                |--------------------------------------------------------------------------
                | Determinar color requerido
                |--------------------------------------------------------------------------
                */

                let colorRequerido = null;


                /*
                |--------------------------------------------------------------------------
                | TOGA UNIVERSITARIA
                |--------------------------------------------------------------------------
                |
                | El color depende de la carrera seleccionada en la capa.
                |
                */

                if (tipoToga === 'UNIVERSITARIA') {

                    colorRequerido =
                        obtenerColorDeCapa(productoId);

                }


                /*
                |--------------------------------------------------------------------------
                | TOGA NORMAL
                |--------------------------------------------------------------------------
                |
                | El color depende del collarín seleccionado.
                |
                */

                else {

                    const collarinOption =
                        collarinSelect?.options[
                            collarinSelect.selectedIndex
                        ];

                    colorRequerido =
                        collarinOption?.dataset?.color || null;
                }


                /*
                |--------------------------------------------------------------------------
                | Si todavía no tenemos color, limpiamos la selección.
                |--------------------------------------------------------------------------
                */

                if (!colorRequerido) {

                    borlaSelect.value = '';

                    borlaSelect.querySelectorAll('option').forEach(function (option) {

                        option.hidden = false;

                    });

                    return;
                }


                colorRequerido =
                    colorRequerido.toUpperCase();


                /*
                |--------------------------------------------------------------------------
                | Mostrar únicamente la borla del color correspondiente.
                |--------------------------------------------------------------------------
                */

                borlaSelect.querySelectorAll('option').forEach(function (option) {

                    /*
                    | La opción vacía siempre permanece disponible.
                    */

                    if (!option.value) {

                        option.hidden = false;

                        return;
                    }


                    const colorBorla =
                        (option.dataset.color || '').toUpperCase();


                    option.hidden =
                        colorBorla !== colorRequerido;

                });


                /*
                |--------------------------------------------------------------------------
                | Seleccionar automáticamente la borla correspondiente.
                |--------------------------------------------------------------------------
                */

                const opcionCorrecta =
                    Array.from(borlaSelect.options).find(function (option) {

                        return (
                            option.value &&
                            !option.hidden &&
                            (option.dataset.color || '').toUpperCase() ===
                                colorRequerido
                        );

                    });


                if (opcionCorrecta) {

                    borlaSelect.value =
                        opcionCorrecta.value;

                } else {

                    borlaSelect.value = '';

                }
            }

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

            const checksAccesorios =
                document.querySelectorAll('.accesorio-check');

            checksAccesorios.forEach(function (check) {

                check.addEventListener('change', function () {

                    const productoId =
                        check.dataset.producto;

                    actualizarAccesoriosIncluidos(productoId);

                    if (check.id.startsWith('borla_incluida_')) {
                        actualizarBorlaIncluida(productoId);
                    }

                });

            });

            const birreteSelects = document.querySelectorAll('.accesorio-select[id^="birrete_"]');

            birreteSelects.forEach(function (select) {
                select.addEventListener('change', function () {
                    const productoId = select.dataset.producto || select.id.replace('birrete_', '');
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hiddenFabricacionCheckbox = document.getElementById('fabricacion_autorizada_hidden');
            const fabricacionMetaPanel = document.getElementById('fabricacion_meta_panel');

            function actualizarFabricacionGlobal() {
                const anyFabricacion = !!document.querySelector('.producto-fabricacion-checkbox:checked');

                if (hiddenFabricacionCheckbox) {
                    hiddenFabricacionCheckbox.value = anyFabricacion ? '1' : '0';
                }

                if (fabricacionMetaPanel) {
                    fabricacionMetaPanel.classList.toggle('d-none', !anyFabricacion);
                }
            }

            function actualizarStockAlert(productoId) {
                const cantidadInput = document.getElementById('cantidad_' + productoId);
                const togaItem = document.getElementById('toga_item_' + productoId);
                const stockAlert = document.getElementById('stock_alert_' + productoId);
                const fabricacionNotice = document.getElementById('fabricacion_notice_' + productoId);
                const cantidad = parseInt(cantidadInput?.value || '0', 10) || 0;
                const stock = parseInt(togaItem?.dataset.stock || '0', 10) || 0;
                const exceso = cantidad > stock;

                if (stockAlert) {
                    stockAlert.classList.toggle('d-none', !exceso || !cantidadInput || cantidadInput.disabled);
                }

                if (fabricacionNotice) {
                    fabricacionNotice.classList.toggle('d-none', !exceso);
                }

                if (!exceso && fabricacionNotice) {
                    const checkbox = fabricacionNotice.querySelector('.producto-fabricacion-checkbox');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }

                actualizarFabricacionGlobal();
            }

            function inicializarStockAlerts() {
                document.querySelectorAll('.cantidad-input').forEach(function (input) {
                    const productoId = input.dataset.productoId || obtenerProductoIdDesdeId(input.id);

                    input.addEventListener('input', function () {
                        actualizarStockAlert(productoId);
                    });

                    input.addEventListener('change', function () {
                        actualizarStockAlert(productoId);
                    });

                    actualizarStockAlert(productoId);
                });
            }

            function obtenerProductoIdDesdeId(id) {
                if (!id) return null;
                const partes = id.split('_');
                return partes[partes.length - 1];
            }

            document.querySelectorAll('.btn-reset-stock').forEach(function (button) {
                button.addEventListener('click', function () {
                    const productoId = button.dataset.producto;
                    const stock = parseInt(button.dataset.stock || '0', 10) || 0;
                    const cantidadInput = document.getElementById('cantidad_' + productoId);

                    if (cantidadInput) {
                        cantidadInput.value = stock > 0 ? stock : '';
                        cantidadInput.dispatchEvent(new Event('input'));
                        cantidadInput.dispatchEvent(new Event('change'));
                    }
                });
            });

            document.querySelectorAll('.btn-authorize-fabricacion').forEach(function (button) {
                button.addEventListener('click', function () {
                    const productoId = button.dataset.producto;
                    const notice = document.getElementById('fabricacion_notice_' + productoId);
                    const checkbox = document.getElementById('fabricacion_producto_' + productoId);

                    if (notice && checkbox) {
                        notice.classList.remove('d-none');
                        checkbox.checked = true;
                        actualizarFabricacionGlobal();
                    }
                });
            });

            document.querySelectorAll('.producto-fabricacion-checkbox').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    actualizarFabricacionGlobal();
                });
            });

            document.querySelectorAll('.producto-check').forEach(function (check) {
                check.addEventListener('change', function () {
                    const productoId = check.dataset.producto || check.dataset.productoId;
                    actualizarStockAlert(productoId);
                });
            });

            inicializarStockAlerts();
            actualizarFabricacionGlobal();
        });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const clienteSelect = document.getElementById('cliente_id');
        const institucionInput = document.getElementById('institucion_representada');

        if (!clienteSelect || !institucionInput) {
            return;
        }

        function completarInstitucionDesdeCliente() {
            const selectedOption = clienteSelect.options[clienteSelect.selectedIndex];

            if (!selectedOption) {
                return;
            }

            const institucion = selectedOption.dataset.institucion || '';

            institucionInput.value = institucion;
        }

        clienteSelect.addEventListener('change', completarInstitucionDesdeCliente);

        if (clienteSelect.value && !institucionInput.value) {
            completarInstitucionDesdeCliente();
        }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const descuentoInput = document.getElementById('descuento');

        const subtotalElement = document.getElementById('resumen_subtotal');
        const descuentoElement = document.getElementById('resumen_descuento');
        const totalElement = document.getElementById('resumen_total');
        const saldoElement = document.getElementById('resumen_saldo');


        function numero(valor) {
            const numero = parseFloat(valor);

            return Number.isFinite(numero) ? numero : 0;
        }


        function dinero(valor) {
            return numero(valor).toFixed(2);
        }


        function calcularResumen() {

            let subtotal = 0;


            /*
            |--------------------------------------------------------------------------
            | Productos principales
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('.toga-item').forEach(function (item) {

                const productoId = item.id.replace('toga_item_', '');

                const checkbox = document.getElementById('producto_' + productoId);
                const cantidadInput = document.getElementById('cantidad_' + productoId);

                if (!checkbox || !checkbox.checked || !cantidadInput) {
                    return;
                }

                const cantidad = numero(cantidadInput.value);

                if (cantidad <= 0) {
                    return;
                }

                const precio = numero(item.dataset.precio);

                subtotal += precio * cantidad;


                /*
                |--------------------------------------------------------------------------
                | Birrete extra
                |--------------------------------------------------------------------------
                */

                const birreteExtra = document.getElementById(
                    'birrete_extra_' + productoId
                );

                const birreteExtraCantidad = document.getElementById(
                    'birrete_extra_cantidad_' + productoId
                );

                if (
                    birreteExtra &&
                    birreteExtra.value &&
                    birreteExtraCantidad
                ) {
                    const cantidadExtra = numero(
                        birreteExtraCantidad.value
                    );

                    if (cantidadExtra > 0) {

                        const opcion = birreteExtra.options[
                            birreteExtra.selectedIndex
                        ];

                        const tipoBirrete = opcion?.dataset.tipo || 'ESTANDAR';

                        const precioBirrete =
                            tipoBirrete === 'UNIVERSITARIO'
                                ? 50
                                : 25;

                        subtotal += precioBirrete * cantidadExtra;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Borla extra
                |--------------------------------------------------------------------------
                */

                const borlaExtra = document.getElementById(
                    'borla_extra_' + productoId
                );

                const borlaExtraCantidad = document.getElementById(
                    'borla_extra_cantidad_' + productoId
                );

                if (
                    borlaExtra &&
                    borlaExtra.value &&
                    borlaExtraCantidad
                ) {
                    const cantidadExtra = numero(
                        borlaExtraCantidad.value
                    );

                    if (cantidadExtra > 0) {
                        subtotal += 5 * cantidadExtra;
                    }
                }

            });


            /*
            |--------------------------------------------------------------------------
            | Descuentos
            |--------------------------------------------------------------------------
            */

            let descuentoManual = numero(
                descuentoInput?.value
            );

            if (descuentoManual < 0) {
                descuentoManual = 0;
            }

            if (descuentoManual > subtotal) {
                descuentoManual = subtotal;
            }


            /*
            |--------------------------------------------------------------------------
            | Descuento por toga
            |--------------------------------------------------------------------------
            */

            const descuentoPorTogaInput =
                document.getElementById('descuento_por_toga');

            const descuentoTogaPreview =
                document.getElementById('descuento_toga_preview');

            let descuentoPorToga =
                numero(descuentoPorTogaInput?.value);

            if (descuentoPorToga < 0) {
                descuentoPorToga = 0;
            }

            let cantidadTogas = 0;

            document.querySelectorAll('.toga-item').forEach(function (item) {

                const productoId =
                    item.id.replace('toga_item_', '');

                const checkbox =
                    document.getElementById('producto_' + productoId);

                const cantidadInput =
                    document.getElementById('cantidad_' + productoId);

                if (!checkbox || !checkbox.checked || !cantidadInput) {
                    return;
                }

                const cantidad =
                    numero(cantidadInput.value);

                if (cantidad > 0) {
                    cantidadTogas += cantidad;
                }
            });


            /*
            |--------------------------------------------------------------------------
            | Descuento calculado
            |--------------------------------------------------------------------------
            */

            let descuentoToga =
                descuentoPorToga * cantidadTogas;

            if (descuentoToga > subtotal) {
                descuentoToga = subtotal;
            }


            if (descuentoTogaPreview) {
                descuentoTogaPreview.value =
                    'Q ' + dinero(descuentoToga);
            }


            /*
            |--------------------------------------------------------------------------
            | Descuento total
            |--------------------------------------------------------------------------
            */

            const descuentoTotal =
                Math.min(
                    descuentoManual + descuentoToga,
                    subtotal
                );


            /*
            |--------------------------------------------------------------------------
            | Total
            |--------------------------------------------------------------------------
            */

            const total = Math.max(
                subtotal - descuentoTotal,
                0
            );


            /*
            |--------------------------------------------------------------------------
            | Saldo pendiente
            |--------------------------------------------------------------------------
            |
            | Al crear el alquiler todavía no existen pagos.
            |
            */

            const saldoPendiente = total;


            /*
            |--------------------------------------------------------------------------
            | Actualizar interfaz
            |--------------------------------------------------------------------------
            */

            if (subtotalElement) {
                subtotalElement.textContent = dinero(subtotal);
            }

            if (descuentoElement) {
                descuentoElement.textContent = dinero(descuentoTotal);
            }

            if (totalElement) {
                totalElement.textContent = dinero(total);
            }

            if (saldoElement) {
                saldoElement.textContent = dinero(saldoPendiente);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Descuento
        |--------------------------------------------------------------------------
        */

        if (descuentoInput) {

            descuentoInput.addEventListener(
                'input',
                calcularResumen
            );

            descuentoInput.addEventListener(
                'change',
                calcularResumen
            );
        }

        const descuentoPorTogaInput =
            document.getElementById('descuento_por_toga');

        if (descuentoPorTogaInput) {

            descuentoPorTogaInput.addEventListener(
                'input',
                calcularResumen
            );

            descuentoPorTogaInput.addEventListener(
                'change',
                calcularResumen
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Productos y cantidades
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll(
            '.producto-check, .cantidad-input, .accesorio-check, .accesorio-select, .extra-input, .extra-cantidad'
        ).forEach(function (elemento) {

            elemento.addEventListener(
                'change',
                calcularResumen
            );

            elemento.addEventListener(
                'input',
                calcularResumen
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Inicialización
        |--------------------------------------------------------------------------
        */

        calcularResumen();

    });
    </script>


    @endsection