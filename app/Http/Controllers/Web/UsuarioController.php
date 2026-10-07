<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

/**
 * Usuarios que pueden entrar al sistema.
 * Los usuarios no se borran (quedan en el historial de alquileres y pagos):
 * se desactivan, y un usuario desactivado no puede iniciar sesión.
 */
class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::query()
            ->orderByDesc('activo')
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $usuario = User::create($request->datosParaGuardar() + ['activo' => true]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', "Usuario {$usuario->usuario} creado correctamente.");
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(UsuarioRequest $request, User $usuario): RedirectResponse
    {
        $usuario->update($request->datosParaGuardar());

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function desactivar(User $usuario): RedirectResponse
    {
        if ($usuario->is(auth()->user())) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        if (User::where('activo', true)->count() <= 1) {
            return back()->with('error', 'Debe quedar al menos un usuario activo.');
        }

        $usuario->update(['activo' => false]);

        return back()->with('success', 'Usuario desactivado. Ya no podrá iniciar sesión.');
    }

    public function reactivar(User $usuario): RedirectResponse
    {
        $usuario->update(['activo' => true]);

        return back()->with('success', 'Usuario reactivado.');
    }
}
