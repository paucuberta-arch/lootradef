<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    protected $fillable = [
        'ledger_transaction_id', 'ledger_account_id', 'direction', 'amount',
        'currency', 'line_number', 'metadata',
    ];

    protected $casts = ['amount' => 'decimal:2', 'metadata' => 'array'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LedgerTransaction::class, 'ledger_transaction_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }
}
