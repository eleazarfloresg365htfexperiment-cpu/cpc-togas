<?php

namespace App\Http\Requests;

use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del formulario de producto, tanto para crear como para editar.
 *
 * Al editar, la ruta trae el producto ({producto}) y su tipo no se puede
 * cambiar; al crear, el tipo viene del formulario.
 */
class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function productoActual(): ?Producto
    {
        $producto = $this->route('producto');

        return $producto instanceof Producto ? $producto : null;
    }

    public function esCreacion(): bool
    {
        return $this->productoActual() === null;
    }

    public function tipoProducto(): ?string
    {
        return $this->productoActual()?->tipo_producto ?? $this->input('tipo_producto');
    }

    protected function prepareForValidation(): void
    {
        // Los accesorios no tienen precio propio (el extra se cobra con los
        // precios de config/alquiler.php). Siempre quedan en 0.
        if (\App\Models\Producto::esTipoAccesorio($this->tipoProducto())) {
            $this->merge(['precio_alquiler' => 0]);
        }

        // Borla nueva: si el código llegó vacío (p. ej. el navegador no ejecutó
        // el autocompletado), se completa con el que corresponde al color.
        if ($this->esCreacion() && $this->input('tipo_producto') === 'BORLA' && blank($this->input('borla_codigo_color'))) {
            $codigo = app(ProductoService::class)->codigoBorla($this->input('tipo_borla'), $this->input('borla_color'));

            if ($codigo !== null) {
                $this->merge(['borla_codigo_color' => $codigo]);
            }
        }
    }

    /**
     * Los largos máximos coinciden con los de las columnas de la base de datos
     * para que un texto demasiado largo se avise aquí y no como error del servidor.
     */
    public function rules(): array
    {
        $reglas = [
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('productos', 'codigo')->ignore($this->productoActual()?->id),
            ],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio_alquiler' => ['required', 'numeric', 'min:0'],
            'stock_total' => ['required', 'integer', 'min:0'],
            'activo' => ['required', 'boolean'],

            // TOGA
            'tipo_toga' => ['nullable', 'in:ESTANDAR,UNIVERSITARIA'],
            'talla_toga' => ['nullable', 'string', 'max:50'],
            'color_toga' => ['nullable', 'string', 'max:50'],
            'observaciones_toga' => ['nullable', 'string'],

            // CAPA
            'codigo_color_capa' => ['nullable', 'string', 'max:20'],
            'carrera_capa' => ['nullable', Rule::in(array_keys(config('alquiler.carreras_capa', [])))],
            'color_capa' => ['nullable', 'string', 'max:50'],
            'talla_capa' => ['nullable', 'string', 'max:20'],
            'observaciones_capa' => ['nullable', 'string'],

            // BIRRETE
            'tipo_birrete' => ['nullable', 'in:ESTANDAR,NORMAL,UNIVERSITARIO'],
            'color_birrete' => ['nullable', 'string', 'max:50'],
            'observaciones_birrete' => ['nullable', 'string'],

            // COLLARIN
            'tipo_collarin' => ['nullable', 'in:NORMAL,UNIVERSITARIO'],
            'codigo_color_collarin' => ['nullable', 'string', 'max:20'],
            'color_collarin' => ['nullable', Rule::in($this->coloresDeCollarin())],
            'tamano_collarin' => ['nullable', 'in:PEQUENO,GRANDE'],

            // BORLA
            'tipo_borla' => ['nullable', Rule::in(array_keys(config('alquiler.colores_borla_por_tipo', [])))],
            'borla_codigo_color' => ['nullable', 'string', 'max:20'],
            'borla_color' => ['nullable', 'in:Dorado,Rojo,Verde,Rojo-Derecho,Verde-Agronomia,Celeste,Amarillo,Naranja'],
            'borla_observaciones' => ['nullable', 'string'],
        ];

        if ($this->esCreacion()) {
            $reglas['tipo_producto'] = ['required', 'in:TOGA,BIRRETE,COLLARIN,BORLA,CAPA'];
        }

        return $reglas;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $error = match ($this->tipoProducto()) {
                'COLLARIN' => $this->errorDeCollarin(),
                'BIRRETE' => $this->esCreacion() ? $this->errorDeBirrete() : null,
                'CAPA' => $this->esCreacion() ? $this->errorDeCapa() : null,
                'BORLA' => $this->errorDeBorla(),
                default => null,
            };

            if ($error !== null) {
                $validator->errors()->add($error[0], $error[1]);
            }
        });
    }

    /** Todos los colores de collarín que existen (normal y universitario). */
    private function coloresDeCollarin(): array
    {
        return collect(config('alquiler.colores_collarin_por_tipo', []))
            ->flatMap(fn (array $colores) => array_keys($colores))
            ->unique()
            ->values()
            ->all();
    }

    /** @return array{0: string, 1: string}|null [campo, mensaje] */
    private function errorDeCollarin(): ?array
    {
        if (!$this->filled('tipo_collarin')) {
            return ['tipo_collarin', 'Debe seleccionar el tipo de collarín.'];
        }

        if (!$this->filled('color_collarin')) {
            return ['color_collarin', 'Debe seleccionar el color del collarín.'];
        }

        if (!$this->filled('codigo_color_collarin')) {
            return ['codigo_color_collarin', 'Debe indicar el código de color del collarín.'];
        }

        if ($this->input('tipo_collarin') === 'NORMAL' && $this->input('color_collarin') === 'Azul') {
            return ['color_collarin', 'El color azul corresponde únicamente a los collarines universitarios.'];
        }

        if ($this->input('tipo_collarin') === 'UNIVERSITARIO' && $this->input('color_collarin') !== 'Azul') {
            return ['color_collarin', 'Los collarines universitarios deben utilizar el color azul.'];
        }

        return null;
    }

    private function errorDeBirrete(): ?array
    {
        return $this->filled('tipo_birrete')
            ? null
            : ['tipo_birrete', 'Debe seleccionar el tipo de birrete.'];
    }

    private function errorDeCapa(): ?array
    {
        if (!$this->filled('codigo_color_capa') || !$this->filled('color_capa') || !$this->filled('carrera_capa')) {
            return ['carrera_capa', 'Debe indicar la carrera, el código y el color de la capa.'];
        }

        return null;
    }

    private function errorDeBorla(): ?array
    {
        if ($this->esCreacion() && (!$this->filled('borla_codigo_color') || !$this->filled('borla_color'))) {
            return ['borla_color', 'Debe indicar el código y el color de la borla.'];
        }

        // El color debe existir en el tipo elegido (p. ej. no hay borla
        // normal celeste). Los colores antiguos que no están en ninguna
        // lista se aceptan para no impedir guardar productos ya registrados.
        $tipo = $this->input('tipo_borla');
        $color = (string) $this->input('borla_color');
        $porTipo = config('alquiler.colores_borla_por_tipo', []);
        $colorConocido = collect($porTipo)->contains(fn (array $colores) => isset($colores[$color]));

        if ($tipo && $color !== '' && $colorConocido && !isset($porTipo[$tipo][$color])) {
            $nombreTipo = $tipo === 'UNIVERSITARIA' ? 'universitaria' : 'normal';

            return ['borla_color', "No existe borla {$nombreTipo} de color {$color}."];
        }

        return null;
    }
}
