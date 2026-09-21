<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class News extends Model
{
    use HasFactory;
    
    protected $fillable = ['image', 'tanggal_lihat', 'tanggal_tutup', 'tanggal_muncul'];

    protected $casts = [
        'tanggal_muncul' => 'array',
    ];

    /**
     * Berita yang sedang berlaku dan dijadwalkan muncul pada tanggal tersebut.
     *
     * `tanggal_muncul` menyimpan nomor hari. Checkbox form mengirim string,
     * sehingga data bisa tersimpan sebagai ["21"] atau sebagai angka [21];
     * kedua bentuk harus ikut cocok supaya berita tidak hilang dari dashboard.
     */
    public function scopeTampilPada($query, $tanggal = null)
    {
        $tanggal = $tanggal ? Carbon::parse($tanggal) : now();
        $hari = (int) $tanggal->day;

        return $query
            ->whereDate('tanggal_lihat', '<=', $tanggal->toDateString())
            ->whereDate('tanggal_tutup', '>=', $tanggal->toDateString())
            ->where(function ($query) use ($hari) {
                $query->whereJsonContains('tanggal_muncul', $hari)
                    ->orWhereJsonContains('tanggal_muncul', (string) $hari);
            });
    }
}
