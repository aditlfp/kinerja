<?php

namespace App\Services;

use App\Models\Absensi;
use Illuminate\Support\Facades\DB;

class AbsensiService
{
    public function create(array $data): void
    {
        DB::transaction(fn () => Absensi::create($data));
    }
}
