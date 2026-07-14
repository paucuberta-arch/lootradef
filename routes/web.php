<?php

use App\Http\Controllers\CalculadoraController;
use App\Http\Controllers\CalcularController;
use App\Http\Controllers\HolaController;
use App\Http\Controllers\ResultadoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    return view('inicio');

})->name('inicio');

Route::get('/hola', [HolaController::class, 'index'])
    ->name('hola');

Route::get('/suma',

    [CalculadoraController::class, 'mostrar'])

    ->defaults('tipo', 'suma')

    ->name('suma');

Route::get('/resta',

    [CalculadoraController::class, 'mostrar'])

    ->defaults('tipo', 'resta')

    ->name('resta');

Route::get('/multiplicacion',

    [CalculadoraController::class, 'mostrar'])

    ->defaults('tipo', 'multiplicacion')

    ->name('multiplicacion');

Route::get('/division',

    [CalculadoraController::class, 'mostrar'])

    ->defaults('tipo', 'division')

    ->name('division');

Route::post('/calcular',

    [CalcularController::class, 'calcular'])

    ->name('calcular');

Route::get('/resultados', [ResultadoController::class, 'index'])
    ->name('resultados.index');

Route::get('/resultados/{id}', [ResultadoController::class, 'show'])
    ->name('resultados.show');
Route::get('/registrarse', [AuthController::class, 'mostrarRegistro'])
    ->name('registro');

Route::post('/registrarse', [AuthController::class, 'registrar'])
    ->name('registro.store');

Route::get('/iniciar-sesion', [AuthController::class, 'mostrarLogin'])
    ->name('login');

Route::post('/iniciar-sesion', [AuthController::class, 'iniciarSesion'])
    ->name('login.store');

Route::middleware('auth')->group(function () {

    Route::get('/perfil', [PerfilController::class, 'index'])
        ->name('perfil');

    Route::post('/cerrar-sesion', [AuthController::class, 'cerrarSesion'])
        ->name('logout');

});
