{{--
    Datos generales del producto: se usan al crear y al editar.
    Variable opcional: $producto (si viene, se está editando).
    Al editar el tipo no se cambia (se muestra de solo lectura) y el estado
    y el stock se conservan; para cambiar stock existen Entrada y Ajuste.
--}}

@php
    $producto = $producto ?? null;
    $editando = $producto !== null;
    $col = $editando ? 'col-md-6' : 'col-md-4';
    $tiposDeProducto = [
        'TOGA' => 'TOGA',
        'BIRRETE' => 'BIRRETE',
        'COLLARIN' => 'COLLARÍN',
        'BORLA' => 'BORLA',
        'CAPA' => 'CAPA',
    ];
@endphp

<div class="row {{ $editando ? 'g-3' : 'g-4' }}">

    <div class="{{ $col }}">
        <label for="codigo" class="form-label fw-semibold">Código</label>
        <input type="text"
               name="codigo"
               id="codigo"
               class="form-control"
               value="{{ old('codigo', $producto?->codigo) }}"
               @unless($editando) placeholder="Ej. TOGA-S-NEGRA" @endunless
               required>
        @unless($editando)
            <small class="text-muted">Debe ser único.</small>
        @endunless
    </div>

    <div class="{{ $editando ? 'col-md-6' : 'col-md-8' }}">
        <label for="nombre" class="form-label fw-semibold">Nombre del producto</label>
        <input type="text"
               name="nombre"
               id="nombre"
               class="form-control"
               value="{{ old('nombre', $producto?->nombre) }}"
               @unless($editando) placeholder="Ej. Toga negra talla S" @endunless
               required>
    </div>

    <div class="{{ $col }}">
        <label for="tipo_producto" class="form-label fw-semibold">Tipo de producto</label>

        @if($editando)
            <input type="text"
                   id="tipo_producto"
                   class="form-control"
                   value="{{ $producto->tipo_producto }}"
                   readonly>
            <small class="text-muted">
                El tipo de producto no se modifica desde esta pantalla.
            </small>
        @else
            <select name="tipo_producto" id="tipo_producto" class="form-select" required>
                <option value="">Selecciona un tipo</option>
                @foreach($tiposDeProducto as $valor => $texto)
                    <option value="{{ $valor }}" @selected(old('tipo_producto') === $valor)>
                        {{ $texto }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Solo las togas tienen precio. Los accesorios (collarín, capa, birrete,
         borla) van incluidos sin costo; como extra se cobran con los precios
         de config/alquiler.php. Al crear, el JavaScript oculta este campo
         si el tipo elegido es un accesorio. --}}
    <div class="{{ $col }}{{ $editando && $producto->esAccesorio() ? ' d-none' : '' }}" id="grupo-precio">
        <label for="precio_alquiler" class="form-label fw-semibold">Precio de alquiler</label>
        <div class="input-group">
            <span class="input-group-text">Q</span>
            <input type="number"
                   name="precio_alquiler"
                   id="precio_alquiler"
                   class="form-control"
                   value="{{ $editando && $producto->esAccesorio() ? 0 : old('precio_alquiler', $producto?->precio_alquiler ?? 0) }}"
                   step="0.01"
                   min="0"
                   required>
        </div>
    </div>

    @unless($editando)
        <div class="col-md-4">
            <label for="activo" class="form-label fw-semibold">Estado</label>
            <select name="activo" id="activo" class="form-select" required>
                <option value="1" @selected(old('activo', '1') == '1')>Activo</option>
                <option value="0" @selected(old('activo') == '0')>Inactivo</option>
            </select>
        </div>
    @endunless

    <div class="col-md-12">
        <label for="descripcion" class="form-label fw-semibold">Descripción</label>
        <textarea name="descripcion"
                  id="descripcion"
                  class="form-control"
                  rows="3"
                  placeholder="Descripción general del producto...">{{ old('descripcion', $producto?->descripcion) }}</textarea>
    </div>

</div>
