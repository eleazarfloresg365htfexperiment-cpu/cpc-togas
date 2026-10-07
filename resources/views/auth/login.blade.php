@extends('layouts.acceso')

@section('title', 'Iniciar sesión')

@section('content')
    <h2 class="h5 fw-bold mb-1">Iniciar sesión</h2>
    <p class="text-muted small mb-4">Escribe tu usuario y contraseña.</p>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.iniciar') }}">
        @csrf

        <div class="mb-3">
            <label for="usuario" class="form-label fw-semibold">Usuario</label>
            <input type="text" name="usuario" id="usuario" class="form-control"
                   value="{{ old('usuario') }}" autocomplete="username" autocapitalize="none"
                   required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control"
                   autocomplete="current-password" required>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="recordar" id="recordar" value="1">
            <label class="form-check-label small" for="recordar">Mantener la sesión abierta en esta computadora</label>
        </div>

        <button type="submit" class="btn btn-primary btn-acceso w-100 rounded-pill">Entrar</button>
    </form>

    <p class="text-muted small mt-4 mb-0">
        ¿Olvidaste tu contraseña? Pide a otro usuario del sistema que la cambie en <strong>Usuarios</strong>.
    </p>
@endsection
