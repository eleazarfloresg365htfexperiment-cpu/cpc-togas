@extends('layouts.app')

@section('title', 'Usuarios')
@section('page_title', '🔐 Usuarios')
@section('page_subtitle', 'Personas que pueden entrar al sistema')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <p class="text-muted mb-0">
            Los usuarios no se borran: se desactivan, y así su nombre se conserva en los alquileres que registraron.
        </p>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary rounded-pill">➕ Nuevo usuario</a>
    </div>

    <div class="page-card p-3 p-md-4">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                        <tr>
                            <td>
                                <strong>{{ $usuario->nombre_completo }}</strong>
                                @if($usuario->is(auth()->user()))
                                    <span class="badge bg-primary-subtle text-primary border ms-1">Tú</span>
                                @endif
                            </td>
                            <td><code>{{ $usuario->usuario }}</code></td>
                            <td>{{ $usuario->email ?: '—' }}</td>
                            <td>
                                @if($usuario->activo)
                                    <span class="badge-soft badge-entrada">Activo</span>
                                @else
                                    <span class="badge-soft badge-ajuste">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary rounded-pill">Editar</a>

                                @if($usuario->activo)
                                    @unless($usuario->is(auth()->user()))
                                        <form action="{{ route('usuarios.desactivar', $usuario) }}" method="POST" class="d-inline confirm-action-form"
                                              data-title="¿Desactivar usuario?"
                                              data-text="{{ $usuario->nombre_completo }} ya no podrá iniciar sesión."
                                              data-confirm="Sí, desactivar">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Desactivar</button>
                                        </form>
                                    @endunless
                                @else
                                    <form action="{{ route('usuarios.reactivar', $usuario) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill">Reactivar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
