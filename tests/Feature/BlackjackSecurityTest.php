<?php

namespace Tests\Feature;

use App\Models\BlackjackHand;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BlackjackSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_deal_or_charge_again_while_a_hand_is_active(): void
    {
        $user = $this->player();
        $this->activeHand($user);

        $this->actingAs($user)->postJson(route('blackjack.deal'), ['apuesta' => 25, 'request_token' => Str::uuid()->toString()])
            ->assertConflict()
            ->assertJsonPath('message', 'Ya tienes una mano en juego. La hemos recuperado para que puedas terminarla.')
            ->assertJsonPath('active_hand.estado', 'jugando');

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

    public function test_retried_deal_is_idempotent_and_does_not_charge_twice(): void
    {
        $user = $this->player();
        $payload = ['apuesta' => 10, 'request_token' => Str::uuid()->toString()];

        $first = $this->actingAs($user)->postJson(route('blackjack.deal'), $payload)->assertOk();
        $balance = $user->cartera->fresh()->saldo;
        $second = $this->actingAs($user)->postJson(route('blackjack.deal'), $payload)->assertOk();

        $this->assertSame($first->json('id'), $second->json('id'));
        $this->assertEquals($balance, $user->cartera->fresh()->saldo);
        $this->assertSame(1, BlackjackHand::where('usuario_id', $user->id)->count());
    }

    public function test_classic_variant_uses_six_decks(): void
    {
        $user = $this->player();

        $this->actingAs($user)->postJson(route('blackjack.classic.deal'), [
            'apuesta' => 5,
            'request_token' => Str::uuid()->toString(),
        ])->assertOk();

        $hand = BlackjackHand::where('usuario_id', $user->id)->where('variante', 'classic')->firstOrFail();
        $this->assertCount(308, $hand->baraja);
    }

    public function test_active_hand_status_hides_the_hole_card_and_can_recover_finished_state(): void
    {
        $user = $this->player();
        $hand = $this->activeHand($user);

        $this->actingAs($user)->getJson(route('games.blackjack.vip.status', ['hand_id' => $hand->id]))
            ->assertOk()
            ->assertJsonPath('id', $hand->id)
            ->assertJsonPath('mano_dealer.1.oculta', true)
            ->assertJsonPath('puntos_dealer', 10);

        $this->actingAs($user)->postJson(route('blackjack.stand'))->assertOk();
        $this->actingAs($user)->getJson(route('games.blackjack.vip.status', ['hand_id' => $hand->id]))
            ->assertOk()
            ->assertJsonMissingPath('mano_dealer.1.oculta')
            ->assertJsonPath('estado', fn (string $state) => in_array($state, ['win', 'lose', 'push'], true));
    }

    public function test_blackjack_screen_exposes_recovery_and_visible_error_feedback(): void
    {
        $user = $this->player();

        $this->actingAs($user)->get(route('games.blackjack.vip'))
            ->assertOk()
            ->assertSee('request_token: this.dealToken', false)
            ->assertSee('syncHand({ handId: this.handId })', false)
            ->assertSee('role="alert"', false);
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
