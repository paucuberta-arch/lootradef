<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoEconomyEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('features.economy.enabled'), 404, 'La economía demo está desactivada.');

        return $next($request);
    }
}
