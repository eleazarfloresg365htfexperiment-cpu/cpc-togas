<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarAlquilerRequest;
use App\Models\Alquiler;
use App\Models\Cliente;
use App\Models\Producto;
use App\Services\AlquilerService;
use Illuminate\Http\Request;

class AlquilerController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado');
        $estadoPago = $request->input('estado_pago');

        $consulta = Alquiler::query()
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
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($estadoPago, function ($query, $estadoPago) {
                $query->where('estado_pago', $estadoPago);
            });

        // Resumen de TODOS los alquileres que cumplen el filtro (no solo la página).
        $resumen = [
            'total' => (clone $consulta)->count(),
            'porEntregar' => (clone $consulta)->whereIn('estado', Alquiler::ESTADOS_ANTES_DE_ENTREGA)->count(),
            'enFabricacion' => (clone $consulta)->where('estado', 'EN_FABRICACION')->count(),
            'entregados' => (clone $consulta)->where('estado', 'ENTREGADO')->count(),
            'porCobrar' => (float) (clone $consulta)->where('estado', '!=', 'CANCELADO')->sum('saldo_pendiente'),
        ];

        $alquileres = $consulta
            ->with(['cliente', 'pagos'])
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $totalAlquileres = Alquiler::count();
        $alquileresReservados = Alquiler::whereIn('estado', Alquiler::ESTADOS_ANTES_DE_ENTREGA)->count();
        $alquileresEntregados = Alquiler::where('estado', 'ENTREGADO')->count();
        $alquileresDevueltos = Alquiler::where('estado', 'DEVUELTO')->count();
        $alquileresCancelados = Alquiler::where('estado', 'CANCELADO')->count();

        return view('alquileres.index', compact(
            'alquileres',
            'resumen',
            'totalAlquileres',
            'alquileresReservados',
            'alquileresEntregados',
            'alquileresDevueltos',
            'alquileresCancelados',
            'buscar',
            'estado',
            'estadoPago'
        ));
    }

    public function create()
    {
        $clientes = Cliente::where('activo', true)
            ->orderBy('nombres')
            ->get();

        // Productos con stock de cada tipo, junto con su detalle.
        $disponibles = fn (string $tipo, string $relacion) => Producto::with($relacion)
            ->where('activo', true)
            ->where('tipo_producto', $tipo)
            ->where('stock_disponible', '>', 0)
            ->orderBy('nombre')
            ->get();

        $togas = $disponibles('TOGA', 'toga');
        $capas = $disponibles('CAPA', 'capa');
        $collarines = $disponibles('COLLARIN', 'collarin');
        $birretes = $disponibles('BIRRETE', 'birrete');
        $borlas = $disponibles('BORLA', 'borla');

        return view('alquileres.create', compact(
            'clientes',
            'togas',
            'collarines',
            'birretes',
            'borlas',
            'capas'
        ));
    }

    public function store(GuardarAlquilerRequest $request, AlquilerService $alquileres)
    {
        $datos = $request->validated();

        try {
            $alquiler = $alquileres->crearAlquiler(
                clienteId: (int) $datos['cliente_id'],
                productos: $request->detallesParaServicio(),
                descuento: (float) ($datos['descuento'] ?? 0),
                descuentoPorToga: (float) ($datos['descuento_por_toga'] ?? 0),
                fechaAlquiler: $datos['fecha_alquiler'],
                fechaEntrega: $datos['fecha_entrega'],
                fechaDevolucionProgramada: $datos['fecha_devolucion_programada'],
                observaciones: $datos['observaciones'] ?? null,
                usuarioId: null,
                fabricacionData: $request->datosDeFabricacion(),
            );

            $alquiler->update([
                // Fecha real de reserva seleccionada en el formulario.
                'fecha_alquiler' => $datos['fecha_alquiler'],

                'institucion_representada' => $datos['institucion_representada'] ?? null,
                'representante_alquiler' => $datos['representante_alquiler'] ?? null,

                // Horario exacto del alquiler
                'hora_entrega' => $datos['hora_entrega'] ?? null,
                'hora_devolucion_programada' => $datos['hora_devolucion_programada'] ?? null,

                // Rango de horario mostrado en carta/entrega
                'hora_entrega_inicio' => $datos['hora_entrega_inicio'] ?? null,
                'hora_entrega_fin' => $datos['hora_entrega_fin'] ?? null,

                'fecha_limite_pago_final' => $datos['fecha_limite_pago_final'] ?? null,
            ]);

            return redirect()
                ->route('alquileres.index')
                ->with('success', 'Alquiler creado correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['productos' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Alquiler $alquiler, AlquilerService $alquileres)
    {
        $alquiler->load([
            'cliente',
            'detalles.producto',
            'detalles.producto.toga',
            'detalles.accesorios.producto',
            'pagos',
            'detalles.accesorios.producto.birrete',
            'detalles.accesorios.producto.borla',
            'detalles.accesorios.producto.collarin',
            'detalles.accesorios.producto.capa',
            'historial',
            'danos.producto',
            'fabricaciones.producto',
        ]);

        $productosDanos = $alquiler->puedeRegistrarDanos()
            ? $alquileres->cantidadesAlquiladasPorProducto($alquiler)
            : [];

        return view('alquileres.show', compact('alquiler', 'productosDanos'));
    }

    public function entregar(Alquiler $alquiler, AlquilerService $alquileres)
    {
        try {
            $alquileres->entregarAlquiler($alquiler->id, null);

            return redirect()
                ->route('alquileres.index')
                ->with('success', 'Alquiler entregado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('alquileres.index')
                ->with('error', $e->getMessage());
        }
    }

    public function devolver(Request $request, Alquiler $alquiler, AlquilerService $alquileres)
    {
        $request->validate([
            'descuento_mora' => ['nullable', 'numeric', 'min:0'],
            'observacion_mora' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $alquileres->devolverAlquiler(
                $alquiler->id,
                (float) $request->input('descuento_mora', 0),
                $request->input('observacion_mora'),
                null
            );

            return redirect()
                ->back()
                ->with('success', 'Alquiler devuelto correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function cancelar(Request $request, Alquiler $alquiler, AlquilerService $alquileres)
    {
        $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'max:1000'],
            'responsable' => ['nullable', 'string', 'max:255'],
        ], [
            'motivo_cancelacion.required' => 'Debes indicar el motivo de la cancelación.',
        ]);

        try {
            $cancelado = $alquileres->cancelarAlquiler(
                $alquiler->id,
                $request->input('motivo_cancelacion'),
                $request->input('responsable'),
                auth()->id()
            );

            $mensaje = 'Alquiler cancelado correctamente.';

            if ($cancelado->pagos()->exists()) {
                $mensaje .= ' Los pagos registrados quedan retenidos (sin reembolso).';
            }

            return redirect()
                ->route('alquileres.show', $alquiler->id)
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()
                ->route('alquileres.show', $alquiler->id)
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Alquiler $alquiler, AlquilerService $alquileres)
    {
        $alquiler->load(['cliente', 'historial']);

        if (!$alquiler->puedeEditarse()) {
            return redirect()
                ->route('alquileres.show', $alquiler->id)
                ->with('error', 'No se puede editar un alquiler en estado ' . $alquiler->estado . '.');
        }

        $camposEditables = $alquileres->camposEditables($alquiler);

        return view('alquileres.edit', compact('alquiler', 'camposEditables'));
    }

    public function update(Request $request, Alquiler $alquiler, AlquilerService $alquileres)
    {
        $camposEditables = $alquileres->camposEditables($alquiler);

        $reglas = [
            'fecha_entrega' => ['required', 'date'],
            'hora_entrega' => ['nullable', 'date_format:H:i'],
            'fecha_devolucion_programada' => ['required', 'date'],
            'hora_devolucion_programada' => ['nullable', 'date_format:H:i'],
            'hora_entrega_inicio' => ['nullable', 'date_format:H:i'],
            'hora_entrega_fin' => ['nullable', 'date_format:H:i'],
            'institucion_representada' => ['nullable', 'string', 'max:255'],
            'representante_alquiler' => ['nullable', 'string', 'max:255'],
            'fecha_limite_pago_final' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];

        $datos = $request->validate(
            array_intersect_key($reglas, array_flip($camposEditables)) + [
                'motivo_cambio' => ['required', 'string', 'max:1000'],
                'responsable' => ['nullable', 'string', 'max:255'],
            ],
            [
                'motivo_cambio.required' => 'Debes indicar el motivo del cambio.',
                'fecha_entrega.required' => 'Debes indicar la fecha de entrega.',
                'fecha_devolucion_programada.required' => 'Debes indicar la fecha de devolución.',
                'date_format' => 'La hora debe tener el formato HH:MM.',
            ]
        );

        try {
            $alquileres->actualizarAlquiler(
                $alquiler->id,
                array_intersect_key($datos, array_flip($camposEditables)),
                $datos['motivo_cambio'],
                $datos['responsable'] ?? null,
                auth()->id()
            );

            return redirect()
                ->route('alquileres.show', $alquiler->id)
                ->with('success', 'Alquiler actualizado correctamente. El cambio quedó en el historial.');
        } catch (\Exception $e) {
            return redirect()
                ->route('alquileres.edit', $alquiler->id)
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
