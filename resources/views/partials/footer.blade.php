<footer class="border-t border-white/5 bg-[#08080f]">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

            {{-- Brand --}}
            <div>
                <a href="{{ url('/') }}" class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black font-black text-xs">L</div>
                    <span class="font-extrabold text-white">Lootra<span class="text-brand-400">Casino</span></span>
                </a>
                <p class="text-sm text-slate-500 leading-relaxed">La mejor experiencia de casino online. Juega de forma responsable.</p>
            </div>

            {{-- Juegos --}}
            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Juegos</h4>
                <ul class="space-y-2.5 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-brand-400 transition">Slots</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Ruleta</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Blackjack</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Poker</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Live Casino</a></li>
                </ul>
            </div>

            {{-- Soporte --}}
            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Soporte</h4>
                <ul class="space-y-2.5 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-brand-400 transition">Centro de ayuda</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Contacto</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Terminos y condiciones</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Politica de privacidad</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Legal</h4>
                <ul class="space-y-2.5 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-brand-400 transition">Juego responsable</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Verificacion de edad</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Autoexclusion</a></li>
                </ul>
                <div class="mt-4 flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">+18</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-semibold">Juego Responsable</span>
                </div>
            </div>

        </div>

        <div class="mt-10 pt-8 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-600">&copy; {{ date('Y') }} Lootra Casino. Todos los derechos reservados.</p>
            <p class="text-xs text-slate-700">El juego puede ser adictivo. Juega responsablemente.</p>
        </div>
    </div>
</footer>
