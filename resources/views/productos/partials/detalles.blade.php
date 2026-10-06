{{--
    Detalles específicos según el tipo de producto (toga, capa, birrete,
    collarín y borla). Se usa al crear y al editar.

    - Al crear se dibujan los cinco bloques y public/js/productos-formulario.js
      muestra solo el del tipo elegido.
    - Al editar solo se dibuja el bloque del tipo del producto (siempre visible).

    Variable opcional: $producto (si viene, se está editando).
--}}

@php
    $producto = $producto ?? null;
    $editando = $producto !== null;
    $tipoProducto = $editando ? $producto->tipo_producto : null;

    $toga = $producto?->toga;
    $capa = $producto?->capa;
    $birrete = $producto?->birrete;
    $collarin = $producto?->collarin;
    $borla = $producto?->borla;

    // En crear todos los bloques existen (ocultos); en editar solo el del tipo.
    $dibujar = fn (string $tipo) => !$editando || $tipoProducto === $tipo;
    $oculto = $editando ? '' : 'd-none';
    $col = $editando ? 'col-md-6' : 'col-md-4';

    $carreras = config('alquiler.carreras_capa', []);
    $coloresCollarinPorTipo = config('alquiler.colores_collarin_por_tipo', []);
    $coloresBorlaPorTipo = config('alquiler.colores_borla_por_tipo', []);

    // Colores de capa: los de las carreras (Celeste, Rojo, Verde, ...).
    $coloresCapa = array_keys($coloresBorlaPorTipo['UNIVERSITARIA'] ?? []);
@endphp

{{-- ================= TOGA ================= --}}
@if($dibujar('TOGA'))
    <div id="campos-toga" class="tipo-extra {{ $oculto }}">
        <div class="row g-3">

            <div class="{{ $col }}">
                <label for="talla_toga" class="form-label fw-semibold">Talla de toga</label>
                <input type="text"
                       name="talla_toga"
                       id="talla_toga"
                       class="form-control"
                       value="{{ old('talla_toga', $toga->talla ?? '') }}"
                       placeholder="Ej. S, M, L, XL"
                       @required($editando)>
            </div>

            <div class="{{ $col }}">
                <label for="tipo_toga" class="form-label fw-semibold">Tipo de toga</label>
                <select name="tipo_toga" id="tipo_toga" class="form-select" @required($editando)>
                    @php $tipoToga = old('tipo_toga', $toga->tipo_toga ?? 'ESTANDAR'); @endphp
                    <option value="ESTANDAR" @selected($tipoToga === 'ESTANDAR')>Estándar</option>
                    <option value="UNIVERSITARIA" @selected($tipoToga === 'UNIVERSITARIA')>Universitaria</option>
                </select>
            </div>

            <div class="{{ $col }}">
                <label for="color_toga" class="form-label fw-semibold">Color de toga</label>
                <input type="text"
                       name="color_toga"
                       id="color_toga"
                       class="form-control"
                       value="{{ old('color_toga', $toga->color ?? '') }}"
                       placeholder="Ej. Negro, azul marino"
                       @required($editando)>
            </div>

            <div class="{{ $col }}">
                <label for="observaciones_toga" class="form-label fw-semibold">Observaciones</label>
                <textarea name="observaciones_toga"
                          id="observaciones_toga"
                          class="form-control"
                          rows="2"
                          placeholder="Ej. Modelo, tela, zipper...">{{ old('observaciones_toga', $toga->observaciones ?? '') }}</textarea>
            </div>

        </div>
    </div>
@endif

{{-- ================= CAPA ================= --}}
@if($dibujar('CAPA'))
    @php
        $colorCapaActual = (string) old('color_capa', $capa->color ?? '');
        $carreraActual = old('carrera_capa', $capa->carrera ?? '');
    @endphp

    <div id="campos-capa" class="tipo-extra {{ $oculto }}">
        <div class="row g-3">

            <div class="{{ $col }}">
                <label for="talla_capa" class="form-label fw-semibold">Talla de capa</label>
                <input type="text"
                       name="talla_capa"
                       id="talla_capa"
                       class="form-control"
                       value="{{ old('talla_capa', $capa->talla ?? '') }}"
                       placeholder="Ej. S, M, L, XL">
            </div>

            <div class="{{ $col }}">
                <label for="carrera_capa" class="form-label fw-semibold">Carrera</label>
                <select name="carrera_capa" id="carrera_capa" class="form-select">
                    <option value="">Seleccione...</option>
                    @foreach($carreras as $valor => $datos)
                        <option value="{{ $valor }}" @selected($carreraActual === $valor)>
                            {{ $datos['nombre'] }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Al elegirla se completan el color y el código.</small>
            </div>

            <div class="{{ $col }}">
                <label for="color_capa" class="form-label fw-semibold">Color de capa</label>
                <select name="color_capa" id="color_capa" class="form-select">
                    <option value="">Seleccione...</option>
                    @foreach($coloresCapa as $color)
                        <option value="{{ $color }}" @selected($colorCapaActual === $color)>{{ $color }}</option>
                    @endforeach

                    {{-- Color antiguo que ya no está en la lista: se conserva
                         para que guardar no lo borre en silencio. --}}
                    @if($colorCapaActual !== '' && !in_array($colorCapaActual, $coloresCapa, true))
                        <option value="{{ $colorCapaActual }}" selected>{{ $colorCapaActual }}</option>
                    @endif
                </select>
            </div>

            <div class="{{ $col }}">
                <label for="codigo_color_capa" class="form-label fw-semibold">Código de color</label>
                <input type="text"
                       name="codigo_color_capa"
                       id="codigo_color_capa"
                       class="form-control"
                       maxlength="20"
                       value="{{ old('codigo_color_capa', $capa->codigo_color ?? '') }}"
                       placeholder="Se completa al elegir la carrera">
            </div>

            <div class="col-md-12">
                <label for="observaciones_capa" class="form-label fw-semibold">Observaciones</label>
                <textarea name="observaciones_capa"
                          id="observaciones_capa"
                          class="form-control"
                          rows="2"
                          placeholder="Detalles adicionales de la capa...">{{ old('observaciones_capa', $capa->observaciones ?? '') }}</textarea>
            </div>

        </div>
    </div>
@endif

{{-- ================= BIRRETE ================= --}}
@if($dibujar('BIRRETE'))
    @php
        $tipoBirreteActual = old('tipo_birrete', $birrete->tipo_birrete ?? '');

        // "Estándar" y "Normal" son lo mismo: los birretes antiguos guardados
        // como ESTANDAR se muestran como Normal (mismo precio, misma regla).
        if ($tipoBirreteActual === 'ESTANDAR') {
            $tipoBirreteActual = 'NORMAL';
        }

        $tiposBirrete = ['NORMAL' => 'Normal', 'UNIVERSITARIO' => 'Universitario'];
    @endphp

    <div id="campos-birrete" class="tipo-extra {{ $oculto }}">
        <div class="row g-3">

            <div class="{{ $col }}">
                <label for="tipo_birrete" class="form-label fw-semibold">Tipo de birrete</label>
                <select name="tipo_birrete" id="tipo_birrete" class="form-select" @required($editando)>
                    @unless($editando)
                        <option value="">Selecciona tipo...</option>
                    @endunless
                    @foreach($tiposBirrete as $valor => $texto)
                        <option value="{{ $valor }}" @selected($tipoBirreteActual === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>

            <div class="{{ $col }}">
                <label for="color_birrete" class="form-label fw-semibold">Color</label>
                <input type="text"
                       name="color_birrete"
                       id="color_birrete"
                       class="form-control"
                       maxlength="50"
                       value="{{ old('color_birrete', $birrete->color ?? '') }}"
                       placeholder="Ej. Negro"
                       @required($editando)>
            </div>

        </div>
    </div>
@endif

{{-- ================= COLLARÍN ================= --}}
@if($dibujar('COLLARIN'))
    @php
        $tipoCollarinActual = old('tipo_collarin', $collarin->tipo_collarin ?? 'NORMAL');
        $colorCollarinActual = (string) old('color_collarin', $collarin->color ?? '');
        $coloresDelTipo = array_keys($coloresCollarinPorTipo[$tipoCollarinActual] ?? $coloresCollarinPorTipo['NORMAL'] ?? []);
    @endphp

    <div id="campos-collarin" class="tipo-extra {{ $oculto }}">
        <div class="row g-3">

            <div class="col-md-12">
                <label class="form-label fw-semibold mb-2">Tipo de collarín</label>

                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_collarin"
                               id="collarin_normal" value="NORMAL"
                               @checked($tipoCollarinActual === 'NORMAL')>
                        <label class="form-check-label" for="collarin_normal">Normal</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_collarin"
                               id="collarin_universitario" value="UNIVERSITARIO"
                               @checked($tipoCollarinActual === 'UNIVERSITARIO')>
                        <label class="form-check-label" for="collarin_universitario">Universitario</label>
                    </div>
                </div>
            </div>

            <div class="{{ $col }}">
                <label for="color_collarin" class="form-label fw-semibold">Color</label>
                <select id="color_collarin" name="color_collarin" class="form-select" required>
                    <option value="">Seleccione...</option>
                    @foreach($coloresDelTipo as $color)
                        <option value="{{ $color }}" @selected($colorCollarinActual === $color)>{{ $color }}</option>
                    @endforeach
                </select>
            </div>

            <div class="{{ $col }}">
                <label for="codigo_color_collarin" class="form-label fw-semibold">Código de color</label>
                <input type="text"
                       id="codigo_color_collarin"
                       name="codigo_color_collarin"
                       class="form-control"
                       maxlength="20"
                       value="{{ old('codigo_color_collarin', $collarin->codigo_color ?? '') }}"
                       placeholder="Se completa al elegir el color">
            </div>

        </div>
    </div>
@endif

{{-- ================= BORLA ================= --}}
@if($dibujar('BORLA'))
    @php
        $colorBorlaActual = (string) old('borla_color', $borla->color ?? '');
        $tipoBorlaActual = old('tipo_borla', $borla->tipo_borla
            ?? app(\App\Services\ProductoService::class)->tipoBorla(null, $colorBorlaActual));
        $coloresBorla = array_keys($coloresBorlaPorTipo[$tipoBorlaActual] ?? $coloresBorlaPorTipo['NORMAL'] ?? []);
    @endphp

    <div id="campos-borla" class="tipo-extra {{ $oculto }}">
        <div class="row g-3">

            <div class="col-md-12">
                <label class="form-label fw-semibold mb-2">Tipo de borla</label>

                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_borla"
                               id="borla_normal" value="NORMAL"
                               @checked($tipoBorlaActual === 'NORMAL')>
                        <label class="form-check-label" for="borla_normal">Normal (toga estándar)</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_borla"
                               id="borla_universitaria" value="UNIVERSITARIA"
                               @checked($tipoBorlaActual === 'UNIVERSITARIA')>
                        <label class="form-check-label" for="borla_universitaria">Universitaria (color de carrera)</label>
                    </div>
                </div>
            </div>

            <div class="{{ $col }}">
                <label for="borla_color" class="form-label fw-semibold">Color de borla</label>
                <select id="borla_color" name="borla_color" class="form-select">
                    <option value="">Seleccione...</option>
                    @foreach($coloresBorla as $color)
                        <option value="{{ $color }}" @selected($colorBorlaActual === $color)>{{ $color }}</option>
                    @endforeach

                    {{-- Color antiguo que ya no está en la lista (p. ej. "Rojo-Derecho"):
                         se conserva para que guardar no lo borre en silencio. --}}
                    @if($colorBorlaActual !== '' && !in_array($colorBorlaActual, $coloresBorla, true))
                        <option value="{{ $colorBorlaActual }}" selected>{{ $colorBorlaActual }}</option>
                    @endif
                </select>
            </div>

            <div class="{{ $col }}">
                <label for="borla_codigo_color" class="form-label fw-semibold">Código de color</label>
                <input type="text"
                       id="borla_codigo_color"
                       name="borla_codigo_color"
                       class="form-control"
                       maxlength="20"
                       value="{{ old('borla_codigo_color', $borla->codigo_color ?? '') }}"
                       placeholder="Se completa al elegir el color">
            </div>

            <div class="col-md-12">
                <label for="borla_observaciones" class="form-label fw-semibold">Observaciones de borla</label>
                <textarea name="borla_observaciones"
                          id="borla_observaciones"
                          class="form-control"
                          rows="2"
                          placeholder="Detalles adicionales de la borla...">{{ old('borla_observaciones', $borla->observaciones ?? '') }}</textarea>
            </div>

        </div>
    </div>
@endif
