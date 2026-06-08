<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoCollarin extends Model
{
    use HasFactory;

    protected $table = 'producto_collarines';

    protected $fillable = [
        'producto_id',
        'tipo_collarin',
        'color',
        'tamano',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}