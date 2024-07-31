<?php

use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\InventarioTipoController;
use App\Http\Controllers\ProductoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('categoria')->group(function(){
    Route::post('create', [CategoriaController::class, 'create']);
    Route::get('show', [CategoriaController::class, 'show']);
    Route::put('update/{id}', [CategoriaController::class, 'update']);
    Route::delete('delete/{id}', [CategoriaController::class, 'delete']);
});

Route::prefix('contenedor')->group(function(){
    Route::post('create', [ContenedorController::class, 'create']);
    Route::get('show', [ContenedorController::class, 'show']);
    Route::post('update/{id}', [ContenedorController::class, 'update']);
    Route::delete('delete/{id}', [ContenedorController::class, 'delete']);
});

Route::prefix('tipoInventario')->group(function(){
    Route::post('create', [InventarioTipoController::class, 'create']);
    Route::get('show', [InventarioTipoController::class, 'show']);
    Route::post('update/{id}', [InventarioTipoController::class, 'update']);
    Route::delete('delete/{id}', [InventarioTipoController::class, 'delete']);
});

Route::prefix('productos')->group(function(){
    Route::post('create', [ProductoController::class, 'create']);
    Route::get('show/{id?}', [ProductoController::class, 'show']);
    Route::post('update/{id}', [ProductoController::class, 'update']);
    Route::delete('delete/{id}', [ProductoController::class, 'delete']);
});


Route::prefix('inventario')->group(function(){
    Route::post('create/{id_tipo}', [InventarioController::class, 'create']);
    Route::get('show/{idTipo}', [InventarioController::class, 'show']);
    Route::get('show/hoy/{id_tipo}',[InventarioController::class, 'showInventariohoy']);

    Route::get('show/byid/{id}', [InventarioController::class, 'getRegistroById']);

    Route::post('update/{id}', [InventarioController::class, 'update']);
    Route::delete('delete/{id}', [InventarioController::class, 'delete']);
    
    Route::get('show/hoy/{id}',[InventarioController::class, 'inventarioHoy']);
    Route::get('checar/terminado/{id}',[InventarioController::class, 'check']);
    
    Route::get('fechas-inventarios', [InventarioController::class, 'getFechasInventarios']);

    Route::post('terminar/{id}',[InventarioController::class, 'terminarInventario']);
});
