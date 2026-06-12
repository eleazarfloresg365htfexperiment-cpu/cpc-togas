<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\AlquilerDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ControlAlquilerController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::today();

        $filtro = $request->input('filtro', 'vigentes');
        $busqueda = trim($request->input('busqueda', ''));

        /*
        |--------------------------------------------------------------------------
        | Tarjetas superiores
        |--------------------------------------------------------------------------
        */

        $reservasHoy = Alquiler::whereDate('fecha_alquiler', $hoy)->count();

        $alquileresVigentes = Alquiler::whereIn('estado', ['RESERVADO', 'ENTREGADO'])->count();

        $noDevueltos = Alquiler::where('estado', 'ENTREGADO')->count();

        $atrasados = Alquiler::where('estado', 'ENTREGADO')
            ->whereDate('fecha_devolucion_programada', '<', $hoy)
            ->count();

        $entregasHoy = Alquiler::whereDate('fecha_entrega', $hoy)
            ->where('estado', 'RESERVADO')
            ->count();

        $entregadosHoy = Alquiler::whereDate('fecha_entrega', $hoy)
            ->where('estado', 'ENTREGADO')
            ->count();

        $devolucionesHoy = Alquiler::whereDate('fecha_devolucion_programada', $hoy)
            ->where('estado', 'ENTREGADO')
            ->count();

        $devueltosHoy = Alquiler::whereDate('fecha_devolucion_real', $hoy)
            ->where('estado', 'DEVUELTO')
            ->count();

        $saldoPendienteTotal = Alquiler::where('estado', '!=', 'CANCELADO')
            ->sum('saldo_pendiente');

        $togasFuera = AlquilerDetalle::query()
            ->join('alquileres', 'alquiler_detalles.alquiler_id', '=', 'alquileres.id')
            ->join('productos', 'alquiler_detalles.producto_id', '=', 'productos.id')
            ->where('alquileres.estado', 'ENTREGADO')
            ->where('productos.tipo_producto', 'TOGA')
            ->sum('alquiler_detalles.cantidad');

        /*
        |--------------------------------------------------------------------------
        | Consulta principal para tabla
        |--------------------------------------------------------------------------
        */

        $alquileresQuery = Alquiler::query()
            ->with(['cliente', 'detalles.producto'])
            ->where('estado', '!=', 'CANCELADO');

        if ($busqueda !== '') {
            $alquileresQuery->where(function ($query) use ($busqueda) {
                $query->where('codigo_recibo', 'LIKE', "%{$busqueda}%")
                    ->orWhereHas('cliente', function ($clienteQuery) use ($busqueda) {
                        $clienteQuery->where('nombres', 'LIKE', "%{$busqueda}%")
                            ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                            ->orWhere('telefono', 'LIKE', "%{$busqueda}%")
                            ->orWhere('dpi', 'LIKE', "%{$busqueda}%");
                    })
                    ->orWhere('institucion_representada', 'LIKE', "%{$busqueda}%");
            });
        }

        match ($filtro) {
            'todos' => $alquileresQuery,

            'reservados' => $alquileresQuery->where('estado', 'RESERVADO'),

            'entregados' => $alquileresQuery->where('estado', 'ENTREGADO'),

            'atrasados' => $alquileresQuery
                ->where('estado', 'ENTREGADO')
                ->whereDate('fecha_devolucion_programada', '<', $hoy),

            'pendientes_pago' => $alquileresQuery
                ->whereIn('estado_pago', ['PENDIENTE', 'PARCIAL']),

            'pagados' => $alquileresQuery
                ->where('estado_pago', 'PAGADO'),

            'entregas_hoy' => $alquileresQuery
                ->whereDate('fecha_entrega', $hoy)
                ->where('estado', 'RESERVADO'),

            'entregados_hoy' => $alquileresQuery
                ->whereDate('fecha_entrega', $hoy)
                ->where('estado', 'ENTREGADO'),

            'devoluciones_hoy' => $alquileresQuery
                ->whereDate('fecha_devolucion_programada', $hoy)
                ->where('estado', 'ENTREGADO'),
            
            'devueltos_hoy' => $alquileresQuery
                ->whereDate('fecha_devolucion_real', $hoy)
                ->where('estado', 'DEVUELTO'),

            default => $alquileresQuery->whereIn('estado', ['RESERVADO', 'ENTREGADO']),
        };

        $alquileres = $alquileresQuery
            ->orderByRaw("
                CASE 
                    WHEN estado = 'ENTREGADO' AND fecha_devolucion_programada < CURDATE() THEN 1
                    WHEN estado = 'ENTREGADO' THEN 2
                    WHEN estado = 'RESERVADO' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('fecha_devolucion_programada')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Resumen de togas fuera por talla
        |--------------------------------------------------------------------------
        */

        $togasFueraPorTalla = AlquilerDetalle::query()
            ->select(
                'producto_togas.talla',
                DB::raw('SUM(alquiler_detalles.cantidad) as total')
            )
            ->join('alquileres', 'alquiler_detalles.alquiler_id', '=', 'alquileres.id')
            ->join('productos', 'alquiler_detalles.producto_id', '=', 'productos.id')
            ->join('producto_togas', 'productos.id', '=', 'producto_togas.producto_id')
            ->where('alquileres.estado', 'ENTREGADO')
            ->where('productos.tipo_producto', 'TOGA')
            ->groupBy('producto_togas.talla')
            ->orderBy('producto_togas.talla')
            ->get();

        return view('control-alquileres.index', compact(
            'filtro',
            'busqueda',
            'reservasHoy',
            'alquileresVigentes',
            'noDevueltos',
            'atrasados',
            'entregasHoy',
            'entregadosHoy',
            'devolucionesHoy',
            'devueltosHoy',
            'saldoPendienteTotal',
            'togasFuera',
            'alquileres',
            'togasFueraPorTalla'
        ));
    }
}