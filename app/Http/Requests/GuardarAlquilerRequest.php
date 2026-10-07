<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del formulario "Nuevo alquiler" y traducción de sus datos a lo
 * que espera AlquilerService::crearAlquiler().
 */
class GuardarAlquilerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Solo se validan las togas marcadas como seleccionadas.
     *
     * IMPORTANTE: el filtrado NO debe hacerse con $request->merge(). Si la
     * validación falla, el formulario se vuelve a pintar con el input original
     * y el blade busca old("productos.{id_del_producto}.campo"); si el input se
     * hubiera reindexado, ninguna clave coincidiría y el formulario se vería
     * vacío. Por eso aquí solo se cambia lo que se valida (validationData) y
     * el input original queda intacto.
     */
    public function validationData(): array
    {
        return array_merge(
            $this->except('productos'),
            ['productos' => $this->productosSeleccionados()]
        );
    }

    private function productosSeleccionados(): array
    {
        return collect($this->input('productos', []))
            ->filter(fn ($producto) => !empty($producto['seleccionado'])
                && !empty($producto['producto_id'])
                && !empty($producto['cantidad']))
            ->values()
            ->toArray();
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'exists:clientes,id'],

            'fecha_alquiler' => ['required', 'string', 'date', 'before_or_equal:fecha_entrega'],
            'fecha_entrega' => ['required', 'string', 'date', 'after_or_equal:fecha_alquiler'],
            'hora_entrega' => ['nullable', 'date_format:H:i'],
            'fecha_devolucion_programada' => ['required', 'string', 'date', 'after_or_equal:fecha_entrega'],
            'hora_devolucion_programada' => ['nullable', 'date_format:H:i'],

            'descuento' => ['nullable', 'numeric', 'min:0'],
            'descuento_por_toga' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],

            'institucion_representada' => ['nullable', 'string', 'max:255'],
            'representante_alquiler' => ['nullable', 'string', 'max:255'],
            'hora_entrega_inicio' => ['nullable', 'date_format:H:i'],
            'hora_entrega_fin' => ['nullable', 'date_format:H:i', 'after_or_equal:hora_entrega_inicio'],
            'fecha_limite_pago_final' => ['nullable', 'date'],

            'fabricacion_autorizada' => ['nullable', 'boolean'],
            'fabricacion_responsable' => ['nullable', 'string', 'max:255'],
            'fabricacion_motivo' => ['nullable', 'string', 'max:255'],
            'fabricacion_observaciones' => ['nullable', 'string', 'max:500'],

            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'exists:productos,id'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
            'productos.*.fabricacion_autorizada' => ['nullable', 'boolean'],

            'productos.*.collarin_id' => ['required', 'exists:productos,id'],
            'productos.*.capa_id' => ['nullable', 'exists:productos,id'],

            'productos.*.birrete_incluido' => ['nullable'],
            'productos.*.birrete_id' => ['nullable', 'exists:productos,id'],

            'productos.*.borla_incluida' => ['nullable'],
            'productos.*.borla_id' => ['nullable', 'exists:productos,id'],

            'productos.*.birrete_extra_id' => ['nullable', 'exists:productos,id'],
            'productos.*.birrete_extra_cantidad' => ['nullable', 'integer', 'min:1'],

            'productos.*.borla_extra_id' => ['nullable', 'exists:productos,id'],
            'productos.*.borla_extra_cantidad' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_alquiler.required' => 'Debes indicar la fecha de reserva.',
            'fecha_alquiler.date' => 'La fecha de reserva no tiene un formato válido.',
            'fecha_alquiler.before_or_equal' => 'La fecha de reserva no puede ser posterior a la fecha de entrega.',

            'fecha_entrega.required' => 'Debes indicar la fecha de entrega.',
            'fecha_entrega.date' => 'La fecha de entrega no tiene un formato válido.',
            'fecha_entrega.after_or_equal' => 'La fecha de entrega no puede ser anterior a la fecha de reserva.',

            'fecha_devolucion_programada.required' => 'Debes indicar la fecha de devolución programada.',
            'fecha_devolucion_programada.date' => 'La fecha de devolución programada no tiene un formato válido.',
            'fecha_devolucion_programada.after_or_equal' => 'La fecha de devolución programada no puede ser anterior a la fecha de entrega.',

            'productos.required' => 'Debes seleccionar al menos una toga.',
            'productos.*.collarin_id.required' => 'Cada toga seleccionada debe tener un collarín obligatorio.',
            'productos.*.birrete_extra_cantidad.min' => 'La cantidad de birretes extra debe ser al menos 1.',
            'productos.*.borla_extra_cantidad.min' => 'La cantidad de borlas extra debe ser al menos 1.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $error = $this->errorDeHorasDelMismoDia()
                ?? $this->errorDeIncluidos($validator->getData()['productos'])
                ?? $this->errorDeExtras($validator->getData()['productos']);

            if ($error !== null) {
                $validator->errors()->add($error[0], $error[1]);
            }
        });
    }

    /** @return array{0: string, 1: string}|null [campo, mensaje] */
    private function errorDeHorasDelMismoDia(): ?array
    {
        if (
            $this->input('fecha_entrega') === $this->input('fecha_devolucion_programada') &&
            $this->input('hora_entrega') &&
            $this->input('hora_devolucion_programada') &&
            $this->input('hora_devolucion_programada') <= $this->input('hora_entrega')
        ) {
            return [
                'hora_devolucion_programada',
                'Si la entrega y devolución son el mismo día, la hora de devolución debe ser posterior a la hora de entrega.',
            ];
        }

        return null;
    }

    /**
     * "Birrete incluido" / "Borla incluida" marcados sin elegir cuál: antes
     * se guardaba el alquiler sin ese accesorio y sin avisar.
     *
     * @return array{0: string, 1: string}|null
     */
    private function errorDeIncluidos(array $productos): ?array
    {
        foreach ($productos as $indice => $producto) {
            $numero = $indice + 1;

            if (!empty($producto['birrete_incluido']) && empty($producto['birrete_id'])) {
                return ['productos', "En la toga seleccionada #{$numero}, marcaste \"Birrete incluido\" pero no elegiste cuál birrete."];
            }

            if (!empty($producto['borla_incluida']) && empty($producto['borla_id'])) {
                return ['productos', "En la toga seleccionada #{$numero}, marcaste \"Borla incluida\" pero no elegiste cuál borla."];
            }
        }

        return null;
    }

    /**
     * Un extra cobrable necesita producto y cantidad a la vez: evita colocar
     * una cantidad sin elegir cuál producto, o elegirlo sin cantidad.
     *
     * @return array{0: string, 1: string}|null
     */
    private function errorDeExtras(array $productos): ?array
    {
        foreach ($productos as $indice => $producto) {
            $numero = $indice + 1;

            $extras = [
                ['birrete', 'un birrete', 'birrete extra', 'qué birrete extra será cobrado'],
                ['borla', 'una borla', 'borla extra', 'qué borla extra será cobrada'],
            ];

            foreach ($extras as [$clave, $articulo, $nombre, $queFalta]) {
                $id = $producto["{$clave}_extra_id"] ?? null;
                $cantidad = $producto["{$clave}_extra_cantidad"] ?? null;

                if (empty($id) && !empty($cantidad)) {
                    return ['productos', "En la toga seleccionada #{$numero}, colocaste cantidad de {$nombre}, pero no seleccionaste {$queFalta}."];
                }

                if (!empty($id) && empty($cantidad)) {
                    return ['productos', "En la toga seleccionada #{$numero}, seleccionaste {$articulo} extra, pero no colocaste la cantidad."];
                }
            }
        }

        return null;
    }

    /**
     * Productos en el formato de AlquilerService::crearAlquiler(): cada toga
     * con sus accesorios (incluidos y extras cobrables).
     */
    public function detallesParaServicio(): array
    {
        $detalles = [];

        foreach ($this->validated()['productos'] as $formulario) {
            // La casilla "Autorizar fabricación" de cada toga cubre la toga y
            // sus accesorios: lo que falte de stock queda como fabricación
            // pendiente en lugar de recortar la cantidad.
            $fabricar = !empty($formulario['fabricacion_autorizada']);

            $accesorio = fn (string $productoId, string $tipo, string $cobro, $cantidad, $precio) => [
                'producto_id' => $productoId,
                'tipo_accesorio' => $tipo,
                'tipo_cobro' => $cobro,
                'cantidad' => $cantidad,
                'fabricar_excedente' => $fabricar,
                'precio_unitario' => $precio,
            ];

            $item = [
                'producto_id' => $formulario['producto_id'],
                'cantidad' => $formulario['cantidad'],
                'fabricar_excedente' => $fabricar,
                'accesorios' => [],
            ];

            $cantidad = $formulario['cantidad'];

            if (!empty($formulario['collarin_id'])) {
                $item['accesorios'][] = $accesorio($formulario['collarin_id'], 'COLLARIN', 'INCLUIDO', $cantidad, 0);
            }

            if (!empty($formulario['capa_id'])) {
                $item['accesorios'][] = $accesorio($formulario['capa_id'], 'CAPA', 'INCLUIDO', $cantidad, 0);
            }

            if (!empty($formulario['birrete_incluido']) && !empty($formulario['birrete_id'])) {
                $item['accesorios'][] = $accesorio($formulario['birrete_id'], 'BIRRETE', 'INCLUIDO', $cantidad, 0);
            }

            if (!empty($formulario['borla_incluida']) && !empty($formulario['borla_id'])) {
                $item['accesorios'][] = $accesorio($formulario['borla_id'], 'BORLA', 'INCLUIDO', $cantidad, 0);
            }

            if (!empty($formulario['birrete_extra_id']) && !empty($formulario['birrete_extra_cantidad'])) {
                $item['accesorios'][] = $accesorio($formulario['birrete_extra_id'], 'BIRRETE', 'EXTRA', $formulario['birrete_extra_cantidad'], null);
            }

            if (!empty($formulario['borla_extra_id']) && !empty($formulario['borla_extra_cantidad'])) {
                $item['accesorios'][] = $accesorio($formulario['borla_extra_id'], 'BORLA', 'EXTRA', $formulario['borla_extra_cantidad'], null);
            }

            $detalles[] = $item;
        }

        return $detalles;
    }

    /**
     * Datos de fabricación a registrar, o [] si no se autorizó ninguna.
     */
    public function datosDeFabricacion(): array
    {
        $datos = $this->validated();

        $algunaTogaAutorizada = collect($datos['productos'])
            ->contains(fn ($producto) => !empty($producto['fabricacion_autorizada']));

        if (empty($datos['fabricacion_autorizada']) && !$algunaTogaAutorizada) {
            return [];
        }

        return [
            'responsable' => $datos['fabricacion_responsable'] ?? null,
            'motivo' => $datos['fabricacion_motivo'] ?? null,
            'observaciones' => $datos['fabricacion_observaciones'] ?? null,
            'usuario_id' => null,
            'fecha' => now()->toDateString(),
        ];
    }
}
