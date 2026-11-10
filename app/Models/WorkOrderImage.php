<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderImage extends Model
{
    protected $fillable = ['path', 'sort_order'];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
