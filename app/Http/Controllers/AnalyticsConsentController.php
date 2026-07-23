<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnalyticsConsentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'choice' => ['required', 'in:granted,denied'],
        ]);

        return redirect()->route('inicio')->withCookie(cookie(
            'lootra_analytics_consent',
            $validated['choice'],
            60 * 24 * 180,
            '/',
            null,
            $request->isSecure() || app()->environment('production'),
            true,
            false,
            'lax',
        ));
    }
}
