<?php

namespace App\Http\Middleware;

use App\Services\CampaignChallengeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleCampaignGameplay
{
    public function __construct(private readonly CampaignChallengeService $challenges) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $request->user() && $this->isGameplayRoute($request)) {
            $challenge = $this->challenges->activeForUser($request->user());
            if ($challenge) {
                $key = 'campaign-gameplay:'.$challenge->id;
                abort_if(RateLimiter::tooManyAttempts($key, 120), 429, 'Demasiadas acciones. Espera antes de continuar.');
                RateLimiter::hit($key, 60);
            }
        }

        return $next($request);
    }

    private function isGameplayRoute(Request $request): bool
    {
        // Crash status is a read-only heartbeat. Counting it as a gameplay action
        // adds cache/database work every second and can delay a real cashout.
        if ($request->routeIs('games.crash.status', 'crash.status', 'crash.crash')) {
            return false;
        }

        return $request->routeIs(
            'games.*.play', 'games.originals.play', 'games.poker.*', 'games.crash.*',
            'slots.play', 'ruleta*.play', 'blackjack.*', 'crash.*', 'poker.*', 'arcade.play'
        );
    }
}
