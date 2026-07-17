@extends('layouts.admin')
@section('admin-title', 'Premios de cajas')

@section('admin-content')
<div class="mx-auto max-w-7xl space-y-8">
    <header>
        <p class="text-xs font-black uppercase tracking-[.2em] text-fuchsia-300">Control de probabilidades</p>
        <h2 class="mt-2 text-2xl font-black sm:text-3xl">Premios y cupos diarios</h2>
        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-400">Los porcentajes de cada caja deben sumar exactamente 100%. Cuando se alcanza el cupo diario, los premios marcados como buenos se retiran temporalmente del sorteo y el resto se recalcula proporcionalmente.</p>
    </header>

    <div class="rounded-2xl border border-amber-300/20 bg-amber-300/5 p-5 text-sm leading-relaxed text-amber-100">
        <strong class="block text-amber-200">Integridad de sorteos reales</strong>
        Los multiplicadores individuales están técnicamente restringidos a cuentas demo, test o simuladas. No pueden aplicarse a usuarios reales ni administradores y todas las modificaciones quedan registradas en Activity Log.
    </div>

    <form method="POST" action="{{ route('admin.case-prizes.update') }}" class="space-y-6">
        @csrf
        @method('PUT')
        @foreach($cases as $caseKey => $case)
            @php
                $setting = $case['setting'];
                $probabilityState = $case['prizes']->mapWithKeys(fn ($prize) => [
                    $prize['rule']->id => (float) old("rules.{$prize['rule']->id}.probability", $prize['rule']->probability),
                ]);
            @endphp
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[.025]" x-data="{ probabilities: @js($probabilityState), get total() { return Object.values(this.probabilities).reduce((sum, value) => sum + (Number(value) || 0), 0) } }">
                <div class="flex flex-col gap-4 border-b border-white/10 p-5 sm:flex-row sm:items-end sm:justify-between">
                    <div><p class="text-xs font-black uppercase tracking-wider text-fuchsia-300">{{ $caseKey }}</p><h3 class="mt-1 text-xl font-black">{{ $case['definition']['nombre'] }}</h3><p class="mt-1 text-xs text-slate-500">{{ $case['good_awarded_today'] }} premios buenos entregados hoy</p></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-xs font-bold text-slate-400">Máx. buenos por día
                            <input name="settings[{{ $setting->id }}][good_daily_cap]" value="{{ old("settings.{$setting->id}.good_daily_cap", $setting->good_daily_cap) }}" type="number" min="0" max="100000" placeholder="Sin límite" class="mt-1 block min-h-11 w-full rounded-xl border border-white/10 bg-black/25 px-3 text-white outline-none focus:border-cyan-300">
                        </label>
                        <div class="rounded-xl border px-4 py-2" :class="Math.abs(total-100)<.0001?'border-emerald-400/25 bg-emerald-400/10':'border-red-400/30 bg-red-400/10'"><span class="block text-[10px] uppercase tracking-wider text-slate-400">Total</span><b x-text="total.toFixed(4)+'%'" :class="Math.abs(total-100)<.0001?'text-emerald-300':'text-red-300'"></b></div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="bg-black/20 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Premio</th><th class="px-4 py-3">Rareza</th><th class="px-4 py-3">Valor</th><th class="px-4 py-3">Porcentaje</th><th class="px-4 py-3 text-center">Premio bueno</th></tr></thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($case['prizes'] as $prize)
                                @php($rule = $prize['rule'])
                                <tr><td class="px-5 py-3 font-semibold text-white">{{ $prize['definition']['nombre'] }}</td><td class="px-4 py-3 capitalize text-slate-400">{{ str_replace('_', ' ', $prize['definition']['rareza']) }}</td><td class="px-4 py-3 text-slate-300">{{ number_format($prize['definition']['valor'], 2, ',', '.') }}</td><td class="px-4 py-3"><div class="flex w-36 items-center rounded-xl border border-white/10 bg-black/25 px-3"><input x-model.number="probabilities[{{ $rule->id }}]" name="rules[{{ $rule->id }}][probability]" value="{{ old("rules.{$rule->id}.probability", $rule->probability) }}" type="number" min="0" max="100" step="0.0001" required class="min-h-10 w-full bg-transparent outline-none"><span class="text-slate-500">%</span></div></td><td class="px-4 py-3 text-center"><input type="hidden" name="rules[{{ $rule->id }}][is_good]" value="0"><input name="rules[{{ $rule->id }}][is_good]" value="1" type="checkbox" @checked(old("rules.{$rule->id}.is_good", $rule->is_good)) class="h-5 w-5 rounded border-white/20 bg-black/30 text-fuchsia-400 focus:ring-fuchsia-400"></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
        <div class="sticky bottom-4 z-20 flex justify-end"><button class="min-h-12 rounded-xl bg-gradient-to-r from-fuchsia-400 to-cyan-300 px-6 font-black text-slate-950 shadow-2xl shadow-fuchsia-950/50">Guardar probabilidades y cupos</button></div>
    </form>

    <section class="rounded-2xl border border-white/10 bg-white/[.025] p-5 sm:p-6">
        <p class="text-xs font-black uppercase tracking-[.2em] text-cyan-300">Entorno de pruebas</p>
        <h3 class="mt-2 text-xl font-black">Multiplicadores para cuentas demo</h3>
        <form method="POST" action="{{ route('admin.case-prizes.boosts.store') }}" class="mt-5 grid gap-4 lg:grid-cols-[1.3fr_.6fr_1fr_auto]">
            @csrf
            <label class="text-xs font-bold text-slate-400">Cuenta demo/test<select name="user_id" required class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-[#111827] px-3 text-white"><option value="">Seleccionar…</option>@foreach($eligibleUsers as $user)<option value="{{ $user->id }}">{{ $user->name }} · {{ $user->email }} · {{ $user->data_origin }}</option>@endforeach</select></label>
            <label class="text-xs font-bold text-slate-400">Multiplicador<input name="multiplier" type="number" min="1.01" max="5" step="0.01" value="2" required class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-black/25 px-3 text-white"></label>
            <label class="text-xs font-bold text-slate-400">Caducidad<input name="expires_at" type="datetime-local" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-black/25 px-3 text-white"></label>
            <button class="mt-auto min-h-11 rounded-xl bg-cyan-300 px-5 font-black text-slate-950">Guardar</button>
            <label class="text-xs font-bold text-slate-400 lg:col-span-4">Motivo auditable<input name="reason" maxlength="500" required placeholder="Ej.: validación QA del cupo de premios épicos" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-black/25 px-3 text-white"></label>
        </form>

        <div class="mt-6 space-y-2">
            @forelse($boosts as $boost)
                <div class="flex flex-col gap-3 rounded-xl border border-white/10 bg-black/20 p-4 sm:flex-row sm:items-center sm:justify-between"><div><b>{{ $boost->user?->name }}</b><p class="mt-1 text-xs text-slate-500">×{{ number_format($boost->multiplier, 2, ',', '.') }} · {{ $boost->reason }} · {{ $boost->expires_at ? 'hasta '.$boost->expires_at->format('d/m/Y H:i') : 'sin caducidad' }}</p></div><form method="POST" action="{{ route('admin.case-prizes.boosts.destroy', $boost) }}">@csrf @method('DELETE')<button class="min-h-10 rounded-lg border border-red-400/20 px-3 text-xs font-bold text-red-300">Eliminar</button></form></div>
            @empty
                <p class="rounded-xl border border-dashed border-white/10 p-5 text-sm text-slate-500">No hay multiplicadores de prueba configurados.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
