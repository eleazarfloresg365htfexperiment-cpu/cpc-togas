<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrarPagoRequest;
use App\Models\Alquiler;
use App\Services\PagoService;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function create(Alquiler $alquiler)
    {
        $alquiler->load(['cliente', 'detalles.producto', 'pagos']);

        if ($alquiler->saldo_pendiente <= 0) {
            return redirect()
                ->route('alquileres.index')
                ->with('error', 'Este alquiler ya está pagado completamente.');
        }

        return view('pagos.create', compact('alquiler'));
    }

    public function store(RegistrarPagoRequest $request, Alquiler $alquiler, PagoService $pagos)
    {
        $datos = $request->validated();

        try {
            $monto = (float) ($datos['monto'] ?? 0);
            $descuentoAplicado = (float) ($datos['descuento_aplicado'] ?? 0);

            if (($monto + $descuentoAplicado) <= 0) {
                return $this->volverAlFormulario($alquiler, 'monto', 'Debe ingresar un pago o un descuento mayor a cero.');
            }

            if (($monto + $descuentoAplicado) > (float) $alquiler->saldo_pendiente) {
                return $this->volverAlFormulario($alquiler, 'monto', 'La suma del pago y el descuento no puede ser mayor al saldo pendiente.');
            }

            if ($descuentoAplicado > 0 && empty($datos['observacion_descuento'])) {
                return $this->volverAlFormulario($alquiler, 'observacion_descuento', 'Debe ingresar una observación para justificar el descuento aplicado.');
            }

            $pagos->registrarPago(
                alquilerId: (int) $alquiler->id,
                monto: $monto,
                metodoPago: $datos['metodo_pago'],
                referencia: $datos['referencia'] ?? null,
                observaciones: $datos['observaciones'] ?? null,
                usuarioId: auth()->id(),
                descuentoAplicado: $descuentoAplicado,
                observacionDescuento: $datos['observacion_descuento'] ?? null
            );

            // El formulario puede enviar la fecha límite con distintos nombres.
            $fechaLimitePagoFinal =
                $request->input('fecha_limite_pago_final')
                ?? $request->input('fecha_limite_pago')
                ?? $request->input('fecha_pago_final')
                ?? $request->input('limite_pago_final');

            if (!empty($fechaLimitePagoFinal)) {
                DB::table('alquileres')
                    ->where('id', $alquiler->id)
                    ->update([
                        'fecha_limite_pago_final' => $fechaLimitePagoFinal,
                        'updated_at' => now(),
                    ]);
            }

            return redirect()
                ->route('alquileres.show', $alquiler->id)
                ->with('success', 'Pago o descuento registrado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('pagos.create', $alquiler->id)
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    private function volverAlFormulario(Alquiler $alquiler, string $campo, string $mensaje)
    {
        return redirect()
            ->route('pagos.create', $alquiler->id)
            ->withInput()
            ->withErrors([$campo => $mensaje]);
    }
}
