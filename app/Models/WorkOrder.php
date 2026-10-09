<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $casts = [
        'tanggal' => 'date',
        'has_read' => 'boolean',
        'has_complete' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'tanggal',
        'deskripsi',
        'created_by',
        'has_read',
        'has_complete'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Who issued this order. Null for legacy rows, which are attributed to
     * Direksi.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Display name of the issuer, falling back to "Direksi" when unset.
     */
    public function getCreatorNameAttribute(): string
    {
        return $this->creator?->nama_lengkap ?: User::where('name', 'DIREKTUR')->first()->nama_lengkap;
    }

    public function checkPoints()
    {
        return $this->hasMany(CheckPoint::class, 'work_order_id');
    }
}
