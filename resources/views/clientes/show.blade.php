@extends('layouts.app')

@section('title', 'Historial de ' . $cliente->nombres . ' ' . $cliente->apellidos)
@section('page_title', '👤 ' . $cliente->nombres . ' ' . $cliente->apellidos)
@section('page_subtitle', 'Datos del cliente e historial de todo lo que ha alquilado')

@section('content')

    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary rounded-pill">← Volver a clientes</a>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('clientes.show.pdf', $cliente) }}" target="_blank" class="btn btn-outline-danger rounded-pill">📄 PDF para imprimir</a>
            <a href="{{ route('clientes.show.excel', $cliente) }}" class="btn btn-outline-success rounded-pill">📊 Excel</a>
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline-primary rounded-pill">✏️ Editar datos</a>
        </div>
    </div>

    {{-- Datos del cliente --}}
    <div class="page-card p-3 p-md-4 mb-4">
        <div class="section-title mb-3">🪪 Datos del cliente</div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="small text-muted">Nombre</div>
                <div class="fw-semibold">{{ $cliente->nombres }} {{ $cliente->apellidos }}</div>
            </div>
            <div class="col-md-2">
                <div class="small text-muted">Teléfono</div>
                <div class="fw-semibold">{{ $cliente->telefono ?: '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">DPI</div>
                <div class="fw-semibold">{{ $cliente->dpi ?: '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Estado</div>
                @if($cliente->activo)
                    <span class="badge-soft badge-entrada">Activo</span>
                @else
                    <span class="badge-soft badge-ajuste">Inactivo</span>
                @endif
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Dirección</div>
                <div>{{ $cliente->direccion ?: '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Institución</div>
                <div>{{ $cliente->institucion_representada ?: 'Individual' }}</div>
            </div>
            @if($cliente->observaciones)
                <div class="col-12">
                    <div class="small text-muted">Observaciones</div>
                    <div>{{ $cliente->observaciones }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Resumen --}}
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-label">Alquileres</div>
            <div class="stat-value">{{ $resumen['alquileres'] }}</div>
            <div class="stat-sub">
                en {{ $resumen['anios_activos'] }} año(s)
                @if($resumen['cancelados']) · {{ $resumen['cancelados'] }} cancelado(s) @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <div class="stat-label">Togas alquiladas</div>
            <div class="stat-value">{{ $resumen['togas'] }}</div>
            <div class="stat-sub">
                @if($resumen['primer_alquiler'])
                    Cliente desde {{ $resumen['primer_alquiler']->format('d/m/Y') }}
                @else
                    Sin alquileres
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💵</div>
            <div class="stat-label">Total pagado</div>
            <div class="stat-value">Q {{ number_format($resumen['pagado'], 2) }}</div>
            <div class="stat-sub">de Q {{ number_format($resumen['total'], 2) }} alquilados</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-label">Saldo pendiente</div>
            <div class="stat-value {{ $resumen['saldo'] > 0 ? 'amount-negative' : '' }}">Q {{ number_format($resumen['saldo'], 2) }}</div>
            <div class="stat-sub">
                @if($resumen['ultimo_alquiler'])
                    Último alquiler: {{ $resumen['ultimo_alquiler']->format('d/m/Y') }}
                @endif
            </div>
        </div>
    </div>

    {{-- Historial por año --}}
    @forelse($anios as $anio)
        <div class="page-card p-3 p-md-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="section-title mb-0">📅 {{ $anio['anio'] }}</div>
                <div class="small text-muted">
                    {{ $anio['cantidad'] }} alquiler(es) · {{ $anio['togas'] }} toga(s)
                    · Q {{ number_format($anio['total'], 2) }}
                    @if($anio['saldo'] > 0)
                        · <span class="text-danger fw-semibold">saldo Q {{ number_format($anio['saldo'], 2) }}</span>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Recibo</th>
                            <th>Fecha</th>
                            <th>Entrega / devolución</th>
                            <th>Qué alquiló</th>
                            <th>Estado</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Pagado</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anio['alquileres'] as $a)
                            <tr class="{{ $a['cancelado'] ? 'text-muted' : '' }}">
                                <td>
                                    <a href="{{ route('alquileres.show', $a['id']) }}">{{ $a['recibo'] }}</a>
                                    @if($a['institucion'])
                                        <div class="small text-muted">{{ $a['institucion'] }}</div>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $a['fecha']?->format('d/m/Y') ?? '—' }}</td>
                                <td class="small text-nowrap">
                                    Entrega: {{ $a['fecha_entrega']?->format('d/m/Y') ?? '—' }}<br>
                                    Devolvió: {{ $a['fecha_devolucion_real']?->format('d/m/Y') ?? ($a['cancelado'] ? '—' : 'pendiente (' . ($a['fecha_devolucion_programada']?->format('d/m/Y') ?? 's/f') . ')') }}
                                </td>
                                <td class="small">
                                    <strong>{{ $a['togas'] }} toga(s)</strong>{{ $a['tallas'] ? ' — ' . $a['tallas'] : '' }}
                                    @if($a['accesorios'])
                                        <div class="text-muted">{{ $a['accesorios'] }}</div>
                                    @endif
                                    @if($a['danos'])
                                        <div class="text-danger">{{ $a['danos'] }} daño(s)/extravío(s): Q {{ number_format($a['danos_monto'], 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $a['estado']) }}</span>
                                    <div class="small text-muted">{{ str_replace('_', ' ', $a['estado_pago']) }}</div>
                                </td>
                                <td class="text-end">Q {{ number_format($a['total'], 2) }}</td>
                                <td class="text-end">Q {{ number_format($a['pagado'], 2) }}</td>
                                <td class="text-end {{ $a['saldo'] > 0 ? 'text-danger fw-bold' : '' }}">Q {{ number_format($a['saldo'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-light border rounded-4">Este cliente todavía no tiene alquileres.</div>
    @endforelse
@endsection
