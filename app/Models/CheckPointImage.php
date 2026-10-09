<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckPointImage extends Model
{
    protected $fillable = [
        'check_point_item_id',
        'path',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(CheckPointItem::class, 'check_point_item_id');
    }
}
