<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del formulario de pago. Al ser una ruta de un alquiler
 * (/alquileres/{alquiler}/pagar) el monto máximo es el saldo pendiente.
 */
class RegistrarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto' => ['required', 'numeric', 'min:0', 'max:' . $this->route('alquiler')->saldo_pendiente],
            'descuento_aplicado' => ['nullable', 'numeric', 'min:0'],
            'observacion_descuento' => ['nullable', 'string', 'max:1000'],

            'metodo_pago' => ['required', 'in:EFECTIVO,TRANSFERENCIA,TARJETA,OTRO'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],

            'fecha_limite_pago_final' => ['nullable', 'date'],
            'fecha_limite_pago' => ['nullable', 'date'],
            'fecha_pago_final' => ['nullable', 'date'],
            'limite_pago_final' => ['nullable', 'date'],
        ];
    }
}
