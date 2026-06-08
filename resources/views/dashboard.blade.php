@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', '📊 Dashboard')
@section('page_subtitle', 'Resumen general del sistema de alquiler de togas y accesorios')

@section('content')

    <div class="mb-4">
        <div class="section-title">⚡ Accesos rápidos</div>

        <div class="quick-grid">
            <a href="{{ url('/alquileres-web/crear') }}" class="quick-card">
                <div class="quick-icon">🧾</div>
                <div class="card-title-mini">Nuevo alquiler</div>
                <p class="card-desc-mini">Registrar un alquiler rápidamente</p>
            </a>

            <a href="{{ url('/clientes-web/crear') }}" class="quick-card">
                <div class="quick-icon">👤</div>
                <div class="card-title-mini">Registrar cliente</div>
                <p class="card-desc-mini">Agregar un nuevo cliente al sistema</p>
            </a>

            <a href="{{ url('/productos-web') }}" class="quick-card">
                <div class="quick-icon">👗</div>
                <div class="card-title-mini">Ver productos</div>
                <p class="card-desc-mini">Consultar togas, birretes y collarines</p>
            </a>

            <a href="{{ url('/inventario/movimientos') }}" class="quick-card">
                <div class="quick-icon">📦</div>
                <div class="card-title-mini">Ver movimientos</div>
                <p class="card-desc-mini">Revisar entradas, ajustes y devoluciones</p>
            </a>
        </div>
    </div>

    <div class="mb-4">
        <div class="section-title">📌 Resumen general</div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-label">Total productos</div>
                <div class="stat-value">{{ $totalProductos }}</div>
                <div class="stat-sub">Registros totales en inventario</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-label">Productos activos</div>
                <div class="stat-value">{{ $productosActivos }}</div>
                <div class="stat-sub">Disponibles para operar</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⛔</div>
                <div class="stat-label">Productos inactivos</div>
                <div class="stat-value">{{ $productosInactivos }}</div>
                <div class="stat-sub">No aparecen en nuevos alquileres</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-label">Stock total general</div>
                <div class="stat-value">{{ $stockTotalGeneral }}</div>
                <div class="stat-sub">Inventario total acumulado</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🟢</div>
                <div class="stat-label">Stock disponible</div>
                <div class="stat-value">{{ $stockDisponibleGeneral }}</div>
                <div class="stat-sub">Unidades listas para alquilar</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🟠</div>
                <div class="stat-label">Stock alquilado</div>
                <div class="stat-value">{{ $stockAlquiladoGeneral }}</div>
                <div class="stat-sub">Unidades actualmente fuera</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🕒</div>
                <div class="stat-label">Alquileres reservados</div>
                <div class="stat-value">{{ $alquileresReservados }}</div>
                <div class="stat-sub">Pendientes de entrega</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🚚</div>
                <div class="stat-label">Alquileres entregados</div>
                <div class="stat-value">{{ $alquileresEntregados }}</div>
                <div class="stat-sub">Actualmente en poder del cliente</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🔁</div>
                <div class="stat-label">Alquileres devueltos</div>
                <div class="stat-value">{{ $alquileresDevueltos }}</div>
                <div class="stat-sub">Ya finalizados correctamente</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-label">Alquileres cancelados</div>
                <div class="stat-value">{{ $alquileresCancelados }}</div>
                <div class="stat-sub">No cuentan como deuda pendiente</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💸</div>
                <div class="stat-label">Pagos pendientes</div>
                <div class="stat-value">{{ $pagosPendientes }}</div>
                <div class="stat-sub">Alquileres con saldo pendiente</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Total por cobrar</div>
                <div class="stat-value">Q {{ number_format($totalPorCobrar, 2) }}</div>
                <div class="stat-sub">Excluye alquileres cancelados</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🏦</div>
                <div class="stat-label">Ingresos recibidos</div>
                <div class="stat-value">Q {{ number_format($ingresosRecibidos, 2) }}</div>
                <div class="stat-sub">Total cobrado en pagos registrados</div>
            </div>

        </div>
    </div>

    <div class="page-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">🧾 Movimientos recientes de inventario</div>
            <a href="{{ url('/inventario/movimientos') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                Ver todos
            </a>
        </div>

        @if($movimientosRecientes->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Movimiento</th>
                            <th>Cantidad</th>
                            <th>Disponible</th>
                            <th>Alquilado</th>
                            <th>Motivo</th>
                            <th>Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movimientosRecientes as $movimiento)
                            <tr>
                                <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>

                                <td>
                                    @if($movimiento->producto)
                                        <div class="fw-bold">{{ $movimiento->producto->codigo }}</div>
                                        <small class="text-muted">{{ $movimiento->producto->nombre }}</small>
                                    @else
                                        <span class="text-muted">Producto no encontrado</span>
                                    @endif
                                </td>

                                <td>
                                    @if($movimiento->tipo_movimiento === 'ENTRADA')
                                        <span class="badge-soft badge-entrada">ENTRADA</span>
                                    @elseif($movimiento->tipo_movimiento === 'AJUSTE')
                                        <span class="badge-soft badge-ajuste">AJUSTE</span>
                                    @elseif($movimiento->tipo_movimiento === 'ALQUILER')
                                        <span class="badge-soft badge-alquiler">ALQUILER</span>
                                    @elseif($movimiento->tipo_movimiento === 'DEVOLUCION')
                                        <span class="badge-soft badge-devolucion">DEVOLUCIÓN</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $movimiento->tipo_movimiento }}</span>
                                    @endif
                                </td>

                                <td>
                                    @if($movimiento->cantidad < 0)
                                        <span class="amount-negative">{{ $movimiento->cantidad }}</span>
                                    @else
                                        <span class="amount-positive">+{{ $movimiento->cantidad }}</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $movimiento->stock_anterior_disponible }}
                                    →
                                    {{ $movimiento->stock_nuevo_disponible }}
                                </td>

                                <td>
                                    {{ $movimiento->stock_anterior_alquilado }}
                                    →
                                    {{ $movimiento->stock_nuevo_alquilado }}
                                </td>

                                <td>{{ $movimiento->motivo ?? '-' }}</td>
                                <td>{{ $movimiento->referencia ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-light border rounded-4 mb-0">
                Todavía no hay movimientos registrados.
            </div>
        @endif
    </div>

@endsection