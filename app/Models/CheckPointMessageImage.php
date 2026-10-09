<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckPointMessageImage extends Model
{
    protected $fillable = [
        'check_point_message_id',
        'path',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(CheckPointMessage::class, 'check_point_message_id');
    }
}
