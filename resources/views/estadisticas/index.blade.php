@extends('layouts.app')

@section('title', 'Estadísticas')

@section('content')
<style>
    .stats-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f4f7ff 100%);
        border: 1px solid rgba(13, 110, 253, .08);
        box-shadow: 0 16px 35px rgba(15, 23, 42, .07);
    }

    .stats-card {
        border: 0;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .07);
        transition: .2s ease;
        overflow: hidden;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(15, 23, 42, .10);
    }

    .stats-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        background: #eef4ff;
    }

    .chart-box {
        min-height: 300px;
        position: relative;
        padding: 0.5rem 0.25rem 0;
    }

    .chart-box canvas {
        width: 100% !important;
        height: 340px !important;
    }

    .soft-badge {
        background: #eef4ff;
        color: #0d6efd;
        border-radius: 999px;
        padding: .45rem .85rem;
        font-weight: 700;
        font-size: .78rem;
    }

    .stats-filter-card {
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 16px 35px rgba(15, 23, 42, .07);
    }

    .stats-table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
    }

    .stats-table tbody td {
        border-bottom: 1px solid #f1f5f9;
    }

    .ranking-card {
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .07);
        border: 1px solid rgba(226, 232, 240, .8);
    }

    .ranking-number {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        background: #eef4ff;
        color: #0d6efd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex-shrink: 0;
    }

    .ranking-row {
        padding: .8rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .ranking-row:last-child {
        border-bottom: 0;
    }

    .ranking-mini-value {
        font-weight: 800;
        color: #0f172a;
    }

    .insight-card {
        border-radius: 22px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid rgba(226, 232, 240, .9);
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }

</style>

<div class="container-fluid py-4">

    <div class="page-card stats-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="stats-icon">📊</div>
                    <div>
                        <h1 class="h3 mb-1">Estadísticas</h1>
                        <p class="text-muted mb-0">
                            Análisis de alquileres, pagos, productos, descuentos y mora.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <span class="soft-badge">
                    {{ $tituloPeriodo }}
                </span>

                <div class="small text-muted mt-2 mb-3">
                    Del {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2">
                    <a href="{{ route('estadisticas.exportar.xlsx', request()->query()) }}"
                       class="btn btn-success btn-sm rounded-pill px-3">
                        📗 Descargar XLSX
                    </a>

                    <form id="formExportarPdf"
                        method="POST"
                        action="{{ route('estadisticas.exportar.pdf') }}"
                        class="d-inline">
                        @csrf

                        @foreach (request()->query() as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach

                        <input type="hidden" name="grafico_alquileres" id="graficoAlquileresImagen">
                        <input type="hidden" name="grafico_financiero" id="graficoFinancieroImagen">

                        <button type="button"
                                id="btnExportarPdf"
                                class="btn btn-danger btn-sm rounded-pill px-3">
                            📄 Descargar PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="page-card stats-filter-card mb-4">
        <form method="GET" action="{{ route('estadisticas.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Tipo de vista</label>
                    <select name="tipo_vista" id="tipoVista" class="form-select rounded-pill">
                        <option value="dia" {{ $tipoVista === 'dia' ? 'selected' : '' }}>Día</option>
                        <option value="mes" {{ $tipoVista === 'mes' ? 'selected' : '' }}>Mes</option>
                        <option value="anio" {{ $tipoVista === 'anio' ? 'selected' : '' }}>Año</option>
                        <option value="rango" {{ $tipoVista === 'rango' ? 'selected' : '' }}>Rango personalizado</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Área</label>
                    <select name="area" class="form-select rounded-pill">
                        <option value="general" {{ $area === 'general' ? 'selected' : '' }}>General</option>
                        <option value="alquileres" {{ $area === 'alquileres' ? 'selected' : '' }}>Alquileres</option>
                        <option value="pagos" {{ $area === 'pagos' ? 'selected' : '' }}>Pagos</option>
                        <option value="productos" {{ $area === 'productos' ? 'selected' : '' }}>Productos</option>
                        <option value="mora" {{ $area === 'mora' ? 'selected' : '' }}>Mora</option>
                        <option value="descuentos" {{ $area === 'descuentos' ? 'selected' : '' }}>Descuentos</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 filtro-campo filtro-dia">
                    <label class="form-label fw-semibold">Fecha</label>
                    <input type="date" name="fecha" value="{{ $fecha }}" class="form-control rounded-pill">
                </div>

                <div class="col-12 col-md-2 filtro-campo filtro-mes">
                    <label class="form-label fw-semibold">Mes</label>
                    <input type="month" name="mes" value="{{ $mes }}" class="form-control rounded-pill">
                </div>

                <div class="col-12 col-md-2 filtro-campo filtro-anio">
                    <label class="form-label fw-semibold">Año</label>
                    <select name="anio" class="form-select rounded-pill">
                        @for ($year = now()->year + 1; $year >= 2020; $year--)
                            <option value="{{ $year }}" {{ (int) $anio === $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-12 col-md-2 filtro-campo filtro-rango">
                    <label class="form-label fw-semibold">Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}" class="form-control rounded-pill">
                </div>

                <div class="col-12 col-md-2 filtro-campo filtro-rango">
                    <label class="form-label fw-semibold">Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}" class="form-control rounded-pill">
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('estadisticas.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Limpiar
                    </a>

                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        Aplicar filtros
                    </button>
                </div>

            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Alquileres registrados</div>
                        <div class="h2 mb-0">{{ number_format($alquileresRegistrados) }}</div>
                    </div>
                    <div class="stats-icon">📦</div>
                </div>
                <div class="small text-muted mt-3">En el periodo seleccionado</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Pagos registrados</div>
                        <div class="h2 mb-0">{{ number_format($pagosRegistrados) }}</div>
                    </div>
                    <div class="stats-icon">💳</div>
                </div>
                <div class="small text-muted mt-3">Cantidad de pagos realizados</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Ingresos recibidos</div>
                        <div class="h2 mb-0">Q{{ number_format($ingresosRecibidos, 2) }}</div>
                    </div>
                    <div class="stats-icon">💰</div>
                </div>
                <div class="small text-muted mt-3">Dinero real recibido</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Descuentos aplicados</div>
                        <div class="h2 mb-0">Q{{ number_format($descuentosAplicados, 2) }}</div>
                    </div>
                    <div class="stats-icon">🏷️</div>
                </div>
                <div class="small text-muted mt-3">Rebajas autorizadas</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Mora generada</div>
                        <div class="h2 mb-0">Q{{ number_format($moraGenerada, 2) }}</div>
                    </div>
                    <div class="stats-icon">⏰</div>
                </div>
                <div class="small text-muted mt-3">Por devoluciones tardías</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Saldo pendiente actual</div>
                        <div class="h2 mb-0">Q{{ number_format($saldoPendienteActual, 2) }}</div>
                    </div>
                    <div class="stats-icon">🧾</div>
                </div>
                <div class="small text-muted mt-3">Saldo vivo no cancelado</div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Producto más alquilado</div>
                        <div class="h5 mb-0">
                            {{ $productoMasAlquilado->nombre ?? 'Sin datos' }}
                        </div>
                    </div>
                    <div class="stats-icon">🏆</div>
                </div>
                <div class="small text-muted mt-3">
                    {{ $productoMasAlquilado ? number_format($productoMasAlquilado->total_alquilado) . ' unidades' : 'Según cantidad alquilada' }}
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Talla de toga más alquilada</div>
                        <div class="h5 mb-0">
                            {{ $tallaTogaMasAlquilada->talla ?? 'Sin datos' }}
                        </div>
                    </div>
                    <div class="stats-icon">🎓</div>
                </div>
                <div class="small text-muted mt-3">
                    {{ $tallaTogaMasAlquilada ? number_format($tallaTogaMasAlquilada->total_alquilado) . ' togas' : 'Solo productos tipo toga' }}
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-12 col-xl-6">
            <div class="page-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h5 mb-1">Alquileres por periodo</h2>
                        <p class="text-muted small mb-0">Actividad registrada según el filtro seleccionado.</p>
                    </div>
                    <span class="soft-badge">Actividad</span>
                </div>

                <div class="chart-box">
                    <canvas id="graficoAlquileres"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="page-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h5 mb-1">Resumen financiero</h2>
                        <p class="text-muted small mb-0">Ingresos reales, descuentos y mora generada.</p>
                    </div>
                    <span class="soft-badge">Finanzas</span>
                </div>

                <div class="chart-box">
                    <canvas id="graficoFinanciero"></canvas>
                </div>
            </div>
        </div>

    </div>

    <div class="page-card mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">Rankings del periodo</h2>
                <p class="text-muted small mb-0">
                    Datos destacados según el periodo seleccionado.
                </p>
            </div>

            <span class="soft-badge">Análisis</span>
        </div>

        <div class="row g-3 mb-4">

            <div class="col-12 col-md-6 col-xl-3">
                <div class="insight-card h-100 p-3">
                    <div class="text-muted small mb-1">Día con más alquileres</div>
                    <div class="h5 mb-1">
                        {{ $diaMasAlquileres ? \Carbon\Carbon::parse($diaMasAlquileres->periodo)->format('d/m/Y') : 'Sin datos' }}
                    </div>
                    <div class="small text-muted">
                        {{ $diaMasAlquileres ? number_format($diaMasAlquileres->total) . ' alquiler(es)' : 'No hubo registros' }}
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="insight-card h-100 p-3">
                    <div class="text-muted small mb-1">Día con más ingresos</div>
                    <div class="h5 mb-1">
                        {{ $diaMasIngresos ? \Carbon\Carbon::parse($diaMasIngresos->periodo)->format('d/m/Y') : 'Sin datos' }}
                    </div>
                    <div class="small text-muted">
                        {{ $diaMasIngresos ? 'Q' . number_format($diaMasIngresos->total, 2) . ' recibidos' : 'No hubo ingresos' }}
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="insight-card h-100 p-3">
                    <div class="text-muted small mb-1">Día con más descuentos</div>
                    <div class="h5 mb-1">
                        {{ $diaMasDescuentos ? \Carbon\Carbon::parse($diaMasDescuentos->periodo)->format('d/m/Y') : 'Sin datos' }}
                    </div>
                    <div class="small text-muted">
                        {{ $diaMasDescuentos ? 'Q' . number_format($diaMasDescuentos->total, 2) . ' descontados' : 'No hubo descuentos' }}
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="insight-card h-100 p-3">
                    <div class="text-muted small mb-1">Día con más mora</div>
                    <div class="h5 mb-1">
                        {{ $diaMasMora ? \Carbon\Carbon::parse($diaMasMora->periodo)->format('d/m/Y') : 'Sin datos' }}
                    </div>
                    <div class="small text-muted">
                        {{ $diaMasMora ? 'Q' . number_format($diaMasMora->total, 2) . ' de mora' : 'No hubo mora' }}
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4">

            <div class="col-12 col-xl-4">
                <div class="ranking-card h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h3 class="h6 mb-1">Método de pago más usado</h3>
                            <p class="small text-muted mb-0">Según cantidad de pagos.</p>
                        </div>
                        <div class="stats-icon">💳</div>
                    </div>

                    @if ($metodoPagoMasUsado)
                        <div class="mt-3">
                            <div class="h4 mb-1">{{ $metodoPagoMasUsado->metodo_pago }}</div>
                            <div class="text-muted small">
                                {{ number_format($metodoPagoMasUsado->total) }} pago(s)
                                · Q{{ number_format($metodoPagoMasUsado->ingresos, 2) }} recibidos
                            </div>
                        </div>
                    @else
                        <div class="text-muted small mt-3">No hay pagos registrados en este periodo.</div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="ranking-card h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h3 class="h6 mb-1">Institución con más alquileres</h3>
                            <p class="small text-muted mb-0">Según registros del periodo.</p>
                        </div>
                        <div class="stats-icon">🏫</div>
                    </div>

                    @if ($institucionMasAlquileres)
                        <div class="mt-3">
                            <div class="h5 mb-1">{{ $institucionMasAlquileres->institucion_representada }}</div>
                            <div class="text-muted small">
                                {{ number_format($institucionMasAlquileres->total) }} alquiler(es)
                            </div>
                        </div>
                    @else
                        <div class="text-muted small mt-3">No hay instituciones registradas en este periodo.</div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="ranking-card h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h3 class="h6 mb-1">Resumen rápido</h3>
                            <p class="small text-muted mb-0">Lectura general del periodo.</p>
                        </div>
                        <div class="stats-icon">✨</div>
                    </div>

                    <div class="small text-muted mt-3">
                        En este periodo se registraron
                        <strong>{{ number_format($alquileresRegistrados) }}</strong> alquiler(es),
                        <strong>{{ number_format($pagosRegistrados) }}</strong> pago(s) y
                        <strong>Q{{ number_format($ingresosRecibidos, 2) }}</strong> en ingresos reales.
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="ranking-card h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="h6 mb-1">Top 5 productos más alquilados</h3>
                            <p class="small text-muted mb-0">Productos con mayor movimiento.</p>
                        </div>
                        <div class="stats-icon">🏆</div>
                    </div>

                    @forelse ($topProductos as $index => $producto)
                        <div class="ranking-row d-flex align-items-center gap-3">
                            <div class="ranking-number">{{ $index + 1 }}</div>

                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $producto->nombre }}</div>
                                <div class="small text-muted">{{ $producto->tipo_producto }}</div>
                            </div>

                            <div class="ranking-mini-value">
                                {{ number_format($producto->total) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            No hay productos alquilados en este periodo.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="ranking-card h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="h6 mb-1">Top tallas de toga</h3>
                            <p class="small text-muted mb-0">Tallas con más salida.</p>
                        </div>
                        <div class="stats-icon">🎓</div>
                    </div>

                    @forelse ($topTallasToga as $index => $talla)
                        <div class="ranking-row d-flex align-items-center gap-3">
                            <div class="ranking-number">{{ $index + 1 }}</div>

                            <div class="flex-grow-1">
                                <div class="fw-semibold">Talla {{ $talla->talla }}</div>
                                <div class="small text-muted">Togas alquiladas</div>
                            </div>

                            <div class="ranking-mini-value">
                                {{ number_format($talla->total) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            No hay togas alquiladas en este periodo.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <div class="page-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">Tabla resumen</h2>
                <p class="text-muted small mb-0">
                    Resumen agrupado según el periodo seleccionado.
                </p>
            </div>

            <span class="badge rounded-pill bg-light text-dark border">
                {{ $tablaResumen->count() }} periodo(s)
            </span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle stats-table">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th class="text-center">Alquileres</th>
                        <th class="text-center">Pagos</th>
                        <th class="text-end">Ingresos</th>
                        <th class="text-end">Descuentos</th>
                        <th class="text-end">Mora</th>
                        <th class="text-end">Total aplicado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tablaResumen as $fila)
                        <tr>
                            <td class="fw-semibold">{{ $fila['periodo'] }}</td>
                            <td class="text-center">{{ number_format($fila['alquileres']) }}</td>
                            <td class="text-center">{{ number_format($fila['pagos']) }}</td>
                            <td class="text-end">Q{{ number_format($fila['ingresos'], 2) }}</td>
                            <td class="text-end">Q{{ number_format($fila['descuentos'], 2) }}</td>
                            <td class="text-end">Q{{ number_format($fila['mora'], 2) }}</td>
                            <td class="text-end fw-semibold">Q{{ number_format($fila['total_aplicado'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay datos para el periodo seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($tablaResumen->count() > 0)
                    <tfoot>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td class="text-center">{{ number_format($tablaResumen->sum('alquileres')) }}</td>
                            <td class="text-center">{{ number_format($tablaResumen->sum('pagos')) }}</td>
                            <td class="text-end">Q{{ number_format($tablaResumen->sum('ingresos'), 2) }}</td>
                            <td class="text-end">Q{{ number_format($tablaResumen->sum('descuentos'), 2) }}</td>
                            <td class="text-end">Q{{ number_format($tablaResumen->sum('mora'), 2) }}</td>
                            <td class="text-end">Q{{ number_format($tablaResumen->sum('total_aplicado'), 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const labels = @json($chartLabels);
    const alquileres = @json($chartAlquileres);
    const ingresos = @json($chartIngresos);
    const descuentos = @json($chartDescuentos);
    const mora = @json($chartMora);

    const hayDatos = labels.length > 0;

    if (hayDatos) {
        new Chart(document.getElementById('graficoAlquileres'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Alquileres registrados',
                        data: alquileres,
                        borderWidth: 1,
                        borderRadius: 12,
                        barThickness: 42,
                        maxBarThickness: 55,
                        categoryPercentage: 0.55,
                        barPercentage: 0.75
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('graficoFinanciero'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ingresos recibidos',
                        data: ingresos,
                        borderWidth: 1,
                        borderRadius: 10,
                        barThickness: 32,
                        maxBarThickness: 42
                    },
                    {
                        label: 'Descuentos aplicados',
                        data: descuentos,
                        borderWidth: 1,
                        borderRadius: 10,
                        barThickness: 32,
                        maxBarThickness: 42
                    },
                    {
                        label: 'Mora generada',
                        data: mora,
                        borderWidth: 1,
                        borderRadius: 10,
                        barThickness: 32,
                        maxBarThickness: 42
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let valor = context.raw ?? 0;
                                return context.dataset.label + ': Q' + Number(valor).toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    } else {
        document.getElementById('graficoAlquileres').parentElement.innerHTML =
            '<div class="text-center text-muted py-5">No hay datos de alquileres para graficar.</div>';

        document.getElementById('graficoFinanciero').parentElement.innerHTML =
            '<div class="text-center text-muted py-5">No hay datos financieros para graficar.</div>';
    }

    function actualizarFiltrosVisibles() {
        const tipo = document.getElementById('tipoVista').value;

        document.querySelectorAll('.filtro-campo').forEach(campo => {
            campo.classList.add('d-none');
        });

        if (tipo === 'dia') {
            document.querySelectorAll('.filtro-dia').forEach(campo => campo.classList.remove('d-none'));
        }

        if (tipo === 'mes') {
            document.querySelectorAll('.filtro-mes').forEach(campo => campo.classList.remove('d-none'));
        }

        if (tipo === 'anio') {
            document.querySelectorAll('.filtro-anio').forEach(campo => campo.classList.remove('d-none'));
        }

        if (tipo === 'rango') {
            document.querySelectorAll('.filtro-rango').forEach(campo => campo.classList.remove('d-none'));
        }
    }

    document.getElementById('tipoVista').addEventListener('change', actualizarFiltrosVisibles);
    actualizarFiltrosVisibles();

    const btnExportarPdf = document.getElementById('btnExportarPdf');

    if (btnExportarPdf) {
        btnExportarPdf.addEventListener('click', function () {
            const canvasAlquileres = document.getElementById('graficoAlquileres');
            const canvasFinanciero = document.getElementById('graficoFinanciero');

            if (canvasAlquileres && canvasFinanciero && hayDatos) {
                document.getElementById('graficoAlquileresImagen').value =
                    canvasAlquileres.toDataURL('image/png');

                document.getElementById('graficoFinancieroImagen').value =
                    canvasFinanciero.toDataURL('image/png');
            }

            document.getElementById('formExportarPdf').submit();
        });
    }
</script>
@endsection