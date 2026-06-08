<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoBirrete extends Model
{
    use HasFactory;

    protected $table = 'producto_birretes';

    protected $fillable = [
        'producto_id',
        'tipo_birrete',
        'color',
        'carrera',
        'tiene_borlas_extra',
        'descripcion_borlas_extra',
    ];

    protected $casts = [
        'tiene_borlas_extra' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}