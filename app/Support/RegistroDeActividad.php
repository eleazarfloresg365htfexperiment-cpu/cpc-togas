<?php

namespace App\Support;

use App\Models\Alquiler;
use App\Models\Bitacora;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Anota en la bitácora lo que hace cada usuario.
 *
 *   RegistroDeActividad::anotar('PAGO', 'Registró un pago', 'Q150.00 en efectivo', $alquiler);
 *
 * Si por algún motivo no se puede escribir la bitácora, la acción del
 * usuario NO falla: solo se deja constancia en el log.
 */
class RegistroDeActividad
{
    public static function anotar(string $categoria, string $accion, string $descripcion, ?Alquiler $alquiler = null): void
    {
        try {
            Bitacora::create([
                'usuario_id' => auth()->id(),
                'categoria' => $categoria,
                'accion' => $accion,
                'descripcion' => $descripcion,
                'alquiler_id' => $alquiler?->id,
            ]);
        } catch (Throwable $e) {
            Log::warning('No se pudo escribir la bitácora: ' . $e->getMessage());
        }
    }

    /** "REC-2026-000123 (María López)" para las descripciones. */
    public static function alquiler(Alquiler $alquiler): string
    {
        $alquiler->loadMissing('cliente');
        $cliente = trim(($alquiler->cliente->nombres ?? '') . ' ' . ($alquiler->cliente->apellidos ?? ''));

        return $alquiler->codigo_recibo . ($cliente !== '' ? " ({$cliente})" : '');
    }
}
