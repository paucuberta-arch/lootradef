import {test, expect} from '@playwright/test';

const credentials = {
    email: process.env.E2E_EMAIL || 'e2e@example.test',
    password: process.env.E2E_PASSWORD || 'LootraE2E#2026',
};

const routes = [
    ['home', '/', false],
    ['catalog', '/games', false],
    ['slots', '/games/slots/gates-of-olympus', true],
    ['roulette', '/games/roulette/european', true],
    ['blackjack', '/games/blackjack/classic', true],
    ['crash', '/games/crash', true],
    ['plinko', '/games/originals/quantum-plinko', true],
    ['poker', '/games/poker/all-in', true],
];

async function login(page) {
    await page.goto('/iniciar-sesion', {waitUntil: 'domcontentloaded'});
    await page.getByLabel('Correo electrónico').fill(credentials.email);
    await page.locator('input[name="password"]').fill(credentials.password);
    await page.getByRole('button', {name: /^Entrar$/}).click();
    await page.waitForURL(/\/profile|\/perfil/, {waitUntil: 'domcontentloaded'});
}

for (const [name, path, authenticated] of routes) {
    test(`mobile ${name} has no horizontal overflow and keeps controls reachable`, async ({page}, testInfo) => {
        if (authenticated) await login(page);
        await page.goto(path, {waitUntil: 'domcontentloaded'});
        await expect(page.locator('body')).toBeVisible();
        if (authenticated) {
            await expect(page.locator('.game-toolbar')).toBeVisible();
            await expect(page.getByRole('button', {name: /Sonido|Silencio/})).toBeVisible();
        }
        const dimensions = await page.evaluate(() => ({
            viewport: window.innerWidth,
            documentWidth: document.documentElement.scrollWidth,
            bodyWidth: document.body.scrollWidth,
        }));
        expect(dimensions.documentWidth, JSON.stringify(dimensions)).toBeLessThanOrEqual(dimensions.viewport + 2);
        expect(dimensions.bodyWidth, JSON.stringify(dimensions)).toBeLessThanOrEqual(dimensions.viewport + 2);
        await page.screenshot({path: testInfo.outputPath(`${name}.png`), fullPage: true});
    });
}
