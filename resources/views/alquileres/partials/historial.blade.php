{{--
    Historial de cambios del alquiler (ediciones, cancelación, daños).
    Variables: $alquiler (con historial)
--}}

@php
    $historial = $alquiler->historial ?? collect();

    $etiquetaAccion = fn ($accion) => match ($accion) {
        'EDICION' => ['Edición', 'bg-primary-subtle text-primary'],
        'CANCELACION' => ['Cancelación', 'bg-danger-subtle text-danger'],
        'DANO' => ['Daño / extravío', 'bg-warning-subtle text-warning'],
        'DANO_ELIMINADO' => ['Registro eliminado', 'bg-secondary-subtle text-secondary'],
        'FABRICACION' => ['Fabricación', 'bg-info-subtle text-info'],
        default => [$accion, 'bg-light text-dark'],
    };
@endphp

@if($historial->isNotEmpty())
    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="detalle-card p-0 overflow-hidden">

                <div class="p-4 border-bottom bg-white">
                    <h5 class="mb-0 fw-bold">🕘 Historial de cambios</h5>
                    <small class="text-muted">
                        Ediciones, cancelación y daños registrados, con el motivo de cada uno.
                    </small>
                </div>

                <div class="p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                    <th>Campo</th>
                                    <th>Antes</th>
                                    <th>Después</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historial as $registro)
                                    @php
                                        [$textoAccion, $claseAccion] = $etiquetaAccion($registro->accion);
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap small">
                                            {{ optional($registro->created_at)->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $claseAccion }} border">{{ $textoAccion }}</span>
                                        </td>
                                        <td>{{ $registro->etiqueta_campo ?? '—' }}</td>
                                        <td class="text-muted">{{ $registro->valor_anterior ?? '—' }}</td>
                                        <td class="fw-semibold">{{ $registro->valor_nuevo ?? '—' }}</td>
                                        <td>
                                            {{ $registro->motivo ?? '—' }}
                                            @if($registro->responsable)
                                                <div class="small text-muted">Por: {{ $registro->responsable }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif
