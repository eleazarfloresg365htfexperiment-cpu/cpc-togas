{{--
    Fabricación pendiente del alquiler.
    Se muestra si el alquiler tiene registros de fabricación.
    Variables: $alquiler (con fabricaciones.producto)
--}}

@php
    $fabricaciones = $alquiler->fabricaciones ?? collect();
    $puedeRegistrarFabricacion = $alquiler->estado === 'EN_FABRICACION';

    $pendienteTotal = (int) $fabricaciones
        ->filter(fn ($f) => $f->estado !== 'CANCELADO')
        ->sum('cantidad_pendiente');

    $badgeFabricacion = fn ($estado) => match ($estado) {
        'COMPLETADO' => ['Completado', 'bg-success-subtle text-success'],
        'EN_PROGRESO' => ['En progreso', 'bg-warning-subtle text-warning'],
        'CANCELADO' => ['Cancelado', 'bg-secondary-subtle text-secondary'],
        default => ['Autorizado', 'bg-info-subtle text-info'],
    };
@endphp

@if($fabricaciones->isNotEmpty())
    <div class="row g-4 mt-1" id="fabricacion">
        <div class="col-12">
            <div class="detalle-card p-0 overflow-hidden">

                <div class="p-4 border-bottom bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold">🏭 Fabricación</h5>
                        <small class="text-muted">
                            Lo que faltaba de stock al crear el alquiler. Al registrar lo fabricado, entra al inventario.
                            Cuando no quede nada pendiente, el alquiler pasa a <strong>LISTO PARA ENTREGA</strong>.
                        </small>
                    </div>

                    @if($alquiler->estado === 'EN_FABRICACION')
                        <span class="badge bg-warning-subtle text-warning border rounded-pill px-3 py-2">
                            Pendiente: {{ $pendienteTotal }} unidad(es)
                        </span>
                    @elseif($alquiler->estado === 'LISTO_PARA_ENTREGA')
                        <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                            Todo fabricado · listo para entregar
                        </span>
                    @endif
                </div>

                <div class="p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">En el alquiler</th>
                                    <th class="text-center">Pendiente</th>
                                    <th>Estado</th>
                                    <th>Autorización</th>
                                    @if($puedeRegistrarFabricacion)
                                        <th style="min-width: 260px;">Registrar fabricado</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fabricaciones as $fabricacion)
                                    @php
                                        [$textoEstado, $claseEstado] = $badgeFabricacion($fabricacion->estado);
                                        $pendiente = (int) $fabricacion->cantidad_pendiente;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $fabricacion->producto->nombre ?? 'Producto' }}</div>
                                            <div class="small text-muted">{{ $fabricacion->producto->tipo_producto ?? '' }}</div>
                                        </td>
                                        <td class="text-center">{{ $fabricacion->cantidad_solicitada }}</td>
                                        <td class="text-center fw-bold {{ $pendiente > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $pendiente }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $claseEstado }} border">{{ $textoEstado }}</span>
                                        </td>
                                        <td class="small">
                                            @if($fabricacion->responsable)
                                                <div>Responsable: {{ $fabricacion->responsable }}</div>
                                            @endif
                                            @if($fabricacion->motivo)
                                                <div class="text-muted">Motivo: {{ $fabricacion->motivo }}</div>
                                            @endif
                                            @if($fabricacion->fecha)
                                                <div class="text-muted">{{ $fabricacion->fecha->format('d/m/Y') }}</div>
                                            @endif
                                            @if($fabricacion->observaciones)
                                                <div class="text-muted">{{ $fabricacion->observaciones }}</div>
                                            @endif
                                        </td>
                                        @if($puedeRegistrarFabricacion)
                                            <td>
                                                @if($pendiente > 0 && $fabricacion->estado !== 'CANCELADO')
                                                    <form action="{{ route('alquileres.fabricaciones.completar', [$alquiler->id, $fabricacion->id]) }}"
                                                          method="POST"
                                                          class="form-completar-fabricacion"
                                                          data-producto="{{ $fabricacion->producto->nombre ?? 'Producto' }}"
                                                          data-pendiente="{{ $pendiente }}">
                                                        @csrf
                                                        <div class="input-group input-group-sm mb-1">
                                                            <input type="number"
                                                                   name="cantidad_completada"
                                                                   class="form-control"
                                                                   min="1"
                                                                   max="{{ $pendiente }}"
                                                                   value="{{ $pendiente }}"
                                                                   required>
                                                            <button type="submit" class="btn btn-success">
                                                                Registrar
                                                            </button>
                                                        </div>
                                                        <input type="text"
                                                               name="observaciones"
                                                               class="form-control form-control-sm mb-1"
                                                               maxlength="500"
                                                               placeholder="Observación (opcional)">
                                                        <input type="text"
                                                               name="responsable"
                                                               class="form-control form-control-sm"
                                                               maxlength="255"
                                                               placeholder="Quién registra (opcional)">
                                                    </form>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Escapa texto guardado por usuarios (p. ej. nombres de producto)
            // antes de meterlo en el HTML de la ventana de confirmación.
            const escaparHtml = (texto) => String(texto ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));

            document.querySelectorAll('.form-completar-fabricacion').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    const cantidad = Number(form.querySelector('[name="cantidad_completada"]').value || 0);
                    const pendiente = Number(form.dataset.pendiente || 0);
                    const completa = cantidad >= pendiente;

                    Swal.fire({
                        title: '¿Registrar fabricación?',
                        html: `<div class="text-start">
                                   <p class="mb-1"><strong>Producto:</strong> ${escaparHtml(form.dataset.producto)}</p>
                                   <p class="mb-1"><strong>Unidades fabricadas:</strong> ${cantidad} de ${pendiente} pendiente(s)</p>
                                   <p class="mb-0 text-muted">Se sumarán al inventario disponible.${completa ? '' : ' Seguirá quedando pendiente el resto.'}</p>
                               </div>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, registrar',
                        cancelButtonText: 'Volver',
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endif
