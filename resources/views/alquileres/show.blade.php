@extends('layouts.app')

@section('title', 'Detalle del alquiler')
@section('page_title', '🧾 Detalle del alquiler')
@section('page_subtitle', 'Consulta productos, pagos, fechas y estado del alquiler')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="section-title mb-1">📋 Alquiler {{ $alquiler->codigo_recibo }}</div>
        <p class="text-muted mb-0">
            Información completa del alquiler registrado.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('alquileres.web') }}" class="btn btn-outline-secondary rounded-pill">
            ← Volver a alquileres
        </a>

        <a href="{{ route('alquileres.recibo', $alquiler->id) }}"
           target="_blank"
           class="btn btn-outline-primary rounded-pill">
            🖨️ Ver recibo
        </a>

        <a href="{{ route('alquileres.terminos', $alquiler->id) }}"
        target="_blank"
        class="btn btn-outline-primary rounded-pill">
            📄 Carta de compromiso
        </a>

        @if($alquiler->estado !== 'CANCELADO' && $alquiler->saldo_pendiente > 0)
            <a href="{{ route('pagos.create', $alquiler->id) }}"
               class="btn btn-success rounded-pill">
                💰 Registrar pago
            </a>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">

    <div class="col-lg-4">
        <div class="page-card p-4 h-100">

            <div class="mb-3">
                <div class="stat-icon">👤</div>

                @if($alquiler->cliente)
                    <h4 class="fw-bold mb-1">
                        {{ $alquiler->cliente->nombres }} {{ $alquiler->cliente->apellidos }}
                    </h4>

                    @if($alquiler->cliente->telefono)
                        <p class="text-muted mb-0">{{ $alquiler->cliente->telefono }}</p>
                    @else
                        <p class="text-muted mb-0">Sin teléfono registrado</p>
                    @endif
                @else
                    <h4 class="fw-bold mb-1">Cliente no encontrado</h4>
                    <p class="text-muted mb-0">El cliente asociado no está disponible.</p>
                @endif
            </div>

            <hr>

            <div class="mb-3">
                <div class="text-muted small">Código de recibo</div>
                <div class="fw-bold">{{ $alquiler->codigo_recibo }}</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Estado del alquiler</div>
                <div class="mt-1">
                    @if($alquiler->estado === 'RESERVADO')
                        <span class="badge-soft badge-ajuste">RESERVADO</span>
                    @elseif($alquiler->estado === 'ENTREGADO')
                        <span class="badge-soft badge-alquiler">ENTREGADO</span>
                    @elseif($alquiler->estado === 'DEVUELTO')
                        <span class="badge-soft badge-entrada">DEVUELTO</span>
                    @elseif($alquiler->estado === 'CANCELADO')
                        <span class="badge-soft badge-danger-soft">CANCELADO</span>
                    @else
                        <span class="badge bg-secondary">{{ $alquiler->estado }}</span>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Estado de pago</div>
                <div class="mt-1">
                    @if($alquiler->estado_pago === 'PENDIENTE')
                        <span class="badge-soft badge-danger-soft">PENDIENTE</span>
                    @elseif($alquiler->estado_pago === 'PARCIAL')
                        <span class="badge-soft badge-ajuste">PARCIAL</span>
                    @elseif($alquiler->estado_pago === 'PAGADO')
                        <span class="badge-soft badge-entrada">PAGADO</span>
                    @else
                        <span class="badge bg-secondary">{{ $alquiler->estado_pago }}</span>
                    @endif
                </div>
            </div>

            @if($alquiler->cliente)
                <div class="mb-3">
                    <div class="text-muted small">DPI</div>
                    <div class="fw-bold">{{ $alquiler->cliente->dpi ?? 'Sin DPI' }}</div>
                </div>

                <div class="mb-0">
                    <div class="text-muted small">Dirección</div>
                    <div class="fw-bold">{{ $alquiler->cliente->direccion ?? 'Sin dirección' }}</div>
                </div>
            @endif

        </div>
    </div>

    <div class="col-lg-8">
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-label">Fecha de alquiler</div>
                <div class="stat-value" style="font-size: 24px;">
                    {{ $alquiler->fecha_alquiler ? \Carbon\Carbon::parse($alquiler->fecha_alquiler)->format('d/m/Y') : 'Sin fecha' }}
                </div>
                <div class="stat-sub">Fecha de creación del alquiler</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🚚</div>
                <div class="stat-label">Fecha de entrega</div>
                <div class="stat-value" style="font-size: 24px;">
                    {{ $alquiler->fecha_entrega ? \Carbon\Carbon::parse($alquiler->fecha_entrega)->format('d/m/Y') : 'Sin fecha' }}
                </div>
                <div class="stat-sub">Fecha programada de entrega</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🔁</div>
                <div class="stat-label">Devolución programada</div>
                <div class="stat-value" style="font-size: 24px;">
                    {{ $alquiler->fecha_devolucion_programada ? \Carbon\Carbon::parse($alquiler->fecha_devolucion_programada)->format('d/m/Y') : 'Sin fecha' }}
                </div>
                <div class="stat-sub">Fecha esperada de devolución</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Total</div>
                <div class="stat-value" style="font-size: 24px;">
                    Q {{ number_format($alquiler->total, 2) }}
                </div>
                <div class="stat-sub">Total del alquiler</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🏦</div>
                <div class="stat-label">Saldo pendiente</div>
                <div class="stat-value {{ $alquiler->saldo_pendiente > 0 && $alquiler->estado !== 'CANCELADO' ? 'amount-negative' : 'amount-positive' }}" style="font-size: 24px;">
                    @if($alquiler->estado === 'CANCELADO')
                        Q 0.00
                    @else
                        Q {{ number_format($alquiler->saldo_pendiente, 2) }}
                    @endif
                </div>
                <div class="stat-sub">Monto pendiente de pago</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🎟️</div>
                <div class="stat-label">Descuento</div>
                <div class="stat-value" style="font-size: 24px;">
                    Q {{ number_format($alquiler->descuento, 2) }}
                </div>
                <div class="stat-sub">Descuento aplicado</div>
            </div>

        </div>
    </div>

</div>

<div class="row g-4">

    <div class="col-lg-8">
        <div class="page-card p-4 mb-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <div>
                    <div class="section-title mb-1">📦 Productos del alquiler</div>
                    <p class="text-muted mb-0">
                        Detalle de productos incluidos en este recibo.
                    </p>
                </div>

                <span class="badge text-bg-light rounded-pill px-3 py-2">
                    {{ $alquiler->detalles->count() }} productos
                </span>
            </div>

            @if($alquiler->detalles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th>Subtotal</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($alquiler->detalles as $detalle)
                                <tr>
                                    <td>
                                        @if($detalle->producto)
                                            <div class="fw-bold">{{ $detalle->producto->codigo }}</div>
                                            <small class="text-muted">{{ $detalle->producto->nombre }}</small>
                                        @else
                                            <span class="text-muted">Producto no encontrado</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($detalle->producto)
                                            @if($detalle->producto->tipo_producto === 'TOGA')
                                                <span class="badge-soft badge-toga">TOGA</span>
                                            @elseif($detalle->producto->tipo_producto === 'BIRRETE')
                                                <span class="badge-soft badge-birrete">BIRRETE</span>
                                            @elseif($detalle->producto->tipo_producto === 'COLLARIN')
                                                <span class="badge-soft badge-collarin">COLLARÍN</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $detalle->producto->tipo_producto }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>{{ $detalle->cantidad }}</strong>
                                    </td>

                                    <td>
                                        Q {{ number_format($detalle->precio_unitario, 2) }}
                                    </td>

                                    <td>
                                        <strong>Q {{ number_format($detalle->subtotal, 2) }}</strong>
                                    </td>

                                    <td>
                                        @if($detalle->estado === 'PENDIENTE')
                                            <span class="badge-soft badge-ajuste">PENDIENTE</span>
                                        @elseif($detalle->estado === 'ENTREGADO')
                                            <span class="badge-soft badge-alquiler">ENTREGADO</span>
                                        @elseif($detalle->estado === 'DEVUELTO')
                                            <span class="badge-soft badge-entrada">DEVUELTO</span>
                                        @elseif($detalle->estado === 'CANCELADO')
                                            <span class="badge-soft badge-danger-soft">CANCELADO</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $detalle->estado }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            @else
                <div class="alert alert-light border rounded-4 mb-0">
                    Este alquiler no tiene productos registrados.
                </div>
            @endif

        </div>

        <div class="page-card p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <div>
                    <div class="section-title mb-1">💰 Pagos registrados</div>
                    <p class="text-muted mb-0">
                        Historial de pagos asociados al alquiler.
                    </p>
                </div>

                @if($alquiler->estado !== 'CANCELADO' && $alquiler->saldo_pendiente > 0)
                    <a href="{{ route('pagos.create', $alquiler->id) }}" class="btn btn-sm btn-success rounded-pill">
                        ➕ Registrar pago
                    </a>
                @endif
            </div>

            @if($alquiler->pagos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($alquiler->pagos as $pago)
                                <tr>
                                    <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>

                                    <td>
                                        <span class="amount-positive">
                                            Q {{ number_format($pago->monto, 2) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-light rounded-pill px-3 py-2">
                                            {{ $pago->metodo_pago }}
                                        </span>
                                    </td>

                                    <td>{{ $pago->referencia ?? '-' }}</td>
                                    <td>{{ $pago->observaciones ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            @else
                <div class="alert alert-light border rounded-4 mb-0">
                    No hay pagos registrados para este alquiler.
                </div>
            @endif

        </div>
    </div>

    <div class="col-lg-4">
        <div class="page-card p-4">

            <div class="section-title mb-3">⚡ Acciones rápidas</div>

            <div class="d-grid gap-2">

                <a href="{{ route('alquileres.recibo', $alquiler->id) }}"
                   target="_blank"
                   class="btn btn-outline-primary rounded-pill">
                    🖨️ Imprimir recibo
                </a>

                @if($alquiler->estado !== 'CANCELADO' && $alquiler->saldo_pendiente > 0)
                    <a href="{{ route('pagos.create', $alquiler->id) }}"
                       class="btn btn-outline-success rounded-pill">
                        💰 Registrar pago
                    </a>
                @endif

                @if($alquiler->estado === 'RESERVADO')
                    <form action="{{ route('alquileres.entregar', $alquiler->id) }}"
                          method="POST"
                          class="confirm-action-form"
                          data-title="¿Entregar alquiler?"
                          data-text="Se descontará el inventario disponible y el alquiler pasará a ENTREGADO."
                          data-icon="question"
                          data-confirm="Sí, entregar"
                          data-cancel="Cancelar">
                        @csrf

                        <button type="submit" class="btn btn-outline-warning rounded-pill w-100">
                            🚚 Entregar alquiler
                        </button>
                    </form>

                    @if($alquiler->saldo_pendiente <= 0)
                        <form action="{{ route('alquileres.cancelar', $alquiler->id) }}"
                              method="POST"
                              class="confirm-action-form"
                              data-title="¿Cancelar alquiler?"
                              data-text="El alquiler será marcado como CANCELADO y no contará como deuda pendiente."
                              data-icon="warning"
                              data-confirm="Sí, cancelar"
                              data-cancel="Volver">
                            @csrf

                            <button type="submit" class="btn btn-outline-danger rounded-pill w-100">
                                ❌ Cancelar alquiler
                            </button>
                        </form>
                    @endif
                @endif

                @if($alquiler->estado === 'ENTREGADO')
                    <form action="{{ route('alquileres.devolver', $alquiler->id) }}"
                          method="POST"
                          class="confirm-action-form"
                          data-title="¿Registrar devolución?"
                          data-text="Se restaurará el inventario disponible y el alquiler pasará a DEVUELTO."
                          data-icon="question"
                          data-confirm="Sí, devolver"
                          data-cancel="Cancelar">
                        @csrf

                        <button type="submit" class="btn btn-outline-info rounded-pill w-100">
                            🔁 Registrar devolución
                        </button>
                    </form>
                @endif

            </div>

            @if($alquiler->observaciones)
                <hr>

                <div class="section-title mb-2" style="font-size: 16px;">
                    📝 Observaciones
                </div>

                <div class="alert alert-light border rounded-4 mb-0">
                    {{ $alquiler->observaciones }}
                </div>
            @endif

        </div>
    </div>

</div>

@endsection