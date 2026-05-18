<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//* Recuperar contraseña 
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update');

Route::middleware('auth')->group(function () {
    Route::get('/pedidos', [PedidoController::class,'index'])->name('pedidos');
    Route::post('/pedidos', [PedidoController::class,'store'])->name('pedidos.store');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'cambiarEstado'])->name('pedidos.cambiarEstado');
    Route::get('/pedidos/{pedido}/documentos', [PedidoController::class,'verDocumentos'])->name('pedidos.documentos');
    Route::delete('/pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');
    Route::post('/pedidos/{pedido}/marcar', [PedidoController::class, 'marcarProductos'])->name('pedidos.marcarProductos');
    Route::post('/pedidos/{pedido}/repetir', [PedidoController::class, 'repetir'])->name('pedidos.repetir');

    Route::post('/admin/usuarios', [PedidoController::class, 'storeUsuario'])->name('admin.usuarios.store');
    Route::patch('/admin/usuarios/{user}', [PedidoController::class, 'updateUsuario'])->name('admin.usuarios.update');
    Route::delete('/admin/usuarios/{user}', [PedidoController::class, 'destroyUsuario'])->name('admin.usuarios.destroy');

    Route::post('/admin/productos', [PedidoController::class, 'storeProducto'])->name('admin.productos.store');
    Route::patch('/admin/productos/{producto}', [PedidoController::class, 'updateProducto'])->name('admin.productos.update');
    Route::patch('/admin/productos/{producto}/stock', [PedidoController::class, 'updateStock'])->name('admin.productos.stock');
    Route::delete('/admin/productos/{producto}', [PedidoController::class, 'destroyProducto'])->name('admin.productos.destroy');
    Route::post('/admin/usuarios/{user}/reset-password', [PedidoController::class, 'resetPassword'])
    ->name('admin.usuarios.reset-password');
});
