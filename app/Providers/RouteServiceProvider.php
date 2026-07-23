<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/perfil';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by('login:'.Str::transliterate($email).'|'.$request->ip()),
                Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('registration', fn (Request $request) => [
            Limit::perHour(5)->by('registration:'.$request->ip()),
            Limit::perDay(20)->by('registration-day:'.$request->ip()),
        ]);

        RateLimiter::for('password.email', function (Request $request) {
            return [
                Limit::perMinutes(10, 3)->by('password-email:'.$request->ip()),
                Limit::perMinutes(10, 3)->by('password-email-address:'.sha1(Str::lower((string) $request->input('email')))),
            ];
        });

        RateLimiter::for('password.reset', fn (Request $request) => Limit::perMinutes(10, 5)->by('password-reset:'.$request->ip()));

        RateLimiter::for('demo-deposit', fn (Request $request) => [
            Limit::perMinute(5)->by('demo-deposit-user:'.$request->user()->getAuthIdentifier()),
            Limit::perHour(20)->by('demo-deposit-ip:'.$request->ip()),
        ]);

        RateLimiter::for('authenticated-actions', fn (Request $request) => Limit::perMinute(180)->by('authenticated-actions:'.$request->user()->getAuthIdentifier())
        );

        RateLimiter::for('feedback', fn (Request $request) => [
            Limit::perMinutes(10, 5)->by('feedback:'.($request->user()?->getAuthIdentifier() ?? $request->ip())),
            Limit::perHour(20)->by('feedback-ip:'.$request->ip()),
        ]);

        RateLimiter::for('community-write', fn (Request $request) => Limit::perMinutes(10, 10)->by('community-write:'.$request->user()->getAuthIdentifier())
        );

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
