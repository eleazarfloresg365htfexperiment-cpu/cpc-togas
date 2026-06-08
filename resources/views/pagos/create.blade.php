@extends('layouts.app')

@section('title', 'Registrar pago')
@section('page_title', '💰 Registrar pago')
@section('page_subtitle', 'Agrega un pago al alquiler seleccionado')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">💰 Nuevo pago</div>
        <p class="text-muted mb-0">
            Registra un abono o pago completo para este alquiler.
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
                        <div class="text-muted small">Total del alquiler</div>
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
                        <div class="text-muted small">Descuento aplicado</div>
                        <div class="h4 fw-bold mb-0">
                            Q {{ number_format($alquiler->descuento, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border rounded-4 mt-4 mb-0">
                <strong>Nota:</strong><br>
                El sistema actualizará automáticamente el saldo pendiente y el estado de pago.
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

            @if ($alquiler->saldo_pendiente > 0 && $alquiler->estado !== 'CANCELADO')
                <div class="mb-3">
                    <label for="fecha_limite_pago_final" class="form-label">
                        Fecha límite para pago final
                    </label>

                    <input
                        type="date"
                        name="fecha_limite_pago_final"
                        id="fecha_limite_pago_final"
                        class="form-control"
                        value="{{ old('fecha_limite_pago_final', optional($alquiler->fecha_limite_pago_final)->format('Y-m-d')) }}"
                    >

                    <div class="form-text">
                        Esta fecha aparecerá en la carta de compromiso cuando exista saldo pendiente.
                    </div>
                </div>
            @endif

            @if($alquiler->estado === 'CANCELADO')
                <div class="alert alert-danger rounded-4 mb-0">
                    Este alquiler está cancelado, por lo tanto no se pueden registrar pagos.
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
                      data-text="Se agregará el pago al alquiler y se actualizará el saldo pendiente."
                      data-icon="question"
                      data-confirm="Sí, registrar pago"
                      data-cancel="Cancelar">

                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="monto" class="form-label">Monto a pagar</label>
                            <input type="number"
                                   name="monto"
                                   id="monto"
                                   class="form-control"
                                   value="{{ old('monto') }}"
                                   min="0.01"
                                   max="{{ $alquiler->saldo_pendiente }}"
                                   step="0.01"
                                   placeholder="Ej: {{ number_format($alquiler->saldo_pendiente, 2, '.', '') }}"
                                   required>

                            <small class="text-muted">
                                Máximo permitido: <strong>Q {{ number_format($alquiler->saldo_pendiente, 2) }}</strong>
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
                            <label for="referencia" class="form-label">Referencia</label>
                            <input type="text"
                                   name="referencia"
                                   id="referencia"
                                   class="form-control"
                                   value="{{ old('referencia') }}"
                                   placeholder="Ej: No. de transferencia, recibo interno, autorización...">
                        </div>

                        <div class="col-md-12">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones"
                                      id="observaciones"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Ej: Pago parcial, cancelación completa, pago realizado por familiar...">{{ old('observaciones') }}</textarea>
                        </div>

                    </div>

                    <div class="alert alert-light border rounded-4 mt-4">
                        <div class="fw-bold mb-1">Resumen de la acción</div>
                        <div class="text-muted">
                            Si el pago cubre todo el saldo pendiente, el alquiler pasará a estado de pago <strong>PAGADO</strong>. Si no, quedará como <strong>PARCIAL</strong>.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                        <a href="{{ route('alquileres.show', $alquiler->id) }}" class="btn btn-outline-secondary rounded-pill px-4">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            💰 Registrar pago
                        </button>
                    </div>

                </form>

            @endif

        </div>
    </div>

</div>

@endsection