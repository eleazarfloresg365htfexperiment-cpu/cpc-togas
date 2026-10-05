<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlquilerHistorial extends Model
{
    protected $table = 'alquiler_historial';

    protected $fillable = [
        'alquiler_id',
        'accion',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'motivo',
        'responsable',
        'usuario_id',
    ];

    /**
     * Nombre legible de cada campo editable, para mostrarlo en el historial.
     */
    public const ETIQUETAS_CAMPOS = [
        'estado' => 'Estado',
        'fecha_entrega' => 'Fecha de entrega',
        'hora_entrega' => 'Hora de entrega',
        'fecha_devolucion_programada' => 'Fecha de devolución',
        'hora_devolucion_programada' => 'Hora de devolución',
        'hora_entrega_inicio' => 'Recogida desde',
        'hora_entrega_fin' => 'Recogida hasta',
        'institucion_representada' => 'Institución representada',
        'representante_alquiler' => 'Representante del alquiler',
        'fecha_limite_pago_final' => 'Fecha límite de pago final',
        'observaciones' => 'Observaciones',
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'alquiler_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getEtiquetaCampoAttribute(): ?string
    {
        if (!$this->campo) {
            return null;
        }

        return self::ETIQUETAS_CAMPOS[$this->campo] ?? $this->campo;
    }
}
