<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MovimientoInventario;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_producto',
        'descripcion',
        'precio_alquiler',
        'stock_total',
        'stock_disponible',
        'stock_alquilado',
        'activo',
    ];

    protected $casts = [
        'precio_alquiler' => 'decimal:2',
        'stock_total' => 'integer',
        'stock_disponible' => 'integer',
        'stock_alquilado' => 'integer',
        'activo' => 'boolean',
    ];

    public function toga()
    {
        return $this->hasOne(ProductoToga::class, 'producto_id');
    }

    public function capa()
    {
        return $this->hasOne(ProductoCapa::class, 'producto_id');
    }

    public function birrete()
    {
        return $this->hasOne(ProductoBirrete::class, 'producto_id');
    }

    public function collarin()
    {
        return $this->hasOne(ProductoCollarin::class, 'producto_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    public function alquilerDetalles()
    {
        return $this->hasMany(AlquilerDetalle::class, 'producto_id');
    }

    public function borla()
    {
        return $this->hasOne(ProductoBorla::class, 'producto_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    /**
     * Collarín, capa, birrete y borla. No tienen precio propio: incluidos se
     * entregan sin costo y, como extra, se cobran con los precios de
     * config/alquiler.php (accessory_types).
     */
    public static function esTipoAccesorio(?string $tipo): bool
    {
        return array_key_exists((string) $tipo, config('alquiler.accessory_types', []));
    }

    public function esAccesorio(): bool
    {
        return self::esTipoAccesorio($this->tipo_producto);
    }
}
