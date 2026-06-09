<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use App\Http\Controllers\ExportacionController;
use App\Http\Controllers\CalendarioController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [WebController::class, 'dashboard']);

Route::get('/productos-web', [WebController::class, 'productos'])
    ->name('productos.index');

 //- - - - PRODUCTOS - - - -

Route::get('/productos-web/crear', [WebController::class, 'crearProducto'])
    ->name('productos.create');
Route::post('/productos-web', [WebController::class, 'guardarProducto'])
    ->name('productos.store');
Route::get('/productos-web/administrar', [WebController::class, 'administrarProductos'])
    ->name('productos.administrar');
Route::get('/productos-web/administrar/{accion}', [WebController::class, 'administrarProductosAccion'])
    ->name('productos.administrar.accion');
Route::get('/productos-web/{id}/editar', [WebController::class, 'editarProducto'])
    ->name('productos.edit');
Route::put('/productos-web/{id}', [WebController::class, 'actualizarProducto'])
    ->name('productos.update');
Route::patch('/productos-web/{id}/desactivar', [WebController::class, 'desactivarProducto'])
    ->name('productos.desactivar');
Route::patch('/productos-web/{id}/reactivar', [WebController::class, 'reactivarProducto'])
    ->name('productos.reactivar');
Route::get('/productos-web/{id}/entrada', [WebController::class, 'entradaProducto'])
    ->name('productos.entrada');
Route::post('/productos-web/{id}/entrada', [WebController::class, 'guardarEntradaProducto'])
    ->name('productos.entrada.guardar');
Route::get('/productos-web/{id}/ajuste', [WebController::class, 'ajusteProducto'])
    ->name('productos.ajuste');
Route::post('/productos-web/{id}/ajuste', [WebController::class, 'guardarAjusteProducto'])
    ->name('productos.ajuste.guardar');
Route::get('/inventario/movimientos', [WebController::class, 'movimientosInventario'])
    ->name('inventario.movimientos');


 //- - - - CLIENTES - - - -    

Route::get('/clientes-web', [WebController::class, 'clientesWeb'])
    ->name('clientes.web');
Route::get('/clientes-web/crear', [WebController::class, 'crearClienteWeb'])
    ->name('clientes.create');
Route::post('/clientes-web', [WebController::class, 'guardarClienteWeb'])
    ->name('clientes.store');
Route::get('/clientes-web/{id}/editar', [WebController::class, 'editarClienteWeb'])
    ->name('clientes.edit');
Route::put('/clientes-web/{id}', [WebController::class, 'actualizarClienteWeb'])
    ->name('clientes.update');
Route::post('/clientes-web/{id}/desactivar', [WebController::class, 'desactivarClienteWeb'])
    ->name('clientes.desactivar');
Route::post('/clientes-web/{id}/reactivar', [WebController::class, 'reactivarClienteWeb'])
    ->name('clientes.reactivar');

 //- - - - ALQUILERES - - - -

Route::get('/alquileres-web', [WebController::class, 'alquileresWeb'])
    ->name('alquileres.web');
Route::get('/alquileres-web/crear', [WebController::class, 'crearAlquilerWeb'])
    ->name('alquileres.create');
Route::post('/alquileres-web', [WebController::class, 'guardarAlquilerWeb'])
    ->name('alquileres.store');
Route::get('/alquileres-web/{id}', [WebController::class, 'verAlquilerWeb'])
    ->name('alquileres.show');
Route::post('/alquileres-web/{id}/entregar', [WebController::class, 'entregarAlquilerWeb'])
    ->name('alquileres.entregar');
Route::post('/alquileres-web/{id}/devolver', [WebController::class, 'devolverAlquilerWeb'])
    ->name('alquileres.devolver');
Route::post('/alquileres-web/{id}/cancelar', [WebController::class, 'cancelarAlquilerWeb'])
    ->name('alquileres.cancelar');

 //- - - - PAGOS - - - -

Route::get('/alquileres-web/{id}/pagar', [WebController::class, 'crearPagoWeb'])
    ->name('pagos.create');
Route::post('/alquileres-web/{id}/pagar', [WebController::class, 'guardarPagoWeb'])
    ->name('pagos.store');
Route::get('/alquileres-web/{id}/recibo', [WebController::class, 'reciboAlquilerWeb'])
    ->name('alquileres.recibo');
Route::get('/alquileres-web/{id}/terminos', [WebController::class, 'terminosAlquilerWeb'])
    ->name('alquileres.terminos');

 //- - - - EXPORTACIONES - - - -

Route::get('/exportaciones/alquileres/excel', [ExportacionController::class, 'alquileresExcel'])
    ->name('exportaciones.alquileres.excel');
Route::get('/exportaciones/movimientos/excel', [ExportacionController::class, 'movimientosExcel'])
    ->name('exportaciones.movimientos.excel');
Route::get('/exportaciones/alquileres/pdf', [ExportacionController::class, 'alquileresPdf'])
    ->name('exportaciones.alquileres.pdf');
Route::get('/exportaciones/movimientos/pdf', [ExportacionController::class, 'movimientosPdf'])
    ->name('exportaciones.movimientos.pdf');

 //- - - - CALENDARIO - - - -

Route::get('/calendario-web', [CalendarioController::class, 'index'])
    ->name('calendario.index');
Route::get('/calendario-web/eventos', [CalendarioController::class, 'eventos'])
    ->name('calendario.eventos');