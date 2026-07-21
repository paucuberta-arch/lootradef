<footer class="relative overflow-hidden border-t border-white/5 bg-[#05070e]">
    <img src="{{ asset('images/lootra_visual_pack/05_top_panels/panel_originals_1920x360.webp') }}" alt="" class="pointer-events-none absolute inset-x-0 top-0 h-80 w-full object-cover opacity-20" width="1920" height="360" loading="lazy" decoding="async" aria-hidden="true">
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[#05070e]/35 via-[#05070e]/90 to-[#05070e]"></div>
    <div class="pointer-events-none absolute -left-24 top-16 h-64 w-64 rounded-full bg-cyan-400/5 blur-[90px]"></div>

    <div class="relative mx-auto max-w-[1400px] px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-5">
                <a href="{{ route('inicio') }}" class="group inline-flex items-center gap-3" aria-label="Lootra Casino, inicio">
                    <div class="brand-gem grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-brand-300 via-brand-400 to-emerald-400 text-sm font-black text-black shadow-lg shadow-brand-500/20"><span>L</span></div>
                    <span class="font-display text-xl font-extrabold tracking-tight text-white">Lootra<span class="text-brand-300">Casino</span></span>
                </a>
                <p class="mt-5 max-w-md text-sm leading-7 text-slate-400">Un universo de casino, originales y experiencias en directo. Cada mundo tiene su ritmo; todos comparten la misma cuenta.</p>

                <div class="mt-7 grid max-w-lg grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach([
                        ['label' => 'Casino', 'route' => route('games.index'), 'icon' => 1, 'color' => '#22d3ee'],
                        ['label' => 'Originals', 'route' => route('games.index', ['cat' => 'arcade']), 'icon' => 13, 'color' => '#c084fc'],
                        ['label' => 'Sports', 'route' => route('sports.index'), 'icon' => 21, 'color' => '#6ee7b7'],
                        ['label' => 'Drops', 'route' => route('cases.index'), 'icon' => 6, 'color' => '#f2cd75'],
                    ] as $world)
                        <a href="{{ $world['route'] }}" class="footer-world-link flex items-center gap-2 rounded-xl border border-white/10 bg-black/20 px-2.5 py-2 text-[11px] font-bold text-slate-300" style="--world-accent:{{ $world['color'] }}">
                            <img src="{{ asset(sprintf('images/lootra_visual_pack/09_icons/icon_ui_%02d_256.webp', $world['icon'])) }}" alt="" class="h-7 w-7 object-contain" width="28" height="28" loading="lazy" decoding="async" aria-hidden="true">
                            {{ $world['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-7 lg:pl-10">
                <div>
                    <h2 class="world-kicker">Explora</h2>
                    <ul class="mt-5 space-y-3 text-sm text-slate-500">
                        <li><a href="{{ route('games.index', ['cat' => 'slots']) }}" class="transition hover:text-white">Slots</a></li>
                        <li><a href="{{ route('games.index', ['cat' => 'ruleta']) }}" class="transition hover:text-white">Ruleta</a></li>
                        <li><a href="{{ route('games.index', ['cat' => 'poker']) }}" class="transition hover:text-white">Póker</a></li>
                        <li><a href="{{ route('games.index', ['cat' => 'live']) }}" class="transition hover:text-white">Casino en vivo</a></li>
                    </ul>
                </div>
                <div>
                    <h2 class="world-kicker">Lootra</h2>
                    <ul class="mt-5 space-y-3 text-sm text-slate-500">
                        <li><a href="{{ route('sports.index') }}" class="transition hover:text-white">Apuestas</a></li>
                        <li><a href="{{ route('cases.index') }}" class="transition hover:text-white">Cajas y drops</a></li>
                        <li><a href="{{ route('feedback.create') }}" class="transition hover:text-white">Feedback</a></li>
                        <li><a href="{{ route('info', 'contacto') }}" class="transition hover:text-white">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <h2 class="world-kicker">Confianza</h2>
                    <ul class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-sm text-slate-500 sm:block sm:space-y-3">
                        <li><a href="{{ route('info', 'ayuda') }}" class="transition hover:text-white">Centro de ayuda</a></li>
                        <li><a href="{{ route('info', 'responsable') }}" class="transition hover:text-white">Juego responsable</a></li>
                        <li><a href="{{ route('info', 'verificacion') }}" class="transition hover:text-white">Verificación de edad</a></li>
                        <li><a href="{{ route('info', 'autoexclusion') }}" class="transition hover:text-white">Autoexclusión</a></li>
                        <li><a href="{{ route('info', 'privacidad') }}" class="transition hover:text-white">Privacidad</a></li>
                        <li><a href="{{ route('info', 'terminos') }}" class="transition hover:text-white">Términos</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-5 border-t border-white/[.07] pt-7 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-2.5 py-1 text-xs font-black text-emerald-300">+18</span>
                <span class="text-xs text-slate-600">Juega con cabeza. La diversión siempre debe seguir siendo diversión.</span>
            </div>
            <p class="text-xs text-slate-700">&copy; {{ date('Y') }} Lootra Casino</p>
        </div>
    </div>
</footer>
