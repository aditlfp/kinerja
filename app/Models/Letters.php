<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letters extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'latters';

    public function cover()
    {
        return $this->belongsTo(LaporanMitra::class, 'cover_id');
    }
}
