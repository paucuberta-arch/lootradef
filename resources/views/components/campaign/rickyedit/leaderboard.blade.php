@props(['leaders' => collect(), 'limit' => 5])
<section {{ $attributes->class('rounded-2xl border border-white/10 bg-white/[.035] p-5') }}>
    <div class="flex items-center justify-between"><h2 class="text-lg font-black">Ranking</h2><a href="{{ route('rickyedit.ranking') }}" class="text-xs font-bold text-cyan-300">Ver completo →</a></div>
    <div class="mt-4 space-y-2">
        @forelse($leaders->take($limit) as $entry)
            <div class="flex items-center gap-3 rounded-xl bg-black/20 px-3 py-2">
                <span class="w-6 text-center text-xs font-black text-slate-500">{{ $loop->iteration }}</span>
                <x-ui.user-avatar :user="$entry->user" size="custom" shape="soft" class="h-8 w-8 ring-fuchsia-300/30" />
                <span class="min-w-0 flex-1 truncate text-sm font-bold">{{ $entry->public_alias }}</span>
                <b class="text-sm text-fuchsia-300">{{ number_format($entry->score) }}</b>
            </div>
        @empty
            <p class="rounded-xl bg-black/20 p-4 text-sm text-slate-500">Aún no hay resultados reales publicados.</p>
        @endforelse
    </div>
</section>
