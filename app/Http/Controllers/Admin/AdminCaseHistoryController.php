<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventarioItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCaseHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'case' => ['nullable', Rule::in(array_keys(config('cajas', [])))],
            'rarity' => ['nullable', Rule::in(['comun', 'poco_comun', 'raro', 'epico', 'legendario'])],
            'status' => ['nullable', Rule::in(['disponible', 'canjeado'])],
            'source' => ['nullable', Rule::in(['real', 'simulated'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $query = InventarioItem::query()
            ->with('usuario:id,name,email,is_demo,data_origin')
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('id', ctype_digit($search) ? (int) $search : -1)
                        ->orWhereHas('usuario', fn (Builder $user) => $user
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($filters['case'] ?? null, fn (Builder $query, string $case) => $query->where('caja', $case))
            ->when($filters['rarity'] ?? null, fn (Builder $query, string $rarity) => $query->where('rareza', $rarity))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('estado', $status))
            ->when(($filters['source'] ?? null) === 'simulated', fn (Builder $query) => $query->whereHas('usuario', fn (Builder $user) => $user->where('is_demo', true)->orWhereIn('data_origin', ['test', 'simulated'])))
            ->when(($filters['source'] ?? null) === 'real', fn (Builder $query) => $query->whereHas('usuario', fn (Builder $user) => $user->where('is_demo', false)->where('data_origin', 'real')))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('created_at', '>=', CarbonImmutable::parse($from)->startOfDay()))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('created_at', '<=', CarbonImmutable::parse($to)->endOfDay()));

        $summary = (clone $query)->reorder()->selectRaw(
            "COUNT(*) as openings,
            COALESCE(SUM(precio_caja), 0) as revenue,
            COALESCE(SUM(valor_virtual), 0) as awarded_value,
            COALESCE(SUM(CASE WHEN estado = 'canjeado' THEN valor_virtual ELSE 0 END), 0) as redeemed_value,
            COALESCE(SUM(CASE WHEN estado = 'disponible' THEN valor_virtual ELSE 0 END), 0) as pending_value,
            COALESCE(SUM(CASE WHEN rareza IN ('epico', 'legendario') THEN 1 ELSE 0 END), 0) as good_prizes"
        )->first();

        return view('admin.cases.history', [
            'items' => $query->latest('id')->paginate(30)->withQueryString(),
            'summary' => $summary,
            'filters' => $filters,
            'cases' => collect(config('cajas', []))->map(fn (array $case) => $case['nombre']),
        ]);
    }
}
