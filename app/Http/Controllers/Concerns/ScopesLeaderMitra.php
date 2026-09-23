<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Client;

trait ScopesLeaderMitra
{
    private function leaderClientIds(): array
    {
        $clientIds = Client::where('name', 'LIKE', '%ngabar%')
            ->pluck('id')
            ->toArray();
        return auth()->id() == 157 ? $clientIds : [auth()->user()->kerjasama->client_id];
    }

    private function scopeLeaderUser($q): void
    {
        $q->when(
            auth()->id() == 157,
            fn($q) => $q->whereHas('kerjasama', fn($q) => $q->whereIn('client_id', $this->leaderClientIds())),
            fn($q) => $q->where('kerjasama_id', auth()->user()->kerjasama_id)
        )->whereHas('jabatan', function ($q) {
            $q->where('type_jabatan', auth()->user()->jabatan->type_jabatan);
        });
    }
}
