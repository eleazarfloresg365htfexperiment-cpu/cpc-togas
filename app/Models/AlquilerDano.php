<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlquilerDano extends Model
{
    protected $table = 'alquiler_danos';

    protected $fillable = [
        'alquiler_id',
        'producto_id',
        'tipo',
        'cantidad',
        'monto',
        'descripcion',
        'responsable',
        'usuario_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'monto' => 'decimal:2',
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'alquiler_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getTipoTextoAttribute(): string
    {
        return $this->tipo === 'EXTRAVIO' ? 'Extravío' : 'Daño';
    }
}
