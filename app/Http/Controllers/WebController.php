<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Services\AlquilerService;
use App\Models\AlquilerDetalleAccesorio;
use App\Models\Cliente;
use App\Models\Pago;
use App\Services\PagoService;
use App\Models\Producto;
use App\Models\ProductoToga;
use App\Models\ProductoBirrete;
use App\Models\ProductoCollarin;
use App\Models\ProductoBorla;
use App\Services\InventarioService;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function dashboard()
    {
        $totalProductos = Producto::count();

        $productosActivos = Producto::where('activo', true)->count();

        $productosInactivos = Producto::where('activo', false)->count();

        $stockTotalGeneral = Producto::sum('stock_total');

        $stockDisponibleGeneral = Producto::sum('stock_disponible');

        $stockAlquiladoGeneral = Producto::sum('stock_alquilado');

        $alquileresEntregados = Alquiler::where('estado', 'ENTREGADO')->count();

        $alquileresReservados = Alquiler::where('estado', 'RESERVADO')->count();

        $alquileresDevueltos = Alquiler::where('estado', 'DEVUELTO')->count();

        $alquileresCancelados = Alquiler::where('estado', 'CANCELADO')->count();

        $pagosPendientes = Alquiler::where('estado', '!=', 'CANCELADO')
            ->where('saldo_pendiente', '>', 0)
            ->count();

        $totalPorCobrar = Alquiler::where('estado', '!=', 'CANCELADO')
            ->sum('saldo_pendiente');

        $ingresosRecibidos = Pago::sum('monto');

        $movimientosRecientes = MovimientoInventario::with('producto')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'totalProductos',
            'productosActivos',
            'productosInactivos',
            'stockTotalGeneral',
            'stockDisponibleGeneral',
            'stockAlquiladoGeneral',
            'alquileresEntregados',
            'alquileresReservados',
            'alquileresDevueltos',
            'alquileresCancelados',
            'pagosPendientes',
            'totalPorCobrar',
            'ingresosRecibidos',
            'movimientosRecientes'
        ));
    }

    // ------------------------------------------------------------
    // PRODUCTOS
    // ------------------------------------------------------------

    public function productos(Request $request)
    {
        $buscar = $request->input('buscar');
        $tipo = $request->input('tipo');
        $estado = $request->input('estado');

        $productos = Producto::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('codigo', 'like', "%{$buscar}%")
                    ->orWhere('nombre', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->when($tipo, function ($query, $tipo) {
                $query->where('tipo_producto', $tipo);
            })
            ->when($estado !== null && $estado !== '', function ($query) use ($estado) {
                $query->where('activo', $estado);
            })
            ->orderBy('tipo_producto')
            ->orderBy('nombre')
            ->get();

        $totalProductos = Producto::count();
        $productosActivos = Producto::where('activo', true)->count();
        $productosInactivos = Producto::where('activo', false)->count();

        $stockTotal = Producto::sum('stock_total');
        $stockDisponible = Producto::sum('stock_disponible');
        $stockAlquilado = Producto::sum('stock_alquilado');

        return view('productos.index', compact(
            'productos',
            'totalProductos',
            'productosActivos',
            'productosInactivos',
            'stockTotal',
            'stockDisponible',
            'stockAlquilado',
            'buscar',
            'tipo',
            'estado'
        ));
    }

    public function crearProducto()
    {
        return view('productos.create');
    }

    public function guardarProducto(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:100|unique:productos,codigo',
            'nombre' => 'required|string|max:255',
            'tipo_producto' => 'required|in:TOGA,BIRRETE,COLLARIN,BORLA',
            'descripcion' => 'nullable|string',
            'precio_alquiler' => 'required|numeric|min:0',
            'stock_total' => 'required|integer|min:0',
            'activo' => 'required|boolean',

            'color_toga' => 'nullable|string|max:100',
            'talla_toga' => 'nullable|string|max:50',
            'observaciones_toga' => 'nullable|string',

            'tipo_birrete' => 'nullable|in:ESTANDAR,NORMAL,UNIVERSITARIO',
            'color_birrete' => 'nullable|string|max:100',
            'carrera_birrete' => 'nullable|in:ADMINISTRACION,AGRONOMIA,DERECHO,PEDAGOGIA',

            'color_collarin' => 'nullable|string|max:100',

            'borla_color' => 'nullable|string|max:100',
            'borla_carrera' => 'nullable|string|max:100',
            'borla_observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {

            $producto = Producto::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'tipo_producto' => $request->tipo_producto,
                'descripcion' => $request->descripcion,
                'precio_alquiler' => $request->precio_alquiler,
                'stock_total' => $request->stock_total,
                'stock_disponible' => $request->stock_total,
                'stock_alquilado' => 0,
                'activo' => $request->activo,
            ]);

            if ($request->tipo_producto === 'TOGA') {
                ProductoToga::create([
                    'producto_id' => $producto->id,
                    'talla' => $request->talla_toga ?? 'No especificado',
                    'color' => $request->color_toga ?? 'No especificado',
                    'observaciones' => $request->observaciones_toga,
                ]);
            }

            if ($request->tipo_producto === 'BIRRETE') {
                ProductoBirrete::create([
                    'producto_id' => $producto->id,
                    'tipo_birrete' => $request->tipo_birrete ?? 'NORMAL',
                    'color' => $request->color_birrete ?? 'No especificado',
                    'carrera' => $request->carrera_birrete,
                ]);
            }

            if ($request->tipo_producto === 'COLLARIN') {
                ProductoCollarin::create([
                    'producto_id' => $producto->id,
                    'color' => $request->color_collarin ?? 'No especificado',
                ]);
            }

            if ($request->tipo_producto === 'BORLA') {
                ProductoBorla::create([
                    'producto_id' => $producto->id,
                    'color' => $request->borla_color ?? 'No especificado',
                    'carrera' => $request->borla_carrera,
                    'observaciones' => $request->borla_observaciones,
                ]);
            }

            if ($producto->stock_total > 0) {
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $producto->stock_total,
                    'stock_anterior_disponible' => 0,
                    'stock_nuevo_disponible' => $producto->stock_disponible,
                    'stock_anterior_alquilado' => 0,
                    'stock_nuevo_alquilado' => 0,
                    'motivo' => 'Registro inicial de producto',
                    'referencia' => 'Producto nuevo',
                    'usuario_id' => null,
                ]);
            }
        });

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    public function administrarProductos()
    {
        return view('productos.administrar');
    }

    public function administrarProductosAccion(Request $request, string $accion)
    {
        $accionesPermitidas = ['editar', 'entrada', 'ajuste', 'estado'];

        if (!in_array($accion, $accionesPermitidas)) {
            abort(404);
        }

        $query = Producto::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                ->orWhere('nombre', 'like', "%{$buscar}%")
                ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('tipo') && $request->tipo !== 'TODOS') {
            $query->where('tipo_producto', $request->tipo);
        }

        $productos = $query->orderBy('tipo_producto')
            ->orderBy('nombre')
            ->get();

        return view('productos.administrar-accion', compact('productos', 'accion'));
    }

    public function editarProducto($id)
    {
        $producto = Producto::with(['toga', 'birrete', 'collarin', 'borla'])->findOrFail($id);

        return view('productos.edit', compact('producto'));
    }
    
    public function actualizarProducto(Request $request, $id)
    {
        $producto = Producto::with(['toga', 'birrete', 'collarin', 'borla'])->findOrFail($id);

        $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('productos', 'codigo')->ignore($producto->id),
            ],
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio_alquiler' => 'required|numeric|min:0',
            'stock_total' => 'required|integer|min:0',
            'activo' => 'required|boolean',

            // Campos específicos de toga
            'talla' => 'nullable|string|max:50',
            'color_toga' => 'nullable|string|max:50',
            'observaciones_toga' => 'nullable|string',

            // Campos específicos de birrete
            'tipo_birrete' => 'nullable|in:ESTANDAR,NORMAL,UNIVERSITARIO',
            'color_birrete' => 'nullable|string|max:100',
            'carrera_birrete' => 'nullable|in:ADMINISTRACION,AGRONOMIA,DERECHO,PEDAGOGIA',
            'tiene_borlas_extra' => 'nullable|boolean',
            'descripcion_borlas_extra' => 'nullable|string',

            // Campos específicos de collarín
            'tipo_collarin' => 'nullable|in:NORMAL,UNIVERSITARIO',
            'color_collarin' => 'nullable|string|max:100',
            'tamano' => 'nullable|in:PEQUENO,GRANDE',

            // Campos específicos de borla
            'borla_color' => 'nullable|string|max:100',
            'borla_carrera' => 'nullable|string|max:100',
            'borla_observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $producto) {
            $stockTotalAnterior = $producto->stock_total;
            $stockAlquilado = $producto->stock_alquilado;

            $nuevoStockTotal = (int) $request->stock_total;

            if ($nuevoStockTotal < $stockAlquilado) {
                throw new \Exception('El stock total no puede ser menor que el stock actualmente alquilado.');
            }

            $diferenciaStock = $nuevoStockTotal - $stockTotalAnterior;
            $nuevoStockDisponible = $producto->stock_disponible + $diferenciaStock;

            if ($nuevoStockDisponible < 0) {
                throw new \Exception('El stock disponible no puede quedar negativo.');
            }

            $producto->update([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio_alquiler' => $request->precio_alquiler,
                'stock_total' => $nuevoStockTotal,
                'stock_disponible' => $nuevoStockDisponible,
                'activo' => $request->activo,
            ]);

            if ($producto->tipo_producto === 'TOGA' && $producto->toga) {
                $producto->toga->update([
                    'talla' => $request->input('talla', $producto->toga->talla),
                    'color' => $request->input('color_toga', $producto->toga->color),
                    'observaciones' => $request->input('observaciones_toga', $producto->toga->observaciones),
                ]);
            }

            if ($producto->tipo_producto === 'BIRRETE' && $producto->birrete) {
                $producto->birrete->update([
                    'tipo_birrete' => $request->input('tipo_birrete', $producto->birrete->tipo_birrete),
                    'color' => $request->input('color_birrete', $producto->birrete->color),
                    'carrera' => $request->input('carrera', $producto->birrete->carrera),
                    'tiene_borlas_extra' => $request->input('tiene_borlas_extra', $producto->birrete->tiene_borlas_extra),
                    'descripcion_borlas_extra' => $request->input('descripcion_borlas_extra', $producto->birrete->descripcion_borlas_extra),
                ]);
            }

            if ($producto->tipo_producto === 'COLLARIN' && $producto->collarin) {
                $producto->collarin->update([
                    'tipo_collarin' => $request->input('tipo_collarin', $producto->collarin->tipo_collarin),
                    'color' => $request->input('color_collarin', $producto->collarin->color),
                    'tamano' => $request->input('tamano', $producto->collarin->tamano),
                ]);
            }

            if ($producto->tipo_producto === 'BORLA') {
                $producto->borla()->updateOrCreate(
                    ['producto_id' => $producto->id],
                    [
                        'color' => $request->input('borla_color', $producto->borla->color ?? 'No especificado'),
                        'carrera' => $request->input('borla_carrera', $producto->borla->carrera ?? null),
                        'observaciones' => $request->input('borla_observaciones', $producto->borla->observaciones ?? null),
                    ]
                );
            }

        });

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function desactivarProducto($id)
    {
        $producto = Producto::findOrFail($id);

        $producto->update([
            'activo' => false,
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto desactivado correctamente.');
    }

    public function reactivarProducto($id)
    {
        $producto = Producto::findOrFail($id);

        $producto->update([
            'activo' => true,
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto reactivado correctamente.');
    }

    public function entradaProducto($id)
    {
        $producto = Producto::findOrFail($id);

        return view('productos.entrada', compact('producto'));
    }

    public function guardarEntradaProducto(Request $request, $id, InventarioService $inventarioService)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:100',
        ]);

        try {
            $inventarioService->registrarEntrada(
                productoId: $producto->id,
                cantidad: (int) $request->cantidad,
                motivo: $request->motivo,
                referencia: $request->referencia,
                usuarioId: null
            );

            return redirect()
                ->route('productos.index')
                ->with('success', 'Entrada de inventario registrada correctamente.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['cantidad' => $e->getMessage()]);
        }
    }

    public function movimientosInventario(Request $request)
    {
        $tipo = $request->input('tipo');
        $buscar = $request->input('buscar');

        $movimientos = MovimientoInventario::with('producto')
            ->when($tipo, function ($query, $tipo) {
            $query->where('tipo_movimiento', $tipo);
        })
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
            
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventario.movimientos', compact(
            'movimientos',
            'tipo',
            'buscar'
        ));
    }

    // CLIENTES

    public function clientes()
    {
        $clientes = Cliente::orderBy('id', 'desc')->get();

        return view('clientes.index', compact('clientes'));
    }


    // ALQUILERES   

    public function alquileres()
    {
        $alquileres = Alquiler::with(['cliente', 'detalles.producto', 'pagos'])
            ->orderByDesc('id')
            ->get();

        return view('alquileres.index', compact('alquileres'));
    }

    // ------------------------------------------------------------
    // CLIENTES
    // ------------------------------------------------------------

    public function clientesWeb(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado');

        $clientes = Cliente::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%")
                    ->orWhere('dpi', 'like', "%{$buscar}%")
                    ->orWhere('direccion', 'like', "%{$buscar}%");
                });
            })
            ->when($estado !== null && $estado !== '', function ($query) use ($estado) {
                $query->where('activo', $estado);
            })
            ->orderByDesc('id')
            ->get();

        $totalClientes = Cliente::count();
        $clientesActivos = Cliente::where('activo', true)->count();
        $clientesInactivos = Cliente::where('activo', false)->count();

        return view('clientes.index', compact(
            'clientes',
            'totalClientes',
            'clientesActivos',
            'clientesInactivos',
            'buscar',
            'estado'
        ));
    }

    public function crearClienteWeb()
    {
        return view('clientes.create');
    }

    public function guardarClienteWeb(Request $request)
    {
        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:25'],
            'dpi' => ['nullable', 'string', 'max:20', 'unique:clientes,dpi'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $datos['activo'] = true;

        Cliente::create($datos);

        return redirect()
            ->route('clientes.web')
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function editarClienteWeb($id)
    {
        $cliente = Cliente::findOrFail($id);

        return view('clientes.edit', compact('cliente'));
    }

    public function actualizarClienteWeb(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:25'],
            'dpi' => ['nullable', 'string', 'max:20', 'unique:clientes,dpi,' . $cliente->id],
            'direccion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $cliente->update($datos);

        return redirect()
            ->route('clientes.web')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function desactivarClienteWeb($id)
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->activo = false;
        $cliente->save();

        return redirect()
            ->route('clientes.web')
            ->with('success', 'Cliente desactivado correctamente.');
    }

    public function reactivarClienteWeb($id)
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->activo = true;
        $cliente->save();

        return redirect()
            ->route('clientes.web')
            ->with('success', 'Cliente reactivado correctamente.');
    }

    // ------------------------------------------------------------
    // ALQUILERES
    // ------------------------------------------------------------

    public function alquileresWeb(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado');
        $estadoPago = $request->input('estado_pago');

        $alquileres = Alquiler::with(['cliente', 'detalles.producto', 'pagos'])
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
            })
            ->orderByDesc('id')
            ->get();

        $totalAlquileres = Alquiler::count();
        $alquileresReservados = Alquiler::where('estado', 'RESERVADO')->count();
        $alquileresEntregados = Alquiler::where('estado', 'ENTREGADO')->count();
        $alquileresDevueltos = Alquiler::where('estado', 'DEVUELTO')->count();
        $alquileresCancelados = Alquiler::where('estado', 'CANCELADO')->count();

        return view('alquileres.index', compact(
            'alquileres',
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

    public function crearAlquilerWeb()
    {
        $clientes = Cliente::where('activo', true)
            ->orderBy('nombres')
            ->get();

        $togas = Producto::with('toga')
            ->where('activo', true)
            ->where('tipo_producto', 'TOGA')
            ->where('stock_disponible', '>', 0)
            ->orderBy('nombre')
            ->get();

        $collarines = Producto::with('collarin')
            ->where('activo', true)
            ->where('tipo_producto', 'COLLARIN')
            ->where('stock_disponible', '>', 0)
            ->orderBy('nombre')
            ->get();

        $birretes = Producto::with('birrete')
            ->where('activo', true)
            ->where('tipo_producto', 'BIRRETE')
            ->where('stock_disponible', '>', 0)
            ->orderBy('nombre')
            ->get();

        $borlas = Producto::with('borla')
            ->where('activo', true)
            ->where('tipo_producto', 'BORLA')
            ->where('stock_disponible', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('alquileres.create', compact(
            'clientes',
            'togas',
            'collarines',
            'birretes',
            'borlas'
        ));
    }

    public function guardarAlquilerWeb(Request $request, AlquilerService $alquilerService)
    {
        $productosFormulario = collect($request->input('productos', []))
            ->filter(function ($producto) {
                return !empty($producto['seleccionado'])
                    && !empty($producto['producto_id'])
                    && !empty($producto['cantidad']);
            })
            ->values()
            ->toArray();

        $request->merge([
            'productos' => $productosFormulario,
        ]);

        $datos = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha_entrega' => ['required', 'date'],
            'fecha_devolucion_programada' => ['required', 'date', 'after_or_equal:fecha_entrega'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],

            'institucion_representada' => ['nullable', 'string', 'max:255'],
            'representante_alquiler' => ['nullable', 'string', 'max:255'],
            'hora_entrega_inicio' => ['nullable', 'date_format:H:i'],
            'hora_entrega_fin' => ['nullable', 'date_format:H:i', 'after_or_equal:hora_entrega_inicio'],
            'fecha_limite_pago_final' => ['nullable', 'date'],

            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'exists:productos,id'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],

            'productos.*.collarin_id' => ['required', 'exists:productos,id'],

            'productos.*.birrete_incluido' => ['nullable'],
            'productos.*.birrete_id' => ['nullable', 'exists:productos,id'],

            'productos.*.borla_incluida' => ['nullable'],
            'productos.*.borla_id' => ['nullable', 'exists:productos,id'],

            'productos.*.birrete_extra_id' => ['nullable', 'exists:productos,id'],
            'productos.*.birrete_extra_cantidad' => ['nullable', 'integer', 'min:1'],

            'productos.*.borla_extra_id' => ['nullable', 'exists:productos,id'],
            'productos.*.borla_extra_cantidad' => ['nullable', 'integer', 'min:1'],
        ], [
            'productos.required' => 'Debes seleccionar al menos una toga.',
            'productos.*.collarin_id.required' => 'Cada toga seleccionada debe tener un collarín obligatorio.',
            'productos.*.birrete_extra_cantidad.min' => 'La cantidad de birretes extra debe ser al menos 1.',
            'productos.*.borla_extra_cantidad.min' => 'La cantidad de borlas extra debe ser al menos 1.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validación de extras cobrables
        |--------------------------------------------------------------------------
        | Evita el error de colocar una cantidad extra sin seleccionar cuál
        | producto extra será usado. También evita seleccionar un extra sin indicar
        | la cantidad que se va a cobrar.
        */
        foreach ($datos['productos'] as $index => $productoFormulario) {
            $numeroProducto = $index + 1;

            $birreteExtraId = $productoFormulario['birrete_extra_id'] ?? null;
            $birreteExtraCantidad = $productoFormulario['birrete_extra_cantidad'] ?? null;

            if (empty($birreteExtraId) && !empty($birreteExtraCantidad)) {
                return back()
                    ->withErrors([
                        'productos' => "En la toga seleccionada #{$numeroProducto}, colocaste cantidad de birrete extra, pero no seleccionaste qué birrete extra será cobrado.",
                    ])
                    ->withInput();
            }

            if (!empty($birreteExtraId) && empty($birreteExtraCantidad)) {
                return back()
                    ->withErrors([
                        'productos' => "En la toga seleccionada #{$numeroProducto}, seleccionaste un birrete extra, pero no colocaste la cantidad.",
                    ])
                    ->withInput();
            }

            $borlaExtraId = $productoFormulario['borla_extra_id'] ?? null;
            $borlaExtraCantidad = $productoFormulario['borla_extra_cantidad'] ?? null;

            if (empty($borlaExtraId) && !empty($borlaExtraCantidad)) {
                return back()
                    ->withErrors([
                        'productos' => "En la toga seleccionada #{$numeroProducto}, colocaste cantidad de borla extra, pero no seleccionaste qué borla extra será cobrada.",
                    ])
                    ->withInput();
            }

            if (!empty($borlaExtraId) && empty($borlaExtraCantidad)) {
                return back()
                    ->withErrors([
                        'productos' => "En la toga seleccionada #{$numeroProducto}, seleccionaste una borla extra, pero no colocaste la cantidad.",
                    ])
                    ->withInput();
            }
        }

        $detalles = [];

        foreach ($datos['productos'] as $productoFormulario) {
            $toga = Producto::with('toga')->findOrFail($productoFormulario['producto_id']);

            if ($toga->tipo_producto !== 'TOGA') {
                return back()
                    ->withErrors(['productos' => 'Solo se pueden seleccionar togas como producto principal.'])
                    ->withInput();
            }

            $cantidadToga = (int) $productoFormulario['cantidad'];

            if ($cantidadToga > $toga->stock_disponible) {
                return back()
                    ->withErrors([
                        'productos' => 'No hay suficiente stock disponible para la toga: ' . $toga->nombre,
                    ])
                    ->withInput();
            }

            $accesorios = [];

            // Collarín obligatorio incluido.
            $collarin = Producto::findOrFail($productoFormulario['collarin_id']);

            if ($collarin->tipo_producto !== 'COLLARIN') {
                return back()
                    ->withErrors(['productos' => 'El accesorio obligatorio debe ser un collarín.'])
                    ->withInput();
            }

            if ($cantidadToga > $collarin->stock_disponible) {
                return back()
                    ->withErrors([
                        'productos' => 'No hay suficiente stock disponible para el collarín: ' . $collarin->nombre,
                    ])
                    ->withInput();
            }

            $accesorios[] = [
                'producto_id' => $collarin->id,
                'tipo_accesorio' => 'COLLARIN',
                'tipo_cobro' => 'INCLUIDO',
                'cantidad' => $cantidadToga,
                'precio_unitario' => 0,
            ];

            // Birrete incluido opcional.
            if (!empty($productoFormulario['birrete_incluido']) && !empty($productoFormulario['birrete_id'])) {
                $birrete = Producto::findOrFail($productoFormulario['birrete_id']);

                if ($birrete->tipo_producto !== 'BIRRETE') {
                    return back()
                        ->withErrors(['productos' => 'El birrete incluido debe ser un producto tipo BIRRETE.'])
                        ->withInput();
                }

                if ($cantidadToga > $birrete->stock_disponible) {
                    return back()
                        ->withErrors([
                            'productos' => 'No hay suficiente stock disponible para el birrete: ' . $birrete->nombre,
                        ])
                        ->withInput();
                }

                $accesorios[] = [
                    'producto_id' => $birrete->id,
                    'tipo_accesorio' => 'BIRRETE',
                    'tipo_cobro' => 'INCLUIDO',
                    'cantidad' => $cantidadToga,
                    'precio_unitario' => 0,
                ];
            }

            // Borla incluida opcional.
            if (!empty($productoFormulario['borla_incluida']) && !empty($productoFormulario['borla_id'])) {
                $borla = Producto::findOrFail($productoFormulario['borla_id']);

                if ($borla->tipo_producto !== 'BORLA') {
                    return back()
                        ->withErrors(['productos' => 'La borla incluida debe ser un producto tipo BORLA.'])
                        ->withInput();
                }

                if ($cantidadToga > $borla->stock_disponible) {
                    return back()
                        ->withErrors([
                            'productos' => 'No hay suficiente stock disponible para la borla: ' . $borla->nombre,
                        ])
                        ->withInput();
                }

                $accesorios[] = [
                    'producto_id' => $borla->id,
                    'tipo_accesorio' => 'BORLA',
                    'tipo_cobro' => 'INCLUIDO',
                    'cantidad' => $cantidadToga,
                    'precio_unitario' => 0,
                ];
            }

            // Birrete extra cobrable.
            if (!empty($productoFormulario['birrete_extra_id'])) {
                $birreteExtra = Producto::with('birrete')->findOrFail($productoFormulario['birrete_extra_id']);
                $cantidadExtra = (int) $productoFormulario['birrete_extra_cantidad'];

                if ($birreteExtra->tipo_producto !== 'BIRRETE') {
                    return back()
                        ->withErrors(['productos' => 'El birrete extra debe ser un producto tipo BIRRETE.'])
                        ->withInput();
                }

                if ($cantidadExtra > $birreteExtra->stock_disponible) {
                    return back()
                        ->withErrors([
                            'productos' => 'No hay suficiente stock disponible para el birrete extra: ' . $birreteExtra->nombre,
                        ])
                        ->withInput();
                }

                $precioExtra = $this->precioExtraAccesorio($birreteExtra);

                $accesorios[] = [
                    'producto_id' => $birreteExtra->id,
                    'tipo_accesorio' => 'BIRRETE',
                    'tipo_cobro' => 'EXTRA',
                    'cantidad' => $cantidadExtra,
                    'precio_unitario' => $precioExtra,
                ];
            }

            // Borla extra cobrable.
            if (!empty($productoFormulario['borla_extra_id'])) {
                $borlaExtra = Producto::findOrFail($productoFormulario['borla_extra_id']);
                $cantidadExtra = (int) $productoFormulario['borla_extra_cantidad'];

                if ($borlaExtra->tipo_producto !== 'BORLA') {
                    return back()
                        ->withErrors(['productos' => 'La borla extra debe ser un producto tipo BORLA.'])
                        ->withInput();
                }

                if ($cantidadExtra > $borlaExtra->stock_disponible) {
                    return back()
                        ->withErrors([
                            'productos' => 'No hay suficiente stock disponible para la borla extra: ' . $borlaExtra->nombre,
                        ])
                        ->withInput();
                }

                $precioExtra = $this->precioExtraAccesorio($borlaExtra);

                $accesorios[] = [
                    'producto_id' => $borlaExtra->id,
                    'tipo_accesorio' => 'BORLA',
                    'tipo_cobro' => 'EXTRA',
                    'cantidad' => $cantidadExtra,
                    'precio_unitario' => $precioExtra,
                ];
            }

            $detalles[] = [
                'producto_id' => $toga->id,
                'cantidad' => $cantidadToga,
                'precio_unitario' => $toga->precio_alquiler,
                'accesorios' => $accesorios,
            ];
        }

        try {
            $alquiler = $alquilerService->crearAlquiler(
                (int) $datos['cliente_id'],
                $detalles,
                (float) ($datos['descuento'] ?? 0),
                $datos['fecha_entrega'],
                $datos['fecha_devolucion_programada'],
                $datos['observaciones'] ?? null,
                null
            );

            $alquiler->update([
                'institucion_representada' => $request->institucion_representada,
                'representante_alquiler' => $request->representante_alquiler,
                'hora_entrega_inicio' => $request->hora_entrega_inicio,
                'hora_entrega_fin' => $request->hora_entrega_fin,
                'fecha_limite_pago_final' => $request->fecha_limite_pago_final,
            ]);

            return redirect()
                ->route('alquileres.web')
                ->with('success', 'Alquiler creado correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['productos' => $e->getMessage()])
                ->withInput();
        }
    }


    public function verAlquilerWeb($id)
    {
        $alquiler = Alquiler::with([
            'cliente',
            'detalles.producto',
            'detalles.producto.toga',
            'detalles.accesorios.producto',
            'pagos',
            'detalles.accesorios.producto.birrete',
            'detalles.accesorios.producto.borla',
            'detalles.accesorios.producto.collarin',
        ])->findOrFail($id);

        return view('alquileres.show', compact('alquiler'));
    }

    public function reciboAlquilerWeb($id)
    {
        $alquiler = Alquiler::with([
            'cliente',
            'detalles.producto',
            'detalles.producto.toga',
            'detalles.accesorios.producto',
            'pagos',
            'detalles.accesorios.producto.birrete',
            'detalles.accesorios.producto.borla',
            'detalles.accesorios.producto.collarin',
        ])->findOrFail($id);

        return view('alquileres.recibo', compact('alquiler'));
    }

    public function terminosAlquilerWeb($id)
    {
        $alquiler = Alquiler::with([
            'cliente',
            'detalles.producto',
            'detalles.producto.toga',
            'detalles.accesorios.producto',
            'pagos',
        ])->findOrFail($id);

        return view('alquileres.terminos', compact('alquiler'));
    }

    public function entregarAlquilerWeb($id, AlquilerService $alquilerService)
    {
        try {
        $alquilerService->entregarAlquiler(
            (int) $id,
            null
        );

        return redirect()
            ->route('alquileres.web')
            ->with('success', 'Alquiler entregado correctamente.');
        } catch (\Exception $e) {
        return redirect()
            ->route('alquileres.web')
            ->with('error', $e->getMessage());
        }
    }


    public function devolverAlquilerWeb($id, AlquilerService $alquilerService)
    {
        try {
        $alquilerService->devolverAlquiler(
            (int) $id,
            null
        );

        return redirect()
            ->route('alquileres.web')
            ->with('success', 'Alquiler devuelto correctamente.');
        } catch (\Exception $e) {
        return redirect()
            ->route('alquileres.web')
            ->with('error', $e->getMessage());
        }
    }

    public function cancelarAlquilerWeb($id)
    {
        $alquiler = Alquiler::with(['pagos', 'detalles'])->findOrFail($id);

        if ($alquiler->estado !== 'RESERVADO') {
            return redirect()
                ->route('alquileres.web')
                ->with('error', 'Solo se pueden cancelar alquileres en estado RESERVADO.');
        }

        if ($alquiler->pagos->count() > 0) {
            return redirect()
                ->route('alquileres.web')
                ->with('error', 'No se puede cancelar este alquiler porque ya tiene pagos registrados.');
        }

        $alquiler->estado = 'CANCELADO';
        $alquiler->estado_pago = 'PENDIENTE';
        $alquiler->saldo_pendiente = 0;
        $alquiler->save();

        foreach ($alquiler->detalles as $detalle) {
            $detalle->estado = 'CANCELADO';
            $detalle->save();
        }

        return redirect()
            ->route('alquileres.web')
            ->with('success', 'Alquiler cancelado correctamente.');
    }

    // ------------------------------------------------------------
    // PAGOS
    // ------------------------------------------------------------

    public function crearPagoWeb($id)
    {
        $alquiler = Alquiler::with(['cliente', 'detalles.producto', 'pagos'])
            ->findOrFail($id);

        if ($alquiler->saldo_pendiente <= 0) {
            return redirect()
                ->route('alquileres.web')
                ->with('error', 'Este alquiler ya está pagado completamente.');
        }

        return view('pagos.create', compact('alquiler'));
    }

    public function guardarPagoWeb(Request $request, $id, PagoService $pagoService)
    {
        $alquiler = Alquiler::findOrFail($id);

        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01', 'max:' . $alquiler->saldo_pendiente],
            'metodo_pago' => ['required', 'in:EFECTIVO,TRANSFERENCIA,TARJETA,OTRO'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],

            // Aceptamos varios nombres por si la vista lo manda diferente.
            'fecha_limite_pago_final' => ['nullable', 'date'],
            'fecha_limite_pago' => ['nullable', 'date'],
            'fecha_pago_final' => ['nullable', 'date'],
            'limite_pago_final' => ['nullable', 'date'],
        ]);

        try {
            $pagoService->registrarPago(
                (int) $alquiler->id,
                (float) $datos['monto'],
                $datos['metodo_pago'],
                $datos['referencia'] ?? null,
                $datos['observaciones'] ?? null,
                null
            );

            /*
            |--------------------------------------------------------------------------
            | Guardar fecha límite de pago final
            |--------------------------------------------------------------------------
            | Usamos DB::table para evitar cualquier problema con $fillable del modelo.
            */
            $fechaLimitePagoFinal =
                $request->input('fecha_limite_pago_final')
                ?? $request->input('fecha_limite_pago')
                ?? $request->input('fecha_pago_final')
                ?? $request->input('limite_pago_final');

            if (!empty($fechaLimitePagoFinal)) {
                DB::table('alquileres')
                    ->where('id', $alquiler->id)
                    ->update([
                        'fecha_limite_pago_final' => $fechaLimitePagoFinal,
                        'updated_at' => now(),
                    ]);
            }

            return redirect()
                ->route('alquileres.show', $alquiler->id)
                ->with('success', 'Pago registrado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('pagos.create', $alquiler->id)
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function ajusteProducto($id)
    {
        $producto = Producto::findOrFail($id);

        return view('productos.ajuste', compact('producto'));
    }

    public function guardarAjusteProducto(Request $request, $id, InventarioService $inventarioService)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nuevo_stock_disponible' => 'required|integer|min:0',
            'motivo' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:100',
        ]);

        try {
            $inventarioService->registrarAjuste(
                productoId: $producto->id,
                nuevoStockDisponible: (int) $request->nuevo_stock_disponible,
                motivo: $request->motivo,
                referencia: $request->referencia,
                usuarioId: null
            );

            return redirect()
                ->route('productos.index')
                ->with('success', 'Ajuste de inventario registrado correctamente.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['nuevo_stock_disponible' => $e->getMessage()]);
        }
    }

    private function precioExtraAccesorio(Producto $producto): float
    {
        if ($producto->tipo_producto === 'BORLA') {
            return 5.00;
        }

        if ($producto->tipo_producto === 'BIRRETE') {
            $tipoBirrete = optional($producto->birrete)->tipo_birrete;

            return $tipoBirrete === 'UNIVERSITARIO'
                ? 50.00
                : 25.00;
        }

        return 0.00;
    }

}