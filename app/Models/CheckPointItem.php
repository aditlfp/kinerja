<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckPointItem extends Model
{
    protected $fillable = [
        'check_point_id',
        'pekerjaan_cp_id',
        'input_manual',
        'deskripsi',
        'tanggal',
        'latitude',
        'longtitude',
        'approve_status',
        'note',
        'urutan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'urutan' => 'integer',
    ];

    public function checkPoint(): BelongsTo
    {
        return $this->belongsTo(CheckPoint::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CheckPointImage::class)->orderBy('urutan');
    }

    public function pekerjaanCp(): BelongsTo
    {
        return $this->belongsTo(PekerjaanCp::class, 'pekerjaan_cp_id');
    }

    /** First image path, handy for single-thumbnail listings. */
    public function getFirstImageAttribute(): ?string
    {
        return $this->images->first()->path ?? null;
    }

    public function isApproved(): bool
    {
        return $this->approve_status === 'accept';
    }

    public function isDenied(): bool
    {
        return $this->approve_status === 'denied';
    }
}
