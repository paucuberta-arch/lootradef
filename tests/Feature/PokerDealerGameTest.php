<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\PokerDealerHand;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PokerDealerGameTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_complete_a_persisted_hand_against_the_dealer(): void
    {
        $usuario = $this->player();

        $start = $this->actingAs($usuario)->postJson(route('poker.dealer.start'), ['ante' => 10]);
        $start->assertCreated()
            ->assertJsonPath('phase', 'preflop')
            ->assertJsonPath('balance', 90)
            ->assertJsonCount(2, 'player')
            ->assertJsonPath('dealer.0.hidden', true);

        $this->actingAs($usuario)->postJson(route('poker.dealer.start'), ['ante' => 10])->assertConflict();
        $this->assertSame(90.0, (float) $usuario->cartera()->value('saldo'));

        $this->actingAs($usuario)->postJson(route('poker.dealer.action'), ['accion' => 'jugar'])
            ->assertOk()->assertJsonPath('phase', 'flop')->assertJsonCount(3, 'community')->assertJsonPath('balance', 80);
        $this->actingAs($usuario)->postJson(route('poker.dealer.action'), ['accion' => 'continuar'])
            ->assertOk()->assertJsonPath('phase', 'turn')->assertJsonCount(4, 'community');
        $this->actingAs($usuario)->postJson(route('poker.dealer.action'), ['accion' => 'continuar'])
            ->assertOk()->assertJsonPath('phase', 'river')->assertJsonCount(5, 'community');
        $finish = $this->actingAs($usuario)->postJson(route('poker.dealer.action'), ['accion' => 'continuar']);
        $finish->assertOk()->assertJsonPath('phase', 'finalizada')->assertJsonCount(2, 'dealer');

        $balance = (float) $usuario->cartera()->value('saldo');
        $this->actingAs($usuario)->postJson(route('poker.dealer.action'), ['accion' => 'continuar'])->assertConflict();
        $this->assertSame($balance, (float) $usuario->cartera()->value('saldo'));
        $this->assertDatabaseCount('partidas', 1);
        $this->assertSame('poker_dealer', Partida::first()->juego);
        $this->assertSame('finalizada', PokerDealerHand::first()->fase);
    }

    public function test_player_can_fold_without_being_charged_twice(): void
    {
        $usuario = $this->player();

        $this->actingAs($usuario)->postJson(route('poker.dealer.start'), ['ante' => 10])->assertCreated();
        $this->actingAs($usuario)->postJson(route('poker.dealer.action'), ['accion' => 'retirarse'])
            ->assertOk()->assertJsonPath('phase', 'retirada')->assertJsonPath('balance', 90);

        $this->assertDatabaseHas('partidas', ['juego' => 'poker_dealer', 'apuesta' => 10, 'ganancia' => 0]);
    }

    public function test_player_can_check_bet_and_fold_on_postflop_streets(): void
    {
        $usuario = $this->player();

        $this->actingAs($usuario)->postJson(route('games.poker.dealer.start'), ['ante' => 10])->assertCreated();
        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), [
            'accion' => 'jugar', 'fase' => 'preflop',
        ])->assertOk()->assertJsonPath('phase', 'flop');

        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), [
            'accion' => 'apostar', 'cantidad' => 15, 'fase' => 'flop',
        ])->assertOk()
            ->assertJsonPath('phase', 'turn')
            ->assertJsonPath('wagered', 35)
            ->assertJsonPath('balance', 65);

        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), [
            'accion' => 'pasar', 'fase' => 'turn',
        ])->assertOk()->assertJsonPath('phase', 'river')->assertJsonPath('balance', 65);

        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), [
            'accion' => 'retirarse', 'fase' => 'river',
        ])->assertOk()
            ->assertJsonPath('phase', 'retirada')
            ->assertJsonPath('dealer.0.hidden', true)
            ->assertJsonPath('balance', 65);

        $this->assertDatabaseHas('partidas', ['juego' => 'poker_dealer', 'apuesta' => 35, 'ganancia' => 0]);
    }

    public function test_stale_street_action_cannot_charge_or_advance_twice(): void
    {
        $usuario = $this->player();
        $this->actingAs($usuario)->postJson(route('games.poker.dealer.start'), ['ante' => 10])->assertCreated();
        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), [
            'accion' => 'jugar', 'fase' => 'preflop',
        ])->assertOk();

        $payload = ['accion' => 'apostar', 'cantidad' => 10, 'fase' => 'flop'];
        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), $payload)
            ->assertOk()->assertJsonPath('phase', 'turn');
        $balance = (float) $usuario->cartera()->value('saldo');

        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), $payload)->assertConflict();
        $this->assertSame($balance, (float) $usuario->cartera()->value('saldo'));
        $this->assertSame('turn', PokerDealerHand::first()->fase);
        $this->assertSame(4, count(PokerDealerHand::first()->comunitarias));
    }

    public function test_manipulated_street_bet_cannot_exceed_twice_the_ante(): void
    {
        $usuario = $this->player();
        $this->actingAs($usuario)->postJson(route('games.poker.dealer.start'), ['ante' => 10])->assertCreated();
        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), [
            'accion' => 'jugar', 'fase' => 'preflop',
        ])->assertOk();
        $balance = (float) $usuario->cartera()->value('saldo');

        $this->actingAs($usuario)->postJson(route('games.poker.dealer.action'), [
            'accion' => 'apostar', 'cantidad' => 20.01, 'fase' => 'flop',
        ])->assertUnprocessable();

        $this->assertSame($balance, (float) $usuario->cartera()->value('saldo'));
        $this->assertSame('flop', PokerDealerHand::first()->fase);
    }

    public function test_poker_screens_expose_all_in_token_and_dealer_decisions(): void
    {
        $usuario = $this->player();

        $this->actingAs($usuario)->get(route('games.poker.all-in'))
            ->assertOk()
            ->assertSee('request_token: this.roundToken', false);
        $this->actingAs($usuario)->get(route('games.poker.dealer'))
            ->assertOk()
            ->assertSee("act('pasar')", false)
            ->assertSee("act('apostar')", false)
            ->assertSee("act('retirarse')", false);
    }

    public function test_all_in_can_play_and_retry_without_a_second_charge(): void
    {
        $usuario = $this->player();
        $payload = ['apuesta' => 10, 'request_token' => Str::uuid()->toString()];

        $first = $this->actingAs($usuario)->postJson(route('games.poker.all-in.play'), $payload)
            ->assertOk()
            ->assertJsonPath('apuesta', 10)
            ->assertJsonCount(2, 'player')
            ->assertJsonCount(2, 'dealer')
            ->assertJsonCount(5, 'community');
        $balance = (float) $usuario->cartera()->value('saldo');

        $second = $this->actingAs($usuario)->postJson(route('games.poker.all-in.play'), $payload)->assertOk();
        $this->assertSame($first->json('player'), $second->json('player'));
        $this->assertSame($balance, (float) $usuario->cartera()->value('saldo'));
        $this->assertSame(1, Partida::where('juego', 'texas-holdem')->where('request_token', $payload['request_token'])->count());
    }

    private function player(): Usuario
    {
        $usuario = Usuario::create([
            'name' => 'Poker Tester',
            'email' => 'poker@example.com',
            'password' => bcrypt('password'),
        ]);
        $usuario->cartera()->create(['saldo' => 100]);

        return $usuario;
    }
}
