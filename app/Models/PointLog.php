<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'adjust_type',
        'amount',
        'earning_point',
        'old_point',
        'new_point',
        'remark',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
