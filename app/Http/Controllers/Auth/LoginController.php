<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Inicio y cierre de sesión con nombre de usuario y contraseña.
 */
class LoginController extends Controller
{
    /** Intentos fallidos permitidos antes de esperar un minuto. */
    private const MAX_INTENTOS = 5;

    public function mostrar()
    {
        // Sistema recién instalado: primero hay que crear el primer usuario.
        if (!User::query()->exists()) {
            return redirect()->route('configuracion-inicial');
        }

        return view('auth.login');
    }

    public function iniciar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'usuario' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [], [
            'usuario' => 'el usuario',
            'password' => 'la contraseña',
        ]);

        $clave = Str::lower($datos['usuario']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS)) {
            throw ValidationException::withMessages([
                'usuario' => trans('auth.throttle', ['seconds' => RateLimiter::availableIn($clave)]),
            ]);
        }

        $credenciales = [
            'usuario' => $datos['usuario'],
            'password' => $datos['password'],
            'activo' => true,
        ];

        if (!Auth::attempt($credenciales, $request->boolean('recordar'))) {
            RateLimiter::hit($clave, 60);

            throw ValidationException::withMessages([
                'usuario' => 'Usuario o contraseña incorrectos.',
            ]);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function cerrar(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
