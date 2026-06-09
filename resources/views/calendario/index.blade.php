@extends('layouts.app')

@section('title', 'Calendario de alquileres')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">📅 Calendario de alquileres</h1>
            <p class="text-muted mb-0">
                Vista mensual de entregas, devoluciones y pagos finales programados.
            </p>
        </div>

        <a href="{{ url('/alquileres-web') }}" class="btn btn-outline-secondary">
            ← Volver a alquileres
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <span class="badge" style="background:#0d6efd;">&nbsp;</span>
                    <strong class="ms-2">Entrega reservada</strong>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <span class="badge" style="background:#ffc107;">&nbsp;</span>
                    <strong class="ms-2">Devolución próxima</strong>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <span class="badge" style="background:#dc3545;">&nbsp;</span>
                    <strong class="ms-2">Pago pendiente</strong>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <span class="badge" style="background:#6c757d;">&nbsp;</span>
                    <strong class="ms-2">Cancelado</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div
                id="calendario-alquileres"
                data-eventos-url="{{ route('calendario.eventos') }}">
            </div>
        </div>
    </div>

</div>

<style>
    #calendario-alquileres {
        min-height: 720px;
    }

    .fc {
        font-family: inherit;
    }

    .fc .fc-toolbar-title {
        font-size: 1.35rem;
        font-weight: 700;
        text-transform: capitalize;
    }

    .fc .fc-button {
        border-radius: 10px;
        font-weight: 600;
        border: none;
        box-shadow: none !important;
    }

    .fc .fc-button-primary {
        background: #0d6efd;
    }

    .fc .fc-button-primary:hover {
        background: #0b5ed7;
    }

    .fc .fc-daygrid-event {
        border-radius: 8px;
        padding: 3px 6px;
        font-size: 0.82rem;
        cursor: pointer;
        margin-bottom: 3px;
    }

    .fc .fc-day-today {
        background: #fff8e1 !important;
    }

    .fc-event-title {
        white-space: normal;
    }

    .fc .fc-col-header-cell {
        padding: 8px 0;
        background: #f8f9fa;
    }

    .fc .fc-daygrid-day-number {
        font-weight: 700;
        text-decoration: none;
        color: #343a40;
    }

    .fc .fc-daygrid-event {
        border-radius: 8px;
        padding: 4px 6px;
        font-size: 0.78rem;
        line-height: 1.15;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .fc .fc-event-title {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .fc .fc-event-time {
        font-weight: 700;
        margin-right: 4px;
    }

    .fc .fc-daygrid-event-dot {
        display: none;
    }
</style>
@endsection