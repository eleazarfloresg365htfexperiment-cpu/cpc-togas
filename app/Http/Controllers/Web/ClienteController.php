<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado');

        $consulta = Cliente::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%")
                        ->orWhere('dpi', 'like', "%{$buscar}%")
                        ->orWhere('direccion', 'like', "%{$buscar}%")
                        ->orWhere('institucion_representada', 'like', "%{$buscar}%");
                });
            })
            ->when($estado !== null && $estado !== '', function ($query) use ($estado) {
                $query->where('activo', $estado);
            });

        // Resumen de TODOS los clientes que cumplen el filtro (no solo la página).
        $resumen = [
            'total' => (clone $consulta)->count(),
            'activos' => (clone $consulta)->where('activo', true)->count(),
            'inactivos' => (clone $consulta)->where('activo', false)->count(),
        ];

        $clientes = $consulta
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $totalClientes = Cliente::count();
        $clientesActivos = Cliente::where('activo', true)->count();
        $clientesInactivos = Cliente::where('activo', false)->count();

        return view('clientes.index', compact(
            'clientes',
            'resumen',
            'totalClientes',
            'clientesActivos',
            'clientesInactivos',
            'buscar',
            'estado'
        ));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(ClienteRequest $request)
    {
        Cliente::create($request->validated() + ['activo' => true]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(ClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function desactivar(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente desactivado correctamente.');
    }

    public function reactivar(Cliente $cliente)
    {
        $cliente->update(['activo' => true]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente reactivado correctamente.');
    }
}
