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
use App\Models\ProductoCapa;
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
            'tipo_producto' => 'required|in:TOGA,BIRRETE,COLLARIN,BORLA,CAPA',
            'descripcion' => 'nullable|string',
            'precio_alquiler' => 'required|numeric|min:0',
            'stock_total' => 'required|integer|min:0',
            'activo' => 'required|boolean',

            'color_toga' => 'nullable|string|max:100',
            'talla_toga' => 'nullable|string|max:50',
            'tipo_toga' => 'nullable|in:ESTANDAR,UNIVERSITARIA',
            'observaciones_toga' => 'nullable|string',

            'tipo_birrete' => 'nullable|in:ESTANDAR,NORMAL,UNIVERSITARIO',
            'color_birrete' => 'nullable|string|max:100',
            'observaciones_birrete' => 'nullable|string',

            'tipo_collarin' => 'nullable|in:NORMAL,UNIVERSITARIO',
            'codigo_color_collarin' => 'nullable|string|max:50',
            'color_collarin' => 'nullable|in:Dorado,Rojo,Verde,Azul',
            'tamano_collarin' => 'nullable|in:PEQUENO,GRANDE',

            'borla_codigo_color' => 'nullable|string|max:50',
            'borla_color' => 'nullable|in:Celeste,Rojo,Verde,Amarillo,Naranja',
            'borla_observaciones' => 'nullable|string',

            'codigo_color_capa' => 'nullable|string|max:50',
            'carrera_capa' => 'nullable|in:AGRONOMIA,DERECHO,PEDAGOGIA,MEDICINA,CIENCIAS_ECONOMICAS',
            'color_capa' => 'nullable|string|max:100',
            'talla_capa' => 'nullable|string|max:50',
            'observaciones_capa' => 'nullable|string',
        ]);

        if ($request->tipo_producto === 'COLLARIN') {

            if (!$request->filled('tipo_collarin')) {

                return back()
                    ->withErrors([
                        'tipo_collarin' => 'Debe seleccionar el tipo de collarín.'
                    ])
                    ->withInput();

            }

            if (!$request->filled('color_collarin')) {

                return back()
                    ->withErrors([
                        'color_collarin' => 'Debe seleccionar el color del collarín.'
                    ])
                    ->withInput();

            }

            if (!$request->filled('codigo_color_collarin')) {

                return back()
                    ->withErrors([
                        'codigo_color_collarin' => 'Debe indicar el código de color del collarín.'
                    ])
                    ->withInput();

            }

            if (
                $request->tipo_collarin === 'NORMAL' &&
                $request->color_collarin === 'Azul'
            ) {

                return back()
                    ->withErrors([
                        'color_collarin' =>
                            'El color azul corresponde únicamente a los collarines universitarios.'
                    ])
                    ->withInput();

            }

            if (
                $request->tipo_collarin === 'UNIVERSITARIO' &&
                $request->color_collarin !== 'Azul'
            ) {

                return back()
                    ->withErrors([
                        'color_collarin' =>
                            'Los collarines universitarios deben utilizar el color azul.'
                    ])
                    ->withInput();

            }

        }

        if ($request->tipo_producto === 'BIRRETE') {

            if (!$request->filled('tipo_birrete')) {

                return back()
                    ->withErrors([
                        'tipo_birrete' => 'Debe seleccionar el tipo de birrete.'
                    ])
                    ->withInput();

            }
        }

        if ($request->tipo_producto === 'CAPA') {

            if (
                !$request->filled('codigo_color_capa') ||
                !$request->filled('color_capa') ||
                !$request->filled('carrera_capa')
            ) {

                return back()
                    ->withErrors([
                        'carrera_capa' =>
                            'Debe indicar la carrera, el código y el color de la capa.'
                    ])
                    ->withInput();
            }

        }

        if ($request->tipo_producto === 'BORLA') {

            if (
                !$request->filled('borla_codigo_color') ||
                !$request->filled('borla_color')
            ) {

                return back()
                    ->withErrors([
                        'borla_color' => 'Debe indicar el código y el color de la borla.'
                    ])
                    ->withInput();

            }

        }

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

            switch ($request->tipo_producto) {

                case 'TOGA':

                    $this->guardarDetalle(
                        'toga',
                        $producto,
                        [
                            'tipo_toga' => $request->tipo_toga ?? 'ESTANDAR',
                            'talla' => $request->talla_toga ?? 'No especificado',
                            'color' => $request->color_toga ?? 'No especificado',
                            'observaciones' => $request->observaciones_toga,
                        ]
                    );

                    break;

                case 'CAPA':

                    $this->guardarDetalle(
                        'capa',
                        $producto,
                        [
                            'talla' => $request->talla_capa ?? 'No especificado',
                            'carrera' => $request->carrera_capa,
                            'codigo_color' => $request->codigo_color_capa,
                            'color' => $request->color_capa ?? 'No especificado',
                            'observaciones' => $request->observaciones_capa,
                        ]
                    );

                    break;

                case 'BIRRETE':

                    $this->guardarDetalle(
                        'birrete',
                        $producto,
                        [
                            'tipo_birrete' => $request->tipo_birrete ?? 'ESTANDAR',
                            'color' => $request->color_birrete,
                            'observaciones' => $request->observaciones_birrete,
                        ]
                    );

                    break;

                case 'COLLARIN':

                    $this->guardarDetalle(
                        'collarin',
                        $producto,
                        [
                            'tipo_collarin' => $request->tipo_collarin,
                            'codigo_color' => $request->codigo_color_collarin,
                            'color' => $request->color_collarin,
                            'tamano' => null,
                        ]
                    );

                    break;

                case 'BORLA':

                    $this->guardarDetalle(
                        'borla',
                        $producto,
                        [
                            'codigo_color' => $request->borla_codigo_color,
                            'color' => $request->borla_color,
                            'observaciones' => $request->borla_observaciones,
                        ]
                    );

                    break;
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
        $producto = Producto::with(['toga', 'birrete', 'collarin', 'borla', 'capa'])->findOrFail($id);

        return view('productos.edit', compact('producto'));
    }
    
    public function actualizarProducto(Request $request, $id)
    {
        $producto = Producto::with([
            'toga',
            'capa',
            'birrete',
            'collarin',
            'borla'
        ])->findOrFail($id);

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

            // TOGA
            'talla_toga' => 'nullable|string|max:50',
            'color_toga' => 'nullable|string|max:50',
            'tipo_toga' => 'nullable|in:ESTANDAR,UNIVERSITARIA',
            'observaciones_toga' => 'nullable|string',

            // CAPA
            'codigo_color_capa' => 'nullable|string|max:50',
            'color_capa' => 'nullable|string|max:50',
            'carrera_capa' => 'nullable|in:AGRONOMIA,DERECHO,PEDAGOGIA,MEDICINA,CIENCIAS_ECONOMICAS',
            'talla_capa' => 'nullable|string|max:50',
            'observaciones_capa' => 'nullable|string',

            // BIRRETE
            'tipo_birrete' => 'nullable|in:ESTANDAR,NORMAL,UNIVERSITARIO',
            'color_birrete' => 'nullable|string|max:100',
            'observaciones_birrete' => 'nullable|string',

            // COLLARIN
            'tipo_collarin' => 'nullable|in:NORMAL,UNIVERSITARIO',
            'codigo_color_collarin' => 'nullable|string|max:50',
            'color_collarin' => 'nullable|in:Dorado,Rojo,Verde,Azul',
            'tamano_collarin' => 'nullable|in:PEQUENO,GRANDE',

            // BORLA
            'borla_codigo_color' => 'nullable|string|max:50',
            'borla_color' => 'nullable|in:Celeste,Rojo,Verde,Amarillo,Naranja',
            'borla_observaciones' => 'nullable|string',

        ]);

        if ($producto->tipo_producto === 'COLLARIN') {

            if (!$request->filled('tipo_collarin')) {

                return back()
                    ->withErrors([
                        'tipo_collarin' => 'Debe seleccionar el tipo de collarín.'
                    ])
                    ->withInput();

            }

            if (!$request->filled('color_collarin')) {

                return back()
                    ->withErrors([
                        'color_collarin' => 'Debe seleccionar el color del collarín.'
                    ])
                    ->withInput();

            }

            if (!$request->filled('codigo_color_collarin')) {

                return back()
                    ->withErrors([
                        'codigo_color_collarin' => 'Debe indicar el código de color del collarín.'
                    ])
                    ->withInput();

            }

            if (
                $request->tipo_collarin === 'NORMAL' &&
                $request->color_collarin === 'Azul'
            ) {

                return back()
                    ->withErrors([
                        'color_collarin' =>
                            'El color azul corresponde únicamente a los collarines universitarios.'
                    ])
                    ->withInput();

            }

            if (
                $request->tipo_collarin === 'UNIVERSITARIO' &&
                $request->color_collarin !== 'Azul'
            ) {

                return back()
                    ->withErrors([
                        'color_collarin' =>
                            'Los collarines universitarios deben utilizar el color azul.'
                    ])
                    ->withInput();

            }

        }

        DB::transaction(function () use ($request, $producto) {

            $stockTotalAnterior = $producto->stock_total;
            $stockAlquilado = $producto->stock_alquilado;

            $nuevoStockTotal = (int) $request->stock_total;

            if ($nuevoStockTotal < $stockAlquilado) {
                throw new \Exception(
                    'El stock total no puede ser menor que el stock actualmente alquilado.'
                );
            }

            $diferenciaStock = $nuevoStockTotal - $stockTotalAnterior;

            $nuevoStockDisponible =
                $producto->stock_disponible + $diferenciaStock;

            if ($nuevoStockDisponible < 0) {
                throw new \Exception(
                    'El stock disponible no puede quedar negativo.'
                );
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

            switch ($producto->tipo_producto) {

                case 'TOGA':

                    $this->guardarDetalle(
                        'toga',
                        $producto,
                        [
                            'tipo_toga' => $request->input(
                                'tipo_toga',
                                $producto->toga->tipo_toga ?? 'ESTANDAR'
                            ),

                            'talla' => $request->input(
                                'talla_toga',
                                $producto->toga->talla ?? null
                            ),

                            'color' => $request->input(
                                'color_toga',
                                $producto->toga->color ?? null
                            ),

                            'observaciones' => $request->input(
                                'observaciones_toga',
                                $producto->toga->observaciones ?? null
                            ),
                        ]
                    );

                    break;

                case 'CAPA':

                    $this->guardarDetalle(
                        'capa',
                        $producto,
                        [
                            'talla' => $request->input(
                                'talla_capa',
                                $producto->capa->talla ?? null
                            ),

                            'carrera' => $request->input(
                                'carrera_capa',
                                $producto->capa->carrera ?? null
                            ),

                            'codigo_color' => $request->input(
                                'codigo_color_capa',
                                $producto->capa->codigo_color ?? null
                            ),

                            'color' => $request->input(
                                'color_capa',
                                $producto->capa->color ?? null
                            ),

                            'observaciones' => $request->input(
                                'observaciones_capa',
                                $producto->capa->observaciones ?? null
                            ),
                        ]
                    );

                    break;

                case 'BIRRETE':

                    $this->guardarDetalle(
                        'birrete',
                        $producto,
                        [
                            'tipo_birrete' => $request->input(
                                'tipo_birrete',
                                $producto->birrete->tipo_birrete ?? 'ESTANDAR'
                            ),

                            'color' => $request->input(
                                'color_birrete',
                                $producto->birrete->color ?? null
                            ),

                            'observaciones' => $request->input(
                                'observaciones_birrete',
                                $producto->birrete->observaciones ?? null
                            ),
                        ]
                    );

                    break;

                case 'COLLARIN':

                    $this->guardarDetalle(
                        'collarin',
                        $producto,
                        [
                            'tipo_collarin' => $request->input(
                                'tipo_collarin',
                                $producto->collarin->tipo_collarin ?? 'NORMAL'
                            ),

                            'codigo_color' => $request->input(
                                'codigo_color_collarin',
                                $producto->collarin->codigo_color ?? null
                            ),

                            'color' => $request->input(
                                'color_collarin',
                                $producto->collarin->color ?? null
                            ),

                            'tamano' => null,
                        ]
                    );

                    break;

                case 'BORLA':

                    $this->guardarDetalle(
                        'borla',
                        $producto,
                        [
                            'codigo_color' => $request->input(
                                'borla_codigo_color',
                                $producto->borla->codigo_color ?? null
                            ),

                            'color' => $request->input(
                                'borla_color',
                                $producto->borla->color ?? null
                            ),

                            'observaciones' => $request->input(
                                'borla_observaciones',
                                $producto->borla->observaciones ?? null
                            ),
                        ]
                    );

                    break;
            }

        });

        return redirect()
            ->route('productos.index')
            ->with(
                'success',
                'Producto actualizado correctamente.'
            );
    }

    /**
     * Crea o actualiza el registro detalle de un producto.
     */
    private function guardarDetalle(
        string $relacion,
        Producto $producto,
        array $datos
    ): void {

        $producto->{$relacion}()->updateOrCreate(
            [
                'producto_id' => $producto->id
            ],
            $datos
        );

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
                    ->orWhere('direccion', 'like', "%{$buscar}%")
                    ->orWhere('institucion_representada', 'like', "%{$buscar}%");
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
            'institucion_representada' => ['nullable', 'string', 'max:255'],
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
            'institucion_representada' => ['nullable', 'string', 'max:255'],
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

        $capas = Producto::with('capa')
            ->where('activo', true)
            ->where('tipo_producto', 'CAPA')
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
            'borlas',
            'capas'
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

            'fecha_alquiler' => ['required', 'string', 'date', 'before_or_equal:fecha_entrega'],
            'fecha_entrega' => ['required', 'string', 'date', 'after_or_equal:fecha_alquiler'],
            'hora_entrega' => ['nullable', 'date_format:H:i'],
            'fecha_devolucion_programada' => ['required', 'string', 'date', 'after_or_equal:fecha_entrega'],
            'hora_devolucion_programada' => ['nullable', 'date_format:H:i'],
            
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'descuento_por_toga' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],

            'institucion_representada' => ['nullable', 'string', 'max:255'],
            'representante_alquiler' => ['nullable', 'string', 'max:255'],
            'hora_entrega_inicio' => ['nullable', 'date_format:H:i'],
            'hora_entrega_fin' => ['nullable', 'date_format:H:i', 'after_or_equal:hora_entrega_inicio'],
            'fecha_limite_pago_final' => ['nullable', 'date'],

            'fabricacion_autorizada' => ['nullable', 'boolean'],
            'fabricacion_responsable' => ['nullable', 'string', 'max:255'],
            'fabricacion_motivo' => ['nullable', 'string', 'max:255'],
            'fabricacion_observaciones' => ['nullable', 'string', 'max:500'],

            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'exists:productos,id'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],

            'productos.*.collarin_id' => ['required', 'exists:productos,id'],
            'productos.*.capa_id' => ['nullable', 'exists:productos,id'],   

            'productos.*.birrete_incluido' => ['nullable'],
            'productos.*.birrete_id' => ['nullable', 'exists:productos,id'],

            'productos.*.borla_incluida' => ['nullable'],
            'productos.*.borla_id' => ['nullable', 'exists:productos,id'],

            'productos.*.birrete_extra_id' => ['nullable', 'exists:productos,id'],
            'productos.*.birrete_extra_cantidad' => ['nullable', 'integer', 'min:1'],

            'productos.*.borla_extra_id' => ['nullable', 'exists:productos,id'],
            'productos.*.borla_extra_cantidad' => ['nullable', 'integer', 'min:1'],
        ], [
            'fecha_alquiler.required' => 'Debes indicar la fecha de reserva.',
            'fecha_alquiler.date' => 'La fecha de reserva no tiene un formato válido.',
            'fecha_alquiler.before_or_equal' => 'La fecha de reserva no puede ser posterior a la fecha de entrega.',

            'fecha_entrega.required' => 'Debes indicar la fecha de entrega.',
            'fecha_entrega.date' => 'La fecha de entrega no tiene un formato válido.',
            'fecha_entrega.after_or_equal' => 'La fecha de entrega no puede ser anterior a la fecha de reserva.',

            'fecha_devolucion_programada.required' => 'Debes indicar la fecha de devolución programada.',
            'fecha_devolucion_programada.date' => 'La fecha de devolución programada no tiene un formato válido.',
            'fecha_devolucion_programada.after_or_equal' => 'La fecha de devolución programada no puede ser anterior a la fecha de entrega.',

            'productos.required' => 'Debes seleccionar al menos una toga.',
            'productos.*.collarin_id.required' => 'Cada toga seleccionada debe tener un collarín obligatorio.',
            'productos.*.birrete_extra_cantidad.min' => 'La cantidad de birretes extra debe ser al menos 1.',
            'productos.*.borla_extra_cantidad.min' => 'La cantidad de borlas extra debe ser al menos 1.',
        ]);

        if (
            $request->fecha_entrega === $request->fecha_devolucion_programada &&
            $request->hora_entrega &&
            $request->hora_devolucion_programada &&
            $request->hora_devolucion_programada <= $request->hora_entrega
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'hora_devolucion_programada' => 'Si la entrega y devolución son el mismo día, la hora de devolución debe ser posterior a la hora de entrega.',
                ]);
        }

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
            $item = [
                'producto_id' => $productoFormulario['producto_id'],
                'cantidad' => $productoFormulario['cantidad'],
                'accesorios' => [],
            ];

            if (!empty($productoFormulario['collarin_id'])) {
                $item['accesorios'][] = [
                    'producto_id' => $productoFormulario['collarin_id'],
                    'tipo_accesorio' => 'COLLARIN',
                    'tipo_cobro' => 'INCLUIDO',
                    'cantidad' => $productoFormulario['cantidad'],
                    'precio_unitario' => 0,
                ];
            }

            if (!empty($productoFormulario['capa_id'])) {
                $item['accesorios'][] = [
                    'producto_id' => $productoFormulario['capa_id'],
                    'tipo_accesorio' => 'CAPA',
                    'tipo_cobro' => 'INCLUIDO',
                    'cantidad' => $productoFormulario['cantidad'],
                    'precio_unitario' => 0,
                ];
            }

            if (!empty($productoFormulario['birrete_incluido']) && !empty($productoFormulario['birrete_id'])) {
                $item['accesorios'][] = [
                    'producto_id' => $productoFormulario['birrete_id'],
                    'tipo_accesorio' => 'BIRRETE',
                    'tipo_cobro' => 'INCLUIDO',
                    'cantidad' => $productoFormulario['cantidad'],
                    'precio_unitario' => 0,
                ];
            }

            if (!empty($productoFormulario['borla_incluida']) && !empty($productoFormulario['borla_id'])) {
                $item['accesorios'][] = [
                    'producto_id' => $productoFormulario['borla_id'],
                    'tipo_accesorio' => 'BORLA',
                    'tipo_cobro' => 'INCLUIDO',
                    'cantidad' => $productoFormulario['cantidad'],
                    'precio_unitario' => 0,
                ];
            }

            if (!empty($productoFormulario['birrete_extra_id']) && !empty($productoFormulario['birrete_extra_cantidad'])) {
                $item['accesorios'][] = [
                    'producto_id' => $productoFormulario['birrete_extra_id'],
                    'tipo_accesorio' => 'BIRRETE',
                    'tipo_cobro' => 'EXTRA',
                    'cantidad' => $productoFormulario['birrete_extra_cantidad'],
                    'precio_unitario' => null,
                ];
            }

            if (!empty($productoFormulario['borla_extra_id']) && !empty($productoFormulario['borla_extra_cantidad'])) {
                $item['accesorios'][] = [
                    'producto_id' => $productoFormulario['borla_extra_id'],
                    'tipo_accesorio' => 'BORLA',
                    'tipo_cobro' => 'EXTRA',
                    'cantidad' => $productoFormulario['borla_extra_cantidad'],
                    'precio_unitario' => null,
                ];
            }

            $detalles[] = $item;
        }

        try {
            $fabricacionData = [];

            if (!empty($datos['fabricacion_autorizada'])) {
                $fabricacionData = [
                    'responsable' => $datos['fabricacion_responsable'] ?? null,
                    'motivo' => $datos['fabricacion_motivo'] ?? null,
                    'observaciones' => $datos['fabricacion_observaciones'] ?? null,
                    'usuario_id' => null,
                    'fecha' => now()->toDateString(),
                ];
            }

            $alquiler = $alquilerService->crearAlquiler(
                clienteId: (int) $datos['cliente_id'],
                productos: $detalles,
                descuento: (float) ($datos['descuento'] ?? 0),
                descuentoPorToga: (float) ($datos['descuento_por_toga'] ?? 0),
                fechaAlquiler: $datos['fecha_alquiler'],
                fechaEntrega: $datos['fecha_entrega'],
                fechaDevolucionProgramada: $datos['fecha_devolucion_programada'],
                observaciones: $datos['observaciones'] ?? null,
                usuarioId: null,
                fabricacionData: $fabricacionData,
            );

            $alquiler->update([
                // Fecha real de reserva seleccionada en el formulario.
                // Se usa el campo fecha_alquiler existente en la tabla alquileres.
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


    public function devolverAlquilerWeb(Request $request, $id, AlquilerService $alquilerService)
    {
        $request->validate([
            'descuento_mora' => ['nullable', 'numeric', 'min:0'],
            'observacion_mora' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $alquilerService->devolverAlquiler(
                (int) $id,
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
            'monto' => ['required', 'numeric', 'min:0', 'max:' . $alquiler->saldo_pendiente],
            'descuento_aplicado' => ['nullable', 'numeric', 'min:0'],
            'observacion_descuento' => ['nullable', 'string', 'max:1000'],

            'metodo_pago' => ['required', 'in:EFECTIVO,TRANSFERENCIA,TARJETA,OTRO'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],

            'fecha_limite_pago_final' => ['nullable', 'date'],
            'fecha_limite_pago' => ['nullable', 'date'],
            'fecha_pago_final' => ['nullable', 'date'],
            'limite_pago_final' => ['nullable', 'date'],
        ]);

        try {
            $monto = (float) ($datos['monto'] ?? 0);
            $descuentoAplicado = (float) ($datos['descuento_aplicado'] ?? 0);

            if (($monto + $descuentoAplicado) <= 0) {
                return redirect()
                    ->route('pagos.create', $alquiler->id)
                    ->withInput()
                    ->withErrors([
                        'monto' => 'Debe ingresar un pago o un descuento mayor a cero.',
                    ]);
            }

            if (($monto + $descuentoAplicado) > (float) $alquiler->saldo_pendiente) {
                return redirect()
                    ->route('pagos.create', $alquiler->id)
                    ->withInput()
                    ->withErrors([
                        'monto' => 'La suma del pago y el descuento no puede ser mayor al saldo pendiente.',
                    ]);
            }

            if ($descuentoAplicado > 0 && empty($datos['observacion_descuento'])) {
                return redirect()
                    ->route('pagos.create', $alquiler->id)
                    ->withInput()
                    ->withErrors([
                        'observacion_descuento' => 'Debe ingresar una observación para justificar el descuento aplicado.',
                    ]);
            }

            $pagoService->registrarPago(
                alquilerId: (int) $alquiler->id,
                monto: $monto,
                metodoPago: $datos['metodo_pago'],
                referencia: $datos['referencia'] ?? null,
                observaciones: $datos['observaciones'] ?? null,
                usuarioId: auth()->id(),
                descuentoAplicado: $descuentoAplicado,
                observacionDescuento: $datos['observacion_descuento'] ?? null
            );

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
                ->with('success', 'Pago o descuento registrado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('pagos.create', $alquiler->id)
                ->withInput()
                ->with('error', $e->getMessage());
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

}