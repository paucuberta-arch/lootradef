<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerTransaction extends Model
{
    protected $fillable = [
        'transaction_uuid', 'type', 'status', 'currency', 'idempotency_key',
        'reference_type', 'reference_id', 'created_by', 'metadata', 'occurred_at',
    ];

    protected $casts = ['metadata' => 'array', 'occurred_at' => 'datetime'];

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
