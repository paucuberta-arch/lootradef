<?php

use App\Http\Controllers\Admin\AdminCampaignController;
use App\Http\Controllers\Admin\AdminCasePrizeController;
use App\Http\Controllers\Admin\AdminChartsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\ApuestasController;
use App\Http\Controllers\ArcadeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlackjackController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CrashController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PokerDealerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RickyEditCampaignController;
use App\Http\Controllers\RuletaController;
use App\Http\Controllers\SlotsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('inicio');
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

Route::get('/info/{page}', function ($page) {
    $pages = [
        'ayuda' => [
            'title' => 'Centro de Ayuda',
            'content' => '<h2 class="text-xl font-bold text-white mb-3">Preguntas Frecuentes</h2>
                <p><strong class="text-white">Como me registro?</strong><br>Haz clic en "Registrarse" y completa el formulario con tus datos. Receiras un email de confirmacion.</p>
                <p><strong class="text-white">Como realizo un deposito?</strong><br>Ve a tu perfil y utiliza el sistema de cartera. Puedes anadir fondos de forma segura.</p>
                <p><strong class="text-white">Los juegos son justos?</strong><br>Si, utilizamos algoritmos probadamente justos para todos nuestros juegos.</p>
                <p><strong class="text-white">Como contacto con soporte?</strong><br>Utiliza nuestra pagina de <a href="'.url('/feedback').'" class="text-brand-400 hover:text-brand-300">feedback</a> para enviar tus consultas.</p>',
        ],
        'contacto' => [
            'title' => 'Contacto',
            'content' => '<p>Puedes contactar con nosotros a traves de nuestro sistema de <a href="'.url('/feedback').'" class="text-brand-400 hover:text-brand-300">feedback</a>.</p>
                <p>Nuestro equipo de soporte respondere en un plazo de 24-48 horas.</p>
                <p><strong class="text-white">Email:</strong> soporte@lootracasino.com</p>',
        ],
        'terminos' => [
            'title' => 'Terminos y Condiciones',
            'content' => '<h2 class="text-xl font-bold text-white mb-3">1. Acceptacion de los Terminos</h2>
                <p>Al acceder y utilizar Lootra Casino, aceptas estos terminos y condiciones en su totalidad.</p>
                <h2 class="text-xl font-bold text-white mb-3 mt-6">2. Elegibilidad</h2>
                <p>Debes ser mayor de 18 anos para utilizar nuestros servicios. El juego es solo para entretenimiento.</p>
                <h2 class="text-xl font-bold text-white mb-3 mt-6">3. Cuentas de Usuario</h2>
                <p>Cada usuario puede tener una sola cuenta. Las cuentas duplicadas seran cerradas.</p>
                <h2 class="text-xl font-bold text-white mb-3 mt-6">4. Juego Responsable</h2>
                <p>Fomentamos el juego responsable. Si sientes que tienes un problema, utiliza nuestras herramientas de autoexclusion.</p>',
        ],
        'privacidad' => [
            'title' => 'Politica de Privacidad',
            'content' => '<h2 class="text-xl font-bold text-white mb-3">Recopilacion de Datos</h2>
                <p>Recopilamos informacion basica de registro (nombre, email) para proporcionar nuestros servicios.</p>
                <h2 class="text-xl font-bold text-white mb-3 mt-6">Uso de Datos</h2>
                <p>Utilizamos tus datos exclusivamente para el funcionamiento de la plataforma y mejora de servicios.</p>
                <h2 class="text-xl font-bold text-white mb-3 mt-6">Proteccion</h2>
                <p>Tus datos son protegidos con encriptacion y nunca se comparten con terceros sin tu consentimiento.</p>',
        ],
        'responsable' => [
            'title' => 'Juego Responsable',
            'content' => '<p>En Lootra Casino creemos que el juego debe ser una forma de entretenimiento, no una fuente de problemas.</p>
                <h2 class="text-xl font-bold text-white mb-3 mt-6">Senales de Alerta</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Juegas mas tiempo del planeado</li>
                    <li>Apuestas mas dinero del que puedes permitirte</li>
                    <li>El juego afecta a tu vida personal o laboral</li>
                </ul>
                <h2 class="text-xl font-bold text-white mb-3 mt-6">Herramientas</h2>
                <p>Utiliza nuestra funcion de autoexclusion si necesitas un descanso del juego.</p>',
        ],
        'verificacion' => [
            'title' => 'Verificacion de Edad',
            'content' => '<p>Lootra Casino se compromete a preventir el acceso de menores de edad a sus servicios.</p>
                <p>Todos los usuarios deben ser mayores de 18 anos. Utilizamos procesos de verificacion para garantizar el cumplimiento.</p>',
        ],
        'autoexclusion' => [
            'title' => 'Autoexclusion',
            'content' => '<p>Si sientes que necesitas un descanso del juego, puedes activar la autoexclusion desde tu perfil.</p>
                <p>La autoexclusion puede ser temporal (30, 60 o 90 dias) o permanente.</p>
                <p>Durante el periodo de autoexclusion, no podras acceder a tus juegos ni realizar apuestas.</p>',
        ],
    ];

    if (! isset($pages[$page])) {
        abort(404);
    }

    return view('info.index', [
        'pageTitle' => $pages[$page]['title'],
        'content' => $pages[$page]['content'],
    ]);
})->name('info');

Route::get('/registrarse', [AuthController::class, 'mostrarRegistro'])->name('registro');
Route::post('/registrarse', [AuthController::class, 'registrar'])->name('registro.store');

Route::get('/iniciar-sesion', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/iniciar-sesion', [AuthController::class, 'iniciarSesion'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::get('/rickyedit/reto', [RickyEditCampaignController::class, 'intro'])->name('rickyedit.intro');
    Route::post('/rickyedit/reto/iniciar', [RickyEditCampaignController::class, 'start'])->middleware('throttle:5,1')->name('rickyedit.start');
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
    Route::post('/wallet/demo-deposit', [PerfilController::class, 'deposit'])->name('wallet.demo-deposit');

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
    Route::post('/perfil/depositar', [PerfilController::class, 'deposit'])->name('perfil.deposit');
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

    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show');

Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin|admin|moderator'])->group(function () {
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

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin|admin'])->group(function () {
    Route::get('/campanas/rickyedit', [AdminCampaignController::class, 'index'])->middleware('permission:campaigns.stats.view')->name('campaigns.rickyedit');
    Route::get('/premios-cajas', [AdminCasePrizeController::class, 'index'])->middleware('permission:case-prizes.manage')->name('case-prizes.index');
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
