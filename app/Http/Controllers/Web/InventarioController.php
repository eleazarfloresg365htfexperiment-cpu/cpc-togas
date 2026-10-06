<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function movimientos(Request $request)
    {
        $tipo = $request->input('tipo');
        $buscar = $request->input('buscar');

        $consulta = MovimientoInventario::query()
            ->when($tipo, function ($query, $tipo) {
                $query->where('tipo_movimiento', $tipo);
            })
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('motivo', 'like', "%{$buscar}%")
                        ->orWhere('referencia', 'like', "%{$buscar}%")
                        ->orWhereHas('producto', function ($productoQuery) use ($buscar) {
                            $productoQuery->where('codigo', 'like', "%{$buscar}%")
                                ->orWhere('nombre', 'like', "%{$buscar}%")
                                ->orWhere('tipo_producto', 'like', "%{$buscar}%");
                        });
                });
            });

        // Resumen de TODOS los movimientos que cumplen el filtro (antes solo
        // contaba los 20 de la página visible).
        $porTipo = (clone $consulta)
            ->selectRaw('tipo_movimiento, COUNT(*) as cantidad')
            ->groupBy('tipo_movimiento')
            ->pluck('cantidad', 'tipo_movimiento');

        $resumen = [
            'total' => (int) $porTipo->sum(),
            'entradas' => (int) ($porTipo['ENTRADA'] ?? 0),
            'alquileres' => (int) ($porTipo['ALQUILER'] ?? 0),
            'devoluciones' => (int) ($porTipo['DEVOLUCION'] ?? 0),
            'ajustes' => (int) ($porTipo['AJUSTE'] ?? 0),
        ];

        $movimientos = $consulta
            ->with('producto')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventario.movimientos', compact(
            'movimientos',
            'resumen',
            'tipo',
            'buscar'
        ));
    }

    public function entrada(Producto $producto)
    {
        return view('productos.entrada', compact('producto'));
    }

    public function guardarEntrada(Request $request, Producto $producto, InventarioService $inventario)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:100',
        ]);

        try {
            $inventario->registrarEntrada(
                productoId: $producto->id,
                cantidad: (int) $request->cantidad,
                motivo: $request->motivo,
                referencia: $request->referencia,
                usuarioId: null
            );

            return redirect()
                ->route('productos.index')
                ->with('success', 'Entrada de inventario registrada correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['cantidad' => $e->getMessage()]);
        }
    }

    public function ajuste(Producto $producto)
    {
        return view('productos.ajuste', compact('producto'));
    }

    public function guardarAjuste(Request $request, Producto $producto, InventarioService $inventario)
    {
        $request->validate([
            'nuevo_stock_disponible' => 'required|integer|min:0',
            'motivo' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:100',
        ]);

        try {
            $inventario->registrarAjuste(
                productoId: $producto->id,
                nuevoStockDisponible: (int) $request->nuevo_stock_disponible,
                motivo: $request->motivo,
                referencia: $request->referencia,
                usuarioId: null
            );

            return redirect()
                ->route('productos.index')
                ->with('success', 'Ajuste de inventario registrado correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['nuevo_stock_disponible' => $e->getMessage()]);
        }
    }
}
