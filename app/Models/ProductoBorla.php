<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoBorla extends Model
{
    protected $table = 'producto_borlas';

    protected $fillable = [
        'producto_id',
        'codigo_color',
        'color',
        'observaciones',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}