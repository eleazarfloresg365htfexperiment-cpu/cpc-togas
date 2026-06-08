<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with([
            'toga',
            'birrete',
            'collarin',
        ])
            ->orderBy('tipo_producto')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'ok' => true,
            'productos' => $productos,
        ]);
    }

    public function show(int $id)
    {
        $producto = Producto::with([
            'toga',
            'birrete',
            'collarin',
            'movimientosInventario',
        ])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'producto' => $producto,
        ]);
    }

    public function disponibles()
    {
        $productos = Producto::with([
            'toga',
            'birrete',
            'collarin',
        ])
            ->where('activo', true)
            ->where('stock_disponible', '>', 0)
            ->orderBy('tipo_producto')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'ok' => true,
            'productos' => $productos,
        ]);
    }
}