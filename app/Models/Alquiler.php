<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Cliente;
use App\Models\AlquilerDetalle;
use App\Models\AlquilerFabricacion;

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
        'hora_entrega',
        'hora_entrega_inicio',
        'hora_entrega_fin',

        'fecha_devolucion_programada',
        'fecha_devolucion_real',
        'hora_devolucion_programada',

        'fecha_hora_devolucion_real',
        'dias_mora',
        'monto_mora_calculado',
        'descuento_mora',
        'monto_mora',
        'observacion_mora',

        'estado',
        'estado_pago',

        'subtotal',
        'descuento',
        'descuento_manual',
        'descuento_toga',
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
        'fecha_hora_devolucion_real' => 'datetime',
        'fecha_limite_pago_final' => 'date',

        'hora_entrega_inicio' => 'datetime:H:i',
        'hora_entrega_fin' => 'datetime:H:i',

        'dias_mora' => 'integer',

        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'descuento_manual' => 'decimal:2',
        'descuento_toga' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_mora_calculado' => 'decimal:2',
        'descuento_mora' => 'decimal:2',
        'monto_mora' => 'decimal:2',
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

    public function fabricaciones()
    {
        return $this->hasMany(AlquilerFabricacion::class, 'alquiler_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'alquiler_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function canTransitionTo(string $estado): bool
    {
        $states = config('alquiler.states', []);

        if (!isset($states[$this->estado])) {
            return false;
        }

        return in_array($estado, $states[$this->estado]['next'] ?? [], true);
    }

    public function isEntregable(): bool
    {
        return $this->estado === 'LISTO_PARA_ENTREGA' || $this->estado === 'RESERVADO';
    }
}