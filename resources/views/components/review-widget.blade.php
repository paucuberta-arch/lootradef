@auth
<div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6" x-data="reviewWidget()">
    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Deja tu review</h3>

    {{-- Star rating --}}
    <div class="flex items-center gap-1 mb-4" x-data="{ hover: 0 }">
        <template x-for="i in 5">
            <button type="button" @click="puntuacion = i" @mouseenter="hover = i" @mouseleave="hover = 0" :aria-label="`Puntuar con ${i} estrellas`"
                    class="text-2xl transition"
                    :class="(hover >= i || puntuacion >= i) ? 'text-brand-400' : 'text-slate-700'">★</button>
        </template>
        <span class="ml-2 text-sm text-slate-500" x-text="puntuacion ? puntuacion + '/5' : ''"></span>
    </div>

    <label for="review-title-{{ $slug }}" class="sr-only">Título de la opinión</label>
    <input id="review-title-{{ $slug }}" x-model="titulo" type="text" placeholder="Título (opcional)"
           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-slate-600 outline-none focus:border-brand-500 transition mb-3">

    <label for="review-content-{{ $slug }}" class="sr-only">Tu opinión</label>
    <textarea id="review-content-{{ $slug }}" x-model="contenido" rows="3" placeholder="Escribe tu opinión..."
              class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-slate-600 outline-none focus:border-brand-500 transition mb-3 resize-none"></textarea>

    <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
        <button @click="submit()" :disabled="!contenido || submitting"
                class="px-5 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold transition disabled:opacity-50">
            <span x-text="submitting ? 'Enviando...' : 'Publicar review'"></span>
        </button>
        <span x-show="success" class="text-sm text-emerald-400" x-text="success" role="status" aria-live="polite"></span>
        <span x-show="error" class="text-sm text-red-400" x-text="error" role="alert"></span>
    </div>

    {{-- Reviews existentes --}}
    <div class="mt-8 border-t border-white/5 pt-6">
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Opiniones</h4>
        <div class="space-y-4">
            @foreach($reviews ?? [] as $rev)
                <div class="flex gap-3">
                    <x-ui.user-avatar :user="$rev->usuario" size="sm" />
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-1">
                            <span class="text-sm font-semibold text-white">{{ $rev->usuario->name ?? 'Anonimo' }}</span>
                            <div class="flex gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-xs {{ $i <= $rev->puntuacion ? 'text-brand-400' : 'text-slate-700' }}">★</span>
                                @endfor
                            </div>
                            <span class="text-xs text-slate-600">{{ $rev->created_at->diffForHumans() }}</span>
                        </div>
                        @if($rev->titulo)<p class="text-sm font-semibold text-white mb-1">{{ $rev->titulo }}</p>@endif
                        <p class="text-sm text-slate-400">{{ $rev->contenido }}</p>
                    </div>
                </div>
            @endforeach

            @if(empty($reviews) || count($reviews) === 0)
                <p class="text-sm text-slate-600">Aun no hay reviews. Se el primero!</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function reviewWidget() {
    return {
        puntuacion: 0,
        titulo: '',
        contenido: '',
        submitting: false,
        success: '',
        error: '',

        async submit() {
            if (!this.contenido) return;
            this.submitting = true;
            this.success = '';
            this.error = '';

            try {
                const res = await fetch('{{ route("reviews.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        juego_slug: @js($slug ?? ''),
                        titulo: this.titulo,
                        contenido: this.contenido,
                        puntuacion: this.puntuacion,
                        tipo: @js($reviewType ?? 'juego'),
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    this.success = data.success;
                    this.titulo = '';
                    this.contenido = '';
                    this.puntuacion = 0;
                } else {
                    this.error = data.error || 'Error al enviar.';
                }
            } catch (e) {
                this.error = 'Error de conexion.';
            }

            this.submitting = false;
        },
    };
}
</script>
@endpush
@endauth

@guest
<div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 text-center">
    <p class="text-slate-400 text-sm mb-3">Inicia sesion para dejar tu review.</p>
    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold transition">Iniciar sesion</a>
</div>
@endguest
