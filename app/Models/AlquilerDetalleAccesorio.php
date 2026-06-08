<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlquilerDetalleAccesorio extends Model
{
    protected $table = 'alquiler_detalle_accesorios';

    protected $fillable = [
        'alquiler_detalle_id',
        'producto_id',
        'tipo_accesorio',
        'tipo_cobro',
        'cantidad',
        'precio_unitario',
        'total_linea',
        'observaciones',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'total_linea' => 'decimal:2',
    ];

    public function detalle()
    {
        return $this->belongsTo(AlquilerDetalle::class, 'alquiler_detalle_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}