<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'divisi_id',
        'work_order_id',
        'type_check',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Normalised list of jobs belonging to this batch.
     * This is the source of truth going forward; the legacy JSON-array
     * columns are kept only until the readers are fully migrated.
     */
    public function items()
    {
        return $this->hasMany(CheckPointItem::class)->orderBy('urutan');
    }

    public function images()
    {
        return $this->hasManyThrough(
            CheckPointImage::class,
            CheckPointItem::class,
            'check_point_id',
            'check_point_item_id',
        )->orderBy('check_point_images.urutan');
    }

    /**
     * Dates this batch covers (for calendar/history highlighting).
     */
    public function getDatesAttribute()
    {
        $dates = $this->relationLoaded('items')
            ? $this->items->pluck('tanggal')
            : $this->items()->pluck('tanggal');

        $dates = $dates->filter();

        return $dates->isEmpty() ? collect([$this->created_at]) : $dates;
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function pekerjaanCp()
    {
        return $this->belongsTo(PekerjaanCp::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }
}
