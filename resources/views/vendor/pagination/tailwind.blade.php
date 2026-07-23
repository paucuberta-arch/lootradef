@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500">
            Mostrando
            <span class="font-bold text-slate-300">{{ $paginator->firstItem() }}</span>
            —
            <span class="font-bold text-slate-300">{{ $paginator->lastItem() }}</span>
            de
            <span class="font-bold text-slate-300">{{ $paginator->total() }}</span>
        </p>

        <div class="flex flex-wrap items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-h-9 items-center rounded-lg border border-white/5 bg-white/[.02] px-3 text-xs font-bold text-slate-600" aria-disabled="true">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex min-h-9 items-center rounded-lg border border-white/10 bg-white/[.04] px-3 text-xs font-bold text-slate-300 transition hover:border-brand-400/30 hover:bg-brand-400/10 hover:text-brand-200">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex min-h-9 min-w-9 items-center justify-center px-1 text-xs font-bold text-slate-600" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border border-brand-300/50 bg-brand-400 px-2 text-xs font-black text-slate-950 shadow-lg shadow-brand-500/20" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border border-white/10 bg-white/[.04] px-2 text-xs font-bold text-slate-300 transition hover:border-brand-400/30 hover:bg-brand-400/10 hover:text-brand-200" aria-label="Ir a la página {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex min-h-9 items-center rounded-lg border border-white/10 bg-white/[.04] px-3 text-xs font-bold text-slate-300 transition hover:border-brand-400/30 hover:bg-brand-400/10 hover:text-brand-200">Siguiente</a>
            @else
                <span class="inline-flex min-h-9 items-center rounded-lg border border-white/5 bg-white/[.02] px-3 text-xs font-bold text-slate-600" aria-disabled="true">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
