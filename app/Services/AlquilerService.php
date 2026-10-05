<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\AlquilerDetalle;
use App\Models\AlquilerDetalleAccesorio;
use App\Models\AlquilerDano;
use App\Models\AlquilerHistorial;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class AlquilerService
{
    public function __construct(
        protected InventarioService $inventarioService,
        protected ReciboService $reciboService,
        protected AlquilerRulesService $rulesService,
        protected DiscountCalculator $discountCalculator,
        protected FabricacionService $fabricacionService
    ) {}

    public function crearAlquiler(
        int $clienteId,
        array $productos,
        float $descuento = 0,
        float $descuentoPorToga = 0,
        ?string $fechaAlquiler = null,
        ?string $fechaEntrega = null,
        ?string $fechaDevolucionProgramada = null,
        ?string $observaciones = null,
        ?int $usuarioId = null,
        array $fabricacionData = []
    ): Alquiler {
        return DB::transaction(function () use (
            $clienteId,
            $productos,
            $descuento,
            $descuentoPorToga,
            $fechaAlquiler,
            $fechaEntrega,
            $fechaDevolucionProgramada,
            $observaciones,
            $usuarioId,
            $fabricacionData
        ) {
            $items = $this->rulesService->prepararItems($productos);

            if (!$fechaAlquiler) {
                $fechaAlquiler = now()->toDateString();
            }

            if ($fechaEntrega && $fechaAlquiler > $fechaEntrega) {
                throw new Exception('La fecha de reserva no puede ser posterior a la fecha de entrega.');
            }

            if ($fechaEntrega && $fechaDevolucionProgramada && $fechaDevolucionProgramada < $fechaEntrega) {
                throw new Exception('La fecha de devolución programada no puede ser anterior a la fecha de entrega.');
            }

            $subtotal = 0;

            foreach ($items as $item) {
                $subtotal += $item['subtotal'] + $item['subtotal_accesorios'];
            }

            $descuentoManual = max(0, (float) $descuento);
            $descuentoPorToga = max(0, $descuentoPorToga ?? 0);

            $descuentoToga = $this->discountCalculator->calcularDescuentoPorTogas(
                $items,
                $descuentoPorToga
            );
                        
            $descuentoTotal = round($descuentoManual + $descuentoToga, 2);

            $total = $this->discountCalculator->calcularTotal(
                $subtotal,
                $descuentoManual,
                $descuentoToga
            );

            $codigoRecibo = $this->reciboService->generarCodigoRecibo();

            $tieneFabricacionPendiente = $this->hasPendingFabricacion($items);
            $fabricacionAutorizada = !empty($fabricacionData);

            if ($tieneFabricacionPendiente && !$fabricacionAutorizada) {
                throw new Exception('Hay cantidades pendientes de fabricación. Activa la autorización de fabricación para crear este alquiler.');
            }

            $estado = $tieneFabricacionPendiente ? 'EN_FABRICACION' : 'RESERVADO';

            $alquiler = Alquiler::create([
                'cliente_id' => $clienteId,
                'codigo_recibo' => $codigoRecibo,
                'fecha_alquiler' => $fechaAlquiler,
                'fecha_entrega' => $fechaEntrega,
                'fecha_devolucion_programada' => $fechaDevolucionProgramada,
                'estado' => $estado,
                'estado_pago' => 'PENDIENTE',
                'subtotal' => $subtotal,
                'descuento' => $descuentoTotal,
                'descuento_por_toga' => $descuentoPorToga,
                'descuento_manual' => $descuentoManual,
                'descuento_toga' => $descuentoToga,
                'total' => $total,
                'saldo_pendiente' => $total,
                'observaciones' => $observaciones,
                'usuario_id' => $usuarioId,
            ]);

            foreach ($items as $item) {
                $detalleCreado = AlquilerDetalle::create([
                    'alquiler_id' => $alquiler->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'estado' => 'PENDIENTE',
                ]);

                foreach (($item['accesorios'] ?? []) as $accesorio) {
                    $detalleAccesorio = AlquilerDetalleAccesorio::create([
                        'alquiler_detalle_id' => $detalleCreado->id,
                        'producto_id' => $accesorio['producto']->id,
                        'tipo_accesorio' => $accesorio['tipo_accesorio'],
                        'tipo_cobro' => $accesorio['tipo_cobro'],
                        'cantidad' => $accesorio['cantidad'],
                        'precio_unitario' => $accesorio['precio_unitario'],
                        'total_linea' => $accesorio['total_linea'],
                    ]);

                    if (!empty($accesorio['cantidad_pendiente'])) {
                        $this->fabricacionService->registrarFabricacionPendiente(
                            $alquiler->id,
                            $detalleAccesorio->id,
                            $accesorio['producto']->id,
                            $accesorio['cantidad'],
                            $accesorio['cantidad_pendiente'],
                            $fabricacionData['responsable'] ?? null,
                            $fabricacionData['motivo'] ?? null,
                            $fabricacionData['observaciones'] ?? null,
                            $fabricacionData['usuario_id'] ?? $usuarioId,
                            $fabricacionData['fecha'] ?? null
                        );
                    }
                }

                if (!empty($item['cantidad_pendiente'])) {
                    $this->fabricacionService->registrarFabricacionPendiente(
                        $alquiler->id,
                        $detalleCreado->id,
                        $item['producto']->id,
                        $item['cantidad'],
                        $item['cantidad_pendiente'],
                        $fabricacionData['responsable'] ?? null,
                        $fabricacionData['motivo'] ?? null,
                        $fabricacionData['observaciones'] ?? null,
                        $fabricacionData['usuario_id'] ?? $usuarioId,
                        $fabricacionData['fecha'] ?? null
                    );
                }
            }

            return $alquiler->fresh(['cliente', 'detalles.producto', 'pagos']);
        });
    }

    protected function hasPendingFabricacion(array $items): bool
    {
        foreach ($items as $item) {
            if (!empty($item['cantidad_pendiente'])) {
                return true;
            }

            foreach ($item['accesorios'] as $accesorio) {
                if (!empty($accesorio['cantidad_pendiente'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function entregarAlquiler(
        int $alquilerId,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use ($alquilerId, $usuarioId) {
            $alquiler = Alquiler::with(['detalles.producto', 'detalles.accesorios.producto', 'fabricaciones'])
                ->lockForUpdate()
                ->findOrFail($alquilerId);

            if ($alquiler->fabricaciones()->where('cantidad_pendiente', '>', 0)->exists()) {
                throw new Exception('No se puede entregar el alquiler mientras haya cantidades pendientes de fabricación.');
            }

            if ($alquiler->estado === 'ENTREGADO') {
                throw new Exception('Este alquiler ya fue entregado.');
            }

            if ($alquiler->estado === 'DEVUELTO') {
                throw new Exception('No se puede entregar un alquiler que ya fue devuelto.');
            }

            if ($alquiler->estado === 'CANCELADO') {
                throw new Exception('No se puede entregar un alquiler cancelado.');
            }

            if ($alquiler->detalles->isEmpty()) {
                throw new Exception('El alquiler no tiene productos agregados.');
            }

            /*
            |--------------------------------------------------------------------------
            | Validación de pago para entregar / retirar togas
            |--------------------------------------------------------------------------
            | Regla:
            | - El cliente puede reservar con pago parcial.
            | - Para retirar las togas, debe haber pagado el 100% del alquiler.
            | - La mora no entra aquí, porque se genera hasta la devolución.
            */
            $totalAlquiler = (float) $alquiler->total;
            $saldoPendiente = (float) $alquiler->saldo_pendiente;
            $montoPagado = $totalAlquiler - $saldoPendiente;

            if ($montoPagado < $totalAlquiler) {
                $faltante = $totalAlquiler - $montoPagado;

                throw new Exception(
                    'No se puede entregar el alquiler. El cliente debe completar el pago antes de retirar las togas. ' .
                    'Faltan Q' . number_format($faltante, 2) . '.'
                );
            }

            foreach ($alquiler->detalles as $detalle) {
                if ($detalle->estado === 'ENTREGADO') {
                    continue;
                }

                $this->inventarioService->registrarAlquiler(
                    $detalle->producto_id,
                    $detalle->cantidad,
                    $alquiler->codigo_recibo,
                    $usuarioId,
                    'Entrega de alquiler ' . $alquiler->codigo_recibo
                );

                foreach ($detalle->accesorios as $accesorio) {
                    $this->inventarioService->registrarAlquiler(
                        $accesorio->producto_id,
                        $accesorio->cantidad,
                        $alquiler->codigo_recibo,
                        $usuarioId,
                        'Entrega de accesorio ' . $accesorio->tipo_accesorio . ' del alquiler ' . $alquiler->codigo_recibo
                    );
                }

                $detalle->estado = 'ENTREGADO';
                $detalle->save();
            }

            $alquiler->estado = 'ENTREGADO';
            $alquiler->fecha_entrega = now()->toDateString();
            $alquiler->save();

            return $alquiler->fresh(['cliente', 'detalles.producto', 'pagos']);
        });
    }

    public function devolverAlquiler(
        int $alquilerId,
        float $descuentoMora = 0,
        ?string $observacionMora = null,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use (
            $alquilerId,
            $descuentoMora,
            $observacionMora,
            $usuarioId
        ) {
            $alquiler = Alquiler::with([
                'detalles.producto',
                'detalles.accesorios.producto',
            ])
                ->lockForUpdate()
                ->findOrFail($alquilerId);

            if ($alquiler->estado !== 'ENTREGADO') {
                throw new Exception('Solo se pueden devolver alquileres que estén entregados.');
            }

            $fechaHoraReal = now();

            /*
            * Regla de mora:
            * La mora empieza a contar desde las 9:00 AM del día siguiente
            * a la fecha de devolución programada.
            *
            * Ejemplo:
            * Fecha devolución programada: 10/06/2026
            * Inicio de mora: 11/06/2026 09:00 AM
            */
            $diasMora = 0;
            $montoMoraCalculado = 0;

            if ($alquiler->fecha_devolucion_programada) {
                $inicioMora = $alquiler->fecha_devolucion_programada
                    ->copy()
                    ->addDay()
                    ->setTime(9, 0, 0);

                if ($fechaHoraReal->greaterThanOrEqualTo($inicioMora)) {
                    /*
                    * Regla:
                    * Si ya llegó o pasó el inicio de mora, ya cuenta como 1 día.
                    * El siguiente día de mora se suma hasta completar otras 24 horas.
                    *
                    * Ejemplo:
                    * Inicio mora: 11/06/2026 09:00 AM
                    * 11/06/2026 09:00 AM a 12/06/2026 08:59 AM = 1 día = Q50
                    * 12/06/2026 09:00 AM a 13/06/2026 08:59 AM = 2 días = Q100
                    */
                    $segundosRetraso = (int) floor($inicioMora->diffInSeconds($fechaHoraReal, true));

                    $diasMora = intdiv($segundosRetraso, 86400) + 1;
                    $montoMoraCalculado = $diasMora * 50;
                }
            }

            $descuentoMora = max($descuentoMora, 0);

            if ($descuentoMora > $montoMoraCalculado) {
                $descuentoMora = $montoMoraCalculado;
            }

            $montoMoraFinal = max($montoMoraCalculado - $descuentoMora, 0);

            /*
            * Devolver inventario alquilado: toga principal Y accesorios
            * (collarín, birrete, borla, capa), igual que entregarAlquiler()
            * hace con registrarAlquiler() para ambos.
            */
            foreach ($alquiler->detalles as $detalle) {
                if (!$detalle->producto) {
                    throw new Exception('Uno de los productos del alquiler no existe.');
                }

                $this->inventarioService->registrarDevolucion(
                    $detalle->producto_id,
                    $detalle->cantidad,
                    $alquiler->codigo_recibo,
                    $usuarioId,
                    'Devolución de alquiler ' . $alquiler->codigo_recibo
                );

                foreach ($detalle->accesorios as $accesorio) {
                    if (!$accesorio->producto) {
                        throw new Exception('Uno de los accesorios del alquiler no existe.');
                    }

                    $this->inventarioService->registrarDevolucion(
                        $accesorio->producto_id,
                        $accesorio->cantidad,
                        $alquiler->codigo_recibo,
                        $usuarioId,
                        'Devolución de accesorio ' . $accesorio->tipo_accesorio . ' del alquiler ' . $alquiler->codigo_recibo
                    );
                }
            }

            /*
            * Guardar devolución y mora.
            */
            $alquiler->estado = 'DEVUELTO';
            $alquiler->fecha_devolucion_real = $fechaHoraReal->toDateString();
            $alquiler->fecha_hora_devolucion_real = $fechaHoraReal;

            $alquiler->dias_mora = $diasMora;
            $alquiler->monto_mora_calculado = $montoMoraCalculado;
            $alquiler->descuento_mora = $descuentoMora;
            $alquiler->monto_mora = $montoMoraFinal;
            $alquiler->observacion_mora = $observacionMora;

            /*
            * La mora final se suma como cargo adicional.
            * Después puede pagarse desde el flujo normal de pagos.
            */
            if ($montoMoraFinal > 0) {
                $alquiler->total = $alquiler->total + $montoMoraFinal;
                $alquiler->saldo_pendiente = $alquiler->saldo_pendiente + $montoMoraFinal;

                if ($alquiler->saldo_pendiente > 0 && $alquiler->estado_pago === 'PAGADO') {
                    $alquiler->estado_pago = 'PARCIAL';
                }
            }

            $alquiler->save();

            return $alquiler->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CANCELACIÓN
    |--------------------------------------------------------------------------
    | Se permite aunque el alquiler ya tenga pagos. Los pagos NO se reembolsan
    | (cláusula de devolución monetaria): quedan registrados en el historial de
    | pagos y el saldo pendiente pasa a cero.
    */
    public function cancelarAlquiler(
        int $alquilerId,
        string $motivo,
        ?string $responsable = null,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use ($alquilerId, $motivo, $responsable, $usuarioId) {
            $alquiler = Alquiler::with(['pagos', 'detalles', 'fabricaciones'])
                ->lockForUpdate()
                ->findOrFail($alquilerId);

            if ($alquiler->estado === 'CANCELADO') {
                throw new Exception('Este alquiler ya está cancelado.');
            }

            if ($alquiler->estado === 'ENTREGADO') {
                throw new Exception('No se puede cancelar un alquiler entregado: las togas están con el cliente. Registra primero la devolución.');
            }

            if (!$alquiler->puedeCancelarse()) {
                throw new Exception('No se puede cancelar un alquiler en estado ' . $alquiler->estado . '.');
            }

            $motivo = trim($motivo);

            if ($motivo === '') {
                throw new Exception('Debes indicar el motivo de la cancelación.');
            }

            $estadoAnterior = $alquiler->estado;
            $totalPagado = round($alquiler->totalPagado(), 2);
            $saldoAnterior = round((float) $alquiler->saldo_pendiente, 2);

            $alquiler->estado = 'CANCELADO';
            $alquiler->fecha_cancelacion = now();
            $alquiler->motivo_cancelacion = $motivo;
            $alquiler->saldo_pendiente = 0;

            if ($alquiler->pagos->isEmpty()) {
                $alquiler->estado_pago = 'PENDIENTE';
            }

            $alquiler->save();

            foreach ($alquiler->detalles as $detalle) {
                $detalle->estado = 'CANCELADO';
                $detalle->save();
            }

            foreach ($alquiler->fabricaciones as $fabricacion) {
                if ((int) $fabricacion->cantidad_pendiente > 0) {
                    $fabricacion->estado = 'CANCELADO';
                    $fabricacion->save();
                }
            }

            $this->registrarHistorial(
                $alquiler,
                'CANCELACION',
                'estado',
                $estadoAnterior,
                'CANCELADO (pagos retenidos: Q' . number_format($totalPagado, 2) .
                    ', saldo anulado: Q' . number_format($saldoAnterior, 2) . ')',
                $motivo,
                $responsable,
                $usuarioId
            );

            return $alquiler->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | EDICIÓN
    |--------------------------------------------------------------------------
    | Antes de la entrega se pueden cambiar fechas, horas y datos de la carta.
    | Ya entregado, solo la fecha y hora de devolución.
    | Productos, cantidades y montos NO se editan aquí.
    | Cada campo modificado queda en el historial con valor anterior y nuevo.
    */
    public const CAMPOS_EDITABLES = [
        'fecha_entrega',
        'hora_entrega',
        'fecha_devolucion_programada',
        'hora_devolucion_programada',
        'hora_entrega_inicio',
        'hora_entrega_fin',
        'institucion_representada',
        'representante_alquiler',
        'fecha_limite_pago_final',
        'observaciones',
    ];

    public const CAMPOS_EDITABLES_ENTREGADO = [
        'fecha_devolucion_programada',
        'hora_devolucion_programada',
    ];

    protected const CAMPOS_FECHA = [
        'fecha_alquiler',
        'fecha_entrega',
        'fecha_devolucion_programada',
        'fecha_limite_pago_final',
    ];

    protected const CAMPOS_HORA = [
        'hora_entrega',
        'hora_devolucion_programada',
        'hora_entrega_inicio',
        'hora_entrega_fin',
    ];

    public function camposEditables(Alquiler $alquiler): array
    {
        return $alquiler->soloEditaDevolucion()
            ? self::CAMPOS_EDITABLES_ENTREGADO
            : self::CAMPOS_EDITABLES;
    }

    public function actualizarAlquiler(
        int $alquilerId,
        array $datos,
        string $motivo,
        ?string $responsable = null,
        ?int $usuarioId = null
    ): Alquiler {
        return DB::transaction(function () use ($alquilerId, $datos, $motivo, $responsable, $usuarioId) {
            $alquiler = Alquiler::lockForUpdate()->findOrFail($alquilerId);

            if (!$alquiler->puedeEditarse()) {
                throw new Exception('No se puede editar un alquiler en estado ' . $alquiler->estado . '.');
            }

            $motivo = trim($motivo);

            if ($motivo === '') {
                throw new Exception('Debes indicar el motivo del cambio.');
            }

            $campos = $this->camposEditables($alquiler);

            // Valores finales (los nuevos donde vienen, los actuales donde no).
            $actuales = [];
            $nuevos = [];

            foreach (array_merge($campos, ['fecha_alquiler', 'fecha_entrega', 'hora_entrega_inicio', 'hora_entrega_fin']) as $campo) {
                $actuales[$campo] = $this->normalizarValor($campo, $alquiler->getRawOriginal($campo));
            }

            foreach ($campos as $campo) {
                $nuevos[$campo] = array_key_exists($campo, $datos)
                    ? $this->normalizarValor($campo, $datos[$campo])
                    : $actuales[$campo];
            }

            $final = array_merge($actuales, $nuevos);

            $this->validarFechasYHoras($alquiler, $final);

            $cambios = [];

            foreach ($campos as $campo) {
                if ($actuales[$campo] !== $nuevos[$campo]) {
                    $cambios[$campo] = [$actuales[$campo], $nuevos[$campo]];
                }
            }

            if (empty($cambios)) {
                throw new Exception('No se detectaron cambios en el alquiler.');
            }

            foreach ($cambios as $campo => [$anterior, $nuevo]) {
                $alquiler->{$campo} = $nuevo;
            }

            $alquiler->save();

            foreach ($cambios as $campo => [$anterior, $nuevo]) {
                $this->registrarHistorial(
                    $alquiler,
                    'EDICION',
                    $campo,
                    $this->formatearValor($campo, $anterior),
                    $this->formatearValor($campo, $nuevo),
                    $motivo,
                    $responsable,
                    $usuarioId
                );
            }

            return $alquiler->fresh();
        });
    }

    protected function validarFechasYHoras(Alquiler $alquiler, array $final): void
    {
        $fechaAlquiler = $final['fecha_alquiler'] ?? null;
        $fechaEntrega = $final['fecha_entrega'] ?? null;
        $fechaDevolucion = $final['fecha_devolucion_programada'] ?? null;

        if (!$alquiler->soloEditaDevolucion() && !$fechaEntrega) {
            throw new Exception('La fecha de entrega es obligatoria.');
        }

        if (!$fechaDevolucion) {
            throw new Exception('La fecha de devolución es obligatoria.');
        }

        if ($fechaAlquiler && $fechaEntrega && $fechaEntrega < $fechaAlquiler) {
            throw new Exception('La fecha de entrega no puede ser anterior a la fecha de reserva (' . $this->formatearValor('fecha_alquiler', $fechaAlquiler) . ').');
        }

        if ($fechaEntrega && $fechaDevolucion < $fechaEntrega) {
            throw new Exception('La fecha de devolución no puede ser anterior a la fecha de entrega (' . $this->formatearValor('fecha_entrega', $fechaEntrega) . ').');
        }

        $inicio = $final['hora_entrega_inicio'] ?? null;
        $fin = $final['hora_entrega_fin'] ?? null;

        if ($inicio && $fin && $fin < $inicio) {
            throw new Exception('La hora final de recogida no puede ser anterior a la hora inicial.');
        }
    }

    /**
     * Lleva fechas a Y-m-d, horas a H:i y textos vacíos a null,
     * para comparar lo guardado con lo enviado sin falsos cambios.
     */
    protected function normalizarValor(string $campo, $valor): ?string
    {
        if ($valor instanceof \DateTimeInterface) {
            $valor = Carbon::instance($valor);
        }

        if ($valor === null || (is_string($valor) && trim($valor) === '')) {
            return null;
        }

        try {
            if (in_array($campo, self::CAMPOS_FECHA, true)) {
                return Carbon::parse($valor)->format('Y-m-d');
            }

            if (in_array($campo, self::CAMPOS_HORA, true)) {
                return Carbon::parse($valor)->format('H:i');
            }
        } catch (\Throwable $e) {
            throw new Exception('El valor de "' . (AlquilerHistorial::ETIQUETAS_CAMPOS[$campo] ?? $campo) . '" no es válido.');
        }

        return trim((string) $valor);
    }

    protected function formatearValor(string $campo, ?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (in_array($campo, self::CAMPOS_FECHA, true)) {
            return Carbon::parse($valor)->format('d/m/Y');
        }

        if (in_array($campo, self::CAMPOS_HORA, true)) {
            return Carbon::parse($valor)->format('h:i A');
        }

        return $valor;
    }

    /*
    |--------------------------------------------------------------------------
    | DAÑOS Y EXTRAVÍOS
    |--------------------------------------------------------------------------
    | Solo en alquileres DEVUELTOS. El monto se suma al total y al saldo
    | pendiente (igual que la mora) y se cobra con "Registrar pago".
    | Lo extraviado se da de baja del inventario (movimiento SALIDA).
    */
    public function cantidadesAlquiladasPorProducto(Alquiler $alquiler): array
    {
        $alquiler->loadMissing(['detalles.producto', 'detalles.accesorios.producto']);

        $productos = [];

        $agregar = function ($producto, int $cantidad, string $etiqueta) use (&$productos) {
            if (!$producto) {
                return;
            }

            if (!isset($productos[$producto->id])) {
                $productos[$producto->id] = [
                    'producto' => $producto,
                    'etiqueta' => $etiqueta,
                    'cantidad' => 0,
                ];
            }

            $productos[$producto->id]['cantidad'] += $cantidad;
        };

        foreach ($alquiler->detalles as $detalle) {
            $agregar(
                $detalle->producto,
                (int) $detalle->cantidad,
                ($detalle->producto->nombre ?? 'Toga') . ' (toga)'
            );

            foreach ($detalle->accesorios as $accesorio) {
                $agregar(
                    $accesorio->producto,
                    (int) $accesorio->cantidad,
                    ($accesorio->producto->nombre ?? 'Accesorio') . ' (' . strtolower($accesorio->tipo_accesorio) . ')'
                );
            }
        }

        return $productos;
    }

    public function registrarDano(
        int $alquilerId,
        int $productoId,
        string $tipo,
        int $cantidad,
        float $monto,
        ?string $descripcion = null,
        ?string $responsable = null,
        ?int $usuarioId = null
    ): AlquilerDano {
        return DB::transaction(function () use (
            $alquilerId,
            $productoId,
            $tipo,
            $cantidad,
            $monto,
            $descripcion,
            $responsable,
            $usuarioId
        ) {
            $alquiler = Alquiler::with(['detalles.producto', 'detalles.accesorios.producto', 'danos'])
                ->lockForUpdate()
                ->findOrFail($alquilerId);

            if (!$alquiler->puedeRegistrarDanos()) {
                throw new Exception('Solo se pueden registrar daños o extravíos en alquileres devueltos.');
            }

            if (!in_array($tipo, ['DANO', 'EXTRAVIO'], true)) {
                throw new Exception('El tipo debe ser daño o extravío.');
            }

            if ($cantidad <= 0) {
                throw new Exception('La cantidad debe ser mayor a cero.');
            }

            $monto = round($monto, 2);

            if ($monto < 0) {
                throw new Exception('El monto no puede ser negativo.');
            }

            $alquilados = $this->cantidadesAlquiladasPorProducto($alquiler);

            if (!isset($alquilados[$productoId])) {
                throw new Exception('El producto seleccionado no forma parte de este alquiler.');
            }

            $yaReportado = (int) $alquiler->danos->where('producto_id', $productoId)->sum('cantidad');
            $maximo = $alquilados[$productoId]['cantidad'] - $yaReportado;

            if ($cantidad > $maximo) {
                throw new Exception(
                    'La cantidad supera lo alquilado de "' . $alquilados[$productoId]['producto']->nombre . '". ' .
                    'Alquilado: ' . $alquilados[$productoId]['cantidad'] . ', ya reportado: ' . $yaReportado .
                    ', máximo a reportar: ' . max($maximo, 0) . '.'
                );
            }

            $dano = AlquilerDano::create([
                'alquiler_id' => $alquiler->id,
                'producto_id' => $productoId,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'monto' => $monto,
                'descripcion' => $descripcion ? trim($descripcion) : null,
                'responsable' => $responsable ? trim($responsable) : null,
                'usuario_id' => $usuarioId,
            ]);

            if ($tipo === 'EXTRAVIO') {
                $this->inventarioService->registrarSalida(
                    $productoId,
                    $cantidad,
                    'Extravío en alquiler ' . $alquiler->codigo_recibo,
                    $alquiler->codigo_recibo,
                    $usuarioId
                );
            }

            if ($monto > 0) {
                $alquiler->monto_danos = round((float) $alquiler->monto_danos + $monto, 2);
                $alquiler->total = round((float) $alquiler->total + $monto, 2);
                $alquiler->saldo_pendiente = round((float) $alquiler->saldo_pendiente + $monto, 2);
                $this->recalcularEstadoPago($alquiler);
                $alquiler->save();
            }

            $this->registrarHistorial(
                $alquiler,
                'DANO',
                null,
                null,
                $dano->tipo_texto . ': ' . $cantidad . ' x ' . $alquilados[$productoId]['producto']->nombre .
                    ' — Q' . number_format($monto, 2),
                $descripcion,
                $responsable,
                $usuarioId
            );

            return $dano;
        });
    }

    public function eliminarDano(
        int $alquilerId,
        int $danoId,
        string $motivo,
        ?string $responsable = null,
        ?int $usuarioId = null
    ): void {
        DB::transaction(function () use ($alquilerId, $danoId, $motivo, $responsable, $usuarioId) {
            $alquiler = Alquiler::lockForUpdate()->findOrFail($alquilerId);

            $dano = AlquilerDano::with('producto')
                ->where('alquiler_id', $alquiler->id)
                ->lockForUpdate()
                ->findOrFail($danoId);

            $motivo = trim($motivo);

            if ($motivo === '') {
                throw new Exception('Debes indicar el motivo para eliminar el registro.');
            }

            $monto = round((float) $dano->monto, 2);

            if ($monto > round((float) $alquiler->saldo_pendiente, 2)) {
                throw new Exception(
                    'No se puede eliminar: este cargo ya fue cobrado total o parcialmente ' .
                    '(saldo pendiente Q' . number_format((float) $alquiler->saldo_pendiente, 2) .
                    ', cargo Q' . number_format($monto, 2) . ').'
                );
            }

            if ($dano->tipo === 'EXTRAVIO') {
                $this->inventarioService->registrarEntrada(
                    $dano->producto_id,
                    $dano->cantidad,
                    'Reversión de extravío en alquiler ' . $alquiler->codigo_recibo,
                    $alquiler->codigo_recibo,
                    $usuarioId
                );
            }

            if ($monto > 0) {
                $alquiler->monto_danos = max(0, round((float) $alquiler->monto_danos - $monto, 2));
                $alquiler->total = max(0, round((float) $alquiler->total - $monto, 2));
                $alquiler->saldo_pendiente = max(0, round((float) $alquiler->saldo_pendiente - $monto, 2));
                $this->recalcularEstadoPago($alquiler);
                $alquiler->save();
            }

            $this->registrarHistorial(
                $alquiler,
                'DANO_ELIMINADO',
                null,
                $dano->tipo_texto . ': ' . $dano->cantidad . ' x ' . ($dano->producto->nombre ?? 'Producto') .
                    ' — Q' . number_format($monto, 2),
                null,
                $motivo,
                $responsable,
                $usuarioId
            );

            $dano->delete();
        });
    }

    /**
     * Misma regla que PagoService para el estado de pago.
     */
    protected function recalcularEstadoPago(Alquiler $alquiler): void
    {
        $saldo = round((float) $alquiler->saldo_pendiente, 2);
        $total = round((float) $alquiler->total, 2);

        if ($saldo <= 0) {
            $alquiler->saldo_pendiente = 0;
            $alquiler->estado_pago = 'PAGADO';
        } elseif ($saldo < $total) {
            $alquiler->estado_pago = 'PARCIAL';
        } else {
            $alquiler->estado_pago = 'PENDIENTE';
        }
    }

    protected function registrarHistorial(
        Alquiler $alquiler,
        string $accion,
        ?string $campo,
        ?string $valorAnterior,
        ?string $valorNuevo,
        ?string $motivo,
        ?string $responsable,
        ?int $usuarioId
    ): AlquilerHistorial {
        return AlquilerHistorial::create([
            'alquiler_id' => $alquiler->id,
            'accion' => $accion,
            'campo' => $campo,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo,
            'motivo' => $motivo,
            'responsable' => $responsable ? trim($responsable) : null,
            'usuario_id' => $usuarioId,
        ]);
    }
}
