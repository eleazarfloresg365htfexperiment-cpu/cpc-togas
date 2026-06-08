<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\PagoService;
use Illuminate\Http\Request;
use Exception;

class PagoController extends Controller
{
    public function __construct(
        protected PagoService $pagoService
    ) {}

    public function index()
    {
        $pagos = Pago::with([
            'alquiler.cliente',
        ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'ok' => true,
            'pagos' => $pagos,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'alquiler_id' => ['required', 'exists:alquileres,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'in:EFECTIVO,TRANSFERENCIA,TARJETA,OTRO'],
            'referencia' => ['nullable', 'string', 'max:150'],
            'observaciones' => ['nullable', 'string'],
            'usuario_id' => ['nullable', 'exists:users,id'],
        ]);

        try {
            $pago = $this->pagoService->registrarPago(
                alquilerId: $datos['alquiler_id'],
                monto: $datos['monto'],
                metodoPago: $datos['metodo_pago'],
                referencia: $datos['referencia'] ?? null,
                observaciones: $datos['observaciones'] ?? null,
                usuarioId: $datos['usuario_id'] ?? null
            );

            $pago->load('alquiler.cliente');

            return response()->json([
                'ok' => true,
                'mensaje' => 'Pago registrado correctamente.',
                'pago' => $pago,
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
        $pago = Pago::with([
            'alquiler.cliente',
        ])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'pago' => $pago,
        ]);
    }
}
