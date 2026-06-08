<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Producto;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'stock_anterior_disponible',
        'stock_nuevo_disponible',
        'stock_anterior_alquilado',
        'stock_nuevo_alquilado',
        'motivo',
        'referencia',
        'usuario_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior_disponible' => 'integer',
        'stock_nuevo_disponible' => 'integer',
        'stock_anterior_alquilado' => 'integer',
        'stock_nuevo_alquilado' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}