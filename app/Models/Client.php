<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'panggilan',
        'address',
        'province',
        'kabupaten',
        'zipcode',
        'email',
        'phone',
        'fax',
        'logo',
    ];
    public function shift()
    {
        return $this->hasMany(Shift::class);
    }

    public function kerjasama()
    {
        return $this->hasMany(Kerjasama::class);
    }
}
