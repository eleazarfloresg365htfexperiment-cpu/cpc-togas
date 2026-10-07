{{--
    Campos de un usuario. Variable opcional: $usuario (si viene, se edita).
--}}
@php
    $usuario = $usuario ?? null;
    $editando = $usuario !== null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombres" class="form-label fw-semibold">Nombres</label>
        <input type="text" name="nombres" id="nombres" class="form-control" maxlength="100"
               value="{{ old('nombres', $usuario?->nombres) }}" required>
    </div>

    <div class="col-md-6">
        <label for="apellidos" class="form-label fw-semibold">Apellidos</label>
        <input type="text" name="apellidos" id="apellidos" class="form-control" maxlength="100"
               value="{{ old('apellidos', $usuario?->apellidos) }}" required>
        <small class="text-muted">El nombre y apellido salen como encargado en los alquileres.</small>
    </div>

    <div class="col-md-6">
        <label for="usuario" class="form-label fw-semibold">Usuario (para iniciar sesión)</label>
        <input type="text" name="usuario" id="usuario" class="form-control" maxlength="50"
               value="{{ old('usuario', $usuario?->usuario) }}" autocomplete="off" autocapitalize="none"
               placeholder="Ej. eflores" required>
        <small class="text-muted">Minúsculas, números, punto o guion. Sin espacios ni tildes.</small>
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label fw-semibold">Correo <span class="text-muted fw-normal">(opcional)</span></label>
        <input type="email" name="email" id="email" class="form-control" maxlength="255"
               value="{{ old('email', $usuario?->email) }}">
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label fw-semibold">
            {{ $editando ? 'Nueva contraseña' : 'Contraseña' }}
            @if($editando) <span class="text-muted fw-normal">(déjala vacía para no cambiarla)</span> @endif
        </label>
        <input type="password" name="password" id="password" class="form-control"
               autocomplete="new-password" minlength="8" @required(!$editando)>
        <small class="text-muted">Al menos 8 caracteres.</small>
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label fw-semibold">Repite la contraseña</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
               autocomplete="new-password" minlength="8" @required(!$editando)>
    </div>
</div>
