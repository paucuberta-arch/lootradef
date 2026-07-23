<?php

namespace Tests\Feature;

use App\Models\CaseRewardSetting;
use App\Models\InventarioItem;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakeCaseOpeningHistoryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_generates_isolated_history_using_each_case_and_daily_caps(): void
    {
        CaseRewardSetting::query()->update(['good_daily_cap' => 1]);

        $this->artisan('cases:fake-history', [
            '--per-case' => 20,
            '--days' => 2,
            '--redeem-rate' => 50,
            '--fresh' => true,
        ])->expectsOutputToContain('Historial ficticio generado')
            ->assertSuccessful();

        $simulator = Usuario::where('email', 'case-simulator@lootra.test')->firstOrFail();
        $this->assertTrue($simulator->is_demo);
        $this->assertSame('simulated', $simulator->data_origin);
        $this->assertSame(80, InventarioItem::where('usuario_id', $simulator->id)->count());

        foreach (array_keys(config('cajas')) as $caseKey) {
            $items = InventarioItem::where('usuario_id', $simulator->id)->where('caja', $caseKey)->get();
            $this->assertCount(20, $items);
            $this->assertLessThanOrEqual(2, $items->whereIn('rareza', ['epico', 'legendario'])->count());
        }

        $this->artisan('cases:fake-history', [
            '--per-case' => 5,
            '--days' => 1,
            '--fresh' => true,
        ])->assertSuccessful();

        $this->assertSame(20, InventarioItem::where('usuario_id', $simulator->id)->count());
    }

    public function test_command_rejects_unsafe_generation_limits(): void
    {
        $this->artisan('cases:fake-history', ['--per-case' => 10001])
            ->expectsOutputToContain('--per-case debe ser un entero entre 1 y 10000')
            ->assertExitCode(2);

        $this->assertDatabaseMissing('usuarios', ['email' => 'case-simulator@lootra.test']);
    }

    public function test_command_cannot_generate_or_delete_simulated_history_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $this->artisan('cases:fake-history', ['--fresh' => true])
            ->expectsOutputToContain('solo está permitida en local o testing')
            ->assertFailed();

        $this->assertDatabaseMissing('usuarios', ['email' => 'case-simulator@lootra.test']);
        $this->assertDatabaseCount('inventario_items', 0);
    }
}
