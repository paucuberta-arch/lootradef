<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! config('security.admin_mfa_required') || ! $user || ! $user->hasAnyRole(['super_admin', 'admin', 'moderator'])) {
            return $next($request);
        }

        if ($request->routeIs('admin.mfa.*')) {
            return $next($request);
        }

        if (! $user->admin_mfa_enabled_at) {
            return redirect()->route('admin.mfa.setup');
        }

        $verifiedAt = (int) $request->session()->get('admin_mfa_verified_at', 0);
        $verifiedUser = (int) $request->session()->get('admin_mfa_verified_user_id', 0);
        if ($verifiedUser !== (int) $user->id || $verifiedAt < now()->subHours(12)->timestamp) {
            return redirect()->route('admin.mfa.challenge');
        }

        return $next($request);
    }
}
