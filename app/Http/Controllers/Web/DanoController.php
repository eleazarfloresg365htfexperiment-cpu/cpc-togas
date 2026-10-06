<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Services\AlquilerService;
use Illuminate\Http\Request;

/**
 * Daños y extravíos registrados sobre un alquiler devuelto.
 */
class DanoController extends Controller
{
    public function store(Request $request, Alquiler $alquiler, AlquilerService $alquileres)
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'tipo' => ['required', 'in:DANO,EXTRAVIO'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'monto' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'responsable' => ['nullable', 'string', 'max:255'],
        ], [
            'producto_id.required' => 'Selecciona el producto dañado o extraviado.',
            'monto.required' => 'Indica el monto a cobrar (puede ser 0).',
        ]);

        $destino = route('alquileres.show', $alquiler->id) . '#danos';

        try {
            $dano = $alquileres->registrarDano(
                alquilerId: $alquiler->id,
                productoId: (int) $datos['producto_id'],
                tipo: $datos['tipo'],
                cantidad: (int) $datos['cantidad'],
                monto: (float) $datos['monto'],
                descripcion: $datos['descripcion'] ?? null,
                responsable: $datos['responsable'] ?? null,
                usuarioId: auth()->id()
            );

            $mensaje = $dano->tipo_texto . ' registrado correctamente.';

            if ((float) $dano->monto > 0) {
                $mensaje .= ' Se agregaron Q' . number_format((float) $dano->monto, 2) . ' al saldo pendiente.';
            }

            if ($dano->tipo === 'EXTRAVIO') {
                $mensaje .= ' Se dio de baja del inventario.';
            }

            return redirect()->to($destino)->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->to($destino)->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, Alquiler $alquiler, int $danoId, AlquilerService $alquileres)
    {
        $request->validate([
            'motivo_eliminacion' => ['required', 'string', 'max:1000'],
        ], [
            'motivo_eliminacion.required' => 'Debes indicar el motivo para eliminar el registro.',
        ]);

        $destino = route('alquileres.show', $alquiler->id) . '#danos';

        try {
            $alquileres->eliminarDano(
                $alquiler->id,
                $danoId,
                $request->input('motivo_eliminacion'),
                $request->input('responsable'),
                auth()->id()
            );

            return redirect()
                ->to($destino)
                ->with('success', 'Registro eliminado. Se revirtió el cargo y, si era extravío, el inventario.');
        } catch (\Exception $e) {
            return redirect()->to($destino)->with('error', $e->getMessage());
        }
    }
}
