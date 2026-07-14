<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;

Route::get('/', function () {
    return view('inicio');
})->name('inicio');

Route::get('/registrarse', [AuthController::class, 'mostrarRegistro'])->name('registro');
Route::post('/registrarse', [AuthController::class, 'registrar'])->name('registro.store');

Route::get('/iniciar-sesion', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/iniciar-sesion', [AuthController::class, 'iniciarSesion'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::post('/cerrar-sesion', [AuthController::class, 'cerrarSesion'])->name('logout');
});
