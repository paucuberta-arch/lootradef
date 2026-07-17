<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AccountMailService;
use App\Services\CampaignAnalytics;
use App\Services\CampaignManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AccountMailService $accountMail,
        private readonly CampaignManager $campaigns,
        private readonly CampaignAnalytics $campaignAnalytics,
    ) {}

    public function mostrarRegistro(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('profile.show');
        }

        $campaignAttributed = $this->campaigns->isAttributed($request);
        if ($campaignAttributed) {
            $this->campaignAnalytics->record(
                'registration_started',
                $this->campaigns->sessionHash($request),
                [], null, null, $this->campaigns->attribution($request), $request
            );
        }

        return view('auth.register', compact('campaignAttributed'));
    }

    public function registrar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:usuarios,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Debes introducir un nombre.',
            'email.required' => 'Debes introducir un correo electrónico.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'password.required' => 'Debes introducir una contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = Usuario::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
        ]);

        $wallet = $usuario->cartera()->create(['saldo' => 0]);
        $wallet->ganar(1000, 'bono_registro');
        $usuario->assignRole('user');

        Auth::login($usuario);

        $request->session()->regenerate();
        $this->accountMail->registered($usuario);

        if ($this->campaigns->isAttributed($request)) {
            $attribution = $this->campaigns->attribution($request);
            $attribution?->update(['user_id' => $usuario->id, 'converted_at' => now()]);
            $this->campaignAnalytics->record(
                'registration_completed', 'user-'.$usuario->id, [], $usuario, null, $attribution, $request
            );

            return redirect()->route('rickyedit.intro')->with('success', 'Tu cuenta se ha creado correctamente.');
        }

        return redirect()
            ->route('profile.show')
            ->with('success', 'Tu cuenta se ha creado correctamente.');
    }

    public function mostrarLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('profile.show');
        }

        return view('auth.login');
    }

    public function iniciarSesion(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        $recordar = $request->boolean('remember');

        if (Auth::attempt($credenciales, $recordar)) {
            $request->session()->regenerate();
            $this->accountMail->login($request->user(), $request->ip());

            return redirect()->intended(route('profile.show'));
        }

        return back()
            ->withErrors([
                'email' => 'El correo o la contraseña no son correctos.',
            ])
            ->onlyInput('email');
    }

    public function cerrarSesion(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('inicio')
            ->with('success', 'Has cerrado sesión correctamente.');
    }
}
