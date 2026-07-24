<?php

namespace App\Console\Commands;

use App\Models\CaseRewardSetting;
use App\Models\InventarioItem;
use App\Models\Usuario;
use App\Services\CasePrizeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FakeCaseOpeningHistory extends Command
{
    private const SIMULATOR_EMAIL = 'case-simulator@lootra.test';

    protected $signature = 'cases:fake-history
        {--per-case=200 : Aperturas que se crearán para cada caja}
        {--days=30 : Días entre los que se distribuirá el historial}
        {--redeem-rate=68 : Porcentaje de premios que aparecerán canjeados}
        {--fresh : Elimina antes el historial del simulador de cajas}';

    protected $description = 'Genera aperturas demo usando las probabilidades y cupos actuales del administrador';

    public function handle(CasePrizeService $prizes): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->components->error('La generación de historial ficticio solo está permitida en local o testing.');

            return self::FAILURE;
        }

        $perCase = $this->integerOption('per-case', 1, 10_000);
        $days = $this->integerOption('days', 1, 365);
        $redeemRate = $this->integerOption('redeem-rate', 0, 100);
        if ($perCase === null || $days === null || $redeemRate === null) {
            return self::INVALID;
        }

        $prizes->syncDefinitions();
        $simulator = Usuario::firstOrCreate(
            ['email' => self::SIMULATOR_EMAIL],
            [
                'name' => 'Simulador de cajas',
                'password' => Hash::make(Str::random(48)),
                'is_demo' => true,
                'data_origin' => 'simulated',
            ]
        );
        $simulator->forceFill(['is_demo' => true, 'data_origin' => 'simulated'])->saveQuietly();

        if ($this->option('fresh')) {
            $deleted = InventarioItem::where('usuario_id', $simulator->id)->delete();
            $this->components->info("Simulaciones anteriores eliminadas: {$deleted}.");
        }

        $settings = CaseRewardSetting::with('prizeRules')->get()->keyBy('case_key');
        $definitions = config('cajas', []);
        $report = [];
        $rows = [];

        foreach ($definitions as $caseKey => $definition) {
            $setting = $settings->get($caseKey);
            if (! $setting) {
                $this->components->error("No existe configuración administrativa para la caja {$caseKey}.");

                return self::FAILURE;
            }

            $rules = $setting->prizeRules->keyBy('prize_key');
            $goodByDate = [];
            $counts = array_fill_keys(array_column($definition['premios'], 'nombre'), 0);

            for ($opening = 0; $opening < $perCase; $opening++) {
                $dayOffset = $opening % $days;
                $dateKey = now()->subDays($dayOffset)->toDateString();
                $goodByDate[$dateKey] ??= 0;
                $capReached = $setting->good_daily_cap !== null
                    && $goodByDate[$dateKey] >= $setting->good_daily_cap;
                $candidates = [];

                foreach ($definition['premios'] as $prize) {
                    $rule = $rules->get($prizes->prizeKey($prize));
                    $isGood = (bool) ($rule?->is_good ?? in_array($prize['rareza'], ['epico', 'legendario'], true));
                    $weight = max(0, (int) round((float) ($rule?->probability ?? $prize['peso']) * 100));
                    if ((! $capReached || ! $isGood) && $weight > 0) {
                        $candidates[] = compact('prize', 'isGood', 'weight');
                    }
                }

                if ($candidates === []) {
                    $this->components->error("La caja {$caseKey} no tiene premios disponibles con la configuración actual.");

                    return self::FAILURE;
                }

                $winner = $this->weightedPick($candidates);
                if ($winner['isGood']) {
                    $goodByDate[$dateKey]++;
                }
                $counts[$winner['prize']['nombre']]++;

                $openedAt = now()->startOfDay()->subDays($dayOffset)->addSeconds(random_int(0, 86_399));
                $redeemed = random_int(1, 100) <= $redeemRate;
                $redeemedAt = $redeemed
                    ? $openedAt->copy()->addMinutes(random_int(5, 720))->min(now())
                    : null;
                $rows[] = [
                    'usuario_id' => $simulator->id,
                    'caja' => $caseKey,
                    'nombre' => $winner['prize']['nombre'],
                    'imagen' => $winner['prize']['imagen'],
                    'rareza' => $winner['prize']['rareza'],
                    'precio_caja' => $definition['precio'],
                    'valor_canje' => $winner['prize']['valor'],
                    'valor_virtual' => (int) round((float) $winner['prize']['valor'] * 100),
                    'estado' => $redeemed ? 'canjeado' : 'disponible',
                    'canjeado_at' => $redeemedAt,
                    'created_at' => $openedAt,
                    'updated_at' => $redeemedAt ?? $openedAt,
                ];
            }

            foreach ($definition['premios'] as $prize) {
                $rule = $rules->get($prizes->prizeKey($prize));
                $hits = $counts[$prize['nombre']];
                $report[] = [
                    $definition['nombre'],
                    $prize['nombre'],
                    number_format((float) ($rule?->probability ?? $prize['peso']), 2, ',', '.').' %',
                    $hits,
                    number_format(($hits / $perCase) * 100, 2, ',', '.').' %',
                ];
            }
        }

        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('inventario_items')->insert($chunk);
            }
        });

        $this->components->info('Historial ficticio generado con las probabilidades actuales.');
        $this->line('Cuenta: '.self::SIMULATOR_EMAIL.' · Registros: '.count($rows).' · Periodo: '.$days.' días.');
        $this->table(['Caja', 'Premio', 'Configurada', 'Apariciones', 'Observada'], $report);
        $this->newLine();
        $this->line('Revísalo en Admin → Historial de cajas usando el filtro Origen: Simulado.');

        return self::SUCCESS;
    }

    private function integerOption(string $name, int $minimum, int $maximum): ?int
    {
        $value = filter_var($this->option($name), FILTER_VALIDATE_INT);
        if ($value === false || $value < $minimum || $value > $maximum) {
            $this->components->error("--{$name} debe ser un entero entre {$minimum} y {$maximum}.");

            return null;
        }

        return $value;
    }

    /** @param array<int, array{prize: array, isGood: bool, weight: int}> $candidates */
    private function weightedPick(array $candidates): array
    {
        $roll = random_int(1, array_sum(array_column($candidates, 'weight')));
        foreach ($candidates as $candidate) {
            $roll -= $candidate['weight'];
            if ($roll <= 0) {
                return $candidate;
            }
        }

        return $candidates[array_key_last($candidates)];
    }
}
