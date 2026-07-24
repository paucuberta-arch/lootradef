import {test, expect} from '@playwright/test';

const credentials = {
    email: process.env.E2E_EMAIL,
    password: process.env.E2E_PASSWORD,
};

const games = [
    {name: 'Gates of Olympus', slug: 'slots-gates', path: '/games/slots/gates-of-olympus', action: /^Girar$/},
    {name: 'Sweet Bonanza', slug: 'slots-sweet', path: '/games/slots/sweet-bonanza', action: /^Girar$/},
    {name: 'Book of Dead', slug: 'slots-book', path: '/games/slots/book-of-dead', action: /^Girar$/},
    {name: 'Starburst', slug: 'slots-starburst', path: '/games/slots/starburst', action: /^Girar$/},
    {name: 'Big Bass Bonanza', slug: 'slots-bass', path: '/games/slots/big-bass-bonanza', action: /^Girar$/},
    {name: 'European Roulette', slug: 'roulette-european', path: '/games/roulette/european', action: /^Girar ruleta$/},
    {name: 'Lightning Roulette', slug: 'roulette-lightning', path: '/games/roulette/lightning', action: /^Girar ruleta$/},
    {name: 'Blackjack VIP', slug: 'blackjack-vip', path: '/games/blackjack/vip', action: /^Repartir$/},
    {name: 'Blackjack Classic', slug: 'blackjack-classic', path: '/games/blackjack/classic', action: /^Repartir$/},
    {name: 'Crash Rocket', slug: 'crash', path: '/games/crash', action: /^Apostar$/},
    {name: 'Crazy Time Neon', slug: 'crazy-time', path: '/games/originals/crazy-time', action: /^Jugar$/},
    {name: 'Poker All-In', slug: 'texas-holdem', path: '/games/poker/all-in', action: /Ir All-In/},
    {name: "Texas Hold'em contra el Dealer", slug: 'dealer-poker', path: '/games/poker/dealer', action: /Repartir nueva mano/},
    {name: 'Neon Mines', slug: 'neon-mines', path: '/games/originals/neon-mines', action: /^Jugar$/},
    {name: 'Dice Arena', slug: 'dice-arena', path: '/games/originals/dice-arena', action: /^Jugar$/},
    {name: 'Higher or Lower', slug: 'high-low', path: '/games/originals/high-low', action: /^Jugar$/},
    {name: 'Quantum Plinko', slug: 'quantum-plinko', path: '/games/originals/quantum-plinko', action: /Liberar esfera/},
    {name: 'Cosmic Keno', slug: 'cosmic-keno', path: '/games/originals/cosmic-keno', action: /^Jugar$/},
    {name: 'Coin Duel', slug: 'coin-duel', path: '/games/originals/coin-duel', action: /^Jugar$/},
    {name: 'Baccarat Royale', slug: 'baccarat-royale', path: '/games/originals/baccarat-royale', action: /^Jugar$/},
    {name: 'Nebula Picks', slug: 'nebula-picks', path: '/games/originals/nebula-picks', action: /^Jugar$/},
];

async function login(page) {
    if (!credentials.email || !credentials.password) {
        throw new Error('Define E2E_EMAIL y E2E_PASSWORD para ejecutar las pruebas E2E.');
    }
    await page.goto('/iniciar-sesion', {waitUntil: 'domcontentloaded'});
    await page.getByLabel('Correo electrónico').fill(credentials.email);
    await page.locator('input[name="password"]').fill(credentials.password);
    await page.getByRole('button', {name: /^Entrar$/}).click();
    await page.waitForURL(/\/profile|\/perfil/, {waitUntil: 'domcontentloaded'});
}

async function collectFrameMetrics(page, duration = 1200) {
    return page.evaluate((sampleDuration) => new Promise(resolve => {
        let frames = 0;
        let slowFrames = 0;
        let previous = performance.now();
        const start = previous;
        const tick = now => {
            frames++;
            if (now - previous > 50) slowFrames++;
            previous = now;
            if (now - start >= sampleDuration) {
                const elapsed = Math.max(1, now - start);
                resolve({
                    fps: Math.round((frames * 1000 / elapsed) * 100) / 100,
                    frames,
                    slowFrames,
                    durationMs: Math.round(elapsed),
                    visibilityState: document.visibilityState,
                });
                return;
            }
            requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }), duration);
}

async function collectBrowserMetrics(page) {
    return page.evaluate(() => ({
        heap: performance.memory ? {
            used: performance.memory.usedJSHeapSize,
            total: performance.memory.totalJSHeapSize,
            limit: performance.memory.jsHeapSizeLimit,
        } : null,
        resources: performance.getEntriesByType('resource').length,
        longTasks: window.__lootraE2E?.longTasks || [],
        canvas: document.querySelectorAll('canvas').length,
        activeAnimations: document.getAnimations?.().map(animation => ({
            name: animation.animationName,
            target: animation.effect?.target?.className || animation.effect?.target?.tagName || null,
        })) || [],
    }));
}

async function clickGameAction(page, game) {
    if (game.slug === 'cosmic-keno') {
        for (let number = 1; number <= 5; number++) {
            await page.getByRole('button', {name: String(number), exact: true}).first().click();
        }
    }

    await page.getByRole('button', {name: game.action}).first().click();

    if (game.slug.startsWith('slots-')) {
        await expect(page.getByText('Ronda completada').or(page.getByText('Sin suerte esta vez')).first()).toBeVisible({timeout: 8_000});
    } else if (game.slug.startsWith('roulette-')) {
        await expect(page.getByText('Número ganador:')).toBeVisible({timeout: 8_000});
    } else if (game.slug.startsWith('blackjack-')) {
        await expect(page.getByText(/Tu turno|¡BLACKJACK!|¡Ganaste!|Dealer gana|Empate/).first()).toBeVisible({timeout: 8_000});
    } else if (game.slug === 'crash') {
        await page.waitForTimeout(1_500);
        const cashout = page.getByRole('button', {name: /Cobrar/});
        if (await cashout.isVisible().catch(() => false)) await cashout.click();
    } else if (game.slug === 'quantum-plinko') {
        await expect(page.getByText('Colapso cuántico completado')).toBeVisible({timeout: 8_000});
    } else if (game.slug === 'dealer-poker') {
        await expect(page.getByText(/Decisión actual|Nueva partida/)).toBeVisible({timeout: 8_000});
    } else {
        await expect(page.getByText(/Premio bruto|Esta vez no hubo premio/).first()).toBeVisible({timeout: 8_000});
    }
}

function attachDiagnostics(page) {
    const diagnostics = {consoleErrors: [], pageErrors: [], failedRequests: [], abortedRequests: []};
    page.on('console', message => {
        if (message.type() === 'error') diagnostics.consoleErrors.push(message.text());
    });
    page.on('pageerror', error => diagnostics.pageErrors.push(String(error)));
    page.on('requestfailed', request => {
        if (request.url().includes('/favicon')) return;
        const failure = request.failure()?.errorText || '';
        const entry = `${request.method()} ${request.url()} ${failure}`;
        if (failure === 'net::ERR_ABORTED') diagnostics.abortedRequests.push(entry);
        else diagnostics.failedRequests.push(entry);
    });
    page.__lootraDiagnostics = diagnostics;
    return diagnostics;
}

test.beforeEach(async ({page}) => {
    await page.context().addCookies([{
        name: 'lootra_analytics_consent',
        value: 'denied',
        domain: '127.0.0.1',
        path: '/',
    }]);
    await page.addInitScript(() => {
        window.__lootraE2E = {longTasks: []};
        if ('PerformanceObserver' in window) {
            try {
                new PerformanceObserver(list => {
                    window.__lootraE2E.longTasks.push(...list.getEntries().map(entry => ({duration: entry.duration, startTime: entry.startTime})));
                }).observe({type: 'longtask', buffered: true});
            } catch { /* Browser does not expose longtask entries. */ }
        }
    });
    attachDiagnostics(page);
    await login(page);
});

test('home and catalog render in the production asset pipeline', async ({page}, testInfo) => {
    await page.goto('/', {waitUntil: 'domcontentloaded'});
    await expect(page.locator('body')).toBeVisible();
    await page.screenshot({path: testInfo.outputPath('home.png')});
    await page.goto('/games', {waitUntil: 'domcontentloaded', timeout: 15_000});
    await expect(page.getByRole('heading', {name: 'Todos los juegos'})).toBeVisible();
    await page.screenshot({path: testInfo.outputPath('catalog.png')});
});

for (const game of games) {
    test(`${game.name} loads, plays one real round and exposes metrics`, async ({page}, testInfo) => {
        await page.goto(game.path, {waitUntil: 'domcontentloaded'});
        await expect(page.locator('.game-toolbar')).toBeVisible();
        await page.screenshot({path: testInfo.outputPath(`${game.slug}-initial.png`), fullPage: true});

        const before = await collectBrowserMetrics(page);
        await clickGameAction(page, game);
        await page.bringToFront();
        const outcomeFrame = await collectFrameMetrics(page);
        await page.waitForTimeout(1_600);
        const settledFrame = await collectFrameMetrics(page);
        const after = await collectBrowserMetrics(page);
        const diagnostics = page.__lootraDiagnostics;
        const report = {game: game.name, path: game.path, frame: {outcome: outcomeFrame, settled: settledFrame}, before, after, diagnostics};
        await testInfo.attach('performance.json', {body: Buffer.from(JSON.stringify(report, null, 2)), contentType: 'application/json'});
        await page.screenshot({path: testInfo.outputPath(`${game.slug}-result.png`), fullPage: true});

        expect(diagnostics.pageErrors, JSON.stringify(diagnostics)).toEqual([]);
        expect(diagnostics.consoleErrors, JSON.stringify(diagnostics)).toEqual([]);
        expect(diagnostics.failedRequests, JSON.stringify(diagnostics)).toEqual([]);
        expect(settledFrame.fps, JSON.stringify(report)).toBeGreaterThan(30);
        if (before.heap && after.heap) expect(after.heap.used, JSON.stringify(report)).toBeLessThan(before.heap.used * 4 + 20_000_000);
    });
}
