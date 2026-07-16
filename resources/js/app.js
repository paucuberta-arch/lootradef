import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

const body = document.body;
const initialBalance = Number(body?.dataset.walletBalance ?? 0);

Alpine.store('wallet', { saldo: Number.isFinite(initialBalance) ? initialBalance : 0 });

const refreshWallet = async () => {
    const url = document.body?.dataset.walletUrl;
    if (!url || document.hidden) return;

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
    refreshWallet();
    window.setInterval(refreshWallet, 15000);
    window.addEventListener('focus', refreshWallet);
    document.addEventListener('visibilitychange', refreshWallet);
}
