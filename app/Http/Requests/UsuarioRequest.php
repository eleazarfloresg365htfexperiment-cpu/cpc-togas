<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Datos de un usuario del sistema (crear y editar).
 * Al editar, la contraseña es opcional: si se deja vacía no se cambia.
 */
class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    private function usuarioActual(): ?User
    {
        $usuario = $this->route('usuario');

        return $usuario instanceof User ? $usuario : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'usuario' => mb_strtolower(trim((string) $this->input('usuario'))),
            'email' => $this->filled('email') ? trim((string) $this->input('email')) : null,
        ]);
    }

    public function rules(): array
    {
        $editando = $this->usuarioActual() !== null;

        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'usuario' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('users', 'usuario')->ignore($this->usuarioActual()?->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->usuarioActual()?->id),
            ],
            'password' => [
                $editando ? 'nullable' : 'required',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'usuario.regex' => 'El usuario solo puede tener letras minúsculas, números, punto, guion y guion bajo (sin espacios ni tildes).',
            'usuario.unique' => 'Ese nombre de usuario ya existe.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function attributes(): array
    {
        return [
            'usuario' => 'el usuario',
            'password' => 'la contraseña',
        ];
    }

    /** Datos listos para User::create()/update() (sin contraseña vacía). */
    public function datosParaGuardar(): array
    {
        $datos = $this->safe()->only(['nombres', 'apellidos', 'usuario', 'email']);

        if ($this->filled('password')) {
            $datos['password'] = $this->input('password');
        }

        return $datos;
    }
}
