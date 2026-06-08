<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Services\AlquilerService;
use Illuminate\Http\Request;
use Exception;

class AlquilerController extends Controller
{
    public function __construct(
        protected AlquilerService $alquilerService
    ) {}

    public function index()
    {
        $alquileres = Alquiler::with([
            'cliente',
            'detalles.producto',
            'pagos',
        ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'ok' => true,
            'alquileres' => $alquileres,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'exists:productos,id'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'fecha_entrega' => ['nullable', 'date'],
            'fecha_devolucion_programada' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
            'usuario_id' => ['nullable', 'exists:users,id'],
        ]);

        try {
            $alquiler = $this->alquilerService->crearAlquiler(
                clienteId: $datos['cliente_id'],
                productos: $datos['productos'],
                descuento: $datos['descuento'] ?? 0,
                fechaEntrega: $datos['fecha_entrega'] ?? null,
                fechaDevolucionProgramada: $datos['fecha_devolucion_programada'] ?? null,
                observaciones: $datos['observaciones'] ?? null,
                usuarioId: $datos['usuario_id'] ?? null
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Alquiler creado correctamente.',
                'alquiler' => $alquiler,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(int $id)
    {
        $alquiler = Alquiler::with([
            'cliente',
            'detalles.producto',
            'pagos',
        ])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'alquiler' => $alquiler,
        ]);
    }

    public function entregar(int $id)
    {
        try {
            $alquiler = $this->alquilerService->entregarAlquiler($id);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Alquiler entregado correctamente.',
                'alquiler' => $alquiler,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }

    public function devolver(int $id)
    {
        try {
            $alquiler = $this->alquilerService->devolverAlquiler($id);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Alquiler devuelto correctamente.',
                'alquiler' => $alquiler,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }
}