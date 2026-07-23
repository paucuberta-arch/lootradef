<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedForRealPlay
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('features.require_verified_for_play') || $request->user()?->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Debes verificar tu correo antes de realizar esta operación.',
            ], 403);
        }

        return redirect()->route('verification.notice');
    }
}
