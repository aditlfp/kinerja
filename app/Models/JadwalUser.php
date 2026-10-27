<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalUser extends Model
{
    use HasFactory;

    /** Nilai jadwal yang boleh tersimpan (dipakai sebagai label shift). */
    public const OPTIONS = ['Pagi', 'Siang', 'Sore', 'Malam', 'OFF'];

    protected $fillable = [
        'user_id',
        'shift_id',
        'tanggal',
        'area_id',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public static function isValidOption(?string $value): bool
    {
        return in_array($value, self::OPTIONS, true);
    }
}
