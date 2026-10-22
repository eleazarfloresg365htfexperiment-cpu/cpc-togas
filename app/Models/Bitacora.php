<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un registro de la bitácora (ver App\Support\RegistroDeActividad).
 */
class Bitacora extends Model
{
    protected $table = 'bitacora';

    public const UPDATED_AT = null;

    public const CATEGORIAS = [
        'ALQUILER' => 'Alquileres',
        'PAGO' => 'Pagos',
        'INVENTARIO' => 'Inventario',
        'PRODUCTO' => 'Productos',
        'CLIENTE' => 'Clientes',
        'USUARIO' => 'Usuarios',
        'SESION' => 'Inicio de sesión',
    ];

    protected $fillable = [
        'usuario_id',
        'categoria',
        'accion',
        'descripcion',
        'alquiler_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'alquiler_id');
    }
}
