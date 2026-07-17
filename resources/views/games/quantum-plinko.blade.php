@extends('layouts.game')
@section('title', 'Quantum Plinko — Lootra Originals')

@section('styles')
<style>
    .quantum-stage {
        background:
            radial-gradient(circle at 50% 8%, rgba(34,211,238,.2), transparent 28%),
            radial-gradient(circle at 20% 70%, rgba(168,85,247,.15), transparent 30%),
            linear-gradient(155deg, #101336, #050611 72%);
        box-shadow: inset 0 0 90px #000b, 0 30px 80px #0008;
    }
    .quantum-stage::before {
        content: ""; position:absolute; inset:0; pointer-events:none; opacity:.22;
        background-image:linear-gradient(rgba(103,232,249,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(103,232,249,.08) 1px,transparent 1px);
        background-size:32px 32px; mask-image:linear-gradient(to bottom,#000,transparent 82%);
    }
    .quantum-core { animation:quantum-core 2.2s ease-in-out infinite; }
    .quantum-live .quantum-core { animation-duration:.65s; }
    .quantum-live .quantum-stage { box-shadow:inset 0 0 100px #000b,0 30px 80px #0008,0 0 45px rgba(34,211,238,.12); }
    .quantum-canvas { width:100%;height:auto;aspect-ratio:720/760;display:block;touch-action:none; }
    .quantum-stat { background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.018)); }
    @keyframes quantum-core { 50% { opacity:.68;filter:drop-shadow(0 0 12px #22d3ee);transform:scale(.96); } }
    @media (prefers-reduced-motion:reduce) { .quantum-core { animation:none; } }
    @media (max-width:639px) {
        .quantum-stage::before { background-size:22px 22px; }
    }
</style>
@endsection

@section('game-content')
<div class="mx-auto max-w-[1380px] px-4 py-6 sm:px-6 sm:py-9" x-data="quantumPlinko()" :class="playing && 'quantum-live'">
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[.24em] text-cyan-300">Lootra Originals · Motor físico</p>
            <h1 class="game-heading mt-2 font-black">Quantum Plinko</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Gravedad, colisiones y rebotes calculados en tiempo real para cada esfera.</p>
        </div>
        <div class="balance-chip self-start rounded-xl px-4 py-3 sm:self-auto"><span class="text-xs text-slate-500">Saldo</span><strong class="ml-2 text-brand-300" x-text="money(saldo)"></strong></div>
    </header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_330px]">
        <main class="min-w-0">
            <section class="quantum-stage relative mx-auto max-w-[760px] overflow-hidden rounded-[2rem] border border-cyan-300/15 p-2 sm:p-4">
                <div class="relative z-10 flex items-center justify-between px-2 pb-1 pt-2 sm:px-4">
                    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[.18em] text-cyan-200/70"><i class="quantum-core h-2.5 w-2.5 rounded-full bg-cyan-300 shadow-[0_0_14px_#22d3ee]"></i><span x-text="playing ? 'Simulación activa' : 'Núcleo estable'"></span></div>
                    <span class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-[10px] text-slate-400">10 niveles · 11 destinos</span>
                </div>
                <canvas x-ref="plinkoCanvas" class="quantum-canvas relative z-10" width="720" height="760" role="img" aria-label="Tablero Quantum Plinko con diez filas de clavijas y once casillas de premio"></canvas>
                <p class="sr-only" aria-live="polite" x-text="statusText"></p>
            </section>

            <div x-show="result" x-transition class="mx-auto mt-5 max-w-[760px] rounded-2xl border p-4 text-center" :class="result?.multiplier>=1?'border-emerald-400/25 bg-emerald-400/10':'border-fuchsia-400/20 bg-fuchsia-400/10'" aria-live="polite">
                <p class="text-[10px] font-black uppercase tracking-[.2em] text-slate-400">Colapso cuántico completado</p>
                <p class="mt-1 text-2xl font-black" :class="result?.multiplier>=1?'text-emerald-300':'text-fuchsia-300'"><span x-text="result?.multiplier+'x'"></span> · <span x-text="money(result?.ganancia)"></span></p>
                <p class="mt-1 text-xs text-slate-500" x-text="netMessage"></p>
            </div>
        </main>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
                <p class="text-[10px] font-black uppercase tracking-[.2em] text-fuchsia-300">Control de lanzamiento</p>
                <h2 class="mt-1 text-lg font-black">Carga la esfera</h2>
                <label class="mt-4 block text-xs text-slate-500" for="quantum-bet">Apuesta</label>
                <div class="mt-2 flex items-center rounded-xl border bg-black/20 px-3" :class="canPlay?'border-white/10':'border-red-400/30'">
                    <span class="text-slate-500">€</span>
                    <input id="quantum-bet" x-model.number="apuesta" :disabled="playing" type="number" min=".2" max="500" step=".2" class="w-full bg-transparent px-3 py-3 font-bold outline-none disabled:opacity-50">
                </div>
                <div class="mt-2 grid grid-cols-4 gap-1"><template x-for="value in [1,5,10,25]" :key="value"><button @click="apuesta=value" :disabled="playing" class="rounded-lg bg-white/5 py-2 text-xs hover:bg-white/10 disabled:opacity-40" x-text="value+'€'"></button></template></div>
                <button @click="play" :disabled="playing||!canPlay" class="cta-shine mt-4 w-full rounded-xl bg-gradient-to-r from-cyan-300 via-brand-400 to-fuchsia-500 py-3.5 font-black text-slate-950 shadow-lg shadow-cyan-500/10 disabled:opacity-40">
                    <span x-text="playing ? 'Esfera en movimiento…' : 'Liberar esfera'"></span>
                </button>
                <p x-show="error" role="alert" class="mt-3 rounded-lg bg-red-500/10 p-2 text-center text-xs text-red-300" x-text="error"></p>
            </section>

            <section class="grid grid-cols-2 gap-2">
                <div class="quantum-stat rounded-xl border border-white/10 p-3"><span class="block text-[10px] uppercase tracking-wider text-slate-500">RTP teórico</span><b class="mt-1 block text-emerald-300">{{ $game['rtp'] }}</b></div>
                <div class="quantum-stat rounded-xl border border-white/10 p-3"><span class="block text-[10px] uppercase tracking-wider text-slate-500">Premio máximo</span><b class="mt-1 block text-fuchsia-300">{{ $game['max_win'] }}</b></div>
                <div class="quantum-stat rounded-xl border border-white/10 p-3"><span class="block text-[10px] uppercase tracking-wider text-slate-500">Gravedad</span><b class="mt-1 block text-cyan-300">9.81 m/s²</b></div>
                <div class="quantum-stat rounded-xl border border-white/10 p-3"><span class="block text-[10px] uppercase tracking-wider text-slate-500">Clavijas</span><b class="mt-1 block text-cyan-300">55 físicas</b></div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
                <h3 class="font-bold">Física de la mesa</h3>
                <p class="mt-2 text-xs leading-relaxed text-slate-500">La esfera acelera por gravedad, pierde energía en cada impacto y recibe impulso lateral al colisionar. El resultado económico permanece validado por el servidor.</p>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
                <h3 class="font-bold">Últimos lanzamientos</h3>
                <div class="mt-3 space-y-2">
                    @forelse($history as $item)
                        @php($net = (float) $item->ganancia - (float) $item->apuesta)
                        <div class="flex items-center justify-between text-xs"><span class="text-slate-500">{{ $item->detalles['multiplier'] ?? 0 }}x</span><b class="{{ $net >= 0 ? 'text-emerald-300' : 'text-fuchsia-300' }}">{{ $net >= 0 ? '+' : '-' }}€{{ number_format(abs($net), 2, ',', '.') }}</b></div>
                    @empty
                        <p class="text-xs text-slate-600">Aún no hay lanzamientos.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function quantumPlinko() {
    const multipliers = [12, 5, 2, 1.2, .7, .4, .7, 1.2, 2, 5, 12];
    const width = 720;
    const height = 760;
    const center = width / 2;
    const pegGap = 52;
    const rowStart = 105;
    const rowGap = 49;
    const slotsTop = 620;
    const floor = 714;

    return {
        saldo: {{ auth()->user()->saldo }},
        apuesta: 2,
        playing: false,
        result: null,
        error: '',
        statusText: 'Tablero preparado para un nuevo lanzamiento.',
        roundToken: null,
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        canvas: null,
        context: null,
        pixelRatio: 1,
        animationFrame: null,
        idleFrame: null,
        lastIdlePaint: 0,
        ball: null,
        trail: [],
        particles: [],
        flashes: new Map(),
        landedSlot: null,
        stars: [],
        get canPlay() {
            const bet = Number(this.apuesta);
            return Number.isFinite(bet) && bet >= .2 && bet <= 500 && bet <= this.saldo;
        },
        get netMessage() {
            if (!this.result) return '';
            const net = Number(this.result.ganancia) - Number(this.result.apuesta);
            if (net > 0) return `Beneficio neto: ${this.money(net)}`;
            if (net === 0) return 'La apuesta ha sido devuelta íntegramente.';
            return `Resultado neto: -${this.money(Math.abs(net))}`;
        },
        init() {
            this.$nextTick(() => this.initCanvas());
        },
        destroy() {
            cancelAnimationFrame(this.animationFrame);
            cancelAnimationFrame(this.idleFrame);
        },
        initCanvas() {
            this.canvas = this.$refs.plinkoCanvas;
            if (!this.canvas) return;
            this.pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
            this.canvas.width = width * this.pixelRatio;
            this.canvas.height = height * this.pixelRatio;
            this.context = this.canvas.getContext('2d', {alpha: false});
            this.context.setTransform(this.pixelRatio, 0, 0, this.pixelRatio, 0, 0);
            this.stars = Array.from({length: 46}, (_, index) => ({
                x: 25 + ((index * 83) % 670),
                y: 28 + ((index * 137) % 570),
                radius: .5 + (index % 3) * .35,
                phase: (index * .71) % Math.PI,
            }));
            this.drawScene(performance.now());
            const idle = time => {
                if (!this.playing && time - this.lastIdlePaint > 42) {
                    this.drawScene(time);
                    this.lastIdlePaint = time;
                }
                this.idleFrame = requestAnimationFrame(idle);
            };
            this.idleFrame = requestAnimationFrame(idle);
        },
        money(value) {
            return new Intl.NumberFormat('es-ES', {style: 'currency', currency: 'EUR'}).format(Number(value) || 0);
        },
        requestToken() {
            const webCrypto = globalThis.crypto;
            if (typeof webCrypto?.randomUUID === 'function') return webCrypto.randomUUID();
            const bytes = new Uint8Array(16);
            webCrypto.getRandomValues(bytes);
            bytes[6] = (bytes[6] & 15) | 64;
            bytes[8] = (bytes[8] & 63) | 128;
            return Array.from(bytes, (byte, index) => ([4, 6, 8, 10].includes(index) ? '-' : '') + byte.toString(16).padStart(2, '0')).join('');
        },
        updateBalance(value) {
            this.saldo = Number(value);
            this.$store.wallet.saldo = this.saldo;
            window.dispatchEvent(new CustomEvent('saldo-updated', {detail: {saldo: this.saldo}}));
        },
        async play() {
            if (this.playing || !this.canPlay) return;
            this.playing = true;
            this.result = null;
            this.error = '';
            this.landedSlot = null;
            this.statusText = 'La esfera está descendiendo por el campo cuántico.';
            this.roundToken ||= this.requestToken();

            try {
                const response = await fetch(@js(route('games.originals.play', ['game' => $game['slug']])), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({apuesta: this.apuesta, request_token: this.roundToken}),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'No se pudo completar el lanzamiento.');

                await this.animateDrop(data);
                this.roundToken = null;
                this.result = data;
                this.updateBalance(data.saldo);
                this.statusText = `La esfera ha caído en la casilla ${data.multiplier}x.`;
            } catch (error) {
                this.error = error.message;
                this.statusText = 'El lanzamiento no se ha podido completar.';
                this.ball = null;
            } finally {
                this.playing = false;
                this.drawScene(performance.now());
            }
        },
        pegPosition(row, column) {
            return {x: center + (column - row / 2) * pegGap, y: rowStart + row * rowGap};
        },
        async animateDrop(data) {
            const path = Array.isArray(data.path) ? data.path.map(Number).slice(0, 10) : [];
            if (path.length !== 10 || path.some(step => ![0, 1].includes(step)) || !Number.isInteger(Number(data.slot))) throw new Error('La trayectoria recibida no es válida.');
            const slot = Math.max(0, Math.min(10, Number(data.slot)));

            this.trail = [];
            this.particles = [];
            this.flashes.clear();
            this.ball = {x: center, y: 48, vx: 7, vy: 0, row: 0, branch: 0, bounces: 0, slot};

            if (this.reducedMotion || !this.context) {
                this.ball = {x: center + (slot - 5) * pegGap, y: floor - 13, vx: 0, vy: 0, row: 10, branch: slot, bounces: 2, slot};
                this.landedSlot = slot;
                this.drawScene(performance.now());
                await new Promise(resolve => setTimeout(resolve, 120));
                return;
            }

            await new Promise(resolve => {
                let previousTime = performance.now();
                const startedAt = previousTime;
                const frame = time => {
                    const delta = Math.min((time - previousTime) / 1000, .025);
                    previousTime = time;
                    const settled = this.stepPhysics(delta, path, time, time - startedAt);
                    this.drawScene(time);
                    if (settled) {
                        this.landedSlot = slot;
                        this.drawScene(time);
                        resolve();
                        return;
                    }
                    this.animationFrame = requestAnimationFrame(frame);
                };
                this.animationFrame = requestAnimationFrame(frame);
            });
        },
        stepPhysics(delta, path, time, elapsed) {
            const ball = this.ball;
            const gravity = 1180;
            const radius = 13;

            if (ball.row < 10) {
                const target = this.pegPosition(ball.row, ball.branch);
                const guide = Math.max(-260, Math.min(260, (target.x - ball.x) * 7.2));
                ball.vx += guide * delta;
                ball.vy += gravity * delta;
                ball.vx *= Math.pow(.993, delta * 60);
                ball.x += ball.vx * delta;
                ball.y += ball.vy * delta;

                if (ball.vy > 0 && ball.y + radius >= target.y - 1 && ball.y < target.y + 12) {
                    const direction = path[ball.row] === 1 ? 1 : -1;
                    const contactOffset = 6 * direction;
                    ball.x = target.x + contactOffset;
                    ball.y = target.y - Math.sqrt((radius + 6) ** 2 - contactOffset ** 2);
                    ball.vx = direction * (108 + ball.row * 3.5);
                    ball.vy = -Math.max(58, Math.abs(ball.vy) * .22);
                    this.flashes.set(`${ball.row}-${ball.branch}`, time + 170);
                    this.spawnParticles(target.x, target.y, direction);
                    ball.branch += path[ball.row];
                    ball.row++;
                }
            } else {
                const targetX = center + (ball.slot - 5) * pegGap;
                ball.vx += (targetX - ball.x) * 10.5 * delta;
                ball.vy += gravity * delta;
                ball.vx *= Math.pow(.965, delta * 60);
                ball.x += ball.vx * delta;
                ball.y += ball.vy * delta;

                if (ball.y >= floor - radius) {
                    ball.y = floor - radius;
                    if (ball.bounces < 2 && Math.abs(ball.vy) > 36) {
                        ball.vy = -Math.abs(ball.vy) * .31;
                        ball.vx *= .45;
                        ball.bounces++;
                        this.spawnParticles(ball.x, floor - 5, ball.vx >= 0 ? 1 : -1);
                    } else {
                        ball.vy = 0;
                        ball.vx *= .65;
                        ball.x += (targetX - ball.x) * .16;
                        if (Math.abs(targetX - ball.x) < .7) {
                            ball.x = targetX;
                            return true;
                        }
                    }
                }
            }

            const railProgress = Math.max(0, Math.min(1, (ball.y - 56) / (slotsTop - 56)));
            const leftRail = center - railProgress * 292 + radius;
            const rightRail = center + railProgress * 292 - radius;
            if (ball.x < leftRail) { ball.x = leftRail; ball.vx = Math.abs(ball.vx) * .62; }
            if (ball.x > rightRail) { ball.x = rightRail; ball.vx = -Math.abs(ball.vx) * .62; }

            this.trail.unshift({x: ball.x, y: ball.y});
            this.trail = this.trail.slice(0, 18);
            this.updateParticles(delta);
            if (elapsed > 6800) {
                ball.x = center + (ball.slot - 5) * pegGap;
                ball.y = floor - radius;
                ball.vx = 0;
                ball.vy = 0;
                return true;
            }
            return false;
        },
        spawnParticles(x, y, direction) {
            for (let index = 0; index < 7; index++) {
                const angle = -Math.PI / 2 + (index - 3) * .28;
                const speed = 45 + index * 8;
                this.particles.push({x, y, vx: Math.cos(angle) * speed + direction * 22, vy: Math.sin(angle) * speed, life: 1, size: 1.5 + index % 3});
            }
        },
        updateParticles(delta) {
            this.particles.forEach(particle => {
                particle.vy += 260 * delta;
                particle.x += particle.vx * delta;
                particle.y += particle.vy * delta;
                particle.life -= delta * 2.5;
            });
            this.particles = this.particles.filter(particle => particle.life > 0);
        },
        drawScene(time) {
            const context = this.context;
            if (!context) return;
            context.setTransform(this.pixelRatio, 0, 0, this.pixelRatio, 0, 0);
            context.clearRect(0, 0, width, height);

            const background = context.createLinearGradient(0, 0, 0, height);
            background.addColorStop(0, '#10163d');
            background.addColorStop(.62, '#090b24');
            background.addColorStop(1, '#050611');
            context.fillStyle = background;
            context.fillRect(0, 0, width, height);

            this.stars.forEach(star => {
                context.globalAlpha = .16 + (Math.sin(time / 850 + star.phase) + 1) * .12;
                context.fillStyle = '#a5f3fc';
                context.beginPath(); context.arc(star.x, star.y, star.radius, 0, Math.PI * 2); context.fill();
            });
            context.globalAlpha = 1;

            const rail = context.createLinearGradient(70, 0, 650, 0);
            rail.addColorStop(0, '#7c3aed'); rail.addColorStop(.5, '#67e8f9'); rail.addColorStop(1, '#7c3aed');
            context.strokeStyle = rail;
            context.lineWidth = 3;
            context.shadowColor = '#22d3ee'; context.shadowBlur = 12;
            context.beginPath(); context.moveTo(center, 55); context.lineTo(666, slotsTop); context.moveTo(center, 55); context.lineTo(54, slotsTop); context.stroke();
            context.shadowBlur = 0;

            for (let row = 0; row < 10; row++) {
                for (let column = 0; column <= row; column++) {
                    const peg = this.pegPosition(row, column);
                    const flashing = (this.flashes.get(`${row}-${column}`) || 0) > time;
                    context.fillStyle = flashing ? '#f0abfc' : '#67e8f9';
                    context.shadowColor = flashing ? '#d946ef' : '#22d3ee';
                    context.shadowBlur = flashing ? 20 : 8 + Math.sin(time / 500 + row) * 2;
                    context.beginPath(); context.arc(peg.x, peg.y, flashing ? 7.5 : 5.5, 0, Math.PI * 2); context.fill();
                    context.shadowBlur = 0;
                }
            }

            const slotLeft = center - 5.5 * pegGap;
            for (let slot = 0; slot < 11; slot++) {
                const x = slotLeft + slot * pegGap;
                const active = this.landedSlot === slot;
                const slotGradient = context.createLinearGradient(0, slotsTop, 0, 742);
                slotGradient.addColorStop(0, active ? 'rgba(217,70,239,.44)' : 'rgba(34,211,238,.08)');
                slotGradient.addColorStop(1, active ? 'rgba(124,58,237,.45)' : 'rgba(124,58,237,.12)');
                context.fillStyle = slotGradient;
                context.fillRect(x, slotsTop, pegGap, 118);
                context.strokeStyle = active ? '#f0abfc' : 'rgba(103,232,249,.26)';
                context.lineWidth = active ? 2 : 1;
                context.strokeRect(x, slotsTop, pegGap, 118);
                context.fillStyle = active ? '#f5d0fe' : (multipliers[slot] >= 2 ? '#67e8f9' : '#94a3b8');
                context.font = `900 ${multipliers[slot] >= 10 ? 14 : 15}px Space Grotesk, sans-serif`;
                context.textAlign = 'center';
                context.fillText(`${multipliers[slot]}x`, x + pegGap / 2, 739);
            }

            this.trail.forEach((point, index) => {
                const alpha = (1 - index / this.trail.length) * .3;
                context.fillStyle = `rgba(217,70,239,${alpha})`;
                context.beginPath(); context.arc(point.x, point.y, Math.max(2, 9 - index * .35), 0, Math.PI * 2); context.fill();
            });
            this.particles.forEach(particle => {
                context.globalAlpha = Math.max(0, particle.life);
                context.fillStyle = '#f0abfc';
                context.beginPath(); context.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2); context.fill();
            });
            context.globalAlpha = 1;

            if (this.ball) {
                const orb = context.createRadialGradient(this.ball.x - 4, this.ball.y - 5, 2, this.ball.x, this.ball.y, 15);
                orb.addColorStop(0, '#ffffff'); orb.addColorStop(.24, '#a5f3fc'); orb.addColorStop(.62, '#d946ef'); orb.addColorStop(1, '#581c87');
                context.fillStyle = orb;
                context.shadowColor = '#d946ef'; context.shadowBlur = 24;
                context.beginPath(); context.arc(this.ball.x, this.ball.y, 13, 0, Math.PI * 2); context.fill();
                context.shadowBlur = 0;
            } else {
                const pulse = 11 + Math.sin(time / 380) * 1.5;
                context.fillStyle = '#a5f3fc'; context.shadowColor = '#22d3ee'; context.shadowBlur = 20;
                context.beginPath(); context.arc(center, 48, pulse, 0, Math.PI * 2); context.fill();
                context.shadowBlur = 0;
            }
        },
    };
}
</script>
@endpush
@endsection
