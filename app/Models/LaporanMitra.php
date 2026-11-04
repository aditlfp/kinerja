<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanMitra extends Model
{
    use HasFactory;
    protected $connection = 'mysql3';
    protected $table = 'covers';

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clients_id');
    }

    public function letters()
    {
        return $this->hasMany(Letters::class, 'cover_id');
    }
}
