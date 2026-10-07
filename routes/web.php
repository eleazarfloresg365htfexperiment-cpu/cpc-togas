<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ControlAlquilerController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\ExportacionController;
use App\Http\Controllers\Auth\ConfiguracionInicialController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\AlquilerController;
use App\Http\Controllers\Web\AlquilerDocumentoController;
use App\Http\Controllers\Web\ClienteController;
use App\Http\Controllers\Web\DanoController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FabricacionController;
use App\Http\Controllers\Web\InventarioController;
use App\Http\Controllers\Web\PagoController;
use App\Http\Controllers\Web\ProductoController;
use App\Http\Controllers\Web\RutaAnteriorController;
use App\Http\Controllers\Web\UsuarioController;

/*
|--------------------------------------------------------------------------
| Rutas web del sistema
|--------------------------------------------------------------------------
| Convención: /recurso, /recurso/crear, /recurso/{id}, /recurso/{id}/editar
| (los verbos "crear" y "editar" se configuran en AppServiceProvider).
| Cada ruta tiene nombre "recurso.accion" y las vistas siempre usan route().
*/

// - - - - ACCESO (sin sesión) - - - -

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'mostrar'])->name('login');
    Route::post('/login', [LoginController::class, 'iniciar'])
        ->middleware('throttle:20,1')
        ->name('login.iniciar');

    // Solo funciona mientras el sistema no tenga ningún usuario.
    Route::get('/configuracion-inicial', [ConfiguracionInicialController::class, 'mostrar'])
        ->name('configuracion-inicial');
    Route::post('/configuracion-inicial', [ConfiguracionInicialController::class, 'guardar'])
        ->name('configuracion-inicial.guardar');
});

// - - - - TODO LO DEMÁS REQUIERE SESIÓN - - - -

Route::middleware(['auth', 'usuario.activo'])->group(function () {
    Route::post('/logout', [LoginController::class, 'cerrar'])->name('logout');

    // - - - - USUARIOS - - - -

    Route::resource('usuarios', UsuarioController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->parameters(['usuarios' => 'usuario']);
    Route::patch('/usuarios/{usuario}/desactivar', [UsuarioController::class, 'desactivar'])
        ->name('usuarios.desactivar');
    Route::patch('/usuarios/{usuario}/reactivar', [UsuarioController::class, 'reactivar'])
        ->name('usuarios.reactivar');

    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // - - - - PRODUCTOS - - - -
    // Las rutas fijas (administrar) van antes que las que llevan {producto}.

    Route::get('/productos/administrar', [ProductoController::class, 'administrar'])
        ->name('productos.administrar');
    Route::get('/productos/administrar/{accion}', [ProductoController::class, 'administrarAccion'])
        ->name('productos.administrar.accion');

    Route::resource('productos', ProductoController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->parameters(['productos' => 'producto']);

    Route::patch('/productos/{producto}/desactivar', [ProductoController::class, 'desactivar'])
        ->name('productos.desactivar');
    Route::patch('/productos/{producto}/reactivar', [ProductoController::class, 'reactivar'])
        ->name('productos.reactivar');

    // Movimientos de inventario de un producto
    Route::get('/productos/{producto}/entrada', [InventarioController::class, 'entrada'])
        ->name('productos.entrada');
    Route::post('/productos/{producto}/entrada', [InventarioController::class, 'guardarEntrada'])
        ->name('productos.entrada.guardar');
    Route::get('/productos/{producto}/ajuste', [InventarioController::class, 'ajuste'])
        ->name('productos.ajuste');
    Route::post('/productos/{producto}/ajuste', [InventarioController::class, 'guardarAjuste'])
        ->name('productos.ajuste.guardar');
    Route::get('/inventario/movimientos', [InventarioController::class, 'movimientos'])
        ->name('inventario.movimientos');

    // - - - - CLIENTES - - - -

    Route::resource('clientes', ClienteController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->parameters(['clientes' => 'cliente']);

    Route::post('/clientes/{cliente}/desactivar', [ClienteController::class, 'desactivar'])
        ->name('clientes.desactivar');
    Route::post('/clientes/{cliente}/reactivar', [ClienteController::class, 'reactivar'])
        ->name('clientes.reactivar');

    // - - - - ALQUILERES - - - -

    Route::resource('alquileres', AlquilerController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
        ->parameters(['alquileres' => 'alquiler']);

    Route::post('/alquileres/{alquiler}/entregar', [AlquilerController::class, 'entregar'])
        ->name('alquileres.entregar');
    Route::post('/alquileres/{alquiler}/devolver', [AlquilerController::class, 'devolver'])
        ->name('alquileres.devolver');
    Route::post('/alquileres/{alquiler}/cancelar', [AlquilerController::class, 'cancelar'])
        ->name('alquileres.cancelar');

    Route::post('/alquileres/{alquiler}/fabricaciones/{fabricacionId}/completar', [FabricacionController::class, 'completar'])
        ->whereNumber('fabricacionId')
        ->name('alquileres.fabricaciones.completar');

    Route::post('/alquileres/{alquiler}/danos', [DanoController::class, 'store'])
        ->name('alquileres.danos.store');
    Route::delete('/alquileres/{alquiler}/danos/{danoId}', [DanoController::class, 'destroy'])
        ->whereNumber('danoId')
        ->name('alquileres.danos.destroy');

    // Documentos para imprimir
    Route::get('/alquileres/{alquiler}/recibo', [AlquilerDocumentoController::class, 'recibo'])
        ->name('alquileres.recibo');
    Route::get('/alquileres/{alquiler}/terminos', [AlquilerDocumentoController::class, 'compromiso'])
        ->name('alquileres.terminos');
    Route::get('/alquileres/{alquiler}/devolucion', [AlquilerDocumentoController::class, 'devolucion'])
        ->name('alquileres.devolucion-carta');

    // - - - - PAGOS - - - -

    Route::get('/alquileres/{alquiler}/pagar', [PagoController::class, 'create'])
        ->name('pagos.create');
    Route::post('/alquileres/{alquiler}/pagar', [PagoController::class, 'store'])
        ->name('pagos.store');

    // - - - - CALENDARIO - - - -

    Route::get('/calendario', [CalendarioController::class, 'index'])
        ->name('calendario.index');
    Route::get('/calendario/eventos', [CalendarioController::class, 'eventos'])
        ->name('calendario.eventos');

    // - - - - CONTROL Y ESTADÍSTICAS - - - -

    Route::get('/control-alquileres', [ControlAlquilerController::class, 'index'])
        ->name('control-alquileres.index');

    Route::get('/estadisticas', [EstadisticasController::class, 'index'])
        ->name('estadisticas.index');
    Route::get('/estadisticas/exportar/xlsx', [EstadisticasController::class, 'exportarXlsx'])
        ->name('estadisticas.exportar.xlsx');
    Route::post('/estadisticas/exportar/pdf', [EstadisticasController::class, 'exportarPdf'])
        ->name('estadisticas.exportar.pdf');

    // - - - - EXPORTACIONES - - - -

    Route::get('/exportaciones/alquileres/excel', [ExportacionController::class, 'alquileresExcel'])
        ->name('exportaciones.alquileres.excel');
    Route::get('/exportaciones/movimientos/excel', [ExportacionController::class, 'movimientosExcel'])
        ->name('exportaciones.movimientos.excel');
    Route::get('/exportaciones/alquileres/pdf', [ExportacionController::class, 'alquileresPdf'])
        ->name('exportaciones.alquileres.pdf');
    Route::get('/exportaciones/movimientos/pdf', [ExportacionController::class, 'movimientosPdf'])
        ->name('exportaciones.movimientos.pdf');

    // - - - - DIRECCIONES ANTERIORES (-web) - - - -
    // Los marcadores, las pestañas abiertas y los formularios que se cargaron antes
    // del cambio siguen funcionando: 308 conserva el método (POST, PUT, ...).
    // Esta ruta va al final para no tapar a ninguna de las anteriores.

    Route::any('/{seccion}-web/{resto?}', RutaAnteriorController::class)
        ->where('seccion', 'productos|clientes|alquileres|calendario')
        ->where('resto', '.*');
});
