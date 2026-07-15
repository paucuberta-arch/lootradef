<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Cartera extends Model
{
    protected $table = 'carteras';

    protected $fillable = [
        'usuario_id',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function tieneSaldo(float $cantidad): bool
    {
        return $this->saldo >= $cantidad;
    }

    public function apostar(float $cantidad): bool
    {
        $updated = DB::table('carteras')
            ->where('id', $this->id)
            ->where('saldo', '>=', $cantidad)
            ->decrement('saldo', $cantidad);

        if ($updated) {
            $this->refresh();
            return true;
        }
        return false;
    }

    public function ganar(float $cantidad): void
    {
        DB::table('carteras')
            ->where('id', $this->id)
            ->increment('saldo', $cantidad);

        $this->refresh();
    }
}
