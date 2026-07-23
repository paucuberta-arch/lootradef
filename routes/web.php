<?php

use App\Http\Controllers\Admin\AdminCampaignController;
use App\Http\Controllers\Admin\AdminCaseHistoryController;
use App\Http\Controllers\Admin\AdminCasePrizeController;
use App\Http\Controllers\Admin\AdminChartsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\AdminMfaController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AnalyticsConsentController;
use App\Http\Controllers\ApuestasController;
use App\Http\Controllers\ArcadeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlackjackController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CrashController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PokerDealerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RickyEditCampaignController;
use App\Http\Controllers\RuletaController;
use App\Http\Controllers\SlotsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('inicio');
Route::post('/privacidad/consentimiento-analitica', [AnalyticsConsentController::class, 'store'])
    ->middleware('throttle:10,1')->name('privacy.analytics-consent');
Route::get('/rickyedit', [RickyEditCampaignController::class, 'landing'])->name('rickyedit.landing');
Route::get('/rickyedit/ranking', [RickyEditCampaignController::class, 'ranking'])->name('rickyedit.ranking');
Route::get('/newsletter/baja/{usuario}', [NewsletterController::class, 'unsubscribe'])->middleware('signed')->name('newsletter.unsubscribe');
Route::post('/rickyedit/event', [RickyEditCampaignController::class, 'event'])->middleware('throttle:30,1')->name('rickyedit.event');
Route::get('/games', [HomeController::class, 'casino'])->name('games.index');
Route::get('/juego/{slug}', [GameController::class, 'show'])->name('juego.show');

Route::get('/apuestas', [ApuestasController::class, 'index'])->name('apuestas');
Route::get('/apuestas/en-vivo', [ApuestasController::class, 'feed'])->name('apuestas.feed');
Route::get('/sports', [ApuestasController::class, 'index'])->name('sports.index');
Route::get('/sports/live', [ApuestasController::class, 'feed'])->name('sports.feed');

Route::get('/cajas', [CajaController::class, 'index'])->name('cajas');
Route::get('/cases', [CajaController::class, 'index'])->name('cases.index');

Route::get('/info/{page}', [InfoController::class, 'show'])->name('info');

Route::get('/registrarse', [AuthController::class, 'mostrarRegistro'])->middleware('guest')->name('registro');
Route::post('/registrarse', [AuthController::class, 'registrar'])->middleware(['guest', 'throttle:registration'])->name('registro.store');

Route::get('/iniciar-sesion', [AuthController::class, 'mostrarLogin'])->middleware('guest')->name('login');
Route::post('/iniciar-sesion', [AuthController::class, 'iniciarSesion'])->middleware(['guest', 'throttle:login'])->name('login.store');
Route::get('/recuperar-contrasena', [PasswordResetController::class, 'requestForm'])->middleware('guest')->name('password.request');
Route::post('/recuperar-contrasena', [PasswordResetController::class, 'sendLink'])->middleware(['guest', 'throttle:password.email'])->name('password.email');
Route::get('/restablecer-contrasena/{token}', [PasswordResetController::class, 'resetForm'])->middleware('guest')->name('password.reset');
Route::post('/restablecer-contrasena', [PasswordResetController::class, 'reset'])->middleware(['guest', 'throttle:password.reset'])->name('password.update');

Route::middleware(['auth', 'auth.session', 'throttle:authenticated-actions'])->group(function () {
    Route::get('/email/verificar', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verificar/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verificacion-notificacion', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:3,1')->name('verification.send');
});

Route::middleware(['auth', 'auth.session', 'verified.real', 'throttle:authenticated-actions'])->group(function () {
    Route::get('/rickyedit/reto', [RickyEditCampaignController::class, 'intro'])->name('rickyedit.intro');
    Route::post('/rickyedit/reto/iniciar', [RickyEditCampaignController::class, 'start'])->middleware(['verified', 'throttle:5,1'])->name('rickyedit.start');
    Route::post('/rickyedit/reto/finalizar', [RickyEditCampaignController::class, 'finish'])->middleware('throttle:5,1')->name('rickyedit.finish');
    Route::get('/rickyedit/reto/estado', [RickyEditCampaignController::class, 'status'])->middleware('throttle:60,1')->name('rickyedit.status');
    Route::prefix('games')->name('games.')->group(function () {
        Route::get('/poker/dealer', [PokerDealerController::class, 'index'])->name('poker.dealer');
        Route::post('/poker/dealer/start', [PokerDealerController::class, 'start'])->name('poker.dealer.start');
        Route::post('/poker/dealer/action', [PokerDealerController::class, 'action'])->name('poker.dealer.action');
        Route::get('/poker/dealer/status', [PokerDealerController::class, 'status'])->name('poker.dealer.status');
        Route::get('/poker/all-in', [ArcadeController::class, 'index'])->defaults('game', 'texas-holdem')->name('poker.all-in');
        Route::post('/poker/all-in', [ArcadeController::class, 'play'])->defaults('game', 'texas-holdem')->name('poker.all-in.play');
        Route::get('/originals/{game}', [ArcadeController::class, 'index'])->name('originals.show');
        Route::post('/originals/{game}', [ArcadeController::class, 'play'])->name('originals.play');
        Route::get('/crash', [CrashController::class, 'index'])->name('crash.show');
        Route::post('/crash', [CrashController::class, 'play'])->name('crash.play');
        Route::post('/crash/status', [CrashController::class, 'status'])->name('crash.status');
        Route::post('/crash/cashout', [CrashController::class, 'cashout'])->name('crash.cashout');
        Route::get('/slots/{slug}', [SlotsController::class, 'index'])->name('slots.show');
        Route::post('/slots/{slug}', [SlotsController::class, 'play'])->name('slots.play');
        Route::get('/roulette/european', [RuletaController::class, 'index'])->defaults('variant', 'european')->name('roulette.european');
        Route::post('/roulette/european', [RuletaController::class, 'play'])->defaults('variant', 'european')->name('roulette.european.play');
        Route::get('/roulette/lightning', [RuletaController::class, 'index'])->defaults('variant', 'lightning')->name('roulette.lightning');
        Route::post('/roulette/lightning', [RuletaController::class, 'play'])->defaults('variant', 'lightning')->name('roulette.lightning.play');
        Route::get('/blackjack/vip', [BlackjackController::class, 'index'])->defaults('variant', 'vip')->name('blackjack.vip');
        Route::post('/blackjack/vip/deal', [BlackjackController::class, 'deal'])->defaults('variant', 'vip')->name('blackjack.vip.deal');
        Route::post('/blackjack/vip/hit', [BlackjackController::class, 'hit'])->defaults('variant', 'vip')->name('blackjack.vip.hit');
        Route::post('/blackjack/vip/stand', [BlackjackController::class, 'stand'])->defaults('variant', 'vip')->name('blackjack.vip.stand');
        Route::get('/blackjack/vip/status', [BlackjackController::class, 'status'])->defaults('variant', 'vip')->name('blackjack.vip.status');
        Route::get('/blackjack/classic', [BlackjackController::class, 'index'])->defaults('variant', 'classic')->name('blackjack.classic');
        Route::post('/blackjack/classic/deal', [BlackjackController::class, 'deal'])->defaults('variant', 'classic')->name('blackjack.classic.deal');
        Route::post('/blackjack/classic/hit', [BlackjackController::class, 'hit'])->defaults('variant', 'classic')->name('blackjack.classic.hit');
        Route::post('/blackjack/classic/stand', [BlackjackController::class, 'stand'])->defaults('variant', 'classic')->name('blackjack.classic.stand');
        Route::get('/blackjack/classic/status', [BlackjackController::class, 'status'])->defaults('variant', 'classic')->name('blackjack.classic.status');
    });

    Route::post('/sports/{partido}', [ApuestasController::class, 'place'])->name('sports.place');
    Route::post('/cases/{caja}/open', [CajaController::class, 'open'])->name('cases.open');
    Route::post('/inventory/{item}/redeem', [CajaController::class, 'redeem'])->name('inventory.redeem');
    Route::get('/profile', [PerfilController::class, 'index'])->name('profile.show');
    Route::get('/wallet', [PerfilController::class, 'index'])->name('wallet.show');
    Route::get('/wallet/history', [PerfilController::class, 'index'])->name('wallet.history');
    Route::get('/wallet/balance', [PerfilController::class, 'saldo'])->name('wallet.balance');
    Route::post('/wallet/demo-deposit', [PerfilController::class, 'deposit'])->middleware('throttle:demo-deposit')->name('wallet.demo-deposit');

    Route::get('/jugar/poker/dealer', [PokerDealerController::class, 'index'])->name('poker.dealer');
    Route::post('/jugar/poker/dealer/iniciar', [PokerDealerController::class, 'start'])->name('poker.dealer.start');
    Route::post('/jugar/poker/dealer/accion', [PokerDealerController::class, 'action'])->name('poker.dealer.action');
    Route::get('/jugar/poker/dealer/estado', [PokerDealerController::class, 'status'])->name('poker.dealer.status');
    Route::post('/apuestas/{partido}', [ApuestasController::class, 'place'])->name('apuestas.place');
    Route::get('/jugar/originales/{game}', [ArcadeController::class, 'index'])->name('arcade');
    Route::post('/jugar/originales/{game}', [ArcadeController::class, 'play'])->name('arcade.play');
    Route::post('/cajas/{caja}/abrir', [CajaController::class, 'open'])->name('cajas.open');
    Route::post('/inventario/{item}/canjear', [CajaController::class, 'redeem'])->name('inventario.redeem');
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::get('/perfil/saldo', [PerfilController::class, 'saldo'])->name('perfil.saldo');
    Route::post('/perfil/depositar', [PerfilController::class, 'deposit'])->middleware('throttle:demo-deposit')->name('perfil.deposit');
    Route::post('/cerrar-sesion', [AuthController::class, 'cerrarSesion'])->name('logout');

    Route::get('/jugar/crash', [CrashController::class, 'index'])->name('crash');
    Route::post('/jugar/crash', [CrashController::class, 'play'])->name('crash.play');
    Route::post('/jugar/crash/estado', [CrashController::class, 'status'])->name('crash.status');
    Route::post('/jugar/crash/cashout', [CrashController::class, 'cashout'])->name('crash.cashout');
    Route::post('/jugar/crash/crash', [CrashController::class, 'crash'])->name('crash.crash');

    Route::get('/jugar/slots', [SlotsController::class, 'index'])->name('slots');
    Route::post('/jugar/slots', [SlotsController::class, 'play'])->name('slots.play');

    Route::get('/jugar/ruleta/europea', [RuletaController::class, 'index'])->defaults('variant', 'european')->name('ruleta');
    Route::post('/jugar/ruleta/europea', [RuletaController::class, 'play'])->defaults('variant', 'european')->name('ruleta.play');
    Route::get('/jugar/ruleta/lightning', [RuletaController::class, 'index'])->defaults('variant', 'lightning')->name('ruleta.lightning');
    Route::post('/jugar/ruleta/lightning', [RuletaController::class, 'play'])->defaults('variant', 'lightning')->name('ruleta.lightning.play');

    Route::get('/jugar/blackjack/vip', [BlackjackController::class, 'index'])->defaults('variant', 'vip')->name('blackjack');
    Route::post('/jugar/blackjack/vip/repartir', [BlackjackController::class, 'deal'])->defaults('variant', 'vip')->name('blackjack.deal');
    Route::post('/jugar/blackjack/vip/pedir', [BlackjackController::class, 'hit'])->defaults('variant', 'vip')->name('blackjack.hit');
    Route::post('/jugar/blackjack/vip/plantarse', [BlackjackController::class, 'stand'])->defaults('variant', 'vip')->name('blackjack.stand');
    Route::get('/jugar/blackjack/vip/estado', [BlackjackController::class, 'status'])->defaults('variant', 'vip')->name('blackjack.status');
    Route::get('/jugar/blackjack/clasico', [BlackjackController::class, 'index'])->defaults('variant', 'classic')->name('blackjack.classic');
    Route::post('/jugar/blackjack/clasico/repartir', [BlackjackController::class, 'deal'])->defaults('variant', 'classic')->name('blackjack.classic.deal');
    Route::post('/jugar/blackjack/clasico/pedir', [BlackjackController::class, 'hit'])->defaults('variant', 'classic')->name('blackjack.classic.hit');
    Route::post('/jugar/blackjack/clasico/plantarse', [BlackjackController::class, 'stand'])->defaults('variant', 'classic')->name('blackjack.classic.stand');
    Route::get('/jugar/blackjack/clasico/estado', [BlackjackController::class, 'status'])->defaults('variant', 'classic')->name('blackjack.classic.status');

    Route::post('/reviews', [ReviewController::class, 'store'])->middleware('throttle:community-write')->name('reviews.store');
});

Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show');

Route::prefix('admin/security/mfa')->name('admin.mfa.')->middleware(['auth', 'auth.session', 'role:super_admin|admin|moderator', 'throttle:6,1'])->group(function () {
    Route::get('/', [AdminMfaController::class, 'setup'])->name('setup');
    Route::post('/', [AdminMfaController::class, 'enable'])->name('enable');
    Route::get('/challenge', [AdminMfaController::class, 'challenge'])->name('challenge');
    Route::post('/challenge', [AdminMfaController::class, 'verify'])->name('verify');
});

Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:feedback')->name('feedback.store');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'auth.session', 'role:super_admin|admin|moderator', 'admin.mfa'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/usuarios', [AdminUserController::class, 'index'])->middleware('permission:users.view')->name('users');
    Route::get('/usuarios/{usuario}/editar', [AdminUserController::class, 'edit'])->middleware('permission:users.edit')->name('users.edit');
    Route::put('/usuarios/{usuario}', [AdminUserController::class, 'update'])->middleware('permission:users.edit')->name('users.update');
    Route::delete('/usuarios/{usuario}', [AdminUserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');

    Route::get('/reviews', [AdminReviewController::class, 'index'])->middleware('permission:reviews.view')->name('reviews');
    Route::put('/reviews/{review}', [AdminReviewController::class, 'update'])->middleware('permission:reviews.moderate')->name('reviews.update');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->middleware('permission:reviews.delete')->name('reviews.destroy');

    Route::get('/feedback', [AdminFeedbackController::class, 'index'])->middleware('permission:feedback.view')->name('feedback');
    Route::put('/feedback/{fb}', [AdminFeedbackController::class, 'update'])->middleware('permission:feedback.respond')->name('feedback.update');
    Route::delete('/feedback/{fb}', [AdminFeedbackController::class, 'destroy'])->middleware('permission:feedback.close')->name('feedback.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'auth.session', 'role:super_admin|admin', 'admin.mfa'])->group(function () {
    Route::get('/campanas/rickyedit', [AdminCampaignController::class, 'index'])->middleware('permission:campaigns.stats.view')->name('campaigns.rickyedit');
    Route::get('/premios-cajas', [AdminCasePrizeController::class, 'index'])->middleware('permission:case-prizes.manage')->name('case-prizes.index');
    Route::get('/historial-cajas', [AdminCaseHistoryController::class, 'index'])->middleware('permission:case-prizes.manage')->name('case-history.index');
    Route::put('/premios-cajas', [AdminCasePrizeController::class, 'update'])->middleware(['permission:case-prizes.manage', 'throttle:10,1'])->name('case-prizes.update');
    Route::post('/premios-cajas/multiplicadores', [AdminCasePrizeController::class, 'storeBoost'])->middleware(['permission:case-prizes.manage', 'throttle:10,1'])->name('case-prizes.boosts.store');
    Route::delete('/premios-cajas/multiplicadores/{boost}', [AdminCasePrizeController::class, 'destroyBoost'])->middleware(['permission:case-prizes.manage', 'throttle:10,1'])->name('case-prizes.boosts.destroy');
    Route::get('/graficos', [AdminChartsController::class, 'index'])->middleware('permission:stats.view')->name('charts');
    Route::get('/roles', [AdminRoleController::class, 'index'])->middleware('permission:roles.view')->name('roles');
    Route::post('/roles', [AdminRoleController::class, 'store'])->middleware('permission:roles.manage')->name('roles.store');
    Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->middleware('permission:roles.manage')->name('roles.update');
    Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->middleware('permission:roles.manage')->name('roles.destroy');

    Route::get('/logs', [AdminLogController::class, 'index'])->middleware('permission:logs.view')->name('logs');
});
