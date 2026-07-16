<?php

namespace Tests\Feature;

use App\Models\BlackjackHand;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlackjackSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_deal_or_charge_again_while_a_hand_is_active(): void
    {
        $user = $this->player();
        $this->activeHand($user);

        $this->actingAs($user)->postJson(route('blackjack.deal'), ['apuesta' => 25])
            ->assertConflict()->assertJsonPath('message', 'Ya tienes una mano en juego. Termínala antes de volver a repartir.');

        $this->assertEquals(100, $user->cartera->fresh()->saldo);
        $this->assertSame(1, BlackjackHand::where('usuario_id', $user->id)->count());
    }

    public function test_finished_hand_cannot_be_paid_twice(): void
    {
        $user = $this->player();
        $this->activeHand($user);

        $this->actingAs($user)->postJson(route('blackjack.stand'))->assertOk();
        $balance = $user->cartera->fresh()->saldo;
        $this->actingAs($user)->postJson(route('blackjack.stand'))->assertConflict();

        $this->assertEquals($balance, $user->cartera->fresh()->saldo);
        $this->assertDatabaseCount('partidas', 1);
    }

    public function test_vip_and_classic_have_separate_routes_and_hands(): void
    {
        $user = $this->player();

        $this->actingAs($user)->get(route('blackjack'))->assertOk()->assertSee('Blackjack VIP');
        $this->actingAs($user)->get(route('blackjack.classic'))->assertOk()->assertSee('Blackjack Classic');
        $this->assertNotSame(route('blackjack'), route('blackjack.classic'));
    }

    private function player(): Usuario
    {
        $user = Usuario::create(['name' => 'Card Tester', 'email' => uniqid().'@example.com', 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }

    private function activeHand(Usuario $user): BlackjackHand
    {
        return BlackjackHand::create([
            'usuario_id' => $user->id,
            'variante' => 'vip',
            'apuesta' => 10,
            'baraja' => [['palo' => '♣', 'valor' => '2'], ['palo' => '♦', 'valor' => '10']],
            'mano_jugador' => [['palo' => '♠', 'valor' => '10'], ['palo' => '♥', 'valor' => '8']],
            'mano_dealer' => [['palo' => '♣', 'valor' => '10'], ['palo' => '♦', 'valor' => '7']],
        ]);
    }
}
