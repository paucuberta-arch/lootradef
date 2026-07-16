<?php

namespace Tests\Feature;

use App\Mail\AccountActivityMail;
use App\Models\ApuestaDeportiva;
use App\Models\Cartera;
use App\Models\PartidoDeportivo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountActivityMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_a_welcome_email(): void
    {
        Mail::fake();
        Role::create(['name' => 'user', 'guard_name' => 'web', 'label' => 'Jugador', 'color' => 'emerald']);

        $this->post(route('registro.store'), [
            'name' => 'Nueva Jugadora',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('profile.show'));

        Mail::assertSent(AccountActivityMail::class, fn ($mail) => $mail->event === 'registered'
            && $mail->hasTo('new@example.com'));
    }

    public function test_login_and_deposit_send_account_activity_emails(): void
    {
        Mail::fake();
        $user = $this->player();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password123'])
            ->assertRedirect(route('profile.show'));
        $this->actingAs($user)->postJson(route('perfil.deposit'), ['amount' => 25])->assertOk();

        Mail::assertSent(AccountActivityMail::class, 2);
        Mail::assertSent(AccountActivityMail::class, fn ($mail) => $mail->event === 'login');
        Mail::assertSent(AccountActivityMail::class, fn ($mail) => $mail->event === 'deposit'
            && $mail->details['amount'] === 25.0
            && $mail->details['balance'] === 125.0);
    }

    public function test_a_settled_sports_bet_sends_only_one_result_email(): void
    {
        Mail::fake();
        $user = $this->player();
        $match = PartidoDeportivo::create([
            'liga' => 'Liga Test', 'local' => 'Local', 'visitante' => 'Visitante',
            'local_siglas' => 'LOC', 'visitante_siglas' => 'VIS', 'inicia_at' => now()->subSeconds(400),
            'duracion_segundos' => 360, 'cuota_local' => 2, 'cuota_empate' => 3, 'cuota_visitante' => 2.5,
            'simulacion' => [['minute' => 20, 'type' => 'goal', 'team' => 'local', 'text' => 'Gol']],
        ]);
        ApuestaDeportiva::create([
            'usuario_id' => $user->id, 'partido_id' => $match->id, 'seleccion' => 'local',
            'cuota' => 2, 'importe' => 10,
        ]);

        $this->actingAs($user)->getJson(route('apuestas.feed'))->assertOk();
        $this->actingAs($user)->getJson(route('apuestas.feed'))->assertOk();

        Mail::assertSent(AccountActivityMail::class, 1);
        Mail::assertSent(AccountActivityMail::class, fn ($mail) => $mail->event === 'sports_bet_settled'
            && $mail->details['status'] === 'ganada'
            && $mail->details['winnings'] === 20.0);
    }

    private function player(): Usuario
    {
        $user = Usuario::create([
            'name' => 'Mail Tester',
            'email' => 'mail@example.com',
            'password' => bcrypt('password123'),
        ]);
        Cartera::create(['usuario_id' => $user->id, 'saldo' => 100]);

        return $user;
    }
}
