<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminMfaController extends Controller
{
    public function __construct(private readonly TotpService $totp) {}

    public function setup(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->hasAnyRole(['super_admin', 'admin', 'moderator']), 403);

        if ($user->admin_mfa_enabled_at) {
            return redirect()->route('admin.dashboard');
        }

        $secret = (string) $request->session()->get('admin_mfa_pending_secret');
        if ($secret === '') {
            $secret = $this->totp->generateSecret();
            $request->session()->put('admin_mfa_pending_secret', $secret);
        }

        return view('admin.mfa.setup', [
            'provisioningUri' => $this->totp->provisioningUri($secret, $user->email, config('app.name')),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'current_password'],
        ]);
        $user = $request->user();
        $secret = (string) $request->session()->get('admin_mfa_pending_secret');

        abort_unless($secret !== '', 422, 'La configuración de MFA ha caducado.');
        $counter = $this->totp->matchingCounter($secret, (string) $request->string('code'));
        if ($counter === null) {
            return back()->withErrors(['code' => 'El código MFA no es válido.']);
        }

        $user->forceFill([
            'admin_mfa_secret' => $secret,
            'admin_mfa_enabled_at' => now(),
            'admin_mfa_last_counter' => $counter,
        ])->save();
        $request->session()->forget('admin_mfa_pending_secret');
        $request->session()->put('admin_mfa_verified_at', now()->timestamp);
        $request->session()->put('admin_mfa_verified_user_id', $user->id);

        return redirect()->route('admin.dashboard')->with('success', 'MFA activado para esta cuenta administrativa.');
    }

    public function challenge(): View
    {
        return view('admin.mfa.challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();
        $counter = DB::transaction(function () use ($user, $request): ?int {
            $lockedUser = Usuario::query()->lockForUpdate()->find($user?->id);
            if (! $lockedUser?->admin_mfa_secret) {
                return null;
            }

            $counter = $this->totp->matchingCounter(
                $lockedUser->admin_mfa_secret,
                (string) $request->string('code')
            );
            if ($counter === null || ($lockedUser->admin_mfa_last_counter !== null && $counter <= $lockedUser->admin_mfa_last_counter)) {
                return null;
            }

            $lockedUser->forceFill(['admin_mfa_last_counter' => $counter])->save();

            return $counter;
        });

        if ($counter === null) {
            return back()->withErrors(['code' => 'El código MFA no es válido.']);
        }

        $request->session()->put('admin_mfa_verified_at', now()->timestamp);
        $request->session()->put('admin_mfa_verified_user_id', $user->id);

        return redirect()->intended(route('admin.dashboard'));
    }
}
