@extends('layouts.app')

@section('title', 'Administrar productos')
@section('page_title', '🛠️ Administrar productos')
@section('page_subtitle', 'Selecciona qué acción deseas realizar sobre el inventario')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">⚙️ Panel de administración</div>
        <p class="text-muted mb-0">
            Elige una acción y luego selecciona el producto correspondiente.
        </p>
    </div>

    <a href="{{ url('/productos-web') }}" class="btn btn-outline-secondary rounded-pill">
        ← Volver a productos
    </a>
</div>

<div class="quick-grid">

    <a href="{{ url('/productos-web/administrar/editar') }}" class="quick-card">
        <div class="quick-icon">✏️</div>
        <div class="card-title-mini">Editar producto</div>
        <p class="card-desc-mini">Modificar datos generales, precio, talla, color o detalles.</p>
    </a>

    <a href="{{ url('/productos-web/administrar/entrada') }}" class="quick-card">
        <div class="quick-icon">➕</div>
        <div class="card-title-mini">Entrada de inventario</div>
        <p class="card-desc-mini">Agregar nuevas unidades al stock disponible.</p>
    </a>

    <a href="{{ url('/productos-web/administrar/ajuste') }}" class="quick-card">
        <div class="quick-icon">⚙️</div>
        <div class="card-title-mini">Ajuste manual</div>
        <p class="card-desc-mini">Corregir el stock por conteo físico o revisión.</p>
    </a>

    <a href="{{ url('/productos-web/administrar/estado') }}" class="quick-card">
        <div class="quick-icon">⛔</div>
        <div class="card-title-mini">Activar / desactivar</div>
        <p class="card-desc-mini">Ocultar o reactivar productos para nuevos alquileres.</p>
    </a>

</div>

@endsection