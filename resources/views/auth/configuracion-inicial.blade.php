@extends('layouts.acceso')

@section('title', 'Configuración inicial')
@section('ancho', '640px')

@section('content')
    <h2 class="h5 fw-bold mb-1">Crear el primer usuario</h2>
    <p class="text-muted small mb-4">
        El sistema todavía no tiene usuarios. Crea el tuyo para entrar; después podrás agregar
        a las demás personas desde <strong>Usuarios</strong>.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('configuracion-inicial.guardar') }}">
        @csrf

        @include('usuarios.partials.formulario')

        <button type="submit" class="btn btn-primary btn-acceso w-100 rounded-pill mt-4">Crear usuario y entrar</button>
    </form>
@endsection
