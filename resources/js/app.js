import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

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
