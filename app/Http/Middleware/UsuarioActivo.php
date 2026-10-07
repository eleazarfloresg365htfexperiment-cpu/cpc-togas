<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si desactivan a un usuario mientras tiene la sesión abierta, se le cierra
 * la sesión en su siguiente clic.
 */
class UsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['usuario' => 'Tu usuario fue desactivado. Pide ayuda a otro usuario del sistema.']);
        }

        return $next($request);
    }
}
