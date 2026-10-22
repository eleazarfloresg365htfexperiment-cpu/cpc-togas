@extends('layouts.app')

@section('title', 'Actividades · Agenda')
@section('page_title', '📋 Actividades')
@section('page_subtitle', 'Qué hay que entregar y recibir, qué está apartado y qué falta cobrar')

@section('content')
    @include('actividades.partials.pestanas')

    {{-- Rango de la agenda --}}
    <div class="page-card p-3 p-md-4 mb-4">
        <form method="GET" action="{{ route('actividades.agenda') }}" class="row g-3 align-items-end" id="formRango">
            <div class="col-md-3">
                <label for="periodo" class="form-label fw-semibold">Periodo</label>
                <select name="periodo" id="periodo" class="form-select">
                    @foreach(\App\Http\Controllers\Web\ActividadController::PERIODOS as $valor => $texto)
                        <option value="{{ $valor }}" @selected($periodo === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 rango-personalizado {{ $periodo === 'personalizado' ? '' : 'd-none' }}">
                <label for="desde" class="form-label fw-semibold">Desde</label>
                <input type="date" name="desde" id="desde" class="form-control" value="{{ $desde->toDateString() }}">
            </div>

            <div class="col-md-3 rango-personalizado {{ $periodo === 'personalizado' ? '' : 'd-none' }}">
                <label for="hasta" class="form-label fw-semibold">Hasta</label>
                <input type="date" name="hasta" id="hasta" class="form-control" value="{{ $hasta->toDateString() }}">
                <small class="text-muted">Máximo dos meses.</small>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill flex-fill">Ver</button>
                <a href="{{ route('actividades.agenda.pdf', request()->query()) }}" target="_blank"
                   class="btn btn-outline-secondary rounded-pill flex-fill">📄 PDF</a>
            </div>
        </form>
    </div>

    {{-- Resumen --}}
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-label">Periodo</div>
            <div class="stat-value" style="font-size: 1.1rem;">
                {{ $desde->translatedFormat('j M') }} – {{ $hasta->translatedFormat('j M Y') }}
            </div>
            <div class="stat-sub">{{ \App\Http\Controllers\Web\ActividadController::PERIODOS[$periodo] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🚚</div>
            <div class="stat-label">Entregas</div>
            <div class="stat-value">{{ $agenda['totales']['entregas'] }}</div>
            <div class="stat-sub">{{ $agenda['totales']['togas_entrega'] }} toga(s)</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔁</div>
            <div class="stat-label">Devoluciones</div>
            <div class="stat-value">{{ $agenda['totales']['devoluciones'] }}</div>
            <div class="stat-sub">{{ $agenda['totales']['togas_devolucion'] }} toga(s)</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-label">Atrasados</div>
            <div class="stat-value {{ count($agenda['atrasados']['entregas']) + count($agenda['atrasados']['devoluciones']) > 0 ? 'amount-negative' : '' }}">
                {{ count($agenda['atrasados']['entregas']) + count($agenda['atrasados']['devoluciones']) }}
            </div>
            <div class="stat-sub">Entregas o devoluciones vencidas</div>
        </div>
    </div>

    {{-- Atrasados (siempre, sin importar el periodo) --}}
    @if(count($agenda['atrasados']['entregas']) || count($agenda['atrasados']['devoluciones']))
        <div class="page-card p-3 p-md-4 mb-4 border border-danger-subtle">
            <div class="section-title mb-3 text-danger">⚠️ Atrasados</div>

            @if(count($agenda['atrasados']['devoluciones']))
                <div class="fw-semibold mb-2">No han devuelto</div>
                @foreach($agenda['atrasados']['devoluciones'] as $item)
                    <div class="small text-muted mt-2">Debía volver el {{ $item['fecha']?->translatedFormat('l j \d\e F') }}</div>
                    @include('actividades.partials.item-agenda', ['item' => $item, 'momento' => 'devolucion'])
                @endforeach
            @endif

            @if(count($agenda['atrasados']['entregas']))
                <div class="fw-semibold mb-2 mt-3">Entregas que no se marcaron como entregadas</div>
                @foreach($agenda['atrasados']['entregas'] as $item)
                    <div class="small text-muted mt-2">Se debía entregar el {{ $item['fecha']?->translatedFormat('l j \d\e F') }}</div>
                    @include('actividades.partials.item-agenda', ['item' => $item, 'momento' => 'entrega'])
                @endforeach
            @endif
        </div>
    @endif

    {{-- Días --}}
    <div class="page-card p-3 p-md-4 mb-4">
        <div class="section-title mb-3">📅 Día por día</div>

        @php $diasConAlgo = collect($agenda['dias'])->filter(fn ($d) => count($d['entregas']) || count($d['devoluciones'])); @endphp

        @forelse($diasConAlgo as $dia)
            <div class="agenda-dia ps-3 mb-4 {{ $dia['fecha']->isSameDay($hoy) ? 'es-hoy' : '' }}">
                <div class="fw-bold mb-2 text-capitalize">
                    {{ $dia['fecha']->translatedFormat('l j \d\e F') }}
                    @if($dia['fecha']->isSameDay($hoy))
                        <span class="badge bg-primary ms-1">Hoy</span>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="small fw-semibold text-muted mb-2">🚚 Entregas ({{ count($dia['entregas']) }})</div>
                        @forelse($dia['entregas'] as $item)
                            @include('actividades.partials.item-agenda', ['item' => $item, 'momento' => 'entrega'])
                        @empty
                            <div class="small text-muted">—</div>
                        @endforelse
                    </div>

                    <div class="col-lg-6">
                        <div class="small fw-semibold text-muted mb-2">🔁 Devoluciones ({{ count($dia['devoluciones']) }})</div>
                        @forelse($dia['devoluciones'] as $item)
                            @include('actividades.partials.item-agenda', ['item' => $item, 'momento' => 'devolucion'])
                        @empty
                            <div class="small text-muted">—</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-light border rounded-4 mb-0">
                No hay entregas ni devoluciones en este periodo.
            </div>
        @endforelse
    </div>

    {{-- Fabricaciones pendientes --}}
    @if(count($agenda['fabricaciones']))
        <div class="page-card p-3 p-md-4">
            <div class="section-title mb-3">🧵 Fabricaciones pendientes</div>

            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Entrega</th>
                            <th>Alquiler</th>
                            <th>Producto</th>
                            <th class="text-center">Faltan</th>
                            <th>Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agenda['fabricaciones'] as $fab)
                            <tr>
                                <td class="text-nowrap">{{ $fab['fecha_entrega']?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('alquileres.show', $fab['alquiler_id']) }}#fabricacion">{{ $fab['recibo'] }}</a>
                                    <div class="small text-muted">{{ $fab['cliente'] }}</div>
                                </td>
                                <td>{{ $fab['producto'] }}</td>
                                <td class="text-center fw-bold">{{ $fab['pendiente'] }}</td>
                                <td>{{ $fab['responsable'] ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <script>
        document.getElementById('periodo')?.addEventListener('change', function () {
            const personalizado = this.value === 'personalizado';
            document.querySelectorAll('.rango-personalizado').forEach(function (el) {
                el.classList.toggle('d-none', !personalizado);
            });

            if (!personalizado) {
                document.getElementById('formRango').submit();
            }
        });
    </script>
@endsection
