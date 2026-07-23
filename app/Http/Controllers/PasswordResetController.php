<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Rules\SafeEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $request->validate([
            'email' => ['required', new SafeEmail, 'email:rfc', 'max:255'],
        ]);

        // Keep the response generic to avoid user enumeration.
        Password::sendResetLink(['email' => $request->string('email')->toString()]);

        return back()->with(
            'status',
            'Si existe una cuenta con ese correo, recibirás instrucciones para restablecer la contraseña.'
        );
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', new SafeEmail, 'email:rfc', 'max:255'],
            'password' => [
                'required',
                'string',
                PasswordRule::min(10)->letters()->numbers(),
                'confirmed',
            ],
            'password_confirmation' => ['required', 'string'],
        ]);

        $status = Password::reset(
            [
                'email' => Str::lower(trim($credentials['email'])),
                'password' => $credentials['password'],
                'password_confirmation' => $credentials['password_confirmation'],
                'token' => $credentials['token'],
            ],
            function (Usuario $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Tu contraseña se ha restablecido. Ya puedes iniciar sesión.');
        }

        return back()->withErrors([
            'email' => 'El enlace de recuperación no es válido o ha caducado.',
        ]);
    }
}
