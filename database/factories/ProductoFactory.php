<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        $tipos = ['TOGA', 'BIRRETE', 'COLLARIN', 'BORLA', 'CAPA'];

        return [
            'codigo' => $this->faker->unique()->bothify('P-###??'),
            'nombre' => $this->faker->words(3, true),
            'tipo_producto' => $this->faker->randomElement($tipos),
            'descripcion' => $this->faker->sentence(),
            'precio_alquiler' => $this->faker->randomFloat(2, 10, 200),
            'stock_total' => 10,
            'stock_disponible' => 10,
            'stock_alquilado' => 0,
            'activo' => true,
        ];
    }
}
