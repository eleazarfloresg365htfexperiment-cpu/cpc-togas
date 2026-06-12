@extends('layouts.app')

@section('title', 'Calendario de alquileres')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">📅 Calendario de alquileres</h1>
            <p class="text-muted mb-0">
                En Mes y Semana se muestra la ocupación de alquileres. En Día se muestra la agenda operativa.
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
                    <strong class="ms-2">Reservado / entrega</strong>
                    <div class="small text-muted mt-1">
                        Alquiler reservado o entrega programada.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <span class="badge" style="background:#fd7e14;">&nbsp;</span>
                    <strong class="ms-2">Entregado / devolución</strong>
                    <div class="small text-muted mt-1">
                        Alquiler entregado o devolución programada.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <span class="badge" style="background:#198754;">&nbsp;</span>
                    <strong class="ms-2">Pago límite</strong>
                    <div class="small text-muted mt-1">
                        Fecha límite de pago final o alquiler pagado.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <span class="badge" style="background:#dc3545;">&nbsp;</span>
                    <strong class="ms-2">Atrasado</strong>
                    <div class="small text-muted mt-1">
                        Devolución pendiente fuera de fecha.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-light border shadow-sm mb-4">
        <div class="fw-bold mb-1">Uso recomendado</div>
        <div class="small text-muted">
            <strong>Mes:</strong> vista general de ocupación.
            <span class="mx-2">|</span>
            <strong>Semana:</strong> ocupación por rango.
            <span class="mx-2">|</span>
            <strong>Día:</strong> agenda del día con entregas, devoluciones, pagos límite y atrasos.
        </div>
    </div>

    <div class="card border-0 shadow-sm calendar-card">
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

    .calendar-card {
        border-radius: 16px;
    }

    .fc {
        font-family: inherit;
    }

    .fc .fc-toolbar {
        gap: 12px;
        flex-wrap: wrap;
    }

    .fc .fc-toolbar-title {
        font-size: 1.35rem;
        font-weight: 800;
        text-transform: capitalize;
        color: #0f172a;
    }

    .fc .fc-button {
        border-radius: 10px;
        font-weight: 700;
        border: none;
        box-shadow: none !important;
        padding: 8px 13px;
    }

    .fc .fc-button-primary {
        background: #0d6efd;
    }

    .fc .fc-button-primary:hover {
        background: #0b5ed7;
    }

    .fc .fc-button-active {
        background: #111827 !important;
    }

    .fc .fc-col-header-cell {
        padding: 8px 0;
        background: #f8f9fa;
    }

    .fc .fc-col-header-cell-cushion {
        font-weight: 800;
        text-transform: lowercase;
    }

    .fc .fc-daygrid-day-number {
        font-weight: 800;
        text-decoration: none;
        color: #343a40;
    }

    .fc .fc-day-today {
        background: #fff8e1 !important;
    }

    .fc .fc-daygrid-event {
        border-radius: 8px;
        padding: 5px 7px;
        font-size: 0.78rem;
        line-height: 1.15;
        cursor: pointer;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border: none;
    }

    .fc .fc-event-title {
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 700;
    }

    .fc .fc-event-time {
        display: none;
    }

    .fc .fc-daygrid-event-dot {
        display: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Vista Día
    |--------------------------------------------------------------------------
    | La pestaña Día se usa como agenda operativa compacta.
    */
    .fc-dayGridDay-view .fc-daygrid-day-frame {
        min-height: 180px;
    }

    .fc-dayGridDay-view .fc-daygrid-event {
        font-size: 0.9rem;
        padding: 8px 10px;
        margin-bottom: 6px;
        border-radius: 10px;
    }

    .fc-dayGridDay-view .fc-daygrid-day-events {
        margin-top: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Vista Semana
    |--------------------------------------------------------------------------
    | Mantiene tarjetas compactas para evitar bloques exagerados.
    */
    .fc-dayGridWeek-view .fc-daygrid-day-frame {
        min-height: 82px;
    }

    @media (max-width: 768px) {
        .fc .fc-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .fc .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }

        .fc .fc-toolbar-title {
            font-size: 1.1rem;
            text-align: center;
        }
    }
</style>
@endsection