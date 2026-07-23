import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.lootraRequestToken = () => {
    const webCrypto = globalThis.crypto;
    if (typeof webCrypto?.randomUUID === 'function') return webCrypto.randomUUID();
    const bytes = new Uint8Array(16);
    webCrypto.getRandomValues(bytes);
    bytes[6] = (bytes[6] & 15) | 64;
    bytes[8] = (bytes[8] & 63) | 128;
    return Array.from(bytes, (byte, index) => `${[4, 6, 8, 10].includes(index) ? '-' : ''}${byte.toString(16).padStart(2, '0')}`).join('');
};

const body = document.body;
const initialBalance = Number(body?.dataset.walletBalance ?? 0);

Alpine.store('wallet', { saldo: Number.isFinite(initialBalance) ? initialBalance : 0 });

const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
const soundStorageKey = 'lootra:sound-enabled';
const initialSoundEnabled = (() => {
    try {
        return window.localStorage.getItem(soundStorageKey) !== '0';
    } catch {
        return true;
    }
})();

const audioContext = {
    context: null,
    get enabled() {
        return Alpine.store('lootra')?.soundEnabled === true;
    },
    ensure() {
        if (!this.enabled) return null;
        const AudioContextClass = globalThis.AudioContext || globalThis.webkitAudioContext;
        if (!AudioContextClass) return null;
        this.context ||= new AudioContextClass();
        if (this.context.state === 'suspended') this.context.resume().catch(() => {});
        return this.context;
    },
    toggle() {
        const next = !this.enabled;
        Alpine.store('lootra').soundEnabled = next;
        try {
            window.localStorage.setItem(soundStorageKey, next ? '1' : '0');
        } catch {
            // Preferences are optional when storage is unavailable.
        }
        return next;
    },
    tone(frequency, duration = 0.08, options = {}) {
        const context = this.ensure();
        if (!context) return;
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const start = context.currentTime + (options.delay || 0);
        const end = start + duration;
        oscillator.type = options.type || 'sine';
        oscillator.frequency.setValueAtTime(frequency, start);
        if (options.to) oscillator.frequency.exponentialRampToValueAtTime(options.to, end);
        gain.gain.setValueAtTime(0.0001, start);
        gain.gain.exponentialRampToValueAtTime(options.gain || 0.035, start + Math.min(0.018, duration / 3));
        gain.gain.exponentialRampToValueAtTime(0.0001, end);
        oscillator.connect(gain).connect(context.destination);
        oscillator.start(start);
        oscillator.stop(end + 0.01);
    },
    play(event = 'click') {
        if (!this.enabled || reducedMotionQuery.matches && event === 'ambient') return;
        const patterns = {
            click: [[260, 0.055, 0]],
            select: [[420, 0.06, 0]],
            spin: [[150, 0.12, 0, {type: 'triangle', to: 240}], [210, 0.1, 0.08, {type: 'triangle', to: 330}]],
            'reel-stop': [[280, 0.065, 0, {type: 'square', gain: 0.025}]],
            card: [[520, 0.055, 0, {type: 'triangle', gain: 0.028}]],
            land: [[380, 0.08, 0, {type: 'triangle', to: 220}], [220, 0.1, 0.06, {type: 'sine', gain: 0.03}]],
            win: [[523, 0.1, 0], [659, 0.12, 0.09], [784, 0.18, 0.2]],
            jackpot: [[392, 0.12, 0], [523, 0.12, 0.1], [659, 0.12, 0.2], [1046, 0.3, 0.32]],
            lose: [[180, 0.16, 0, {type: 'sawtooth', to: 110, gain: 0.025}]],
            cashout: [[440, 0.08, 0], [660, 0.16, 0.08]],
            crash: [[260, 0.1, 0, {type: 'sawtooth', to: 70, gain: 0.03}]],
            error: [[120, 0.12, 0, {type: 'square', gain: 0.022}]],
        };
        for (const [frequency, duration, delay, options] of patterns[event] || patterns.click) {
            this.tone(frequency, duration, {...options, delay});
        }
    },
};

Alpine.store('lootra', {
    soundEnabled: initialSoundEnabled,
    reducedMotion: reducedMotionQuery.matches,
});

window.lootraAudio = audioContext;

Alpine.data('gameToolbar', () => ({
    fullscreen: false,
    reducedMotion: reducedMotionQuery.matches,
    fullscreenHandler: null,
    motionHandler: null,
    init() {
        this.fullscreenHandler = () => { this.fullscreen = Boolean(document.fullscreenElement); };
        this.motionHandler = event => {
            this.reducedMotion = event.matches;
            Alpine.store('lootra').reducedMotion = event.matches;
        };
        document.addEventListener('fullscreenchange', this.fullscreenHandler);
        reducedMotionQuery.addEventListener?.('change', this.motionHandler);
    },
    destroy() {
        document.removeEventListener('fullscreenchange', this.fullscreenHandler);
        reducedMotionQuery.removeEventListener?.('change', this.motionHandler);
    },
    toggleSound() {
        const enabled = window.lootraAudio.toggle();
        if (enabled) window.lootraAudio.play('click');
    },
    async toggleFullscreen() {
        if (document.fullscreenElement) return this.exitFullscreen();
        const target = this.$root.closest('.game-page');
        if (!target?.requestFullscreen) return;
        try { await target.requestFullscreen(); } catch { /* Fullscreen is optional on some mobile browsers. */ }
    },
    async exitFullscreen() {
        if (!document.fullscreenElement || !document.exitFullscreen) return;
        try { await document.exitFullscreen(); } catch { /* Ignore browser-specific fullscreen errors. */ }
    },
}));

Alpine.data('rickyChallenge', (initial) => ({
    seconds: Number(initial.seconds || 0),
    balance: Number(initial.balance || 0),
    games: Number(initial.games || 0),
    timer: null,
    get clock() {
        const minutes = Math.floor(this.seconds / 60);
        return `${String(minutes).padStart(2, '0')}:${String(this.seconds % 60).padStart(2, '0')}`;
    },
    get percent() { return Math.max(0, Math.min(100, (this.seconds / Number(initial.duration || 900)) * 100)); },
    money(value) { return new Intl.NumberFormat('es-ES', {maximumFractionDigits: 2}).format(Number(value) || 0); },
    async sync() {
        try {
            const response = await fetch(initial.statusUrl, {headers: {Accept: 'application/json'}, cache: 'no-store'});
            if (!response.ok) return;
            const data = await response.json();
            this.seconds = Number(data.seconds_remaining || 0);
            this.balance = Number(data.balance || 0);
            this.games = Number(data.games_played || 0);
            Alpine.store('wallet').saldo = this.balance;
        } catch { /* A later refresh will reconcile temporary failures. */ }
    },
    init() {
        this.timer = window.setInterval(() => {
            if (this.seconds > 0) this.seconds--;
            if (this.seconds === 0 || this.seconds % 15 === 0) this.sync();
        }, 1000);
    },
}));

Alpine.data('promoCarousel', ({ count, interval = 6500 }) => ({
    active: 0,
    timer: null,
    observer: null,
    visible: false,
    interacting: false,
    userPaused: false,
    reducedMotion: false,
    visibilityHandler: null,
    get autoplaying() {
        return !this.userPaused && !this.reducedMotion;
    },
    get status() {
        return `Promoción ${this.active + 1} de ${count}`;
    },
    init() {
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.visibilityHandler = () => (document.hidden ? this.stop() : this.start());
        document.addEventListener('visibilitychange', this.visibilityHandler);
        this.load(0);

        if (!('IntersectionObserver' in window)) {
            this.visible = true;
            this.preloadNext();
            this.start();
            return;
        }

        this.observer = new IntersectionObserver(([entry]) => {
            this.visible = entry.isIntersecting;
            if (this.visible) {
                this.preloadNext();
                this.start();
            } else {
                this.stop();
            }
        }, { threshold: 0.2 });
        this.observer.observe(this.$root);
    },
    destroy() {
        this.stop();
        this.observer?.disconnect();
        document.removeEventListener('visibilitychange', this.visibilityHandler);
    },
    image(index) {
        return this.$root.querySelector(`[data-promo-image="${index}"]`);
    },
    load(index) {
        const image = this.image(index);
        if (image?.dataset.src && !image.hasAttribute('src')) {
            if (image.dataset.srcset) {
                image.srcset = image.dataset.srcset;
                image.sizes = image.dataset.sizes || '100vw';
                image.removeAttribute('data-srcset');
                image.removeAttribute('data-sizes');
            }
            image.src = image.dataset.src;
            image.removeAttribute('data-src');
        }
    },
    preloadNext() {
        if (navigator.connection?.saveData) return;
        const index = (this.active + 1) % count;
        const image = this.image(index);
        if (!image?.dataset.src || image.hasAttribute('src')) return;
        const preload = new Image();
        preload.onload = () => this.load(index);
        if (image.dataset.srcset) {
            preload.srcset = image.dataset.srcset;
            preload.sizes = image.dataset.sizes || '100vw';
        }
        preload.src = image.dataset.src;
    },
    go(index, manual = true) {
        const next = (index + count) % count;
        this.load(next);
        this.active = next;
        this.$nextTick(() => this.preloadNext());
        if (manual) this.start();
    },
    next(manual = true) {
        this.go(this.active + 1, manual);
    },
    previous() {
        this.go(this.active - 1);
    },
    stop() {
        window.clearInterval(this.timer);
        this.timer = null;
    },
    start() {
        this.stop();
        if (!this.visible || this.interacting || this.userPaused || this.reducedMotion || document.hidden) return;
        this.timer = window.setInterval(() => this.next(false), interval);
    },
    pauseInteraction() {
        this.interacting = true;
        this.stop();
    },
    resumeInteraction() {
        this.interacting = false;
        this.start();
    },
    toggleAutoplay() {
        if (this.reducedMotion) return;
        this.userPaused = !this.userPaused;
        this.start();
    },
}));

let lastWalletRefresh = Date.now();

const refreshWallet = async ({ force = false } = {}) => {
    const url = document.body?.dataset.walletUrl;
    if (!url || document.hidden) return;

    const now = Date.now();
    if (!force && now - lastWalletRefresh < 15000) return;
    lastWalletRefresh = now;

    try {
        const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!response.ok) return;

        const saldo = Number((await response.json()).saldo);
        if (Number.isFinite(saldo) && saldo !== Alpine.store('wallet').saldo) {
            Alpine.store('wallet').saldo = saldo;
            window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo } }));
        }
    } catch {
        // A temporary network failure must not interrupt the page.
    }
};

Alpine.start();

if (document.querySelector('[data-admin-charts]')) {
    import('chart.js/auto')
        .then(({ default: Chart }) => {
            window.Chart = Chart;
            window.dispatchEvent(new CustomEvent('lootra:charts-ready', { detail: { Chart } }));
        })
        .catch(() => window.dispatchEvent(new CustomEvent('lootra:charts-error')));
}

if (document.body?.dataset.walletUrl) {
    window.setInterval(refreshWallet, 45000);
    window.addEventListener('focus', refreshWallet);
    document.addEventListener('visibilitychange', refreshWallet);
}

const campaignEvent = (event) => fetch('/rickyedit/event', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
    },
    body: JSON.stringify({event}),
    keepalive: true,
}).catch(() => {});

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-campaign-click]')) campaignEvent('banner_click');
    if (event.target.closest('[data-campaign-share]')) campaignEvent('result_shared');
});
