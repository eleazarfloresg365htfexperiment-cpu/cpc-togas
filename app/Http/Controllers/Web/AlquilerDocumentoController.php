<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alquiler;

/**
 * Documentos imprimibles de un alquiler: recibo, carta de compromiso y
 * carta de devolución.
 */
class AlquilerDocumentoController extends Controller
{
    public function recibo(Alquiler $alquiler)
    {
        $alquiler->load([
            'cliente',
            'detalles.producto',
            'detalles.producto.toga',
            'detalles.accesorios.producto',
            'pagos',
            'detalles.accesorios.producto.birrete',
            'detalles.accesorios.producto.borla',
            'detalles.accesorios.producto.collarin',
            'detalles.accesorios.producto.capa',
        ]);

        return view('alquileres.recibo', compact('alquiler'));
    }

    public function compromiso(Alquiler $alquiler)
    {
        $alquiler->load([
            'cliente',
            'detalles.producto',
            'detalles.producto.toga',
            'detalles.accesorios.producto',
            'pagos',
        ]);

        return view('alquileres.terminos', compact('alquiler'));
    }

    public function devolucion(Alquiler $alquiler)
    {
        $alquiler->load([
            'cliente',
            'detalles.producto',
            'detalles.producto.toga',
            'detalles.accesorios.producto',
        ]);

        return view('alquileres.devolucion', compact('alquiler'));
    }
}
