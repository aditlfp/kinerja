<?php

namespace App\Http\Controllers\SPVW_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\RekapDueDateSetting;
use App\Models\User;

class DataRekapController extends Controller
{
    use LocksRekapByDueDate;

    public function index()
    {
        $sessionKey = 'spvw.selected_client_id';
        $modeSessionKey = 'spvw.rekap_mode';
        $hasClientParam = request()->has('client_id');
        $isResetFilter = request()->boolean('reset_filter');
        $selectedClientId = $hasClientParam
            ? (int) request('client_id', 0)
            : (int) session($sessionKey, 0);

        $authClientKabupaten = auth()->user()->kerjasama?->client?->kabupaten;
        $allowedKabupaten = $this->nearbyKabupatenTerms($authClientKabupaten);
        $ownClientId = (int) (auth()->user()->kerjasama?->client_id ?? 0);

        $clients = Client::query()
            ->select(['id', 'name'])
            ->when($allowedKabupaten, fn($query) => $query->where(function ($q) use ($allowedKabupaten) {
                foreach ($allowedKabupaten as $kabupaten) {
                    $q->orWhere('kabupaten', 'like', "%{$kabupaten}%");
                }
            }))
            ->when(in_array((int) (auth()->user()->jabatan_id ?? 0), [35, 20], true), function ($query) use ($ownClientId) {
                $allowedJabatanIds = ((int) (auth()->user()->jabatan_id ?? 0) == 35)
                    ? [8, 11, 16, 17, 18]
                    : [9, 10, 34, 36];

                $query->where(function ($visibility) use ($allowedJabatanIds, $ownClientId) {
                    $visibility->whereHas('kerjasama', function ($kerjasamaQuery) use ($allowedJabatanIds) {
                        $kerjasamaQuery->whereIn('id', User::query()
                            ->select('kerjasama_id')
                            ->whereIn('jabatan_id', $allowedJabatanIds)
                            ->whereNotNull('kerjasama_id'));
                    });

                    // A supervisor must always be able to pick the mitra they
                    // are assigned to, even when none of its staff holds a
                    // jabatan they may review yet.
                    if ($ownClientId > 0) {
                        $visibility->orWhere('id', $ownClientId);
                    }
                });
            })
            ->orderBy('name')
            ->get();

        if ($isResetFilter) {
            $selectedClientId = 0;
            session()->forget($sessionKey);
        }

        if ($selectedClientId <= 0 || !$clients->contains('id', $selectedClientId)) {
            $selectedClientId = 0;
            session()->forget($sessionKey);
        } else {
            session()->put($sessionKey, $selectedClientId);
        }

        $selectedMode = (string) request('mode', (string) session($modeSessionKey, 'pengajuan'));
        if (!in_array($selectedMode, ['pengajuan', 'riwayat'], true)) {
            $selectedMode = 'pengajuan';
        }
        session()->put($modeSessionKey, $selectedMode);

        $dueDate = RekapDueDateSetting::latest()->first();
        $isAfterDueDate = $this->isSubmissionLockedByDueDate();

        return view('spv_w_view.index', [
            'clients' => $clients,
            'selectedClientId' => $selectedClientId,
            'selectedMode' => $selectedMode,
            'dueDate' => $dueDate,
            'isAfterDueDate' => $isAfterDueDate,
            'isSubmissionLocked' => $isAfterDueDate,
        ]);
    }

    private function nearbyKabupatenTerms(?string $kabupaten): array
    {
        $kabupaten = strtolower((string) $kabupaten);

        foreach (config('client_area', []) as $terms) {
            foreach ($terms as $term) {
                if (str_contains($kabupaten, $term)) {
                    return $terms;
                }
            }
        }

        return $kabupaten ? [$kabupaten] : [];
    }
}
