<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlquilerDetalle extends Model
{
    use HasFactory;

    protected $table = 'alquiler_detalles';

    protected $fillable = [
        'alquiler_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'estado',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'alquiler_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function accesorios()
    {
        return $this->hasMany(AlquilerDetalleAccesorio::class, 'alquiler_detalle_id');
    }

    /**
     * Resumen de una toga del alquiler para "Detalles rápidos" (detalle y
     * recibo): talla, birretes, borlas y carrera.
     *
     * Necesita cargadas las relaciones producto.toga y
     * accesorios.producto.(birrete|borla|capa).
     *
     * @return array{talla: string, birretes: string, borlas: string, carrera: ?string}
     */
    public function resumenRapido(): array
    {
        $accesorios = collect($this->accesorios ?? []);
        $deTipo = fn (string $tipo) => $accesorios->filter(
            fn ($accesorio) => ($accesorio->producto->tipo_producto ?? null) === $tipo
        );
        $cantidad = fn ($accesorio) => ' x' . $accesorio->cantidad
            . ($accesorio->tipo_cobro === 'EXTRA' ? ' (extra)' : '');

        $birretes = $deTipo('BIRRETE')->map(function ($accesorio) use ($cantidad) {
            $tipo = $accesorio->producto->birrete->tipo_birrete ?? null;
            $nombreTipo = $tipo === 'UNIVERSITARIO' ? 'Universitario' : 'Normal';

            return ($accesorio->producto->nombre ?? 'Birrete') . " ({$nombreTipo})" . $cantidad($accesorio);
        })->implode(', ');

        $borlas = $deTipo('BORLA')->map(function ($accesorio) use ($cantidad) {
            $borla = $accesorio->producto->borla ?? null;
            $nombreTipo = ($borla->tipo_borla ?? 'NORMAL') === 'UNIVERSITARIA' ? 'Universitaria' : 'Normal';
            $color = $borla->color ?? null;

            return ($accesorio->producto->nombre ?? 'Borla')
                . ' (' . ($color ? "{$color} · " : '') . $nombreTipo . ')'
                . $cantidad($accesorio);
        })->implode(', ');

        // La carrera sale de la capa incluida (solo togas universitarias).
        $capa = $deTipo('CAPA')->first()?->producto?->capa;
        $carrera = $capa?->carrera
            ? (config("alquiler.carreras_capa.{$capa->carrera}.nombre") ?? $capa->carrera)
            : null;

        return [
            'talla' => $this->producto?->toga?->talla ?? 'N/A',
            'birretes' => $birretes,
            'borlas' => $borlas,
            'carrera' => $carrera,
        ];
    }
}
