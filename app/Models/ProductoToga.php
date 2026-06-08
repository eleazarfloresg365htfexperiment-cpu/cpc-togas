<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoToga extends Model
{
    use HasFactory;

    protected $table = 'producto_togas';

    protected $fillable = [
        'producto_id',
        'talla',
        'color',
        'observaciones',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}