<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Alquiler;
use App\Models\Pago;

class ReporteController extends Controller
{
    public function resumen()
    {
        /*
        |--------------------------------------------------------------------------
        | Inventario
        |--------------------------------------------------------------------------
        */
        $totalProductos = Producto::count();
        $productosActivos = Producto::where('activo', true)->count();
        $productosInactivos = Producto::where('activo', false)->count();

        $stockTotal = Producto::sum('stock_total');
        $stockDisponible = Producto::sum('stock_disponible');
        $stockAlquilado = Producto::sum('stock_alquilado');

        /*
        |--------------------------------------------------------------------------
        | Alquileres por estado
        |--------------------------------------------------------------------------
        */
        $alquileresReservados = Alquiler::where('estado', 'RESERVADO')->count();
        $alquileresEntregados = Alquiler::where('estado', 'ENTREGADO')->count();
        $alquileresDevueltos = Alquiler::where('estado', 'DEVUELTO')->count();
        $alquileresCancelados = Alquiler::where('estado', 'CANCELADO')->count();

        /*
        |--------------------------------------------------------------------------
        | Pagos
        |--------------------------------------------------------------------------
        | Importante:
        | Los alquileres CANCELADOS no deben contar como deuda pendiente.
        */
        $pagosPendientes = Alquiler::where('estado_pago', 'PENDIENTE')
            ->where('estado', '!=', 'CANCELADO')
            ->count();

        $pagosParciales = Alquiler::where('estado_pago', 'PARCIAL')
            ->where('estado', '!=', 'CANCELADO')
            ->count();

        $pagosCompletados = Alquiler::where('estado_pago', 'PAGADO')
            ->where('estado', '!=', 'CANCELADO')
            ->count();

        $totalPorCobrar = Alquiler::where('estado', '!=', 'CANCELADO')
            ->sum('saldo_pendiente');

        $totalPagado = Pago::sum('monto');

        /*
        |--------------------------------------------------------------------------
        | Alquileres recientes
        |--------------------------------------------------------------------------
        */
        $alquileresRecientes = Alquiler::with(['cliente', 'detalles.producto', 'pagos'])
            ->orderByDesc('id')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Alertas de inventario
        |--------------------------------------------------------------------------
        */
        $productosConBajoStock = Producto::where('activo', true)
            ->where('stock_disponible', '<=', 2)
            ->orderBy('stock_disponible')
            ->get();

        return response()->json([
            'ok' => true,
            'resumen' => [
                'inventario' => [
                    'total_productos' => $totalProductos,
                    'productos_activos' => $productosActivos,
                    'productos_inactivos' => $productosInactivos,
                    'stock_total' => $stockTotal,
                    'stock_disponible' => $stockDisponible,
                    'stock_alquilado' => $stockAlquilado,
                ],
                'alquileres' => [
                    'reservados' => $alquileresReservados,
                    'entregados' => $alquileresEntregados,
                    'devueltos' => $alquileresDevueltos,
                    'cancelados' => $alquileresCancelados,
                ],
                'pagos' => [
                    'pendientes' => $pagosPendientes,
                    'parciales' => $pagosParciales,
                    'pagados' => $pagosCompletados,
                    'total_por_cobrar' => $totalPorCobrar,
                    'total_pagado' => $totalPagado,
                    'ingresos_recibidos' => $totalPagado,
                ],
                'alertas' => [
                    'productos_bajo_stock' => $productosConBajoStock,
                ],
                'alquileres_recientes' => $alquileresRecientes,
            ],
        ]);
    }
}