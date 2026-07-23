<?php

namespace App\Http\Controllers;

use App\Services\RegistrationBonusService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('profile.show');
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request, RegistrationBonusService $bonuses): RedirectResponse
    {
        $request->fulfill();
        $bonuses->grant($request->user());

        $route = $request->session()->pull('after_verification_route', 'profile.show');

        return redirect()->route($route)->with('success', 'Correo verificado. Tu bono ya está disponible.');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('profile.show');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Te hemos enviado un nuevo enlace de verificación.');
    }
}
