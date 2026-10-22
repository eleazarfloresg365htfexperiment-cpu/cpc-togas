@extends('layouts.app')

@section('title', 'Actividades · Togas por talla')
@section('page_title', '📋 Actividades')
@section('page_subtitle', 'Qué hay, qué está fuera, qué está apartado y qué queda libre')

@section('content')
    @include('actividades.partials.pestanas')

    <div class="page-card p-3 p-md-4 mb-4">
        <form method="GET" action="{{ route('actividades.inventario') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="fecha" class="form-label fw-semibold">¿Cuántas quedan libres para el día…?</label>
                <input type="date" name="fecha" id="fecha" class="form-control" value="{{ $fecha->toDateString() }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill">Consultar</button>
                @unless($fecha->isSameDay($hoy))
                    <a href="{{ route('actividades.inventario') }}" class="btn btn-outline-secondary rounded-pill">Hoy</a>
                @endunless
            </div>
            <div class="col-md-4 small text-muted">
                <strong>Libres</strong> = total + en fabricación − lo ocupado ese día
                (apartado o entregado para esa fecha). Lo que no ha regresado a tiempo cuenta como ocupado.
            </div>
        </form>
    </div>

    @php $porTipo = collect($tallas)->groupBy('tipo'); @endphp

    @forelse(['ESTANDAR' => '🎓 Togas estándar', 'UNIVERSITARIA' => '🎓 Togas universitarias'] as $tipo => $titulo)
        @continue(!$porTipo->has($tipo))

        <div class="page-card p-3 p-md-4 mb-4">
            <div class="section-title mb-3">{{ $titulo }}</div>

            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Talla</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">En bodega</th>
                            <th class="text-center">Fuera</th>
                            <th class="text-center">Apartadas</th>
                            <th class="text-center">En fabricación</th>
                            <th class="text-center">Libres el {{ $fecha->format('d/m') }}</th>
                            <th>Próxima devolución</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $suma = ['total' => 0, 'bodega' => 0, 'fuera' => 0, 'apartadas' => 0, 'en_fabricacion' => 0, 'libres_en_fecha' => 0]; @endphp

                        @foreach($porTipo[$tipo] as $fila)
                            @php foreach ($suma as $k => $v) { $suma[$k] += $fila[$k]; } @endphp
                            <tr>
                                <td><strong>{{ $fila['talla'] }}</strong></td>
                                <td class="text-center">{{ $fila['total'] }}</td>
                                <td class="text-center">{{ $fila['bodega'] }}</td>
                                <td class="text-center">{{ $fila['fuera'] }}</td>
                                <td class="text-center">
                                    @if($fila['apartadas'] > 0)
                                        <a href="#" class="fw-semibold" data-bs-toggle="collapse"
                                           data-bs-target="#apartadas-{{ $tipo }}-{{ \Illuminate\Support\Str::slug($fila['talla']) }}">
                                            {{ $fila['apartadas'] }} ▾
                                        </a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="text-center">{{ $fila['en_fabricacion'] ?: '—' }}</td>
                                <td class="text-center">
                                    @php $libres = $fila['libres_en_fecha']; @endphp
                                    <span class="{{ $libres < 0 ? 'libres-negativo' : ($libres <= 2 ? 'libres-justo' : 'libres-ok') }}">
                                        {{ $libres < 0 ? '⚠ ' : '' }}{{ $libres }}
                                    </span>
                                </td>
                                <td class="small">
                                    @if($fila['proxima_devolucion'])
                                        {{ $fila['proxima_devolucion']['cantidad'] }} el
                                        {{ $fila['proxima_devolucion']['fecha']->translatedFormat('D j M') }}
                                    @else
                                        —
                                    @endif
                                    @if($fila['devoluciones_vencidas'] > 0)
                                        <div class="text-danger">{{ $fila['devoluciones_vencidas'] }} atrasada(s)</div>
                                    @endif
                                </td>
                            </tr>

                            @if($fila['apartadas'] > 0)
                                <tr class="collapse" id="apartadas-{{ $tipo }}-{{ \Illuminate\Support\Str::slug($fila['talla']) }}">
                                    <td colspan="8" class="bg-light">
                                        <div class="small fw-semibold mb-1">Apartadas talla {{ $fila['talla'] }}</div>
                                        <ul class="small mb-0">
                                            @foreach($fila['detalle_apartadas'] as $ap)
                                                <li>
                                                    <strong>{{ $ap['cantidad'] }}</strong> —
                                                    <a href="{{ route('alquileres.show', $ap['alquiler_id']) }}">{{ $ap['recibo'] }}</a>
                                                    · {{ $ap['cliente'] }}
                                                    · entrega {{ $ap['fecha_entrega']?->format('d/m/Y') ?? 'sin fecha' }}
                                                    · {{ str_replace('_', ' ', $ap['estado']) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td class="text-center">{{ $suma['total'] }}</td>
                            <td class="text-center">{{ $suma['bodega'] }}</td>
                            <td class="text-center">{{ $suma['fuera'] }}</td>
                            <td class="text-center">{{ $suma['apartadas'] }}</td>
                            <td class="text-center">{{ $suma['en_fabricacion'] }}</td>
                            <td class="text-center">{{ $suma['libres_en_fecha'] }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
    @endforelse

    @if(empty($tallas))
        <div class="alert alert-light border rounded-4">No hay togas activas registradas.</div>
    @endif

    {{-- Accesorios --}}
    <div class="page-card p-3 p-md-4">
        <div class="section-title mb-1">🧩 Accesorios</div>
        <p class="text-muted small mb-3">Libres hoy = en bodega − apartados (aún sin entregar).</p>

        @php
            $titulosAccesorios = ['COLLARIN' => 'Collarines', 'CAPA' => 'Capas', 'BIRRETE' => 'Birretes', 'BORLA' => 'Borlas'];
        @endphp

        <div class="row g-4">
            @foreach($titulosAccesorios as $tipo => $titulo)
                @continue(empty($accesorios[$tipo]))

                <div class="col-lg-6">
                    <div class="fw-semibold mb-2">{{ $titulo }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Bodega</th>
                                    <th class="text-center">Fuera</th>
                                    <th class="text-center">Apartados</th>
                                    <th class="text-center">Libres</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accesorios[$tipo] as $acc)
                                    <tr>
                                        <td>{{ $acc['nombre'] }}</td>
                                        <td class="text-center">{{ $acc['bodega'] }}</td>
                                        <td class="text-center">{{ $acc['fuera'] }}</td>
                                        <td class="text-center">{{ $acc['apartados'] }}</td>
                                        <td class="text-center">
                                            <span class="{{ $acc['libres'] < 0 ? 'libres-negativo' : ($acc['libres'] <= 2 ? 'libres-justo' : 'libres-ok') }}">
                                                {{ $acc['libres'] < 0 ? '⚠ ' : '' }}{{ $acc['libres'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
