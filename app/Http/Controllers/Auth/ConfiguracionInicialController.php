<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Crea el PRIMER usuario del sistema. Solo funciona mientras no exista
 * ningún usuario; después, los usuarios se crean desde la sección Usuarios.
 */
class ConfiguracionInicialController extends Controller
{
    public function mostrar()
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }

        return view('auth.configuracion-inicial');
    }

    public function guardar(UsuarioRequest $request): RedirectResponse
    {
        abort_if(User::query()->exists(), 403, 'El sistema ya tiene usuarios.');

        $usuario = User::create($request->datosParaGuardar() + ['activo' => true]);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Usuario creado. ¡Bienvenido(a), ' . $usuario->nombre_completo . '!');
    }
}
