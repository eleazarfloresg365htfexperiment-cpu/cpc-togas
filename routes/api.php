<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteController;

Route::get('/estado', function () {
    return response()->json([
        'ok' => true,
        'mensaje' => 'API del Sistema de Control de Togas CPC funcionando correctamente.',
    ]);
});

/*
|--------------------------------------------------------------------------
| Productos
|--------------------------------------------------------------------------
*/

Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/disponibles', [ProductoController::class, 'disponibles']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Clientes
|--------------------------------------------------------------------------
*/

Route::get('/clientes', [ClienteController::class, 'index']);
Route::post('/clientes', [ClienteController::class, 'store']);
Route::get('/clientes/{id}', [ClienteController::class, 'show']);
Route::put('/clientes/{id}', [ClienteController::class, 'update']);
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Alquileres
|--------------------------------------------------------------------------
*/

Route::get('/alquileres', [AlquilerController::class, 'index']);
Route::post('/alquileres', [AlquilerController::class, 'store']);
Route::get('/alquileres/{id}', [AlquilerController::class, 'show']);
Route::post('/alquileres/{id}/entregar', [AlquilerController::class, 'entregar']);
Route::post('/alquileres/{id}/devolver', [AlquilerController::class, 'devolver']);

/*
|--------------------------------------------------------------------------
| Pagos
|--------------------------------------------------------------------------
*/

Route::get('/pagos', [PagoController::class, 'index']);
Route::post('/pagos', [PagoController::class, 'store']);
Route::get('/pagos/{id}', [PagoController::class, 'show']);
Route::get('/reportes/resumen', [ReporteController::class, 'resumen']);