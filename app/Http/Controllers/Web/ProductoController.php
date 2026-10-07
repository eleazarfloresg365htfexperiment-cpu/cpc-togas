<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Models\Producto;
use App\Services\ProductoService;
use DomainException;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $tipo = $request->input('tipo');
        $estado = $request->input('estado');

        $productos = Producto::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('codigo', 'like', "%{$buscar}%")
                        ->orWhere('nombre', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->when($tipo, function ($query, $tipo) {
                $query->where('tipo_producto', $tipo);
            })
            ->when($estado !== null && $estado !== '', function ($query) use ($estado) {
                $query->where('activo', $estado);
            })
            ->orderBy('tipo_producto')
            ->orderBy('nombre')
            ->get();

        $totalProductos = Producto::count();
        $productosActivos = Producto::where('activo', true)->count();
        $productosInactivos = Producto::where('activo', false)->count();

        $stockTotal = Producto::sum('stock_total');
        $stockDisponible = Producto::sum('stock_disponible');
        $stockAlquilado = Producto::sum('stock_alquilado');

        return view('productos.index', compact(
            'productos',
            'totalProductos',
            'productosActivos',
            'productosInactivos',
            'stockTotal',
            'stockDisponible',
            'stockAlquilado',
            'buscar',
            'tipo',
            'estado'
        ));
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(ProductoRequest $request, ProductoService $productos)
    {
        $productos->crear($request->validated(), auth()->id());

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    public function administrar()
    {
        return view('productos.administrar');
    }

    public function administrarAccion(Request $request, string $accion)
    {
        $accionesPermitidas = ['editar', 'entrada', 'ajuste', 'estado'];

        if (!in_array($accion, $accionesPermitidas)) {
            abort(404);
        }

        $query = Producto::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                    ->orWhere('nombre', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('tipo') && $request->tipo !== 'TODOS') {
            $query->where('tipo_producto', $request->tipo);
        }

        $productos = $query->orderBy('tipo_producto')
            ->orderBy('nombre')
            ->get();

        return view('productos.administrar-accion', compact('productos', 'accion'));
    }

    public function edit(Producto $producto)
    {
        $producto->load(['toga', 'birrete', 'collarin', 'borla', 'capa']);

        return view('productos.edit', compact('producto'));
    }

    public function update(ProductoRequest $request, Producto $producto, ProductoService $productos)
    {
        try {
            $productos->actualizar($producto, $request->validated());
        } catch (DomainException $e) {
            // Antes esto terminaba en una pantalla de error del servidor.
            return back()
                ->withInput()
                ->withErrors(['stock_total' => $e->getMessage()]);
        }

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function desactivar(Producto $producto)
    {
        $producto->update(['activo' => false]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto desactivado correctamente.');
    }

    public function reactivar(Producto $producto)
    {
        $producto->update(['activo' => true]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto reactivado correctamente.');
    }
}
