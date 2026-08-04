<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoCapa extends Model
{
    use HasFactory;

    protected $table = 'producto_capas';

    protected $fillable = [
        'producto_id',
        'talla',
        'codigo_color',
        'color',
        'carrera',
        'observaciones',
    ];

    /**
     * Producto principal.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}