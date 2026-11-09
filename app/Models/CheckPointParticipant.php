<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Pivot;

class CheckPointParticipant extends Pivot
{
    protected $table = 'check_point_participants';

    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(CheckPoint::class, 'check_point_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
