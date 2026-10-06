<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Services\FabricacionService;
use Illuminate\Http\Request;

class FabricacionController extends Controller
{
    public function completar(Request $request, Alquiler $alquiler, int $fabricacionId, FabricacionService $fabricaciones)
    {
        $datos = $request->validate([
            'cantidad_completada' => ['required', 'integer', 'min:1'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'responsable' => ['nullable', 'string', 'max:255'],
        ], [
            'cantidad_completada.required' => 'Indica cuántas unidades se fabricaron.',
            'cantidad_completada.min' => 'La cantidad fabricada debe ser al menos 1.',
        ]);

        $destino = route('alquileres.show', $alquiler->id) . '#fabricacion';

        try {
            $fabricacion = $fabricaciones->completarFabricacion(
                fabricacionId: $fabricacionId,
                cantidadCompletada: (int) $datos['cantidad_completada'],
                observaciones: $datos['observaciones'] ?? null,
                usuarioId: auth()->id(),
                alquilerId: $alquiler->id,
                responsable: $datos['responsable'] ?? null
            );

            $mensaje = 'Fabricación registrada: ' . (int) $datos['cantidad_completada'] .
                ' unidad(es) de "' . ($fabricacion->producto->nombre ?? 'producto') . '" agregadas al inventario.';

            if ($alquiler->fresh()->estado === 'LISTO_PARA_ENTREGA') {
                $mensaje .= ' Ya no queda nada pendiente: el alquiler está LISTO PARA ENTREGA.';
            }

            return redirect()->to($destino)->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->to($destino)->with('error', $e->getMessage());
        }
    }
}
