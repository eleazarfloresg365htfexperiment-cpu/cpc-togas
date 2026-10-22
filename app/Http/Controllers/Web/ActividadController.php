<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\User;
use App\Services\ActividadesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Módulo Actividades: agenda, togas por talla, cobros y bitácora.
 * Reemplaza a la antigua pantalla "Control de alquileres".
 */
class ActividadController extends Controller
{
    /** Rango máximo de la agenda personalizada (dos meses). */
    private const MAX_DIAS_AGENDA = 62;

    public const PERIODOS = [
        'semana' => 'Esta semana',
        'proxima_semana' => 'Próxima semana',
        'mes' => 'Este mes',
        'proximo_mes' => 'Próximo mes',
        'personalizado' => 'Personalizado',
    ];

    public function agenda(Request $request, ActividadesService $actividades)
    {
        [$periodo, $desde, $hasta] = $this->rango($request);

        return view('actividades.agenda', [
            'periodo' => $periodo,
            'desde' => $desde,
            'hasta' => $hasta,
            'hoy' => CarbonImmutable::today(),
            'agenda' => $actividades->agenda($desde, $hasta),
        ]);
    }

    public function agendaPdf(Request $request, ActividadesService $actividades)
    {
        [$periodo, $desde, $hasta] = $this->rango($request);

        $pdf = Pdf::loadView('actividades.agenda-pdf', [
            'periodo' => $periodo,
            'desde' => $desde,
            'hasta' => $hasta,
            'hoy' => CarbonImmutable::today(),
            'agenda' => $actividades->agenda($desde, $hasta),
            'generadoPor' => auth()->user()?->nombre_corto,
        ])->setPaper('letter');

        return $pdf->stream('agenda_' . $desde->format('Y-m-d') . '_al_' . $hasta->format('Y-m-d') . '.pdf');
    }

    public function inventario(Request $request, ActividadesService $actividades)
    {
        $hoy = CarbonImmutable::today();
        $fecha = $this->fecha($request->input('fecha')) ?? $hoy;

        return view('actividades.inventario', [
            'fecha' => $fecha,
            'hoy' => $hoy,
            'tallas' => $actividades->togasPorTalla($fecha),
            'accesorios' => $actividades->accesorios(),
        ]);
    }

    public function cobros(ActividadesService $actividades)
    {
        return view('actividades.cobros', [
            'hoy' => CarbonImmutable::today(),
            'alquileres' => $actividades->porCobrar(),
        ]);
    }

    public function bitacora(Request $request)
    {
        $desde = $this->fecha($request->input('desde'));
        $hasta = $this->fecha($request->input('hasta'));

        $registros = Bitacora::query()
            ->with(['usuario', 'alquiler'])
            ->when($request->filled('usuario'), fn ($q) => $q->where('usuario_id', $request->integer('usuario')))
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->input('categoria')))
            ->when($desde, fn ($q) => $q->where('created_at', '>=', $desde->startOfDay()))
            ->when($hasta, fn ($q) => $q->where('created_at', '<=', $hasta->endOfDay()))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $texto = '%' . $request->input('buscar') . '%';
                $q->where(fn ($q) => $q->where('descripcion', 'like', $texto)->orWhere('accion', 'like', $texto));
            })
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('actividades.bitacora', [
            'registros' => $registros,
            'usuarios' => User::orderBy('nombres')->get(),
            'categorias' => Bitacora::CATEGORIAS,
        ]);
    }

    /** @return array{0: string, 1: CarbonImmutable, 2: CarbonImmutable} */
    private function rango(Request $request): array
    {
        $hoy = CarbonImmutable::today();
        $periodo = array_key_exists($request->input('periodo'), self::PERIODOS) ? $request->input('periodo') : 'semana';

        [$desde, $hasta] = match ($periodo) {
            'proxima_semana' => [$hoy->addWeek()->startOfWeek(CarbonImmutable::MONDAY), $hoy->addWeek()->endOfWeek(CarbonImmutable::SUNDAY)],
            'mes' => [$hoy->startOfMonth(), $hoy->endOfMonth()],
            'proximo_mes' => [$hoy->addMonthNoOverflow()->startOfMonth(), $hoy->addMonthNoOverflow()->endOfMonth()],
            'personalizado' => [
                $this->fecha($request->input('desde')) ?? $hoy,
                $this->fecha($request->input('hasta')) ?? $hoy->addDays(6),
            ],
            default => [$hoy->startOfWeek(CarbonImmutable::MONDAY), $hoy->endOfWeek(CarbonImmutable::SUNDAY)],
        };

        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        if ($desde->diffInDays($hasta) > self::MAX_DIAS_AGENDA) {
            $hasta = $desde->addDays(self::MAX_DIAS_AGENDA);
        }

        return [$periodo, $desde->startOfDay(), $hasta->startOfDay()];
    }

    private function fecha(?string $valor): ?CarbonImmutable
    {
        if (!$valor || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $valor)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
