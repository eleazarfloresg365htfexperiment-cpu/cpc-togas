{{--
    Daños y extravíos del alquiler.
    Se muestra si el alquiler está DEVUELTO o si ya tiene registros.
    Variables: $alquiler (con danos.producto), $productosDanos (de AlquilerService::cantidadesAlquiladasPorProducto)
--}}

@php
    $danos = $alquiler->danos ?? collect();
    $productosDanos = $productosDanos ?? [];
    $puedeRegistrar = $alquiler->puedeRegistrarDanos();

    // Cuánto falta por reportar de cada producto (alquilado - ya reportado).
    $disponibleReportar = collect($productosDanos)->map(function ($item, $productoId) use ($danos) {
        return max(0, $item['cantidad'] - (int) $danos->where('producto_id', $productoId)->sum('cantidad'));
    });
@endphp

@if($puedeRegistrar || $danos->isNotEmpty())
    <div class="row g-4 mt-1" id="danos">
        <div class="col-12">
            <div class="detalle-card p-0 overflow-hidden">

                <div class="p-4 border-bottom bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold">🧯 Daños y extravíos</h5>
                        <small class="text-muted">
                            El monto se suma al saldo pendiente y se cobra con "Registrar pago". Lo extraviado se da de baja del inventario.
                        </small>
                    </div>

                    @if($danos->isNotEmpty())
                        <span class="badge bg-danger-subtle text-danger border rounded-pill px-3 py-2">
                            Total: Q {{ number_format((float) $danos->sum('monto'), 2) }}
                        </span>
                    @endif
                </div>

                <div class="p-4">

                    @if($danos->isNotEmpty())
                        <div class="table-responsive mb-4">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-end">Monto</th>
                                        <th>Descripción</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($danos as $dano)
                                        <tr>
                                            <td class="text-nowrap">{{ optional($dano->created_at)->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($dano->tipo === 'EXTRAVIO')
                                                    <span class="badge bg-danger-subtle text-danger border">Extravío</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border">Daño</span>
                                                @endif
                                            </td>
                                            <td>{{ $dano->producto->nombre ?? 'Producto' }}</td>
                                            <td class="text-center">{{ $dano->cantidad }}</td>
                                            <td class="text-end fw-bold">Q {{ number_format((float) $dano->monto, 2) }}</td>
                                            <td>
                                                {{ $dano->descripcion ?: '—' }}
                                                @if($dano->responsable)
                                                    <div class="small text-muted">Registró: {{ $dano->responsable }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <form action="{{ route('alquileres.danos.destroy', [$alquiler->id, $dano->id]) }}"
                                                      method="POST"
                                                      class="d-inline form-eliminar-dano">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="motivo_eliminacion">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($puedeRegistrar)
                        <div class="alert alert-light border rounded-4">
                            No hay daños ni extravíos registrados. Si las togas se devolvieron completas y en buen estado, no hace falta registrar nada.
                        </div>
                    @endif

                    @if($puedeRegistrar)
                        <form action="{{ route('alquileres.danos.store', $alquiler->id) }}"
                              method="POST"
                              id="formRegistrarDano"
                              class="border rounded-4 p-4 bg-light">
                            @csrf

                            <div class="fw-bold mb-3">➕ Registrar daño o extravío</div>

                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold" for="dano_producto_id">Producto</label>
                                    <select name="producto_id" id="dano_producto_id" class="form-select" required>
                                        <option value="">Selecciona…</option>
                                        @foreach($productosDanos as $productoId => $item)
                                            <option value="{{ $productoId }}"
                                                    data-max="{{ $disponibleReportar[$productoId] ?? 0 }}"
                                                    @disabled(($disponibleReportar[$productoId] ?? 0) <= 0)
                                                    @selected(old('producto_id') == $productoId)>
                                                {{ $item['etiqueta'] }} — alquilado: {{ $item['cantidad'] }}
                                                @if(($disponibleReportar[$productoId] ?? 0) <= 0)
                                                    (ya reportado completo)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold" for="dano_tipo">Tipo</label>
                                    <select name="tipo" id="dano_tipo" class="form-select" required>
                                        <option value="DANO" @selected(old('tipo') === 'DANO')>Daño</option>
                                        <option value="EXTRAVIO" @selected(old('tipo') === 'EXTRAVIO')>Extravío</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold" for="dano_cantidad">Cantidad</label>
                                    <input type="number" name="cantidad" id="dano_cantidad" class="form-control"
                                           min="1" value="{{ old('cantidad', 1) }}" required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold" for="dano_monto">Monto (Q)</label>
                                    <input type="number" name="monto" id="dano_monto" class="form-control"
                                           min="0" step="0.01" value="{{ old('monto', '0.00') }}" required>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label fw-semibold" for="dano_descripcion">Descripción</label>
                                    <textarea name="descripcion" id="dano_descripcion" class="form-control" rows="2"
                                              maxlength="1000"
                                              placeholder="Ej. Manga descosida; collarín manchado; birrete no devuelto.">{{ old('descripcion') }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" for="dano_responsable">Quién registra</label>
                                    <input type="text" name="responsable" id="dano_responsable" class="form-control"
                                           maxlength="255" value="{{ old('responsable', auth()->user()?->nombre_corto) }}" placeholder="Opcional">
                                </div>

                                <div class="col-12">
                                    <div class="form-text" id="dano_ayuda_tipo">
                                        Daño: se cobra la reparación; el inventario no cambia.
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-danger rounded-pill">
                                        Registrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif

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

            const producto = document.getElementById('dano_producto_id');
            const cantidad = document.getElementById('dano_cantidad');
            const tipo = document.getElementById('dano_tipo');
            const ayuda = document.getElementById('dano_ayuda_tipo');
            const formDano = document.getElementById('formRegistrarDano');

            function ajustarMaximo() {
                if (!producto || !cantidad) {
                    return;
                }

                const opcion = producto.options[producto.selectedIndex];
                const max = Number(opcion?.dataset?.max || 0);

                if (max > 0) {
                    cantidad.max = max;

                    if (Number(cantidad.value) > max) {
                        cantidad.value = max;
                    }
                } else {
                    cantidad.removeAttribute('max');
                }
            }

            function ajustarAyuda() {
                if (!tipo || !ayuda) {
                    return;
                }

                ayuda.textContent = tipo.value === 'EXTRAVIO'
                    ? 'Extravío: se cobra la reposición y la cantidad se da de baja del inventario.'
                    : 'Daño: se cobra la reparación; el inventario no cambia.';
            }

            producto?.addEventListener('change', ajustarMaximo);
            tipo?.addEventListener('change', ajustarAyuda);
            ajustarMaximo();
            ajustarAyuda();

            formDano?.addEventListener('submit', function (event) {
                event.preventDefault();

                const opcion = producto.options[producto.selectedIndex];
                const monto = Number(document.getElementById('dano_monto').value || 0);
                const esExtravio = tipo.value === 'EXTRAVIO';

                Swal.fire({
                    title: esExtravio ? '¿Registrar extravío?' : '¿Registrar daño?',
                    html: `
                        <div class="text-start">
                            <p class="mb-1"><strong>Producto:</strong> ${escaparHtml(opcion ? opcion.text.split('—')[0].trim() : '')}</p>
                            <p class="mb-1"><strong>Cantidad:</strong> ${escaparHtml(cantidad.value)}</p>
                            <p class="mb-1"><strong>Monto a cobrar:</strong> Q ${monto.toFixed(2)}</p>
                            ${esExtravio ? '<p class="mb-0 text-danger">Se dará de baja del inventario.</p>' : ''}
                        </div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, registrar',
                    cancelButtonText: 'Volver',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        formDano.submit();
                    }
                });
            });

            document.querySelectorAll('.form-eliminar-dano').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        title: '¿Eliminar este registro?',
                        html: `<div class="text-start small mb-2">
                                   Se quitará el cargo del saldo pendiente. Si era un extravío, la cantidad vuelve al inventario.
                               </div>
                               <textarea id="swalMotivoDano" class="swal2-textarea m-0 w-100" rows="2"
                                         placeholder="Motivo (obligatorio)"></textarea>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Volver',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true,
                        focusConfirm: false,
                        preConfirm: () => {
                            const motivo = document.getElementById('swalMotivoDano').value.trim();

                            if (!motivo) {
                                Swal.showValidationMessage('Escribe el motivo.');
                                return false;
                            }

                            return motivo;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.querySelector('[name="motivo_eliminacion"]').value = result.value;
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endif
