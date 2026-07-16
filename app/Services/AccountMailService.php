<?php

namespace App\Services;

use App\Mail\AccountActivityMail;
use App\Models\ApuestaDeportiva;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AccountMailService
{
    public function registered(Usuario $user): void
    {
        $this->send($user, 'registered', ['balance' => $user->saldo]);
    }

    public function login(Usuario $user, ?string $ip): void
    {
        $this->send($user, 'login', ['ip' => $ip, 'time' => now()]);
    }

    public function deposit(Usuario $user, float $amount): void
    {
        $this->send($user, 'deposit', ['amount' => $amount, 'balance' => $user->cartera->fresh()->saldo]);
    }

    public function sportsBetSettled(ApuestaDeportiva $bet): void
    {
        $bet->loadMissing(['usuario', 'partido']);
        $this->send($bet->usuario, 'sports_bet_settled', [
            'match' => $bet->partido->local.' — '.$bet->partido->visitante,
            'status' => $bet->estado,
            'stake' => $bet->importe,
            'winnings' => $bet->ganancia,
            'score' => $bet->partido->goles_local.' - '.$bet->partido->goles_visitante,
        ]);
    }

    private function send(Usuario $user, string $event, array $details): void
    {
        try {
            Mail::to($user->email)->send(new AccountActivityMail($event, [
                'name' => $user->name,
                ...$details,
            ]));
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar un correo de actividad de cuenta.', [
                'usuario_id' => $user->id,
                'evento' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
