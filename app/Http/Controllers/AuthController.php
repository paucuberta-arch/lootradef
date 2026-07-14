<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function mostrarRegistro(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('perfil');
        }

return view('auth.register');
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

    $usuario->cartera()->create(['saldo' => 1000.00]);

    Auth::login($usuario);

    $request->session()->regenerate();

    return redirect()
        ->route('perfil')
        ->with('success', 'Tu cuenta se ha creado correctamente.');
}

public function mostrarLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('perfil');
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

        return redirect()->intended(route('perfil'));
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