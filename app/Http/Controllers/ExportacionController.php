<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\MovimientoInventario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportacionController extends Controller
{

    public function alquileresExcel(Request $request)
    {
        $alquileres = $this->queryAlquileres($request)->get();

        $html = view('exportaciones.alquileres-excel', [
            'alquileres' => $alquileres,
            'fechaGeneracion' => now(),
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="alquileres.xls"');
    }

    public function movimientosExcel(Request $request)
    {
        $movimientos = $this->queryMovimientos($request)->get();

        $html = view('exportaciones.movimientos-excel', [
            'movimientos' => $movimientos,
            'fechaGeneracion' => now(),
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="movimientos_inventario.xls"');
    }

    public function alquileresPdf(Request $request)
    {
        $alquileres = $this->queryAlquileres($request)->get();

        $pdf = Pdf::loadView('exportaciones.alquileres-pdf', [
            'alquileres' => $alquileres,
            'fechaGeneracion' => now(),
        ])->setPaper('letter', 'landscape');

        return $pdf->download('alquileres.pdf');
    }

    public function movimientosPdf(Request $request)
    {
        $movimientos = $this->queryMovimientos($request)->get();

        $pdf = Pdf::loadView('exportaciones.movimientos-pdf', [
            'movimientos' => $movimientos,
            'fechaGeneracion' => now(),
        ])->setPaper('letter', 'landscape');

        return $pdf->download('movimientos_inventario.pdf');
    }

    private function queryAlquileres(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado');
        $estadoPago = $request->input('estado_pago');

        return Alquiler::with(['cliente', 'detalles.producto', 'pagos'])
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('codigo_recibo', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($buscar) {
                            $clienteQuery->where('nombres', 'like', "%{$buscar}%")
                                ->orWhere('apellidos', 'like', "%{$buscar}%")
                                ->orWhere('telefono', 'like', "%{$buscar}%")
                                ->orWhere('dpi', 'like', "%{$buscar}%");
                        });
                });
            })
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->when($estadoPago, fn ($query) => $query->where('estado_pago', $estadoPago))
            ->orderByDesc('id');
    }

    private function queryMovimientos(Request $request)
    {
        $tipo = $request->input('tipo');
        $buscar = $request->input('buscar');

        return MovimientoInventario::with('producto')
            ->when($tipo, fn ($query) => $query->where('tipo_movimiento', $tipo))
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('motivo', 'like', "%{$buscar}%")
                        ->orWhere('referencia', 'like', "%{$buscar}%")
                        ->orWhereHas('producto', function ($productoQuery) use ($buscar) {
                            $productoQuery->where('codigo', 'like', "%{$buscar}%")
                                ->orWhere('nombre', 'like', "%{$buscar}%")
                                ->orWhere('tipo_producto', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderByDesc('id');
    }
}