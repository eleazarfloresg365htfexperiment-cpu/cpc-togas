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
        $vista = $request->query('vista', 'dayGridMonth');

        if (!$start || !$end) {
            return response()->json([]);
        }

        $fechaInicio = Carbon::parse($start)->startOfDay();
        $fechaFinExclusiva = Carbon::parse($end)->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Vista Día
        |--------------------------------------------------------------------------
        | La pestaña Día usa listDay.
        | En esta vista NO se muestra ocupación, sino agenda operativa.
        */
        if ($vista === 'listDay') {
            return response()->json(
                $this->eventosOperativosDelDia($fechaInicio)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Vista Mes / Semana
        |--------------------------------------------------------------------------
        | Aquí se muestra ocupación completa del alquiler.
        */
        return response()->json(
            $this->eventosOcupacion($fechaInicio, $fechaFinExclusiva)
        );
    }

    private function eventosOcupacion(Carbon $fechaInicio, Carbon $fechaFinExclusiva): array
    {
        $alquileres = Alquiler::with('cliente')
            ->whereNotNull('fecha_entrega')
            ->whereNotNull('fecha_devolucion_programada')
            ->whereDate('fecha_devolucion_programada', '>=', $fechaInicio->toDateString())
            ->whereDate('fecha_entrega', '<', $fechaFinExclusiva->toDateString())
            ->where('estado', '!=', 'CANCELADO')
            ->orderBy('fecha_entrega')
            ->get();

        $eventos = [];

        foreach ($alquileres as $alquiler) {
            $inicio = $alquiler->fecha_entrega
                ? Carbon::parse($alquiler->fecha_entrega)->startOfDay()
                : null;

            $fin = $alquiler->fecha_devolucion_programada
                ? Carbon::parse($alquiler->fecha_devolucion_programada)->addDay()->startOfDay()
                : null;

            if (!$inicio || !$fin) {
                continue;
            }

            if ($fin->lessThanOrEqualTo($inicio)) {
                $fin = $inicio->copy()->addDay();
            }

            $codigo = $alquiler->codigo_recibo ?? 'Sin recibo';

            $eventos[] = $this->crearEventoBase($alquiler, [
                'id' => 'ocupacion-' . $alquiler->id,
                'title' => '📌 ' . $this->codigoCorto($codigo),
                'start' => $inicio->format('Y-m-d'),
                'end' => $fin->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => $this->colorEstado($alquiler->estado),
                'borderColor' => $this->colorEstado($alquiler->estado),
                'tipo_evento' => 'OCUPACION',
                'tipo_evento_texto' => 'Ocupación del alquiler',
            ]);
        }

        return $eventos;
    }

    private function eventosOperativosDelDia(Carbon $dia): array
    {
        $fecha = $dia->toDateString();
        $eventos = [];

        /*
        |--------------------------------------------------------------------------
        | Entregas programadas del día
        |--------------------------------------------------------------------------
        */
        $entregas = Alquiler::with('cliente')
            ->whereDate('fecha_entrega', $fecha)
            ->where('estado', '!=', 'CANCELADO')
            ->orderBy('hora_entrega')
            ->orderBy('id')
            ->get();

        foreach ($entregas as $alquiler) {
            $codigo = $alquiler->codigo_recibo ?? 'Sin recibo';
            $horaTexto = $this->formatearHora($alquiler->hora_entrega);

            $eventos[] = $this->crearEventoBase($alquiler, [
                'id' => 'entrega-' . $alquiler->id,
                'title' => '🚚 Entrega' . ($horaTexto ? ' ' . $horaTexto : '') . ' - ' . $this->codigoCorto($codigo),
                'start' => $this->crearFechaHora($alquiler->fecha_entrega, $alquiler->hora_entrega)->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'backgroundColor' => '#0d6efd',
                'borderColor' => '#0d6efd',
                'tipo_evento' => 'ENTREGA',
                'tipo_evento_texto' => 'Entrega programada',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Devoluciones programadas del día
        |--------------------------------------------------------------------------
        */
        $devoluciones = Alquiler::with('cliente')
            ->whereDate('fecha_devolucion_programada', $fecha)
            ->whereNotIn('estado', ['CANCELADO', 'DEVUELTO'])
            ->orderBy('hora_devolucion_programada')
            ->orderBy('id')
            ->get();

        foreach ($devoluciones as $alquiler) {
            $codigo = $alquiler->codigo_recibo ?? 'Sin recibo';
            $horaTexto = $this->formatearHora($alquiler->hora_devolucion_programada);

            $eventos[] = $this->crearEventoBase($alquiler, [
                'id' => 'devolucion-' . $alquiler->id,
                'title' => '📦 Devolución' . ($horaTexto ? ' ' . $horaTexto : '') . ' - ' . $this->codigoCorto($codigo),
                'start' => $this->crearFechaHora($alquiler->fecha_devolucion_programada, $alquiler->hora_devolucion_programada)->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'backgroundColor' => '#fd7e14',
                'borderColor' => '#fd7e14',
                'tipo_evento' => 'DEVOLUCION',
                'tipo_evento_texto' => 'Devolución programada',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fechas límite de pago final
        |--------------------------------------------------------------------------
        */
        $pagosLimite = Alquiler::with('cliente')
            ->whereDate('fecha_limite_pago_final', $fecha)
            ->where('estado', '!=', 'CANCELADO')
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('id')
            ->get();

        foreach ($pagosLimite as $alquiler) {
            $codigo = $alquiler->codigo_recibo ?? 'Sin recibo';

            $eventos[] = $this->crearEventoBase($alquiler, [
                'id' => 'pago-limite-' . $alquiler->id,
                'title' => '💰 Pago límite - ' . $this->codigoCorto($codigo) . ' | Q' . number_format((float) $alquiler->saldo_pendiente, 2),
                'start' => Carbon::parse($fecha . ' 09:00:00')->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'backgroundColor' => '#198754',
                'borderColor' => '#198754',
                'tipo_evento' => 'PAGO_LIMITE',
                'tipo_evento_texto' => 'Fecha límite de pago',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Alquileres atrasados
        |--------------------------------------------------------------------------
        | Aparecen cuando ya pasó la fecha de devolución programada y siguen
        | en estado ENTREGADO.
        */
        $atrasados = Alquiler::with('cliente')
            ->whereDate('fecha_devolucion_programada', '<', $fecha)
            ->where('estado', 'ENTREGADO')
            ->orderBy('fecha_devolucion_programada')
            ->get();

        foreach ($atrasados as $alquiler) {
            $codigo = $alquiler->codigo_recibo ?? 'Sin recibo';

            $diasAtraso = Carbon::parse($alquiler->fecha_devolucion_programada)
                ->startOfDay()
                ->diffInDays($dia->copy()->startOfDay());

            $eventos[] = $this->crearEventoBase($alquiler, [
                'id' => 'atrasado-' . $alquiler->id,
                'title' => '⚠️ Atrasado ' . $diasAtraso . ' día' . ($diasAtraso == 1 ? '' : 's') . ' - ' . $this->codigoCorto($codigo),
                'start' => Carbon::parse($fecha . ' 10:00:00')->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'backgroundColor' => '#dc3545',
                'borderColor' => '#dc3545',
                'tipo_evento' => 'ATRASADO',
                'tipo_evento_texto' => 'Devolución atrasada',
            ]);
        }

        return $eventos;
    }

    private function crearEventoBase(Alquiler $alquiler, array $datos): array
    {
        $cliente = $alquiler->cliente
            ? trim($alquiler->cliente->nombres . ' ' . $alquiler->cliente->apellidos)
            : 'Cliente no registrado';

        $codigo = $alquiler->codigo_recibo ?? 'Sin recibo';

        return [
            'id' => $datos['id'],
            'title' => $datos['title'],
            'start' => $datos['start'],
            'end' => $datos['end'] ?? null,
            'allDay' => $datos['allDay'] ?? false,
            'backgroundColor' => $datos['backgroundColor'],
            'borderColor' => $datos['borderColor'],
            'textColor' => '#ffffff',
            'url' => url('/alquileres-web/' . $alquiler->id),
            'extendedProps' => [
                'tipo_evento' => $datos['tipo_evento'],
                'tipo_evento_texto' => $datos['tipo_evento_texto'],
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
                'fecha_limite_pago_final' => $alquiler->fecha_limite_pago_final,
            ],
        ];
    }

    private function crearFechaHora($fecha, $hora): Carbon
    {
        $fechaFormato = Carbon::parse($fecha)->toDateString();

        if ($hora) {
            return Carbon::parse($fechaFormato . ' ' . $hora);
        }

        return Carbon::parse($fechaFormato . ' 08:00:00');
    }

    private function formatearHora($hora): ?string
    {
        if (!$hora) {
            return null;
        }

        return Carbon::parse($hora)->format('h:i A');
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