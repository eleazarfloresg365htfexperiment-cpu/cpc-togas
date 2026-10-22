{{-- Pestañas del módulo Actividades --}}
@php
    $pestanas = [
        'actividades.agenda' => '📅 Agenda',
        'actividades.inventario' => '🎓 Togas por talla',
        'actividades.cobros' => '💰 Por cobrar',
        'actividades.bitacora' => '📝 Bitácora',
    ];
@endphp

<ul class="nav nav-pills actividades-pestanas mb-4 flex-wrap gap-2">
    @foreach($pestanas as $ruta => $texto)
        <li class="nav-item">
            <a href="{{ route($ruta) }}"
               class="nav-link rounded-pill {{ request()->routeIs($ruta) ? 'active' : 'bg-white border' }}">
                {{ $texto }}
            </a>
        </li>
    @endforeach
</ul>

@once
    <style>
        .actividades-pestanas .nav-link { color: #4338ca; font-weight: 600; }
        .actividades-pestanas .nav-link.active { background: #4f46e5; color: #fff; }
        .agenda-dia { border-left: 4px solid #e5e7eb; }
        .agenda-dia.es-hoy { border-left-color: #4f46e5; }
        .agenda-item { border: 1px solid #eef0f4; border-radius: 14px; padding: 10px 14px; background: #fff; }
        .agenda-item + .agenda-item { margin-top: 8px; }
        .agenda-item.hecho { opacity: .6; }
        .agenda-item.atrasado { border-color: #fecaca; background: #fff7f7; }
        .tallas-texto { font-size: .9rem; color: #374151; }
        .libres-negativo { color: #b91c1c; font-weight: 700; }
        .libres-justo { color: #b45309; font-weight: 700; }
        .libres-ok { color: #047857; font-weight: 700; }
    </style>
@endonce
