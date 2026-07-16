<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\PokerDealerHand;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
