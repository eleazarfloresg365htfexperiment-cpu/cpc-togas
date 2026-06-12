@extends('layouts.app')

@section('title', 'Registrar pago')
@section('page_title', '💰 Registrar pago')
@section('page_subtitle', 'Agrega un pago o descuento al alquiler seleccionado')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">💰 Nuevo pago</div>
        <p class="text-muted mb-0">
            Registra un abono, pago completo o descuento autorizado para este alquiler.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('alquileres.show', $alquiler->id) }}" class="btn btn-outline-secondary rounded-pill">
            ← Volver al detalle
        </a>

        <a href="{{ route('alquileres.web') }}" class="btn btn-outline-primary rounded-pill">
            🧾 Ver alquileres
        </a>
    </div>
</div>

<div class="row g-4">

    <div class="col-lg-4">
        <div class="page-card p-4 h-100">

            <div class="mb-3">
                <div class="stat-icon">🧾</div>
                <h4 class="fw-bold mb-1">{{ $alquiler->codigo_recibo }}</h4>

                @if($alquiler->cliente)
                    <p class="text-muted mb-0">
                        {{ $alquiler->cliente->nombres }} {{ $alquiler->cliente->apellidos }}
                    </p>
                @else
                    <p class="text-muted mb-0">Cliente no encontrado</p>
                @endif
            </div>

            <hr>

            <div class="mb-3">
                <div class="text-muted small">Estado del alquiler</div>
                <div class="mt-1">
                    @if($alquiler->estado === 'RESERVADO')
                        <span class="badge-soft badge-ajuste">RESERVADO</span>
                    @elseif($alquiler->estado === 'ENTREGADO')
                        <span class="badge-soft badge-alquiler">ENTREGADO</span>
                    @elseif($alquiler->estado === 'DEVUELTO')
                        <span class="badge-soft badge-entrada">DEVUELTO</span>
                    @elseif($alquiler->estado === 'CANCELADO')
                        <span class="badge-soft badge-danger-soft">CANCELADO</span>
                    @else
                        <span class="badge bg-secondary">{{ $alquiler->estado }}</span>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Estado de pago</div>
                <div class="mt-1">
                    @if($alquiler->estado_pago === 'PENDIENTE')
                        <span class="badge-soft badge-danger-soft">PENDIENTE</span>
                    @elseif($alquiler->estado_pago === 'PARCIAL')
                        <span class="badge-soft badge-ajuste">PARCIAL</span>
                    @elseif($alquiler->estado_pago === 'PAGADO')
                        <span class="badge-soft badge-entrada">PAGADO</span>
                    @else
                        <span class="badge bg-secondary">{{ $alquiler->estado_pago }}</span>
                    @endif
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12">
                    <div class="p-3 rounded-4 bg-light">
                        <div class="text-muted small">Total actual del alquiler</div>
                        <div class="h4 fw-bold mb-0">
                            Q {{ number_format($alquiler->total, 2) }}
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-4 bg-light">
                        <div class="text-muted small">Saldo pendiente</div>
                        <div class="h4 fw-bold mb-0 amount-negative">
                            Q {{ number_format($alquiler->saldo_pendiente, 2) }}
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-4 bg-light">
                        <div class="text-muted small">Descuento acumulado</div>
                        <div class="h4 fw-bold mb-0">
                            Q {{ number_format($alquiler->descuento, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border rounded-4 mt-4 mb-0">
                <strong>Nota:</strong><br>
                El pago representa dinero recibido. El descuento representa una rebaja autorizada y también reduce el saldo pendiente.
            </div>

        </div>
    </div>

    <div class="col-lg-8">
        <div class="page-card p-4">

            <div class="section-title mb-3">📝 Datos del pago</div>

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

            @if($alquiler->estado === 'CANCELADO')
                <div class="alert alert-danger rounded-4 mb-0">
                    Este alquiler está cancelado, por lo tanto no se pueden registrar pagos ni descuentos.
                </div>
            @elseif($alquiler->saldo_pendiente <= 0)
                <div class="alert alert-success rounded-4 mb-0">
                    Este alquiler ya está completamente pagado.
                </div>
            @else

                <form action="{{ route('pagos.store', $alquiler->id) }}"
                      method="POST"
                      class="confirm-action-form"
                      data-title="¿Registrar pago?"
                      data-text="Se actualizará el saldo pendiente del alquiler."
                      data-icon="question"
                      data-confirm="Sí, registrar"
                      data-cancel="Cancelar">

                    @csrf

                    <div class="mb-3">
                        <label for="fecha_limite_pago_final" class="form-label">
                            Fecha límite para pago final
                        </label>

                        <input
                            type="date"
                            id="fecha_limite_pago_final"
                            name="fecha_limite_pago_final"
                            class="form-control"
                            value="{{ old('fecha_limite_pago_final', $alquiler->fecha_limite_pago_final ? \Carbon\Carbon::parse($alquiler->fecha_limite_pago_final)->format('Y-m-d') : '') }}"
                        >

                        <div class="form-text">
                            Esta fecha aparecerá en la carta de compromiso cuando exista saldo pendiente.
                        </div>
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="monto" class="form-label">Monto a pagar</label>
                            <input type="number"
                                   name="monto"
                                   id="monto"
                                   class="form-control"
                                   value="{{ old('monto', '0.00') }}"
                                   min="0"
                                   max="{{ $alquiler->saldo_pendiente }}"
                                   step="0.01"
                                   placeholder="Ej: {{ number_format($alquiler->saldo_pendiente, 2, '.', '') }}">

                            <small class="text-muted">
                                Dinero recibido. Máximo permitido: <strong>Q {{ number_format($alquiler->saldo_pendiente, 2) }}</strong>
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label for="metodo_pago" class="form-label">Método de pago</label>
                            <select name="metodo_pago" id="metodo_pago" class="form-select" required>
                                <option value="EFECTIVO" {{ old('metodo_pago', 'EFECTIVO') === 'EFECTIVO' ? 'selected' : '' }}>
                                    Efectivo
                                </option>
                                <option value="TRANSFERENCIA" {{ old('metodo_pago') === 'TRANSFERENCIA' ? 'selected' : '' }}>
                                    Transferencia
                                </option>
                                <option value="TARJETA" {{ old('metodo_pago') === 'TARJETA' ? 'selected' : '' }}>
                                    Tarjeta
                                </option>
                                <option value="OTRO" {{ old('metodo_pago') === 'OTRO' ? 'selected' : '' }}>
                                    Otro
                                </option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <div class="p-3 rounded-4 border bg-light">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="1"
                                        id="aplicar_descuento"
                                        name="aplicar_descuento"
                                        {{ old('aplicar_descuento') ? 'checked' : '' }}
                                    >

                                    <label class="form-check-label fw-bold" for="aplicar_descuento">
                                        Aplicar descuento autorizado
                                    </label>
                                </div>

                                <div class="form-text">
                                    Activa esta opción solo si se autorizó una rebaja en este pago.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 d-none" id="bloque-descuento">
                            <div class="row g-3 p-3 rounded-4 border border-warning bg-warning bg-opacity-10">

                                <div class="col-md-6">
                                    <label for="descuento_aplicado" class="form-label">Cantidad de descuento</label>
                                    <input type="number"
                                           name="descuento_aplicado"
                                           id="descuento_aplicado"
                                           class="form-control"
                                           value="{{ old('descuento_aplicado', '0.00') }}"
                                           min="0"
                                           max="{{ $alquiler->saldo_pendiente }}"
                                           step="0.01"
                                           placeholder="Ej: 25.00">

                                    <small class="text-muted">
                                        Rebaja autorizada. No cuenta como dinero recibido.
                                    </small>
                                </div>

                                <div class="col-md-6">
                                    <label for="observacion_descuento" class="form-label">
                                        Observación del descuento
                                    </label>
                                    <textarea name="observacion_descuento"
                                              id="observacion_descuento"
                                              rows="3"
                                              class="form-control"
                                              placeholder="Ej: Descuento autorizado por administración, ajuste especial, acuerdo con cliente...">{{ old('observacion_descuento') }}</textarea>

                                    <div class="form-text">
                                        Obligatoria si se aplica un descuento.
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="referencia" class="form-label">Referencia</label>
                            <input type="text"
                                   name="referencia"
                                   id="referencia"
                                   class="form-control"
                                   value="{{ old('referencia') }}"
                                   placeholder="Ej: No. de transferencia, recibo interno, autorización...">
                        </div>

                        <div class="col-md-12">
                            <label for="observaciones" class="form-label">Observaciones del pago</label>
                            <textarea name="observaciones"
                                      id="observaciones"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Ej: Pago parcial, cancelación completa, pago realizado por familiar...">{{ old('observaciones') }}</textarea>
                        </div>

                    </div>

                    <div class="alert alert-light border rounded-4 mt-4">
                        <div class="fw-bold mb-2">Resumen de la acción</div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="small text-muted">Pago recibido</div>
                                <div class="fw-bold" id="resumen-pago">
                                    Q 0.00
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="small text-muted">Descuento aplicado</div>
                                <div class="fw-bold" id="resumen-descuento">
                                    Q 0.00
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="small text-muted">Total aplicado al saldo</div>
                                <div class="fw-bold text-success" id="resumen-total-aplicado">
                                    Q 0.00
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="text-muted">
                            Si el pago más el descuento cubren todo el saldo pendiente, el alquiler pasará a estado de pago <strong>PAGADO</strong>.
                            Si no, quedará como <strong>PARCIAL</strong>.
                        </div>

                        <div class="text-danger fw-semibold mt-2 d-none" id="alerta-exceso">
                            La suma del pago y el descuento no puede ser mayor al saldo pendiente.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                        <a href="{{ route('alquileres.show', $alquiler->id) }}" class="btn btn-outline-secondary rounded-pill px-4">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-success rounded-pill px-4" id="btn-registrar-pago">
                            💰 Registrar pago
                        </button>
                    </div>

                </form>

            @endif

        </div>
    </div>

</div>

@if($alquiler->estado !== 'CANCELADO' && $alquiler->saldo_pendiente > 0)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const saldoPendiente = {{ (float) $alquiler->saldo_pendiente }};

        const aplicarDescuentoCheck = document.getElementById('aplicar_descuento');
        const bloqueDescuento = document.getElementById('bloque-descuento');

        const montoInput = document.getElementById('monto');
        const descuentoInput = document.getElementById('descuento_aplicado');
        const observacionDescuentoInput = document.getElementById('observacion_descuento');

        const resumenPago = document.getElementById('resumen-pago');
        const resumenDescuento = document.getElementById('resumen-descuento');
        const resumenTotalAplicado = document.getElementById('resumen-total-aplicado');
        const alertaExceso = document.getElementById('alerta-exceso');
        const btnRegistrar = document.getElementById('btn-registrar-pago');

        function formatoMoneda(valor) {
            return 'Q ' + Number(valor || 0).toFixed(2);
        }

        function descuentoActivo() {
            return aplicarDescuentoCheck && aplicarDescuentoCheck.checked;
        }

        function actualizarBloqueDescuento() {
            if (descuentoActivo()) {
                bloqueDescuento.classList.remove('d-none');
                descuentoInput.removeAttribute('disabled');
            } else {
                bloqueDescuento.classList.add('d-none');
                descuentoInput.value = '0.00';
                observacionDescuentoInput.value = '';
                observacionDescuentoInput.removeAttribute('required');
                descuentoInput.setAttribute('disabled', 'disabled');
            }

            calcularResumen();
        }

        function calcularResumen() {
            const monto = parseFloat(montoInput.value) || 0;
            const descuento = descuentoActivo()
                ? (parseFloat(descuentoInput.value) || 0)
                : 0;

            const totalAplicado = monto + descuento;

            resumenPago.textContent = formatoMoneda(monto);
            resumenDescuento.textContent = formatoMoneda(descuento);
            resumenTotalAplicado.textContent = formatoMoneda(totalAplicado);

            if (totalAplicado > saldoPendiente) {
                alertaExceso.classList.remove('d-none');
                btnRegistrar.disabled = true;
            } else {
                alertaExceso.classList.add('d-none');
                btnRegistrar.disabled = false;
            }

            if (descuentoActivo() && descuento > 0) {
                observacionDescuentoInput.setAttribute('required', 'required');
            } else {
                observacionDescuentoInput.removeAttribute('required');
            }
        }

        montoInput.addEventListener('input', calcularResumen);
        descuentoInput.addEventListener('input', calcularResumen);
        aplicarDescuentoCheck.addEventListener('change', actualizarBloqueDescuento);

        actualizarBloqueDescuento();
        calcularResumen();
    });
</script>
@endif

@endsection