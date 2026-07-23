<div
    x-data="gameToolbar()"
    x-cloak
    class="game-toolbar mx-auto mb-4 max-w-[1450px] px-4 sm:px-6"
    @keydown.escape.window="exitFullscreen()"
>
    <div class="game-toolbar__inner" role="toolbar" aria-label="Controles de la experiencia de juego">
        <div class="game-toolbar__status">
            <span class="game-toolbar__live-dot" aria-hidden="true"></span>
            <span class="hidden sm:inline" x-text="reducedMotion ? 'Movimiento reducido' : 'Mesa lista'"></span>
            <span class="sm:hidden" x-text="reducedMotion ? 'Movimiento reducido' : 'Listo'"></span>
        </div>

        <div class="flex items-center gap-1.5">
            <button
                type="button"
                class="game-toolbar__button"
                @click="toggleSound()"
                :aria-pressed="$store.lootra.soundEnabled.toString()"
                :title="$store.lootra.soundEnabled ? 'Silenciar sonidos' : 'Activar sonidos'"
            >
                <svg x-show="$store.lootra.soundEnabled" x-cloak aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5 6 9H3v6h3l5 4V5Zm7.07 1.93a7 7 0 0 1 0 10.14M15.54 9.46a3.5 3.5 0 0 1 0 5.08"/></svg>
                <svg x-show="!$store.lootra.soundEnabled" x-cloak aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5 6 9H3v6h3l5 4"/><path stroke-linecap="round" d="m18 10-4 4m0-4 4 4"/></svg>
                <span class="hidden md:inline" x-text="$store.lootra.soundEnabled ? 'Sonido' : 'Silencio'"></span>
            </button>

            <button
                type="button"
                class="game-toolbar__button"
                @click="toggleFullscreen()"
                :aria-pressed="fullscreen.toString()"
                :title="fullscreen ? 'Salir de pantalla completa' : 'Pantalla completa'"
            >
                <svg x-show="!fullscreen" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3H3v5m13-5h5v5M8 21H3v-5m18 0v5h-5"/></svg>
                <svg x-show="fullscreen" x-cloak aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v6H3m12-6v6h6M9 21v-6H3m12 6v-6h6"/></svg>
                <span class="hidden md:inline" x-text="fullscreen ? 'Salir' : 'Pantalla completa'"></span>
            </button>
        </div>
    </div>
</div>
