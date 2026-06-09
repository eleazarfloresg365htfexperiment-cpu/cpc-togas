<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index()
    {
        return view('calendario.index');
    }

    public function eventos(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        if (!$start || !$end) {
            return response()->json([]);
        }

        $fechaInicio = Carbon::parse($start)->startOfDay();
        $fechaFin = Carbon::parse($end)->endOfDay();

        $alquileres = Alquiler::with('cliente')
            ->whereNotNull('fecha_entrega')
            ->whereNotNull('fecha_devolucion_programada')
            ->whereDate('fecha_devolucion_programada', '>=', $fechaInicio)
            ->whereDate('fecha_entrega', '<=', $fechaFin)
            ->orderBy('fecha_entrega')
            ->get();

        $eventos = [];

        foreach ($alquileres as $alquiler) {
            $inicio = $this->crearFechaHora(
                $alquiler->fecha_entrega,
                $alquiler->hora_entrega
            );

            $fin = $this->crearFechaHora(
                $alquiler->fecha_devolucion_programada,
                $alquiler->hora_devolucion_programada
            );

            if (!$inicio || !$fin) {
                continue;
            }

            if ($fin->lessThanOrEqualTo($inicio)) {
                continue;
            }

            $cliente = $alquiler->cliente
                ? trim($alquiler->cliente->nombres . ' ' . $alquiler->cliente->apellidos)
                : 'Cliente no registrado';

            $codigo = $alquiler->codigo_recibo ?? 'Sin recibo';

            $eventos[] = [
                'id' => $alquiler->id,
                'title' => $this->codigoCorto($codigo),
                'start' => $inicio->format('Y-m-d\TH:i:s'),
                'end' => $fin->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'backgroundColor' => $this->colorEstado($alquiler->estado),
                'borderColor' => $this->colorEstado($alquiler->estado),
                'textColor' => '#ffffff',
                'url' => url('/alquileres-web/' . $alquiler->id),
                'extendedProps' => [
                    'alquiler_id' => $alquiler->id,
                    'codigo_recibo' => $codigo,
                    'cliente' => $cliente,
                    'estado' => $alquiler->estado,
                    'estado_pago' => $alquiler->estado_pago,
                    'saldo_pendiente' => $alquiler->saldo_pendiente,
                    'fecha_entrega' => $alquiler->fecha_entrega,
                    'hora_entrega' => $alquiler->hora_entrega,
                    'fecha_devolucion_programada' => $alquiler->fecha_devolucion_programada,
                    'hora_devolucion_programada' => $alquiler->hora_devolucion_programada,
                ],
            ];
        }

        return response()->json($eventos);
    }

    private function crearFechaHora($fecha, $hora): ?Carbon
    {
        if (!$fecha) {
            return null;
        }

        $fechaFormato = Carbon::parse($fecha)->toDateString();

        if ($hora) {
            return Carbon::parse($fechaFormato . ' ' . $hora);
        }

        return Carbon::parse($fechaFormato . ' 08:00:00');
    }

    private function codigoCorto(string $codigo): string
    {
        return str_replace('REC-2026-', 'REC-', $codigo);
    }

    private function colorEstado(?string $estado): string
    {
        return match ($estado) {
            'RESERVADO' => '#0d6efd',
            'ENTREGADO' => '#fd7e14',
            'DEVUELTO' => '#198754',
            'CANCELADO' => '#6c757d',
            default => '#0d6efd',
        };
    }
}