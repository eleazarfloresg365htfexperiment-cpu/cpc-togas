<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del formulario de cliente (crear y editar).
 * Al editar, la ruta trae el cliente ({cliente}) para permitir conservar su DPI.
 */
class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cliente = $this->route('cliente');

        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:25'],
            'dpi' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('clientes', 'dpi')->ignore($cliente instanceof Cliente ? $cliente->id : null),
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'institucion_representada' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }
}
