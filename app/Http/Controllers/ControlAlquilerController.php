<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * La antigua pantalla "Control de alquileres" se reemplazó por el módulo
 * Actividades. Esta ruta solo redirige (marcadores y enlaces viejos) a la
 * pestaña que corresponde a cada filtro de antes.
 */
class ControlAlquilerController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $destino = match ($request->input('filtro')) {
            'pendientes_pago', 'pagados' => route('actividades.cobros'),
            'entregados_hoy', 'devueltos_hoy' => route('actividades.bitacora', [
                'desde' => now()->toDateString(),
                'hasta' => now()->toDateString(),
                'categoria' => 'ALQUILER',
            ]),
            default => route('actividades.agenda'),
        };

        return redirect()->to($destino, 301);
    }
}
