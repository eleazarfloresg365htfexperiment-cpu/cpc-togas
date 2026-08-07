<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlquilerFabricacion extends Model
{
    protected $table = 'alquiler_fabricaciones';

    protected $fillable = [
        'alquiler_id',
        'alquiler_detalle_id',
        'producto_id',
        'cantidad_solicitada',
        'cantidad_pendiente',
        'responsable',
        'motivo',
        'observaciones',
        'fecha',
        'estado',
        'usuario_id',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'integer',
        'cantidad_pendiente' => 'integer',
        'fecha' => 'date',
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'alquiler_id');
    }

    public function detalle()
    {
        return $this->belongsTo(AlquilerDetalle::class, 'alquiler_detalle_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function isPending(): bool
    {
        return $this->cantidad_pendiente > 0 && $this->estado !== 'COMPLETADO';
    }
}
