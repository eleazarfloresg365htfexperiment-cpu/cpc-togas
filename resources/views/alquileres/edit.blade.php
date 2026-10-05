@extends('layouts.app')

@section('title', 'Editar alquiler')
@section('page_title', '✏️ Editar alquiler')
@section('page_subtitle', 'Cambia fechas, horarios y datos de la carta. Cada cambio queda en el historial.')

@section('content')

@php
    $editable = fn (string $campo) => in_array($campo, $camposEditables, true);

    // Valor para inputs: old() primero, luego lo guardado en formato del input.
    $valorFecha = function (string $campo) use ($alquiler) {
        $raw = $alquiler->getRawOriginal($campo);

        return old($campo, $raw ? \Carbon\Carbon::parse($raw)->format('Y-m-d') : '');
    };

    $valorHora = function (string $campo) use ($alquiler) {
        $raw = $alquiler->getRawOriginal($campo);

        return old($campo, $raw ? \Carbon\Carbon::parse($raw)->format('H:i') : '');
    };

    $valorTexto = fn (string $campo) => old($campo, $alquiler->getRawOriginal($campo));

    $clienteNombre = trim(($alquiler->cliente->nombres ?? '') . ' ' . ($alquiler->cliente->apellidos ?? ''));
@endphp

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">✏️ Alquiler {{ $alquiler->codigo_recibo }}</div>
        <p class="text-muted mb-0">
            {{ $clienteNombre ?: 'Cliente' }} · Estado: <strong>{{ $alquiler->estado }}</strong>
        </p>
    </div>

    <a href="{{ route('alquileres.show', $alquiler->id) }}" class="btn btn-outline-secondary rounded-pill">
        ← Volver al alquiler
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger rounded-4">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger rounded-4">
        <div class="fw-bold mb-1">Revisa los datos:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">

    <div class="col-lg-4">
        <div class="page-card p-4 h-100">
            <div class="stat-icon">🛈</div>
            <h4 class="fw-bold mb-1">Qué se puede cambiar</h4>

            @if($alquiler->soloEditaDevolucion())
                <p class="text-muted">
                    Este alquiler ya fue <strong>entregado</strong>: solo se puede mover la fecha y hora de devolución.
                </p>
                <div class="alert alert-warning rounded-4 mb-3">
                    La mora se calcula con la nueva fecha: empieza a las 9:00 AM del día siguiente a la devolución programada.
                </div>
            @else
                <p class="text-muted">
                    Fechas y horas de entrega y devolución, horario de recogida y los datos que salen en la carta de compromiso.
                </p>
            @endif

            <div class="alert alert-light border rounded-4 mb-0">
                <strong>No se editan aquí:</strong> productos, tallas, accesorios ni montos.
                Si eso cambia, cancela este alquiler y crea uno nuevo.
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <form action="{{ route('alquileres.update', $alquiler->id) }}" method="POST" id="formEditarAlquiler">
            @csrf
            @method('PUT')

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0">
                    <div class="fw-bold">🗓️ Fechas y horas</div>
                    <small class="text-muted">
                        Fecha de reserva: {{ optional($alquiler->fecha_alquiler)->format('d/m/Y') }} (no editable)
                    </small>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        @if($editable('fecha_entrega'))
                            <div class="col-md-6">
                                <label class="form-label" for="fecha_entrega">Fecha de entrega</label>
                                <input type="date" name="fecha_entrega" id="fecha_entrega"
                                       class="form-control @error('fecha_entrega') is-invalid @enderror"
                                       value="{{ $valorFecha('fecha_entrega') }}"
                                       min="{{ optional($alquiler->fecha_alquiler)->format('Y-m-d') }}"
                                       required>
                                <small class="text-muted">Día programado para retirar las togas.</small>
                            </div>
                        @endif

                        @if($editable('hora_entrega'))
                            <div class="col-md-6">
                                <label class="form-label" for="hora_entrega">Hora de entrega</label>
                                <input type="time" name="hora_entrega" id="hora_entrega"
                                       class="form-control @error('hora_entrega') is-invalid @enderror"
                                       value="{{ $valorHora('hora_entrega') }}">
                                <small class="text-muted">Opcional.</small>
                            </div>
                        @endif

                        @if($editable('fecha_devolucion_programada'))
                            <div class="col-md-6">
                                <label class="form-label" for="fecha_devolucion_programada">Fecha de devolución programada</label>
                                <input type="date" name="fecha_devolucion_programada" id="fecha_devolucion_programada"
                                       class="form-control @error('fecha_devolucion_programada') is-invalid @enderror"
                                       value="{{ $valorFecha('fecha_devolucion_programada') }}"
                                       min="{{ $editable('fecha_entrega') ? $valorFecha('fecha_entrega') : optional($alquiler->fecha_entrega)->format('Y-m-d') }}"
                                       required>
                                <small class="text-muted">Día límite para devolver sin mora.</small>
                            </div>
                        @endif

                        @if($editable('hora_devolucion_programada'))
                            <div class="col-md-6">
                                <label class="form-label" for="hora_devolucion_programada">Hora de devolución programada</label>
                                <input type="time" name="hora_devolucion_programada" id="hora_devolucion_programada"
                                       class="form-control @error('hora_devolucion_programada') is-invalid @enderror"
                                       value="{{ $valorHora('hora_devolucion_programada') }}">
                                <small class="text-muted">Opcional.</small>
                            </div>
                        @endif

                        @if($editable('hora_entrega_inicio'))
                            <div class="col-md-6">
                                <label class="form-label" for="hora_entrega_inicio">Recogida desde</label>
                                <input type="time" name="hora_entrega_inicio" id="hora_entrega_inicio"
                                       class="form-control @error('hora_entrega_inicio') is-invalid @enderror"
                                       value="{{ $valorHora('hora_entrega_inicio') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="hora_entrega_fin">Recogida hasta</label>
                                <input type="time" name="hora_entrega_fin" id="hora_entrega_fin"
                                       class="form-control @error('hora_entrega_fin') is-invalid @enderror"
                                       value="{{ $valorHora('hora_entrega_fin') }}">
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            @if($editable('institucion_representada'))
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0">
                        <div class="fw-bold">📄 Datos para la carta de compromiso</div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" for="institucion_representada">Institución representada</label>
                                <input type="text" name="institucion_representada" id="institucion_representada"
                                       class="form-control" maxlength="255"
                                       value="{{ $valorTexto('institucion_representada') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="representante_alquiler">Representante o encargado</label>
                                <input type="text" name="representante_alquiler" id="representante_alquiler"
                                       class="form-control" maxlength="255"
                                       value="{{ $valorTexto('representante_alquiler') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="fecha_limite_pago_final">Fecha límite de pago final</label>
                                <input type="date" name="fecha_limite_pago_final" id="fecha_limite_pago_final"
                                       class="form-control"
                                       value="{{ $valorFecha('fecha_limite_pago_final') }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="observaciones">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control"
                                          rows="2" maxlength="500">{{ $valorTexto('observaciones') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 mb-4 border-warning">
                <div class="card-header bg-white border-0">
                    <div class="fw-bold">📝 Motivo del cambio</div>
                    <small class="text-muted">Obligatorio. Queda guardado en el historial del alquiler.</small>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="motivo_cambio">Motivo</label>
                            <textarea name="motivo_cambio" id="motivo_cambio" rows="2" maxlength="1000"
                                      class="form-control @error('motivo_cambio') is-invalid @enderror"
                                      placeholder="Ej. El cliente pidió mover la graduación una semana."
                                      required>{{ old('motivo_cambio') }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="responsable">Quién hace el cambio</label>
                            <input type="text" name="responsable" id="responsable" class="form-control"
                                   maxlength="255" value="{{ old('responsable') }}"
                                   placeholder="Opcional">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('alquileres.show', $alquiler->id) }}" class="btn btn-outline-secondary rounded-pill">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary rounded-pill">
                    💾 Guardar cambios
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mantener el mínimo de la devolución igual a la fecha de entrega elegida.
        const entrega = document.getElementById('fecha_entrega');
        const devolucion = document.getElementById('fecha_devolucion_programada');

        if (entrega && devolucion) {
            entrega.addEventListener('change', function () {
                devolucion.min = entrega.value;

                if (devolucion.value && devolucion.value < entrega.value) {
                    devolucion.value = entrega.value;
                }
            });
        }
    });
</script>

@endsection
