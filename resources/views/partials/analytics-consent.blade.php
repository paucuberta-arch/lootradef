@if(!in_array(request()->cookie('lootra_analytics_consent'), ['granted', 'denied'], true))
<aside class="fixed inset-x-4 bottom-4 z-[120] mx-auto max-w-3xl rounded-2xl border border-white/15 bg-[#0b1020]/95 p-4 shadow-2xl backdrop-blur-xl sm:flex sm:items-center sm:gap-5" aria-label="Preferencias de analítica">
    <p class="text-sm text-slate-200">Usamos analítica opcional para entender el uso de la plataforma. No se cargará hasta que la aceptes. <a href="{{ route('info', ['page' => 'privacidad']) }}" class="font-bold text-cyan-300">Más información</a>.</p>
    <div class="mt-3 flex shrink-0 gap-2 sm:mt-0">
        <form method="POST" action="{{ route('privacy.analytics-consent') }}">@csrf<input type="hidden" name="choice" value="denied"><button class="rounded-xl border border-white/15 px-4 py-2 text-sm font-bold text-slate-200">No permitir</button></form>
        <form method="POST" action="{{ route('privacy.analytics-consent') }}">@csrf<input type="hidden" name="choice" value="granted"><button class="rounded-xl bg-cyan-300 px-4 py-2 text-sm font-black text-slate-950">Aceptar</button></form>
    </div>
</aside>
@endif
