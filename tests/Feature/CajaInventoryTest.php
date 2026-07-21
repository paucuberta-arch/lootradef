<?php

namespace Tests\Feature;

use App\Models\CaseRewardSetting;
use App\Models\InventarioItem;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CajaInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_a_case_charges_wallet_and_persists_prize(): void
    {
        $usuario = $this->userWithBalance(100);

        $response = $this->actingAs($usuario)->postJson(route('cajas.open', 'starter'));

        $response->assertOk()->assertJsonStructure([
            'item' => ['id', 'nombre', 'imagen', 'rareza', 'valor_canje', 'estado', 'created_at'],
            'saldo',
            'reel',
            'winner_index',
        ]);
        $response->assertJsonPath('item.estado', 'disponible');
        $winnerIndex = $response->json('winner_index');
        $this->assertSame($response->json('item.nombre'), $response->json("reel.{$winnerIndex}.nombre"));
        $this->assertGreaterThan(1, collect($response->json('reel'))->pluck('nombre')->unique()->count());
        $this->assertSame(97.01, round($usuario->cartera()->value('saldo'), 2));
        $this->assertDatabaseHas('inventario_items', [
            'id' => $response->json('item.id'),
            'usuario_id' => $usuario->id,
            'caja' => 'starter',
            'estado' => 'disponible',
        ]);
    }

    public function test_case_cannot_be_opened_without_enough_balance(): void
    {
        $usuario = $this->userWithBalance(1);

        $this->actingAs($usuario)
            ->postJson(route('cajas.open', 'starter'))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'No tienes saldo suficiente para abrir esta caja.');

        $this->assertDatabaseCount('inventario_items', 0);
        $this->assertSame(1.0, (float) $usuario->cartera()->value('saldo'));
    }

    public function test_opening_uses_the_prize_configured_by_the_server_instead_of_a_fixed_item(): void
    {
        $usuario = $this->userWithBalance(100);
        $setting = CaseRewardSetting::where('case_key', 'starter')->firstOrFail();
        $rules = $setting->prizeRules()->get();
        $expected = $rules->get(1);

        $setting->prizeRules()->update(['probability' => 0]);
        $expected->update(['probability' => 100]);

        $response = $this->actingAs($usuario)->postJson(route('cajas.open', 'starter'));

        $response->assertOk()->assertJsonPath('item.nombre', $expected->name);
        $this->assertDatabaseHas('inventario_items', [
            'id' => $response->json('item.id'),
            'nombre' => $expected->name,
        ]);
    }

    public function test_prize_can_only_be_redeemed_once_by_its_owner(): void
    {
        $owner = $this->userWithBalance(10);
        $other = $this->userWithBalance(10, 'other@example.com');
        $item = InventarioItem::create([
            'usuario_id' => $owner->id,
            'caja' => 'starter',
            'nombre' => 'Premio de prueba',
            'imagen' => 'https://example.com/prize.jpg',
            'rareza' => 'raro',
            'precio_caja' => 2.99,
            'valor_canje' => 12.50,
        ]);

        $this->actingAs($other)->postJson(route('inventario.redeem', $item))->assertNotFound();

        $this->actingAs($owner)
            ->postJson(route('inventario.redeem', $item))
            ->assertOk()
            ->assertJson(['saldo' => 22.5, 'valor' => 12.5]);

        $this->actingAs($owner)->postJson(route('inventario.redeem', $item))->assertConflict();
        $this->assertSame(22.5, (float) $owner->cartera()->value('saldo'));
        $this->assertDatabaseHas('inventario_items', ['id' => $item->id, 'estado' => 'canjeado']);
    }

    private function userWithBalance(float $balance, string $email = 'owner@example.com'): Usuario
    {
        $usuario = Usuario::create([
            'name' => 'Case User',
            'email' => $email,
            'password' => bcrypt('password'),
        ]);
        $usuario->cartera()->create(['saldo' => $balance]);

        return $usuario;
    }
}
