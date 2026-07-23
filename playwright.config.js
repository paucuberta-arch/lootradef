import { defineConfig, devices } from '@playwright/test';

const appUrl = process.env.BASE_URL || 'http://127.0.0.1:8000';
const databaseEnv = {
    APP_ENV: 'testing',
    APP_URL: appUrl,
    TRUSTED_HOSTS: '127.0.0.1,localhost',
    DB_CONNECTION: 'mysql',
    DB_HOST: '127.0.0.1',
    DB_PORT: process.env.LOOTRA_TEST_DB_PORT || '3307',
    DB_DATABASE: process.env.LOOTRA_TEST_DB_DATABASE || 'lootra_testing',
    DB_USERNAME: process.env.LOOTRA_TEST_DB_USERNAME || 'lootra_test',
    DB_PASSWORD: process.env.LOOTRA_TEST_DB_PASSWORD || 'lootra_test_password',
    REQUIRE_VERIFIED_FOR_PLAY: 'false',
};

export default defineConfig({
    testDir: './tests/e2e',
    outputDir: 'test-results',
    timeout: 30_000,
    expect: {timeout: 5_000},
    // The game smoke tests share one isolated wallet, so serial execution avoids
    // races between balance updates and active rounds.
    fullyParallel: false,
    forbidOnly: Boolean(process.env.CI),
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : 1,
    reporter: [['list'], ['json', {outputFile: 'test-results/results.json'}]],
    use: {
        baseURL: appUrl,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        // Video capture changes compositor timing and would invalidate FPS samples.
        video: 'off',
        launchOptions: {args: [
            '--enable-precise-memory-info',
            '--disable-background-timer-throttling',
            '--disable-renderer-backgrounding',
            '--disable-backgrounding-occluded-windows',
            '--disable-features=CalculateNativeWinOcclusion',
            '--use-gl=swiftshader',
        ]},
    },
    projects: [
        {
            name: 'chromium-desktop',
            testMatch: '**/game-smoke.spec.js',
            use: {...devices['Desktop Chrome'], viewport: {width: 1440, height: 900}},
        },
        {
            name: 'iphone-small',
            testMatch: '**/mobile-smoke.spec.js',
            use: {...devices['iPhone SE']},
        },
        {
            name: 'android-small',
            testMatch: '**/mobile-smoke.spec.js',
            use: {...devices['Pixel 5']},
        },
        {
            name: 'tablet-portrait',
            testMatch: '**/mobile-smoke.spec.js',
            use: {...devices['iPad Mini']},
        },
        {
            name: 'tablet-landscape',
            testMatch: '**/mobile-smoke.spec.js',
            use: {...devices['iPad Mini landscape']},
        },
    ],
    webServer: {
        command: `APP_ENV=${databaseEnv.APP_ENV} APP_URL=${databaseEnv.APP_URL} TRUSTED_HOSTS=${databaseEnv.TRUSTED_HOSTS} DB_CONNECTION=${databaseEnv.DB_CONNECTION} DB_HOST=${databaseEnv.DB_HOST} DB_PORT=${databaseEnv.DB_PORT} DB_DATABASE=${databaseEnv.DB_DATABASE} DB_USERNAME=${databaseEnv.DB_USERNAME} DB_PASSWORD=${databaseEnv.DB_PASSWORD} REQUIRE_VERIFIED_FOR_PLAY=${databaseEnv.REQUIRE_VERIFIED_FOR_PLAY} php artisan serve --host=127.0.0.1 --port=8000`,
        url: appUrl,
        reuseExistingServer: !process.env.CI,
        timeout: 120_000,
    },
});
