<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVote extends Model
{
    protected $table = 'review_votes';

    protected $fillable = ['usuario_id', 'review_id', 'upvote'];

    protected $casts = [
        'upvote' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
