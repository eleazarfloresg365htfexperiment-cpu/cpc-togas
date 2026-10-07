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

        <a href="{{ route('alquileres.index') }}" class="btn btn-outline-secondary rounded-pill">
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
                                    <small class="d-block text-muted mb-1">Se llena con tu nombre; cámbialo si atiende otra persona.</small>
                                    <input
                                        type="text"
                                        name="representante_alquiler"
                                        id="representante_alquiler"
                                        class="form-control"
                                        value="{{ old('representante_alquiler', auth()->user()?->nombre_corto) }}"
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
                                                        No alcanza el stock disponible.
                                                    </div>
                                                    <ul class="mb-1 ps-3 small" id="stock_detalle_{{ $toga->id }}">
                                                        <li>Toga: hay {{ $toga->stock_disponible }} disponible(s).</li>
                                                    </ul>
                                                    <div class="small">
                                                        Para continuar con esta cantidad, autoriza la fabricación de lo que falta. Si no, reduce la cantidad o elige otro accesorio.
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
                                                                data-stock="{{ $collarin->stock_disponible }}"
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
                                                                    data-color="{{ $capa->capa->color }}"
                                                                    data-stock="{{ $capa->stock_disponible }}"
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
                                                                data-stock="{{ $birrete->stock_disponible }}"
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
                                                                data-color="{{ $borla->borla->color ?? '' }}"
                                                                data-tipo-borla="{{ $borla->borla->tipo_borla ?? 'NORMAL' }}"
                                                                data-stock="{{ $borla->stock_disponible }}"
                                                                {{ old("productos.$toga->id.borla_id") == $borla->id ? 'selected' : '' }}
                                                            >
                                                                {{ $borla->nombre }}
                                                                ({{ $borla->borla->color ?? 'Sin color' }} · {{ ($borla->borla->tipo_borla ?? 'NORMAL') === 'UNIVERSITARIA' ? 'Universitaria' : 'Normal' }})
                                                                - Disp: {{ $borla->stock_disponible }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <div class="form-text text-danger d-none" id="borla_aviso_{{ $toga->id }}"></div>
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
                                                                    ({{ $borla->borla->color ?? 'Sin color' }} · {{ ($borla->borla->tipo_borla ?? 'NORMAL') === 'UNIVERSITARIA' ? 'Universitaria' : 'Normal' }})
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
                        <a href="{{ route('alquileres.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
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

    @php
        $datosParaJs = [
            'coloresPorCarrera' => config('alquiler.colores_borla_por_carrera', []),
        ];
    @endphp

    <script>
        window.CPC_ALQUILER = @json($datosParaJs);
    </script>
    {{-- La lógica de esta pantalla está en public/js/alquileres-crear.js --}}
    <script src="{{ asset('js/alquileres-crear.js') }}?v={{ filemtime(public_path('js/alquileres-crear.js')) }}"></script>


    @endsection