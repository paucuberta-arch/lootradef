<footer class="border-t border-white/5 bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">

            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-black text-xs">
                    L
                </div>
                <span class="font-bold text-white">Lootra</span>
            </div>

            <div class="flex items-center gap-6 text-sm text-slate-500">
                <a href="{{ url('/') }}" class="hover:text-white transition">Inicio</a>
                <a href="{{ route('suma') }}" class="hover:text-white transition">Calculadora</a>
                <a href="{{ route('hola') }}" class="hover:text-white transition">Casino</a>
                <a href="{{ route('resultados.index') }}" class="hover:text-white transition">Resultados</a>
            </div>

            <p class="text-sm text-slate-600">
                &copy; {{ date('Y') }} Lootra. Todos los derechos reservados.
            </p>

        </div>
    </div>
</footer>
