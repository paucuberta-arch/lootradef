<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoWithdrawal extends Model
{
    public const REQUESTED = 'requested';
    public const UNDER_REVIEW = 'under_review';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'usuario_id', 'request_token', 'amount', 'currency', 'method_key',
        'status', 'rejection_reason', 'reviewed_by', 'reserved_at',
        'reviewed_at', 'completed_at', 'metadata',
    ];

    protected $casts = [
        'amount' => 'float',
        'reserved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'reviewed_by');
    }
}
