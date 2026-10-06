{{--
    JavaScript del formulario de productos (crear y editar).
    Las tablas de colores y códigos salen de config/alquiler.php.
--}}

@php
    $capaPorCarrera = collect(config('alquiler.carreras_capa', []))
        ->map(fn ($datos, $carrera) => [
            'codigo' => $datos['codigo'],
            'color' => config("alquiler.colores_borla_por_carrera.$carrera"),
        ])
        ->all();

    $configuracionJs = [
        'coloresBorla' => config('alquiler.colores_borla_por_tipo', []),
        'coloresCollarin' => config('alquiler.colores_collarin_por_tipo', []),
        'capaPorCarrera' => $capaPorCarrera,
    ];
@endphp

<script>
    window.CPC_PRODUCTOS = @json($configuracionJs);
</script>
<script src="{{ asset('js/productos-formulario.js') }}?v={{ filemtime(public_path('js/productos-formulario.js')) }}"></script>
