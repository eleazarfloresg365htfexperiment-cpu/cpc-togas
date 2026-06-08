<?php

namespace App\Services;

use App\Models\Alquiler;
use Illuminate\Support\Facades\DB;

class ReciboService
{
    public function generarCodigoRecibo(): string
    {
        return DB::transaction(function () {
            $anio = now()->year;

            $ultimoAlquiler = Alquiler::where('codigo_recibo', 'LIKE', "REC-{$anio}-%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            if (!$ultimoAlquiler) {
                $siguienteNumero = 1;
            } else {
                $partes = explode('-', $ultimoAlquiler->codigo_recibo);
                $ultimoNumero = (int) end($partes);
                $siguienteNumero = $ultimoNumero + 1;
            }

            return 'REC-' . $anio . '-' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
        });
    }
}