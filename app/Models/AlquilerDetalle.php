<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlquilerDetalle extends Model
{
    use HasFactory;

    protected $table = 'alquiler_detalles';

    protected $fillable = [
        'alquiler_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'estado',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'alquiler_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function accesorios()
    {
        return $this->hasMany(AlquilerDetalleAccesorio::class, 'alquiler_detalle_id');
    }
}