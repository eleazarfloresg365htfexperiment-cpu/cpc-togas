@extends('layouts.app')

@section('title', 'Nuevo usuario')
@section('page_title', '➕ Nuevo usuario')
@section('page_subtitle', 'Persona que podrá entrar al sistema y registrar alquileres')

@section('content')
    <div class="mb-4">
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary rounded-pill">← Volver a usuarios</a>
    </div>

    <div class="page-card p-3 p-md-4">
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

        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf

            @include('usuarios.partials.formulario')

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Crear usuario</button>
            </div>
        </form>
    </div>
@endsection
