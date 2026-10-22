{{--
    Un alquiler dentro de la agenda.
    Variables: $item (ActividadesService::resumen), $momento ('entrega'|'devolucion')
--}}
<div class="agenda-item d-flex flex-wrap justify-content-between gap-2 {{ $item['hecho'] ? 'hecho' : '' }} {{ $item['atrasado'] ? 'atrasado' : '' }}">
    <div class="me-auto">
        <div class="fw-semibold">
            @if($item['hora'])
                <span class="badge bg-light text-dark border me-1">
                    {{ $item['hora'] }}@if($item['hora_fin'])–{{ $item['hora_fin'] }}@endif
                </span>
            @endif
            <a href="{{ route('alquileres.show', $item['id']) }}" class="text-decoration-none">{{ $item['recibo'] }}</a>
            · {{ $item['cliente'] }}
            @if($item['telefono'])
                <span class="text-muted small">· {{ $item['telefono'] }}</span>
            @endif
        </div>

        @if($item['institucion'])
            <div class="small text-muted">{{ $item['institucion'] }}</div>
        @endif

        <div class="tallas-texto mt-1">
            🎓 {{ $item['togas'] }} toga(s){{ $item['tallas'] ? ' — ' . $item['tallas'] : '' }}
        </div>
    </div>

    <div class="text-end small">
        @if($item['hecho'])
            <span class="badge bg-success-subtle text-success border">
                {{ $momento === 'entrega' ? 'Entregado' : 'Devuelto' }} ✓
            </span>
        @elseif($item['atrasado'])
            <span class="badge bg-danger-subtle text-danger border">Atrasado</span>
        @elseif($momento === 'devolucion' && $item['estado'] === 'ENTREGADO')
            <span class="badge bg-warning-subtle text-warning-emphasis border">Por recibir</span>
        @else
            <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $item['estado']) }}</span>
        @endif

        @if($item['saldo'] > 0)
            <div class="mt-1 text-danger fw-semibold">Saldo Q {{ number_format($item['saldo'], 2) }}</div>
        @elseif($momento === 'entrega' && !$item['hecho'])
            <div class="mt-1 text-success">Pagado</div>
        @endif
    </div>
</div>
