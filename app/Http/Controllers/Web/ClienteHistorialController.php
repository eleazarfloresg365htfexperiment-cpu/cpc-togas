<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\HistorialClienteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Historial de los clientes: pantalla de un cliente y exportaciones a PDF
 * y Excel, de un cliente o de todos (respetando los filtros del listado).
 */
class ClienteHistorialController extends Controller
{
    public function show(Cliente $cliente, HistorialClienteService $historiales)
    {
        return view('clientes.show', $historiales->de($cliente));
    }

    public function pdf(Cliente $cliente, HistorialClienteService $historiales)
    {
        return $this->responderPdf([$historiales->de($cliente)], $this->nombreArchivo($cliente));
    }

    public function excel(Cliente $cliente, HistorialClienteService $historiales)
    {
        return $this->responderExcel([$historiales->de($cliente)], $this->nombreArchivo($cliente));
    }

    public function todosPdf(Request $request, HistorialClienteService $historiales)
    {
        // Con muchos clientes el PDF puede tardar más de lo normal.
        @set_time_limit(300);

        return $this->responderPdf(
            $historiales->deVarios($this->clientesFiltrados($request)),
            'historial_clientes_' . now()->format('Y-m-d')
        );
    }

    public function todosExcel(Request $request, HistorialClienteService $historiales)
    {
        return $this->responderExcel(
            $historiales->deVarios($this->clientesFiltrados($request)),
            'historial_clientes_' . now()->format('Y-m-d')
        );
    }

    private function responderPdf(array $historiales, string $nombre)
    {
        return Pdf::loadView('clientes.historial-pdf', [
            'historiales' => $historiales,
            'fechaGeneracion' => now(),
            'generadoPor' => auth()->user()?->nombre_corto,
        ])->setPaper('letter')->stream($nombre . '.pdf');
    }

    private function responderExcel(array $historiales, string $nombre)
    {
        $html = view('clientes.historial-excel', [
            'historiales' => $historiales,
            'fechaGeneracion' => now(),
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $nombre . '.xls"');
    }

    private function nombreArchivo(Cliente $cliente): string
    {
        return 'historial_' . (Str::slug($cliente->nombres . ' ' . $cliente->apellidos, '_') ?: 'cliente_' . $cliente->id);
    }

    /** Los mismos filtros del listado de clientes (buscar, estado, solo con alquileres). */
    private function clientesFiltrados(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado');

        return Cliente::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%")
                        ->orWhere('dpi', 'like', "%{$buscar}%")
                        ->orWhere('direccion', 'like', "%{$buscar}%")
                        ->orWhere('institucion_representada', 'like', "%{$buscar}%");
                });
            })
            ->when($estado !== null && $estado !== '', fn ($query) => $query->where('activo', $estado))
            ->when($request->boolean('solo_con_alquileres', true), fn ($query) => $query->has('alquileres'))
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();
    }
}
