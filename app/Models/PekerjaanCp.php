<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PekerjaanCp extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'divisi_id',
        'kerjasama_id',
        'name',
        'type_check',
        'weighting_value'
    ];
    
    protected $casts = ['weighting_value' => 'integer'];

    public function kerjasama()
    {
        return $this->belongsTo(Kerjasama::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }
}
