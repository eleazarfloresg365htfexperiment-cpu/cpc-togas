@extends('layouts.app')

@section('title', 'Actividades · Por cobrar')
@section('page_title', '📋 Actividades')
@section('page_subtitle', 'Alquileres con saldo pendiente: no se pueden entregar sin el pago completo')

@section('content')
    @include('actividades.partials.pestanas')

    @php
        $totalPendiente = collect($alquileres)->sum('saldo');
        $urgentes = collect($alquileres)->where('urgente', true);
    @endphp

    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Por cobrar</div>
            <div class="stat-value">Q {{ number_format($totalPendiente, 2) }}</div>
            <div class="stat-sub">{{ count($alquileres) }} alquiler(es)</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏰</div>
            <div class="stat-label">Se entregan en 7 días o menos</div>
            <div class="stat-value {{ $urgentes->isNotEmpty() ? 'amount-negative' : '' }}">{{ $urgentes->count() }}</div>
            <div class="stat-sub">Q {{ number_format($urgentes->sum('saldo'), 2) }} pendientes</div>
        </div>
    </div>

    <div class="page-card p-3 p-md-4">
        @if(count($alquileres))
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Entrega</th>
                            <th>Alquiler</th>
                            <th>Togas</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Pagado</th>
                            <th class="text-end">Saldo</th>
                            <th>Límite de pago</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alquileres as $a)
                            <tr class="{{ $a['urgente'] ? 'table-warning' : '' }}">
                                <td class="text-nowrap">
                                    {{ $a['fecha']?->format('d/m/Y') ?? '—' }}
                                    @if($a['dias_para_entrega'] !== null && in_array($a['estado'], \App\Models\Alquiler::ESTADOS_ANTES_DE_ENTREGA, true))
                                        <div class="small {{ $a['dias_para_entrega'] < 0 ? 'text-danger' : 'text-muted' }}">
                                            @if($a['dias_para_entrega'] < 0)
                                                hace {{ abs($a['dias_para_entrega']) }} día(s)
                                            @elseif($a['dias_para_entrega'] === 0)
                                                hoy
                                            @else
                                                en {{ $a['dias_para_entrega'] }} día(s)
                                            @endif
                                        </div>
                                    @else
                                        <div class="small text-muted">{{ str_replace('_', ' ', $a['estado']) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('alquileres.show', $a['id']) }}">{{ $a['recibo'] }}</a>
                                    <div class="small">{{ $a['cliente'] }} @if($a['telefono']) · {{ $a['telefono'] }} @endif</div>
                                </td>
                                <td class="small">{{ $a['togas'] }} — {{ $a['tallas'] ?: '—' }}</td>
                                <td class="text-end">Q {{ number_format($a['total'], 2) }}</td>
                                <td class="text-end">Q {{ number_format($a['pagado'], 2) }}</td>
                                <td class="text-end fw-bold text-danger">Q {{ number_format($a['saldo'], 2) }}</td>
                                <td class="small {{ $a['limite_vencido'] ? 'text-danger fw-semibold' : '' }}">
                                    {{ $a['fecha_limite_pago']?->format('d/m/Y') ?? '—' }}
                                    @if($a['limite_vencido']) (vencido) @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('pagos.create', $a['id']) }}" class="btn btn-sm btn-outline-primary rounded-pill">Cobrar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-light border rounded-4 mb-0">No hay saldos pendientes. 🎉</div>
        @endif
    </div>
@endsection
