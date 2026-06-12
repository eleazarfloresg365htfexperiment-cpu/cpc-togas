@extends('layouts.app')

@section('title', 'Control de alquileres')
@section('page_title', '📦 Control de alquileres')
@section('page_subtitle', 'Audita alquileres vigentes, entregados, atrasados y togas fuera')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📦 Panel operativo</div>
        <p class="text-muted mb-0">
            Revisa qué alquileres siguen activos, cuáles no han vuelto y qué inventario está fuera.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('alquileres.create') }}" class="btn btn-primary rounded-pill">
            ➕ Nuevo alquiler
        </a>

        <a href="{{ route('calendario.index') }}" class="btn btn-outline-primary rounded-pill">
            📅 Ver calendario
        </a>
    </div>
</div>

<div class="row g-3 mb-4">

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'entregados']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">No devueltos</div>
                <div class="h3 fw-bold mb-0">{{ $noDevueltos }}</div>
                <div class="small text-muted mt-1">Alquileres entregados</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'atrasados']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">Atrasados</div>
                <div class="h3 fw-bold mb-0 text-danger">{{ $atrasados }}</div>
                <div class="small text-muted mt-1">Devolución vencida</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'vigentes']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">Alquileres vigentes</div>
                <div class="h3 fw-bold mb-0">{{ $alquileresVigentes }}</div>
                <div class="small text-muted mt-1">Reservados + entregados</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="page-card p-3 h-100">
            <div class="text-muted small">Togas fuera</div>
            <div class="h3 fw-bold mb-0">{{ $togasFuera }}</div>
            <div class="small text-muted mt-1">Unidades en alquileres entregados</div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'todos']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">Reservas hoy</div>
                <div class="h3 fw-bold mb-0">{{ $reservasHoy }}</div>
                <div class="small text-muted mt-1">Registradas en la fecha actual</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'entregas_hoy']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">Entregas para hoy</div>
                <div class="h3 fw-bold mb-0">{{ $entregasHoy }}</div>
                <div class="small text-muted mt-1">Aún pendientes de entregar</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'entregados_hoy']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">Entregados hoy</div>
                <div class="h3 fw-bold mb-0">{{ $entregadosHoy }}</div>
                <div class="small text-muted mt-1">Ya fueron entregados al cliente</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'devoluciones_hoy']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">Devoluciones para hoy</div>
                <div class="h3 fw-bold mb-0">{{ $devolucionesHoy }}</div>
                <div class="small text-muted mt-1">Aún pendientes de recibir</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'devueltos_hoy']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">Devueltos hoy</div>
                <div class="h3 fw-bold mb-0 text-success">{{ $devueltosHoy }}</div>
                <div class="small text-muted mt-1">Ya regresaron al inventario</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="{{ route('control-alquileres.index', ['filtro' => 'pendientes_pago']) }}" class="text-decoration-none">
            <div class="page-card p-3 h-100">
                <div class="text-muted small">Saldo pendiente</div>
                <div class="h3 fw-bold mb-0 amount-negative">
                    Q {{ number_format($saldoPendienteTotal, 2) }}
                </div>
                <div class="small text-muted mt-1">Alquileres no cancelados</div>
            </div>
        </a>
    </div>

</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="page-card p-4 h-100">
            <div class="section-title mb-3">🔎 Filtros</div>

            <form method="GET" action="{{ route('control-alquileres.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="filtro" class="form-label">Estado a revisar</label>
                    <select name="filtro" id="filtro" class="form-select">
                        <option value="vigentes" {{ $filtro === 'vigentes' ? 'selected' : '' }}>Vigentes</option>
                        <option value="todos" {{ $filtro === 'todos' ? 'selected' : '' }}>Todos no cancelados</option>
                        <option value="reservados" {{ $filtro === 'reservados' ? 'selected' : '' }}>Reservados</option>
                        <option value="entregados" {{ $filtro === 'entregados' ? 'selected' : '' }}>Entregados / no devueltos</option>
                        <option value="atrasados" {{ $filtro === 'atrasados' ? 'selected' : '' }}>Atrasados</option>
                        <option value="entregas_hoy" {{ $filtro === 'entregas_hoy' ? 'selected' : '' }}>Entregas para hoy</option>
                        <option value="entregados_hoy" {{ $filtro === 'entregados_hoy' ? 'selected' : '' }}>Entregados hoy</option>
                        <option value="devoluciones_hoy" {{ $filtro === 'devoluciones_hoy' ? 'selected' : '' }}>Devoluciones para hoy</option>
                        <option value="devueltos_hoy" {{ $filtro === 'devueltos_hoy' ? 'selected' : '' }}>Devueltos hoy</option>
                        <option value="pendientes_pago" {{ $filtro === 'pendientes_pago' ? 'selected' : '' }}>Pendientes de pago</option>
                        <option value="pagados" {{ $filtro === 'pagados' ? 'selected' : '' }}>Pagados</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text"
                           name="busqueda"
                           id="busqueda"
                           class="form-control"
                           value="{{ $busqueda }}"
                           placeholder="Recibo, cliente, teléfono, DPI o institución">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill flex-fill">
                        Buscar
                    </button>

                    <a href="{{ route('control-alquileres.index') }}" class="btn btn-outline-secondary rounded-pill">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="page-card p-4 h-100">
            <div class="section-title mb-3">🎓 Togas fuera por talla</div>

            @if($togasFueraPorTalla->count() > 0)
                <div class="d-flex flex-column gap-2">
                    @foreach($togasFueraPorTalla as $item)
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-4 bg-light">
                            <span class="fw-semibold">Talla {{ $item->talla }}</span>
                            <span class="badge bg-dark rounded-pill">{{ $item->total }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted">
                    No hay togas fuera actualmente.
                </div>
            @endif
        </div>
    </div>
</div>

<div class="page-card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <div class="section-title mb-1">📋 Alquileres en control</div>
            <p class="text-muted mb-0">
                Lista filtrada según el estado seleccionado.
            </p>
        </div>

        <div class="text-muted small">
            Mostrando {{ $alquileres->count() }} de {{ $alquileres->total() }} registros
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Recibo</th>
                    <th>Cliente</th>
                    <th>Institución</th>
                    <th>Reserva</th>
                    <th>Entrega</th>
                    <th>Devolución</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>Togas</th>
                    <th>Saldo</th>
                    <th>Situación</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($alquileres as $alquiler)
                    @php
                        $fechaEntrega = $alquiler->fecha_entrega ? \Carbon\Carbon::parse($alquiler->fecha_entrega) : null;
                        $fechaDevolucion = $alquiler->fecha_devolucion_programada ? \Carbon\Carbon::parse($alquiler->fecha_devolucion_programada) : null;
                        $hoy = \Carbon\Carbon::today();

                        $cantidadTogas = $alquiler->detalles
                            ->filter(fn($detalle) => $detalle->producto && $detalle->producto->tipo_producto === 'TOGA')
                            ->sum('cantidad');

                        $situacion = 'Sin situación';
                        $situacionClase = 'text-muted';

                        if ($alquiler->estado === 'RESERVADO') {
                            if ($fechaEntrega && $fechaEntrega->isToday()) {
                                $situacion = 'Entrega hoy';
                                $situacionClase = 'text-primary fw-semibold';
                            } elseif ($fechaEntrega && $fechaEntrega->isPast()) {
                                $dias = $fechaEntrega->diffInDays($hoy);
                                $situacion = 'Entrega pendiente hace ' . $dias . ' día(s)';
                                $situacionClase = 'text-warning fw-semibold';
                            } else {
                                $situacion = 'Reservado';
                                $situacionClase = 'text-muted';
                            }
                        }

                        if ($alquiler->estado === 'ENTREGADO') {
                            if ($fechaDevolucion && $fechaDevolucion->isToday()) {
                                $situacion = 'Devuelve hoy';
                                $situacionClase = 'text-primary fw-semibold';
                            } elseif ($fechaDevolucion && $fechaDevolucion->lt($hoy)) {
                                $dias = $fechaDevolucion->diffInDays($hoy);
                                $situacion = 'Atrasado por ' . $dias . ' día(s)';
                                $situacionClase = 'text-danger fw-bold';
                            } else {
                                $dias = $fechaEntrega ? $fechaEntrega->diffInDays($hoy) : 0;
                                $situacion = 'Fuera hace ' . $dias . ' día(s)';
                                $situacionClase = 'text-warning fw-semibold';
                            }
                        }

                        if ($alquiler->estado === 'DEVUELTO') {
                            $situacion = 'Devuelto';
                            $situacionClase = 'text-success fw-semibold';
                        }
                    @endphp

                    <tr>
                        <td>
                            <div class="fw-bold">{{ $alquiler->codigo_recibo }}</div>
                            <div class="small text-muted">ID {{ $alquiler->id }}</div>
                        </td>

                        <td>
                            @if($alquiler->cliente)
                                <div class="fw-semibold">
                                    {{ $alquiler->cliente->nombres }} {{ $alquiler->cliente->apellidos }}
                                </div>
                                <div class="small text-muted">
                                    {{ $alquiler->cliente->telefono ?? 'Sin teléfono' }}
                                </div>
                            @else
                                <span class="text-muted">Cliente no encontrado</span>
                            @endif
                        </td>

                        <td>
                            {{ $alquiler->institucion_representada ?: '—' }}
                        </td>

                        <td>
                            {{ $alquiler->fecha_alquiler ? \Carbon\Carbon::parse($alquiler->fecha_alquiler)->format('d/m/Y') : '—' }}
                        </td>

                        <td>
                            @if($alquiler->fecha_entrega)
                                <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($alquiler->fecha_entrega)->format('d/m/Y') }}
                                </div>

                                @if($alquiler->hora_entrega_inicio && $alquiler->hora_entrega_fin)
                                    <div class="small text-muted">
                                        {{ \Carbon\Carbon::parse($alquiler->hora_entrega_inicio)->format('h:i A') }}
                                        -
                                        {{ \Carbon\Carbon::parse($alquiler->hora_entrega_fin)->format('h:i A') }}
                                    </div>
                                @elseif($alquiler->hora_entrega)
                                    <div class="small text-muted">
                                        {{ \Carbon\Carbon::parse($alquiler->hora_entrega)->format('h:i A') }}
                                    </div>
                                @else
                                    <div class="small text-muted">
                                        Sin hora
                                    </div>
                                @endif
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            @if($alquiler->fecha_devolucion_programada)
                                <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($alquiler->fecha_devolucion_programada)->format('d/m/Y') }}
                                </div>

                                @if($alquiler->hora_devolucion_programada)
                                    <div class="small text-muted">
                                        {{ \Carbon\Carbon::parse($alquiler->hora_devolucion_programada)->format('h:i A') }}
                                    </div>
                                @else
                                    <div class="small text-muted">
                                        Sin hora
                                    </div>
                                @endif
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            @if($alquiler->estado === 'RESERVADO')
                                <span class="badge-soft badge-ajuste">RESERVADO</span>
                            @elseif($alquiler->estado === 'ENTREGADO')
                                <span class="badge-soft badge-alquiler">ENTREGADO</span>
                            @elseif($alquiler->estado === 'DEVUELTO')
                                <span class="badge-soft badge-entrada">DEVUELTO</span>
                            @else
                                <span class="badge bg-secondary">{{ $alquiler->estado }}</span>
                            @endif
                        </td>

                        <td>
                            @if($alquiler->estado_pago === 'PENDIENTE')
                                <span class="badge-soft badge-danger-soft">PENDIENTE</span>
                            @elseif($alquiler->estado_pago === 'PARCIAL')
                                <span class="badge-soft badge-ajuste">PARCIAL</span>
                            @elseif($alquiler->estado_pago === 'PAGADO')
                                <span class="badge-soft badge-entrada">PAGADO</span>
                            @else
                                <span class="badge bg-secondary">{{ $alquiler->estado_pago }}</span>
                            @endif
                        </td>

                        <td>
                            <span class="fw-bold">{{ $cantidadTogas }}</span>
                        </td>

                        <td>
                            @if($alquiler->saldo_pendiente > 0)
                                <span class="fw-bold amount-negative">
                                    Q {{ number_format($alquiler->saldo_pendiente, 2) }}
                                </span>
                            @else
                                <span class="text-success fw-bold">Q 0.00</span>
                            @endif
                        </td>

                        <td>
                            <span class="{{ $situacionClase }}">
                                {{ $situacion }}
                            </span>
                        </td>

                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary rounded-pill dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    Acciones
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-4 border-0">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('alquileres.show', $alquiler->id) }}">
                                            👁️ Ver detalle
                                        </a>
                                    </li>

                                    @if($alquiler->estado === 'RESERVADO')
                                        <li>
                                            <form action="{{ route('alquileres.entregar', $alquiler->id) }}"
                                                method="POST"
                                                class="confirm-action-form"
                                                data-title="¿Marcar como entregado?"
                                                data-text="Este alquiler pasará a estado ENTREGADO y el inventario quedará como alquilado."
                                                data-icon="question"
                                                data-confirm="Sí, entregar"
                                                data-cancel="Cancelar">
                                                @csrf

                                                <button type="submit" class="dropdown-item">
                                                    📦 Entregar
                                                </button>
                                            </form>
                                        </li>
                                    @endif

                                    @if($alquiler->estado === 'ENTREGADO')
                                        <li>
                                            <form action="{{ route('alquileres.devolver', $alquiler->id) }}"
                                                method="POST"
                                                class="confirm-action-form"
                                                data-title="¿Registrar devolución?"
                                                data-text="Este alquiler pasará a estado DEVUELTO y el inventario regresará a disponible."
                                                data-icon="warning"
                                                data-confirm="Sí, devolver"
                                                data-cancel="Cancelar">
                                                @csrf

                                                <button type="submit" class="dropdown-item">
                                                    📥 Devolver
                                                </button>
                                            </form>
                                        </li>
                                    @endif

                                    @if($alquiler->saldo_pendiente > 0 && $alquiler->estado !== 'DEVUELTO')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('pagos.create', $alquiler->id) }}">
                                                💰 Registrar pago
                                            </a>
                                        </li>
                                    @endif

                                    <li>
                                        <a class="dropdown-item" href="{{ route('alquileres.recibo', $alquiler->id) }}" target="_blank">
                                            🧾 Ver recibo
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-5">
                            No hay alquileres para mostrar con este filtro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $alquileres->links() }}
    </div>
</div>

@endsection