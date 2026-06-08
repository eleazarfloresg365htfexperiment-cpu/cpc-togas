<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'alquiler_id',
        'monto',
        'metodo_pago',
        'referencia',
        'observaciones',
        'usuario_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'alquiler_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}