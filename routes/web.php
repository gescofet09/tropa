<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;


Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {
    Route::get('/pedidos', [PedidoController::class,'index'])->name('pedidos');
    Route::post('/pedidos', [PedidoController::class,'store'])->name('pedidos.store');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class,'cambiarEstado'])->name('pedidos.actualizar');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'cambiarEstado'])->name('pedidos.cambiarEstado');
    Route::get('/pedidos/{pedido}/documentos', [PedidoController::class,'verDocumentos'])->name('pedidos.documentos');
    Route::delete('/pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');
    Route::post('/pedidos/{pedido}/marcar', [PedidoController::class, 'marcarProductos'])->name('pedidos.marcarProductos');

});
