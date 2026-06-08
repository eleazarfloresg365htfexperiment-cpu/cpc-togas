<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        return response()->json([
            'ok' => true,
            'clientes' => $clientes,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'dpi' => ['nullable', 'string', 'max:30', 'unique:clientes,dpi'],
            'direccion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $datos['activo'] = $datos['activo'] ?? true;

        $cliente = Cliente::create($datos);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Cliente creado correctamente.',
            'cliente' => $cliente,
        ], 201);
    }

    public function show(int $id)
    {
        $cliente = Cliente::with([
            'alquileres.detalles.producto',
            'alquileres.pagos',
        ])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'cliente' => $cliente,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $cliente = Cliente::findOrFail($id);

        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'dpi' => ['nullable', 'string', 'max:30', 'unique:clientes,dpi,' . $cliente->id],
            'direccion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $cliente->update($datos);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Cliente actualizado correctamente.',
            'cliente' => $cliente,
        ]);
    }

    public function destroy(int $id)
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->activo = false;
        $cliente->save();

        return response()->json([
            'ok' => true,
            'mensaje' => 'Cliente desactivado correctamente.',
        ]);
    }
}