<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;

$uegos = [
    'gates-of-olympus' => ['name' => 'Gates of Olympus', 'provider' => 'Pragmatic Play', 'cat' => 'Slots', 'grad' => 'game-gradient-5', 'rtp' => '96.5%', 'volatilidad' => 'Alta', 'max_win' => 'x5000', 'min_bet' => '€0.20', 'max_bet' => '€125', 'lines' => '20', 'reels' => '6', 'description' => 'Viaja al Monte del Olimpo con Zeus en esta emocionante slot de Pragmatic Play. Con un sistema de pagos por clusters y multiplicadores hasta x500, Gates of Olympus ofrece una experiencia de juego unica con graficos espectaculares y efectos de sonido envolventes.'],
    'crazy-time' => ['name' => 'Crazy Time', 'provider' => 'Evolution', 'cat' => 'Live Casino', 'grad' => 'game-gradient-2', 'rtp' => '96.08%', 'volatilidad' => 'Media', 'max_win' => 'x25000', 'min_bet' => '€0.10', 'max_bet' => '€1000', 'lines' => '-', 'reels' => '-', 'description' => 'El juego de casino en vivo mas emocionante del mundo. Crazy Time combina una ruleta con multiples bonificaciones en vivo que pueden multiplicar tus ganancias hasta 25.000x. Presentado por un host en vivo con graficos HD.'],
    'sweet-bonanza' => ['name' => 'Sweet Bonanza', 'provider' => 'Pragmatic Play', 'cat' => 'Slots', 'grad' => 'game-gradient-3', 'rtp' => '96.48%', 'volatilidad' => 'Alta', 'max_win' => 'x21175', 'min_bet' => '€0.20', 'max_bet' => '€100', 'lines' => 'Pay Anywhere', 'reels' => '6', 'description' => 'Un mundo de dulces y frutas te espera en esta slot vibrante. Sweet Bonanza utiliza un sistema de pagos por clusters con multiplicadores que pueden llegar hasta x100 durante las tiradas gratis.'],
    'european-roulette' => ['name' => 'European Roulette', 'provider' => 'NetEnt', 'cat' => 'Ruleta', 'grad' => 'game-gradient-11', 'rtp' => '97.3%', 'volatilidad' => 'Variable', 'max_win' => 'x35', 'min_bet' => '€0.10', 'max_bet' => '€500', 'lines' => '-', 'reels' => '-', 'description' => 'La clasica ruleta europea con un solo cero. Disfruta de una experiencia autentica con graficos realistas, animaciones fluidas y todas las apuestas clasicas: rojo/negro, par/impar, docenas, columnas y mas.'],
    'blackjack-vip' => ['name' => 'Blackjack VIP', 'provider' => 'Evolution', 'cat' => 'Blackjack', 'grad' => 'game-gradient-4', 'rtp' => '99.28%', 'volatilidad' => 'Baja', 'max_win' => 'x3', 'min_bet' => '€5', 'max_bet' => '€5000', 'lines' => '-', 'reels' => '-', 'description' => 'Mesa VIP de blackjack en vivo con crupieres profesionales. Disfruta de reglas clasicas con la opcion de apostar detras. Streaming HD con multiples camaras para la mejor experiencia.'],
    'book-of-dead' => ['name' => 'Book of Dead', 'provider' => "Play'n GO", 'cat' => 'Slots', 'grad' => 'game-gradient-1', 'rtp' => '96.21%', 'volatilidad' => 'Alta', 'max_win' => 'x5000', 'min_bet' => '€0.10', 'max_bet' => '€100', 'lines' => '10', 'reels' => '5', 'description' => 'Acompaña a Rich Wilde en una aventura por el antiguo Egipto. Book of Dead es una de las slots mas populares del mundo con giros gratis y simbolo expandible que puede cubrir los carretes completos.'],
    'crash-rocket' => ['name' => 'Crash Rocket', 'provider' => 'Spribe', 'cat' => 'Crash', 'grad' => 'game-gradient-8', 'rtp' => '97.0%', 'volatilidad' => 'Alta', 'max_win' => 'x∞', 'min_bet' => '€0.10', 'max_bet' => '€200', 'lines' => '-', 'reels' => '-', 'description' => 'Un juego de tipo crash donde debes cobrar antes de que el cohete explote. Cuanto mas alto llegue, mayor sera tu multiplicador. Toma tus decisiones con estrategia para maximizar tus ganancias.'],
    'texas-holdem' => ['name' => "Texas Hold'em", 'provider' => 'PokerStars', 'cat' => 'Poker', 'grad' => 'game-gradient-6', 'rtp' => '98.5%', 'volatilidad' => 'Variable', 'max_win' => 'Sin limite', 'min_bet' => '€1', 'max_bet' => '€10000', 'lines' => '-', 'reels' => '-', 'description' => 'El juego de poker mas popular del mundo. Enfrentate a otros jugadores en mesas de Texas Hold\'em con diferentes niveles de apuesta. Torneos y cash games disponibles las 24 horas.'],
    'starburst' => ['name' => 'Starburst', 'provider' => 'NetEnt', 'cat' => 'Slots', 'grad' => 'game-gradient-7', 'rtp' => '96.09%', 'volatilidad' => 'Baja', 'max_win' => 'x500', 'min_bet' => '€0.10', 'max_bet' => '€100', 'lines' => '10', 'reels' => '5', 'description' => 'La slot mas iconica de NetEnt. Starburst combina graficos brillantes conwilds expansivos y re-spins. Un clasico atemporal con una volatilidad baja ideal para sesiones de juego largas.'],
    'lightning-roulette' => ['name' => 'Lightning Roulette', 'provider' => 'Evolution', 'cat' => 'Ruleta', 'grad' => 'game-gradient-12', 'rtp' => '97.3%', 'volatilidad' => 'Media', 'max_win' => 'x500', 'min_bet' => '€0.20', 'max_bet' => '€500', 'lines' => '-', 'reels' => '-', 'description' => 'Ruleta en vivo con multiplicadores electricos. En cada ronda, entre 1 y 5 numeros reciben multiplicadores de x50 o x500. Una experiencia unica que combina la ruleta clasica con ganancias extraordinarias.'],
    'big-bass-bonanza' => ['name' => 'Big Bass Bonanza', 'provider' => 'Pragmatic Play', 'cat' => 'Slots', 'grad' => 'game-gradient-9', 'rtp' => '96.71%', 'volatilidad' => 'Alta', 'max_win' => 'x2100', 'min_bet' => '€0.10', 'max_bet' => '€250', 'lines' => '10', 'reels' => '5', 'description' => 'Salva de pesca en esta slot acuatica. Recoge simbolos de pez y pescador durante los giros gratis para multiplicar tus ganancias. Un tema divertido con potencial de ganancias grandes.'],
    'blackjack-classic' => ['name' => 'Blackjack Classic', 'provider' => 'Microgaming', 'cat' => 'Blackjack', 'grad' => 'game-gradient-10', 'rtp' => '99.91%', 'volatilidad' => 'Baja', 'max_win' => 'x3', 'min_bet' => '€1', 'max_bet' => '€2000', 'lines' => '-', 'reels' => '-', 'description' => 'Blackjack clasico con la mejor tasa de retorno del 99.91%. Reglas estandar con 6 barajas, dealer se para en 17. Decisiones rapidas y estrategia optima para maximizar tus posibilidades.'],
];

Route::get('/', function () {
    return view('inicio');
})->name('inicio');

Route::get('/juego/{slug}', function ($slug) use ($uegos) {
    if (!isset($uegos[$slug])) abort(404);
    return view('juego.show', ['juego' => $uegos[$slug], 'slug' => $slug]);
})->name('juego.show');

Route::get('/apuestas', function () {
    return view('apuestas.index');
})->name('apuestas');

Route::get('/cajas', function () {
    return view('cajas.index');
})->name('cajas');

Route::get('/registrarse', [AuthController::class, 'mostrarRegistro'])->name('registro');
Route::post('/registrarse', [AuthController::class, 'registrar'])->name('registro.store');

Route::get('/iniciar-sesion', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/iniciar-sesion', [AuthController::class, 'iniciarSesion'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::post('/cerrar-sesion', [AuthController::class, 'cerrarSesion'])->name('logout');
});
