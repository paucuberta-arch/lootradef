<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class InfoController extends Controller
{
    public function show(string $page): Response
    {
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

        abort_unless(isset($pages[$page]), 404);

        return response()->view('info.index', [
            'pageTitle' => $pages[$page]['title'],
            'content' => $pages[$page]['content'],
        ]);
    }
}
