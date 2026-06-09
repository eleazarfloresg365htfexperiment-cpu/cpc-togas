@extends('layouts.app')

@section('title', 'Editar cliente')
@section('page_title', '✏️ Editar cliente')
@section('page_subtitle', 'Actualiza los datos del cliente seleccionado')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">👤 Editar información del cliente</div>
        <p class="text-muted mb-0">
            Modifica los datos principales del cliente.
        </p>
    </div>

    <a href="{{ route('clientes.web') }}" class="btn btn-outline-secondary rounded-pill">
        ← Volver a clientes
    </a>
</div>

<div class="row g-4">

    <div class="col-lg-4">
        <div class="page-card p-4 h-100">

            <div class="mb-3">
                <div class="stat-icon">👤</div>
                <h4 class="fw-bold mb-1">
                    {{ $cliente->nombres }} {{ $cliente->apellidos }}
                </h4>

                @if($cliente->telefono)
                    <p class="text-muted mb-0">{{ $cliente->telefono }}</p>
                @else
                    <p class="text-muted mb-0">Sin teléfono registrado</p>
                @endif
            </div>

            <hr>

            <div class="mb-3">
                <div class="text-muted small">ID del cliente</div>
                <div class="fw-bold">{{ $cliente->id }}</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">DPI</div>
                <div class="fw-bold">
                    {{ $cliente->dpi ?? 'Sin DPI' }}
                </div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Estado</div>
                <div class="mt-1">
                    @if($cliente->activo)
                        <span class="badge-soft badge-entrada">Activo</span>
                    @else
                        <span class="badge-soft badge-ajuste">Inactivo</span>
                    @endif
                </div>
            </div>

            <div class="alert alert-light border rounded-4 mt-4 mb-0">
                <strong>Nota:</strong><br>
                Esta pantalla actualiza los datos del cliente, pero no cambia directamente sus alquileres registrados.
            </div>

        </div>
    </div>

    <div class="col-lg-8">
        <div class="page-card p-4">

            <div class="section-title mb-3">📝 Datos del cliente</div>

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

            <form action="{{ route('clientes.update', $cliente->id) }}"
                  method="POST"
                  class="confirm-action-form"
                  data-title="¿Guardar cambios?"
                  data-text="Se actualizará la información del cliente seleccionado."
                  data-icon="question"
                  data-confirm="Sí, guardar cambios"
                  data-cancel="Cancelar">

                @csrf
                @method('PUT')

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text"
                               name="nombres"
                               id="nombres"
                               class="form-control"
                               value="{{ old('nombres', $cliente->nombres) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text"
                               name="apellidos"
                               id="apellidos"
                               class="form-control"
                               value="{{ old('apellidos', $cliente->apellidos) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text"
                               name="telefono"
                               id="telefono"
                               class="form-control"
                               value="{{ old('telefono', $cliente->telefono) }}"
                               placeholder="Ej: 5555-1111">
                    </div>

                    <div class="col-md-6">
                        <label for="dpi" class="form-label">DPI</label>
                        <input type="text"
                               name="dpi"
                               id="dpi"
                               class="form-control"
                               value="{{ old('dpi', $cliente->dpi) }}"
                               placeholder="Ej: 1234567890101">
                    </div>

                    <div class="col-md-12">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text"
                               name="direccion"
                               id="direccion"
                               class="form-control"
                               value="{{ old('direccion', $cliente->direccion) }}"
                               placeholder="Ej: Jalapa, Guatemala">
                    </div>

                    <div class="mb-3">
                        <label for="institucion_representada" class="form-label">
                            Institución representada <span class="text-muted">(opcional)</span>
                        </label>

                        <input type="text"
                            name="institucion_representada"
                            id="institucion_representada"
                            class="form-control @error('institucion_representada') is-invalid @enderror"
                            value="{{ old('institucion_representada', $cliente->institucion_representada) }}"
                            placeholder="Ejemplo: Colegio El Porvenir, UMG, CPC, etc.">

                        <div class="form-text">
                            Déjalo vacío si el cliente viene como persona individual.
                        </div>

                        @error('institucion_representada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones"
                                  id="observaciones"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Ej: Cliente frecuente, institución, referencia, detalles importantes...">{{ old('observaciones', $cliente->observaciones) }}</textarea>
                    </div>

                </div>

                <div class="alert alert-light border rounded-4 mt-4">
                    <div class="fw-bold mb-1">Resumen de la acción</div>
                    <div class="text-muted">
                        Se guardarán los cambios realizados en la información del cliente.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                    <a href="{{ route('clientes.web') }}" class="btn btn-outline-secondary rounded-pill px-4">
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

@endsection