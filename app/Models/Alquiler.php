<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Cliente;
use App\Models\AlquilerDetalle;

class Alquiler extends Model
{
    use HasFactory;

    protected $table = 'alquileres';

    protected $fillable = [
        'cliente_id',
        'codigo_recibo',

        'institucion_representada',
        'representante_alquiler',

        'fecha_alquiler',
        'fecha_entrega',
        'hora_entrega_inicio',
        'hora_entrega_fin',

        'fecha_devolucion_programada',
        'fecha_devolucion_real',

        'estado',
        'estado_pago',

        'subtotal',
        'descuento',
        'total',
        'saldo_pendiente',
        'fecha_limite_pago_final',

        'observaciones',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_alquiler' => 'date',
        'fecha_entrega' => 'date',
        'fecha_devolucion_programada' => 'date',
        'fecha_devolucion_real' => 'date',
        'fecha_limite_pago_final' => 'date',

        'hora_entrega_inicio' => 'datetime:H:i',
        'hora_entrega_fin' => 'datetime:H:i',

        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles()
    {
        return $this->hasMany(AlquilerDetalle::class, 'alquiler_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'alquiler_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}