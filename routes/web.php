<?php

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
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PokerDealerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RuletaController;
use App\Http\Controllers\SlotsController;
use Illuminate\Support\Facades\Route;

$uegos = [
    'gates-of-olympus' => ['name' => 'Gates of Olympus', 'provider' => 'Pragmatic Play', 'cat' => 'Slots', 'grad' => 'game-gradient-5', 'rtp' => '96.5%', 'volatilidad' => 'Alta', 'max_win' => 'x5000', 'min_bet' => '€0.20', 'max_bet' => '€125', 'lines' => '20', 'reels' => '6', 'image' => 'https://images.unsplash.com/photo-1551524559-8af4e6624178?w=800&h=600&fit=crop', 'description' => 'Viaja al Monte del Olimpo con Zeus en esta emocionante slot de Pragmatic Play. Con un sistema de pagos por clusters y multiplicadores hasta x500, Gates of Olympus ofrece una experiencia de juego unica con graficos espectaculares y efectos de sonido envolventes.'],
    'crazy-time' => ['name' => 'Crazy Time', 'provider' => 'Evolution', 'cat' => 'Live Casino', 'grad' => 'game-gradient-2', 'rtp' => '96.08%', 'volatilidad' => 'Media', 'max_win' => 'x25000', 'min_bet' => '€0.10', 'max_bet' => '€1000', 'lines' => '-', 'reels' => '-', 'image' => 'https://images.unsplash.com/photo-1511882150382-421056c89033?w=800&h=600&fit=crop', 'description' => 'El juego de casino en vivo mas emocionante del mundo. Crazy Time combina una ruleta con multiples bonificaciones en vivo que pueden multiplicar tus ganancias hasta 25.000x. Presentado por un host en vivo con graficos HD.'],
    'sweet-bonanza' => ['name' => 'Sweet Bonanza', 'provider' => 'Pragmatic Play', 'cat' => 'Slots', 'grad' => 'game-gradient-3', 'rtp' => '96.48%', 'volatilidad' => 'Alta', 'max_win' => 'x21175', 'min_bet' => '€0.20', 'max_bet' => '€100', 'lines' => 'Pay Anywhere', 'reels' => '6', 'image' => 'https://images.unsplash.com/photo-1575224300306-1b8da36134ec?auto=format&fit=crop&w=1200&h=700&q=90', 'description' => 'Un mundo de dulces y frutas te espera en esta slot vibrante. Sweet Bonanza utiliza un sistema de pagos por clusters con multiplicadores que pueden llegar hasta x100 durante las tiradas gratis.'],
    'european-roulette' => ['name' => 'European Roulette', 'provider' => 'NetEnt', 'cat' => 'Ruleta', 'grad' => 'game-gradient-11', 'rtp' => '97.3%', 'volatilidad' => 'Variable', 'max_win' => 'x35', 'min_bet' => '€0.10', 'max_bet' => '€500', 'lines' => '-', 'reels' => '-', 'image' => 'https://images.unsplash.com/photo-1517232115160-ff93364542dd?w=800&h=600&fit=crop', 'description' => 'La clasica ruleta europea con un solo cero. Disfruta de una experiencia autentica con graficos realistas, animaciones fluidas y todas las apuestas clasicas: rojo/negro, par/impar, docenas, columnas y mas.'],
    'blackjack-vip' => ['name' => 'Blackjack VIP', 'provider' => 'Evolution', 'cat' => 'Blackjack', 'grad' => 'game-gradient-4', 'rtp' => '99.28%', 'volatilidad' => 'Baja', 'max_win' => 'x3', 'min_bet' => '€5', 'max_bet' => '€5000', 'lines' => '-', 'reels' => '-', 'image' => 'https://images.unsplash.com/photo-1541278107931-e006523892df?w=800&h=600&fit=crop', 'description' => 'Mesa VIP de blackjack en vivo con crupieres profesionales. Disfruta de reglas clasicas con la opcion de apostar detras. Streaming HD con multiples camaras para la mejor experiencia.'],
    'book-of-dead' => ['name' => 'Book of Dead', 'provider' => "Play'n GO", 'cat' => 'Slots', 'grad' => 'game-gradient-1', 'rtp' => '96.21%', 'volatilidad' => 'Alta', 'max_win' => 'x5000', 'min_bet' => '€0.10', 'max_bet' => '€100', 'lines' => '10', 'reels' => '5', 'image' => 'https://images.unsplash.com/photo-1539768942893-daf53e736b68?w=800&h=600&fit=crop', 'description' => 'Acompaña a Rich Wilde en una aventura por el antiguo Egipto. Book of Dead es una de las slots mas populares del mundo con giros gratis y simbolo expandible que puede cubrir los carretes completos.'],
    'crash-rocket' => ['name' => 'Crash Rocket', 'provider' => 'Spribe', 'cat' => 'Crash', 'grad' => 'game-gradient-8', 'rtp' => '97.0%', 'volatilidad' => 'Alta', 'max_win' => 'x∞', 'min_bet' => '€0.10', 'max_bet' => '€200', 'lines' => '-', 'reels' => '-', 'image' => 'https://images.unsplash.com/photo-1516849841032-87cbdec47910?w=800&h=600&fit=crop', 'description' => 'Un juego de tipo crash donde debes cobrar antes de que el cohete explote. Cuanto mas alto llegue, mayor sera tu multiplicador. Toma tus decisiones con estrategia para maximizar tus ganancias.'],
    'texas-holdem' => ['name' => "Texas Hold'em", 'provider' => 'PokerStars', 'cat' => 'Poker', 'grad' => 'game-gradient-6', 'rtp' => '98.5%', 'volatilidad' => 'Variable', 'max_win' => 'Sin limite', 'min_bet' => '€1', 'max_bet' => '€10000', 'lines' => '-', 'reels' => '-', 'image' => 'https://images.unsplash.com/photo-1542317783-24cb2074f0a5?w=800&h=600&fit=crop', 'description' => 'El juego de poker mas popular del mundo. Enfrentate a otros jugadores en mesas de Texas Hold\'em con diferentes niveles de apuesta. Torneos y cash games disponibles las 24 horas.'],
    'starburst' => ['name' => 'Starburst', 'provider' => 'NetEnt', 'cat' => 'Slots', 'grad' => 'game-gradient-7', 'rtp' => '96.09%', 'volatilidad' => 'Baja', 'max_win' => 'x500', 'min_bet' => '€0.10', 'max_bet' => '€100', 'lines' => '10', 'reels' => '5', 'image' => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=800&h=600&fit=crop', 'description' => 'La slot mas iconica de NetEnt. Starburst combina graficos brillantes conwilds expansivos y re-spins. Un clasico atemporal con una volatilidad baja ideal para sesiones de juego largas.'],
    'lightning-roulette' => ['name' => 'Lightning Roulette', 'provider' => 'Evolution', 'cat' => 'Ruleta', 'grad' => 'game-gradient-12', 'rtp' => '97.3%', 'volatilidad' => 'Media', 'max_win' => 'x500', 'min_bet' => '€0.20', 'max_bet' => '€500', 'lines' => '-', 'reels' => '-', 'image' => 'https://images.unsplash.com/photo-1507400492013-162706c8c05e?w=800&h=600&fit=crop', 'description' => 'Ruleta en vivo con multiplicadores electricos. En cada ronda, entre 1 y 5 numeros reciben multiplicadores de x50 o x500. Una experiencia unica que combina la ruleta clasica con ganancias extraordinarias.'],
    'big-bass-bonanza' => ['name' => 'Big Bass Bonanza', 'provider' => 'Pragmatic Play', 'cat' => 'Slots', 'grad' => 'game-gradient-9', 'rtp' => '96.71%', 'volatilidad' => 'Alta', 'max_win' => 'x2100', 'min_bet' => '€0.10', 'max_bet' => '€250', 'lines' => '10', 'reels' => '5', 'image' => 'https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?auto=format&fit=crop&w=1200&h=700&q=90', 'description' => 'Salva de pesca en esta slot acuatica. Recoge simbolos de pez y pescador durante los giros gratis para multiplicar tus ganancias. Un tema divertido con potencial de ganancias grandes.'],
    'blackjack-classic' => ['name' => 'Blackjack Classic', 'provider' => 'Microgaming', 'cat' => 'Blackjack', 'grad' => 'game-gradient-10', 'rtp' => '99.91%', 'volatilidad' => 'Baja', 'max_win' => 'x3', 'min_bet' => '€1', 'max_bet' => '€2000', 'lines' => '-', 'reels' => '-', 'image' => 'https://images.unsplash.com/photo-1560015534-cee980ba7e13?w=800&h=600&fit=crop', 'description' => 'Blackjack clasico con la mejor tasa de retorno del 99.91%. Reglas estandar con 6 barajas, dealer se para en 17. Decisiones rapidas y estrategia optima para maximizar tus posibilidades.'],
];

$uegos = array_merge($uegos, config('arcade_games'));

Route::get('/', function () {
    return view('inicio');
})->name('inicio');

Route::get('/juego/{slug}', function ($slug) use ($uegos) {
    if (! isset($uegos[$slug])) {
        abort(404);
    }

    $gameRoutes = [
        'crash-rocket' => route('crash'),
        'sweet-bonanza' => route('slots', ['game' => 'sweet-bonanza']),
        'gates-of-olympus' => route('slots', ['game' => 'gates-of-olympus']),
        'book-of-dead' => route('slots', ['game' => 'book-of-dead']),
        'starburst' => route('slots', ['game' => 'starburst']),
        'big-bass-bonanza' => route('slots', ['game' => 'big-bass-bonanza']),
        'european-roulette' => route('ruleta'),
        'lightning-roulette' => route('ruleta.lightning'),
        'blackjack-vip' => route('blackjack'),
        'blackjack-classic' => route('blackjack.classic'),
    ];

    if (config("arcade_games.{$slug}")) {
        $gameRoutes[$slug] = route('arcade', ['game' => $slug]);
    }
    if ($slug === 'dealer-poker') {
        $gameRoutes[$slug] = route('poker.dealer');
    }

    return view('juego.show', [
        'juego' => $uegos[$slug],
        'slug' => $slug,
        'playUrl' => $gameRoutes[$slug] ?? null,
    ]);
})->name('juego.show');

Route::get('/apuestas', [ApuestasController::class, 'index'])->name('apuestas');
Route::get('/apuestas/en-vivo', [ApuestasController::class, 'feed'])->name('apuestas.feed');

Route::get('/cajas', [CajaController::class, 'index'])->name('cajas');

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
    Route::get('/jugar/poker/dealer', [PokerDealerController::class, 'index'])->name('poker.dealer');
    Route::post('/jugar/poker/dealer/iniciar', [PokerDealerController::class, 'start'])->name('poker.dealer.start');
    Route::post('/jugar/poker/dealer/accion', [PokerDealerController::class, 'action'])->name('poker.dealer.action');
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
    Route::get('/jugar/blackjack/clasico', [BlackjackController::class, 'index'])->defaults('variant', 'classic')->name('blackjack.classic');
    Route::post('/jugar/blackjack/clasico/repartir', [BlackjackController::class, 'deal'])->defaults('variant', 'classic')->name('blackjack.classic.deal');
    Route::post('/jugar/blackjack/clasico/pedir', [BlackjackController::class, 'hit'])->defaults('variant', 'classic')->name('blackjack.classic.hit');
    Route::post('/jugar/blackjack/clasico/plantarse', [BlackjackController::class, 'stand'])->defaults('variant', 'classic')->name('blackjack.classic.stand');

    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin|admin|moderator'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users');
    Route::get('/usuarios/{usuario}/editar', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{usuario}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{usuario}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews');
    Route::put('/reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback');
    Route::put('/feedback/{fb}', [AdminFeedbackController::class, 'update'])->name('feedback.update');
    Route::delete('/feedback/{fb}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin|admin'])->group(function () {
    Route::get('/graficos', [AdminChartsController::class, 'index'])->name('charts');
    Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles');
    Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/logs', [AdminLogController::class, 'index'])->name('logs');
});
