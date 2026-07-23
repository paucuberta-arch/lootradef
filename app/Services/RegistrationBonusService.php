<?php

namespace App\Services;

use App\Models\Cartera;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class RegistrationBonusService
{
    public function __construct(private readonly WalletService $wallets) {}

    public function grant(Usuario $user): void
    {
        abort_unless($user->hasVerifiedEmail(), 403, 'Debes verificar tu correo para recibir el bono.');

        DB::transaction(function () use ($user): void {
            $wallet = Cartera::where('usuario_id', $user->id)->lockForUpdate()->firstOrFail();
            $this->wallets->credit(
                $wallet,
                (float) config('features.registration_bonus', 1000),
                'bono_registro',
                ['origen' => 'verificacion_email'],
                null,
                'registration-bonus:'.$user->id,
            );
        });
    }
}
