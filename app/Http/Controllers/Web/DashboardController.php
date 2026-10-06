<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Models\MovimientoInventario;
use App\Models\Pago;
use App\Models\Producto;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $totalProductos = Producto::count();

        $productosActivos = Producto::where('activo', true)->count();

        $productosInactivos = Producto::where('activo', false)->count();

        $stockTotalGeneral = Producto::sum('stock_total');

        $stockDisponibleGeneral = Producto::sum('stock_disponible');

        $stockAlquiladoGeneral = Producto::sum('stock_alquilado');

        $alquileresEntregados = Alquiler::where('estado', 'ENTREGADO')->count();

        $alquileresReservados = Alquiler::whereIn('estado', Alquiler::ESTADOS_ANTES_DE_ENTREGA)->count();

        $alquileresDevueltos = Alquiler::where('estado', 'DEVUELTO')->count();

        $alquileresCancelados = Alquiler::where('estado', 'CANCELADO')->count();

        $pagosPendientes = Alquiler::where('estado', '!=', 'CANCELADO')
            ->where('saldo_pendiente', '>', 0)
            ->count();

        $totalPorCobrar = Alquiler::where('estado', '!=', 'CANCELADO')
            ->sum('saldo_pendiente');

        $ingresosRecibidos = Pago::sum('monto');

        $movimientosRecientes = MovimientoInventario::with('producto')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'totalProductos',
            'productosActivos',
            'productosInactivos',
            'stockTotalGeneral',
            'stockDisponibleGeneral',
            'stockAlquiladoGeneral',
            'alquileresEntregados',
            'alquileresReservados',
            'alquileresDevueltos',
            'alquileresCancelados',
            'pagosPendientes',
            'totalPorCobrar',
            'ingresosRecibidos',
            'movimientosRecientes'
        ));
    }
}
