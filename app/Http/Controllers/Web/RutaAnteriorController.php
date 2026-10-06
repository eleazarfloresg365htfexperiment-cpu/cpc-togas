<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Redirige las direcciones anteriores (/productos-web, /clientes-web,
 * /alquileres-web y /calendario-web) a las actuales (/productos, /clientes,
 * /alquileres y /calendario).
 *
 * Se usa 308 (redirección permanente que conserva el método) para que un
 * formulario que se abrió antes del cambio pueda enviarse sin perder datos.
 */
class RutaAnteriorController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $ruta = preg_replace(
            '#^/(productos|clientes|alquileres|calendario)-web(?=/|$)#',
            '/$1',
            $request->getPathInfo()
        );

        $consulta = $request->getQueryString();

        return redirect()->to($ruta . ($consulta ? '?' . $consulta : ''), 308);
    }
}
