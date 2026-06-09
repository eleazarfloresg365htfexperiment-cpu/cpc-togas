@extends('layouts.app')

@section('title', 'Registrar cliente')
@section('page_title', '➕ Registrar cliente')
@section('page_subtitle', 'Agrega un nuevo cliente para usarlo en alquileres de togas')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">👤 Nuevo cliente</div>
        <p class="text-muted mb-0">
            Ingresa los datos principales del cliente.
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
                <div class="stat-icon">👥</div>
                <h4 class="fw-bold mb-1">Registro de cliente</h4>
                <p class="text-muted mb-0">
                    Los clientes activos aparecerán disponibles al crear nuevos alquileres.
                </p>
            </div>

            <hr>

            <div class="mb-3">
                <div class="fw-bold mb-1">Datos recomendados</div>
                <p class="text-muted mb-0">
                    Registra al menos nombre, apellido y teléfono para localizar fácilmente al cliente.
                </p>
            </div>

            <div class="alert alert-light border rounded-4 mb-0">
                <strong>Nota:</strong><br>
                El DPI, dirección y observaciones ayudan a tener un mejor control, pero pueden completarse según la información disponible.
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

            <form action="{{ route('clientes.store') }}"
                  method="POST"
                  class="confirm-action-form"
                  data-title="¿Registrar cliente?"
                  data-text="Se guardará este cliente en el sistema."
                  data-icon="question"
                  data-confirm="Sí, registrar"
                  data-cancel="Cancelar">

                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text"
                               name="nombres"
                               id="nombres"
                               class="form-control"
                               value="{{ old('nombres') }}"
                               placeholder="Ej: Juan Carlos"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text"
                               name="apellidos"
                               id="apellidos"
                               class="form-control"
                               value="{{ old('apellidos') }}"
                               placeholder="Ej: Pérez López"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text"
                               name="telefono"
                               id="telefono"
                               class="form-control"
                               value="{{ old('telefono') }}"
                               placeholder="Ej: 5555-1111">
                    </div>

                    <div class="col-md-6">
                        <label for="dpi" class="form-label">DPI</label>
                        <input type="text"
                               name="dpi"
                               id="dpi"
                               class="form-control"
                               value="{{ old('dpi') }}"
                               placeholder="Ej: 1234567890101">
                    </div>

                    <div class="col-md-12">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text"
                               name="direccion"
                               id="direccion"
                               class="form-control"
                               value="{{ old('direccion') }}"
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
                            value="{{ old('institucion_representada') }}"
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
                                  placeholder="Ej: Cliente frecuente, institución, referencia, detalles importantes...">{{ old('observaciones') }}</textarea>
                    </div>

                </div>

                <div class="alert alert-light border rounded-4 mt-4">
                    <div class="fw-bold mb-1">Resumen de la acción</div>
                    <div class="text-muted">
                        El cliente será registrado como <strong>activo</strong> y podrá seleccionarse al crear alquileres.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                    <a href="{{ route('clientes.web') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        💾 Registrar cliente
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection