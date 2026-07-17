<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CaseDemoPrizeBoost;
use App\Models\CasePrizeRule;
use App\Models\CaseRewardSetting;
use App\Models\Usuario;
use App\Services\CasePrizeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminCasePrizeController extends Controller
{
    public function __construct(private readonly CasePrizeService $prizes) {}

    public function index(): View
    {
        $cases = $this->prizes->adminCases();
        $boosts = CaseDemoPrizeBoost::with(['user', 'creator'])->latest()->get();
        $eligibleUsers = Usuario::query()
            ->where(fn ($query) => $query->where('is_demo', true)->orWhereIn('data_origin', ['test', 'simulated']))
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['super_admin', 'admin']))
            ->orderBy('name')->get(['id', 'name', 'email', 'data_origin', 'is_demo']);

        return view('admin.cases.prizes', compact('cases', 'boosts', 'eligibleUsers'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = CaseRewardSetting::with('prizeRules')->get()->keyBy('id');
        $rules = CasePrizeRule::with('setting')->get()->keyBy('id');
        $validator = Validator::make($request->all(), [
            'settings' => ['required', 'array'],
            'settings.*.good_daily_cap' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'rules' => ['required', 'array'],
            'rules.*.probability' => ['required', 'numeric', 'min:0', 'max:100'],
            'rules.*.is_good' => ['required', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request, $settings, $rules) {
            foreach ($settings as $setting) {
                if (! $request->has("settings.{$setting->id}")) {
                    $validator->errors()->add('settings', "Falta la configuración de la caja {$setting->case_key}.");

                    continue;
                }

                $caseRules = $rules->where('case_reward_setting_id', $setting->id);
                $sum = $caseRules->sum(fn (CasePrizeRule $rule) => (float) $request->input("rules.{$rule->id}.probability", -1));
                if (abs($sum - 100) > 0.0001) {
                    $validator->errors()->add('rules', "Los porcentajes de {$setting->case_key} deben sumar 100% (ahora suman ".number_format($sum, 4, ',', '.').'%).');
                }

                $cap = $request->input("settings.{$setting->id}.good_daily_cap");
                $hasAvailableRegularPrize = $caseRules->contains(fn (CasePrizeRule $rule) => ! $request->boolean("rules.{$rule->id}.is_good")
                    && (float) $request->input("rules.{$rule->id}.probability", 0) > 0
                );
                if ($cap !== null && $cap !== '' && ! $hasAvailableRegularPrize) {
                    $validator->errors()->add('rules', "{$setting->case_key} necesita al menos un premio normal con porcentaje mayor que cero para aplicar el cupo diario.");
                }
            }

            foreach ($rules as $rule) {
                if (! $request->has("rules.{$rule->id}")) {
                    $validator->errors()->add('rules', "Falta el premio {$rule->name}.");
                }
            }
        });
        $validated = $validator->validate();

        DB::transaction(function () use ($validated, $settings, $rules) {
            foreach ($settings as $setting) {
                $cap = $validated['settings'][$setting->id]['good_daily_cap'] ?? null;
                $setting->update(['good_daily_cap' => $cap === '' ? null : $cap]);
            }
            foreach ($rules as $rule) {
                $input = $validated['rules'][$rule->id];
                $rule->update([
                    'probability' => round((float) $input['probability'], 4),
                    'is_good' => (bool) $input['is_good'],
                ]);
            }

            ActivityLog::log('probabilidades_cajas_actualizadas', 'CaseRewardSetting', null, [
                'boxes' => $settings->pluck('case_key')->values()->all(),
            ]);
        });

        return back()->with('success', 'Probabilidades y cupos diarios actualizados.');
    }

    public function storeBoost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:usuarios,id'],
            'multiplier' => ['required', 'numeric', 'min:1.01', 'max:5'],
            'reason' => ['required', 'string', 'max:500'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);
        $user = Usuario::findOrFail($validated['user_id']);
        abort_unless($this->prizes->isDemoEligible($user), 422, 'Los multiplicadores individuales solo se permiten para cuentas demo, test o simuladas.');
        abort_if($user->hasAnyRole(['super_admin', 'admin']), 422, 'No se pueden configurar multiplicadores para administradores.');

        $boost = CaseDemoPrizeBoost::updateOrCreate(
            ['user_id' => $user->id],
            [...$validated, 'created_by' => $request->user()->id]
        );
        ActivityLog::log('multiplicador_demo_cajas_guardado', CaseDemoPrizeBoost::class, $boost->id, [
            'target_user_id' => $user->id,
            'multiplier' => (float) $boost->multiplier,
            'expires_at' => $boost->expires_at?->toIso8601String(),
        ]);

        return back()->with('success', 'Multiplicador de pruebas guardado.');
    }

    public function destroyBoost(CaseDemoPrizeBoost $boost): RedirectResponse
    {
        ActivityLog::log('multiplicador_demo_cajas_eliminado', CaseDemoPrizeBoost::class, $boost->id, [
            'target_user_id' => $boost->user_id,
        ]);
        $boost->delete();

        return back()->with('success', 'Multiplicador de pruebas eliminado.');
    }
}
