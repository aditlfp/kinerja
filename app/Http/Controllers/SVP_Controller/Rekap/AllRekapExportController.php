<?php

namespace App\Http\Controllers\SVP_Controller\Rekap;

use App\Models\FinishedTraining;
use App\Models\Kerjasama;
use App\Models\Overtime;
use App\Models\KeteranganLanjutan;
use App\Models\PerformanceCuts;
use App\Models\PersonIn;
use App\Models\PersonOut;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Controllers\SVP_Controller\Rekap\Concerns\TransformCuttings;
use App\Http\Controllers\SVP_Controller\Rekap\Concerns\TransformOvertimes;
use Illuminate\Http\Request;

class AllRekapExportController extends RekapController
{
    use TransformOvertimes, TransformCuttings;

    public function getAllRekapData(Request $request, $kerjasama)
    {
        try {
            $month = $request->input('month', now()->format('Y-m'));
            $date = Carbon::createFromFormat('Y-m', $month);
            $startDate = $request->filled(['start_date', 'end_date'])
                ? Carbon::parse($request->start_date)->startOfDay()
                : $date->copy()->subMonth()->setDay(26)->startOfDay();
            $endDate = $request->filled(['start_date', 'end_date'])
                ? Carbon::parse($request->end_date)->endOfDay()
                : $date->copy()->setDay(25)->endOfDay();
            $includeAllStatus = $request->boolean('include_all_status');

            $kerjasamaData = Kerjasama::with('client:id,name')->findOrFail($kerjasama);
            $clientId = (int) $kerjasamaData->client_id;
            $clientName = $kerjasamaData->client?->name ?? 'Semua Mitra';
            $allowedJabatanIds = $this->allowedSeeData();
            $authTypeJabatan = auth()->user()->jabatan->type_jabatan ?? null;
            $filterJabatan = function ($q) use ($allowedJabatanIds, $authTypeJabatan) {
                return !empty($allowedJabatanIds)
                    ? $q->whereIn('jabatan_id', $allowedJabatanIds)
                    : $q->whereHas('jabatan', fn($j) => $j->where('type_jabatan', $authTypeJabatan));
            };

            if ($clientId <= 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'overtimes' => [],
                        'person_ins' => [],
                        'person_outs' => [],
                        'cuttings' => [],
                        'finished_trainings' => [],
                        'keterangan_lanjutan' => [],
                        'client' => ['name' => $clientName],
                        'period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
                    ]
                ]);
            }

            // Build overtimes query
            $overtimesQuery = Overtime::with([
                'user:id,id,nama_lengkap,kerjasama_id,jabatan_id',
                'user.kerjasama:id,client_id',
                'user.kerjasama.client:id,name',
                'user.jabatan:id,name_jabatan'
            ])->whereHas('user', function ($q) use ($clientId, $filterJabatan) {
                $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $clientId));
                $filterJabatan($q);
            });
            if (!$includeAllStatus) $overtimesQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            // Selecting only the recap table's columns keeps the joined user's
            // id/created_at from overwriting the row's own values.
            $overtimesQuery = $overtimesQuery->join('users', 'overtimes.user_id', '=', 'users.id')
                ->select('overtimes.*');
            if ($startDate && $endDate) {
                $overtimesQuery->whereBetween('date_overtime', [$startDate, $endDate]);
            } else {
                $overtimesQuery->whereBetween('date_overtime', monthBounds($date));
            }
            $overtimes = $overtimesQuery->orderBy('users.kerjasama_id')
                ->orderBy('users.jabatan_id')
                ->orderBy('user_id')
                ->orderBy('date_overtime')
                ->get();

            // Build person_ins query
            $personInsQuery = PersonIn::with(['jabatan:id,name_jabatan'])
                ->where('client_id', $clientId);
            $filterJabatan($personInsQuery);
            if (!$includeAllStatus) $personInsQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            if ($startDate && $endDate) {
                $personInsQuery->whereBetween('date_in', [$startDate, $endDate]);
            } else {
                $personInsQuery->whereBetween('date_in', monthBounds($date));
            }
            $personIns = $personInsQuery->orderBy('date_in')->get();

            // Build person_outs query
            $personOutsQuery = PersonOut::with([
                'user' => fn($q) => $q->withTrashed(),
                'user.kerjasama.client',
                'user.jabatan:id,name_jabatan',
                'createdBy:id,nama_lengkap'
            ])->whereHas('user', function ($q) use ($clientId, $filterJabatan) {
                $q->withTrashed()
                    ->whereNotNull('nama_lengkap')
                    ->whereHas('kerjasama', fn($k) => $k->where('client_id', $clientId));
                $filterJabatan($q);
            });
            if (!$includeAllStatus) $personOutsQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            $personOutsQuery = $personOutsQuery->join('users', 'person_outs.user_id', '=', 'users.id')
                ->select('person_outs.*');
            if ($startDate && $endDate) {
                $personOutsQuery->whereBetween('out_date', [$startDate, $endDate]);
            } else {
                $personOutsQuery->whereBetween('out_date', monthBounds($date));
            }
            $personOuts = $personOutsQuery->orderBy('users.kerjasama_id')
                ->orderBy('users.jabatan_id')
                ->orderBy('user_id')
                ->orderBy('out_date')
                ->get();

            // Build cuttings query
            $cuttingsQuery = PerformanceCuts::with(['user:id,nama_lengkap,kerjasama_id,jabatan_id', 'user.kerjasama:id,client_id', 'user.jabatan:id,name_jabatan'])
                ->whereHas('user', function ($q) use ($clientId, $filterJabatan) {
                    $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $clientId));
                    $filterJabatan($q);
                });
            if (!$includeAllStatus) $cuttingsQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            $cuttingsQuery = $cuttingsQuery->join('users', 'performance_cuts.user_id', '=', 'users.id')
                ->select('performance_cuts.*');
            if ($startDate && $endDate) {
                $cuttingsQuery->whereBetween('date_cut', [$startDate, $endDate]);
            } else {
                $cuttingsQuery->whereBetween('date_cut', monthBounds($date));
            }
            $cuttings = $cuttingsQuery->orderBy('users.kerjasama_id')
                ->orderBy('users.jabatan_id')
                ->orderBy('user_id')
                ->orderBy('date_cut')
                ->get();

            // Build finished trainings query
            $finishedTrainingsQuery = FinishedTraining::with(['user:id,nama_lengkap,kerjasama_id,jabatan_id', 'user.kerjasama:id,client_id', 'user.jabatan:id,name_jabatan'])
                ->whereHas('user', function ($q) use ($clientId, $filterJabatan) {
                    $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $clientId));
                    $filterJabatan($q);
                });
            if (!$includeAllStatus) $finishedTrainingsQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            $finishedTrainingsQuery = $finishedTrainingsQuery->join('users', 'finished_trainings.user_id', '=', 'users.id')
                ->select('finished_trainings.*');
            if ($startDate && $endDate) {
                $finishedTrainingsQuery->whereBetween('date_finish_train', [$startDate, $endDate]);
            } else {
                $finishedTrainingsQuery->whereBetween('date_finish_train', monthBounds($date));
            }
            $finishedTrainings = $finishedTrainingsQuery->orderBy('users.kerjasama_id')
                ->orderBy('users.jabatan_id')
                ->orderBy('user_id')
                ->orderBy('date_finish_train')
                ->get();

            // Build keterangan_lanjutan query. A note belongs to the mitra, not
            // to the author's jabatan: filtering by the author's position hid
            // every note written by a role outside the viewer's list.
            $keteranganQuery = KeteranganLanjutan::with([
                'user' => fn($q) => $q->withTrashed()
                    ->select('id', 'nama_lengkap', 'kerjasama_id', 'jabatan_id'),
                'user.kerjasama:id,client_id',
                'user.kerjasama.client:id,name',
                'user.jabatan:id,name_jabatan',
                'createdBy:id,nama_lengkap'
            ])->whereHas('user', fn($q) => $q->withTrashed()
                ->whereHas('kerjasama', fn($k) => $k->where('client_id', $clientId))
            )->join('users', 'keterangan_lanjutans.user_id', '=', 'users.id')
                ->select('keterangan_lanjutans.*');
            if ($startDate && $endDate) {
                $keterangan = $keteranganQuery->whereBetween('keterangan_lanjutans.created_at', [$startDate, $endDate])
                    ->orderBy('users.kerjasama_id')
                    ->orderBy('users.jabatan_id')
                    ->orderBy('keterangan_lanjutans.created_at')
                    ->get();
            } else {
                $keterangan = $keteranganQuery->whereBetween('keterangan_lanjutans.created_at', monthBounds($date))
                    ->orderBy('users.kerjasama_id')
                    ->orderBy('users.jabatan_id')
                    ->orderBy('keterangan_lanjutans.created_at')
                    ->get();
            }

            $data = [
                'overtimes' => $this->transformOvertimes($overtimes),
                'person_ins' => $personIns,
                'person_outs' => $personOuts,
                'cuttings' =>  $this->transformCuttings($cuttings),
                'finished_trainings' => $finishedTrainings,
                'keterangan_lanjutan' => $keterangan,
                'client' => ['name' => $clientName],
                'period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
                'show_status' => $request->boolean('with_status'),
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getGlobalRekapData(Request $request)
    {
        try {
            $month = $request->input('month', now()->format('Y-m'));
            $date = Carbon::createFromFormat('Y-m', $month);
            $startDate = $request->filled(['start_date', 'end_date'])
                ? Carbon::parse($request->start_date)->startOfDay()
                : $date->copy()->subMonth()->setDay(26)->startOfDay();
            $endDate = $request->filled(['start_date', 'end_date'])
                ? Carbon::parse($request->end_date)->endOfDay()
                : $date->copy()->setDay(25)->endOfDay();
            $includeAllStatus = false; // Debug parameter to include all status

            $clientsQuery = Kerjasama::query()
                ->whereNotNull('client_id');

            if ($request->routeIs('spvw.*')) {
                $authClientKabupaten = auth()->user()->kerjasama?->client?->kabupaten;
                $allowedKabupaten = $this->nearbyKabupatenTerms($authClientKabupaten);
                $allowedJabatanIds = $this->allowedSeeData();
                $ownClientId = (int) (auth()->user()->kerjasama?->client_id ?? 0);

                $clientsQuery
                    ->when($allowedKabupaten, fn($query) => $query->whereHas('client', fn($client) => $client->where(function ($q) use ($allowedKabupaten) {
                        foreach ($allowedKabupaten as $kabupaten) {
                            $q->orWhere('kabupaten', 'like', "%{$kabupaten}%");
                        }
                    })))
                    ->when(!empty($allowedJabatanIds), function ($query) use ($allowedJabatanIds, $ownClientId) {
                        $query->where(function ($visibility) use ($allowedJabatanIds, $ownClientId) {
                            $visibility->whereIn('id', User::query()
                                ->select('kerjasama_id')
                                ->whereIn('jabatan_id', $allowedJabatanIds)
                                ->whereNotNull('kerjasama_id'));

                            // The supervisor's own mitra is exported even when
                            // no one there holds a jabatan in allowedSeeData();
                            // the data queries below still apply that filter.
                            if ($ownClientId > 0) {
                                $visibility->orWhere('client_id', $ownClientId);
                            }
                        });
                    });
            }

            $clients = $clientsQuery
                ->distinct()
                ->pluck('client_id');

            if ($clients->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'overtimes' => [],
                        'person_ins' => [],
                        'person_outs' => [],
                        'cuttings' => [],
                        'finished_trainings' => [],
                        'keterangan_lanjutan' => [],
                        'client' => ['name' => 'Semua Mitra'],
                        'period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
                    ],
                ]);
            }

            $overtimesQuery = Overtime::with([
                'user:id,id,nama_lengkap,kerjasama_id,jabatan_id',
                'user.kerjasama:id,client_id',
                'user.kerjasama.client:id,name',
                'user.jabatan:id,name_jabatan',
            ])
                ->whereHas('user', function ($q) use ($clients) {
                    $q->whereHas('kerjasama', fn($k) => $k->whereIn('client_id', $clients))
                        ->whereIn('jabatan_id', $this->allowedSeeData());
                });
            if (!$includeAllStatus) {
                $overtimesQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            }
            $overtimesQuery = $overtimesQuery->join('users', 'overtimes.user_id', '=', 'users.id')
                ->select('overtimes.*');
            if ($startDate && $endDate) {
                $overtimesQuery->whereBetween('date_overtime', [$startDate, $endDate]);
            } else {
                $overtimesQuery->whereBetween('date_overtime', monthBounds($date));
            }
            $overtimes = $overtimesQuery
                ->orderBy('users.kerjasama_id')
                ->get();

            $personInsQuery = PersonIn::with(['jabatan:id,name_jabatan', 'client:id,name'])
                ->whereIn('client_id', $clients)
                ->whereIn('jabatan_id', $this->allowedSeeData());
            if (!$includeAllStatus) {
                $personInsQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            }
            if ($startDate && $endDate) {
                $personInsQuery->whereBetween('date_in', [$startDate, $endDate]);
            } else {
                $personInsQuery->whereBetween('date_in', monthBounds($date));
            }
            $personIns = $personInsQuery
                ->orderBy('client_id')
                ->oldest('date_in')
                ->get();

            $personOutsQuery = PersonOut::with([
                'user' => fn($q) => $q->withTrashed(),
                'user.kerjasama.client',
                'user.jabatan:id,name_jabatan',
                'createdBy:id,nama_lengkap',
            ])
                ->whereHas('user', function ($q) use ($clients) {
                    $q->withTrashed()
                        ->whereNotNull('nama_lengkap')
                        ->whereHas('kerjasama', fn($k) => $k->whereIn('client_id', $clients))
                        ->whereIn('jabatan_id', $this->allowedSeeData());
                });
            if (!$includeAllStatus) {
                $personOutsQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            }
            $personOutsQuery = $personOutsQuery->join('users', 'person_outs.user_id', '=', 'users.id');
            $personOutsQuery = $personOutsQuery->join('kerjasamas', 'users.kerjasama_id', '=', 'kerjasamas.id');

            if ($startDate && $endDate) {
                $personOutsQuery->whereBetween('out_date', [$startDate, $endDate]);
            } else {
                $personOutsQuery->whereBetween('out_date', monthBounds($date));
            }

            $personOutsQuery->select('person_outs.*');

            $personOuts = $personOutsQuery
                ->orderBy('kerjasamas.id')
                ->orderBy('users.jabatan_id')
                ->oldest('person_outs.out_date')
                ->orderBy('person_outs.user_id')
                ->get();

            $cuttingsQuery = PerformanceCuts::with([
                'user:id,nama_lengkap,kerjasama_id',
                'user.kerjasama:id,client_id',
                'user.jabatan:id,name_jabatan',
                'user.kerjasama.client:id,name',
            ])
                ->whereHas('user', function ($q) use ($clients) {
                    $q->whereHas('kerjasama', fn($k) => $k->whereIn('client_id', $clients))
                        ->whereIn('jabatan_id', $this->allowedSeeData());
                });
            if (!$includeAllStatus) {
                $cuttingsQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            }
            $cuttingsQuery = $cuttingsQuery->join('users', 'performance_cuts.user_id', '=', 'users.id')
                ->select('performance_cuts.*');
            if ($startDate && $endDate) {
                $cuttingsQuery->whereBetween('date_cut', [$startDate, $endDate]);
            } else {
                $cuttingsQuery->whereBetween('date_cut', monthBounds($date));
            }
            $cuttings = $cuttingsQuery->orderBy('users.kerjasama_id')
                ->orderBy('users.jabatan_id')
                ->orderBy('user_id')
                ->oldest('date_cut')
                ->get();

            $finishedTrainingsQuery = FinishedTraining::with([
                'user:id,nama_lengkap,kerjasama_id',
                'user.kerjasama:id,client_id',
                'user.jabatan:id,name_jabatan',
                'user.kerjasama.client:id,name',
            ])
                ->whereHas('user', function ($q) use ($clients) {
                    $q->whereHas('kerjasama', fn($k) => $k->whereIn('client_id', $clients))
                        ->whereIn('jabatan_id', $this->allowedSeeData());
                });
            if (!$includeAllStatus) {
                $finishedTrainingsQuery->whereNotIn('status', ['Di Tolak', 'Pending']);
            }
            $finishedTrainingsQuery = $finishedTrainingsQuery->join('users', 'finished_trainings.user_id', '=', 'users.id')
                ->select('finished_trainings.*');
            if ($startDate && $endDate) {
                $finishedTrainingsQuery->whereBetween('date_finish_train', [$startDate, $endDate]);
            } else {
                $finishedTrainingsQuery->whereBetween('date_finish_train', monthBounds($date));
            }
            $finishedTrainings = $finishedTrainingsQuery->orderBy('users.kerjasama_id')
                ->orderBy('users.jabatan_id')
                ->orderBy('user_id')
                ->oldest('date_finish_train')
                ->get();

            $keteranganQuery = KeteranganLanjutan::with([
                'user' => fn($q) => $q->withTrashed()
                    ->select('id', 'nama_lengkap', 'kerjasama_id', 'jabatan_id'),
                'user.kerjasama:id,client_id',
                'user.kerjasama.client:id,name',
                'user.jabatan:id,name_jabatan',
                'createdBy:id,nama_lengkap',
            ])
                ->whereHas('user', fn($q) => $q->withTrashed()
                    ->whereHas('kerjasama', fn($k) => $k->whereIn('client_id', $clients))
                )
                ->join('users', 'keterangan_lanjutans.user_id', '=', 'users.id')
                ->select('keterangan_lanjutans.*');
            if ($startDate && $endDate) {
                $keterangan = $keteranganQuery->whereBetween('keterangan_lanjutans.created_at', [$startDate, $endDate])
                    ->orderBy('users.kerjasama_id')
                    ->orderBy('users.jabatan_id')
                    ->oldest('keterangan_lanjutans.created_at')
                    ->get();
            } else {
                $keterangan = $keteranganQuery->whereBetween('keterangan_lanjutans.created_at', monthBounds($date))
                    ->orderBy('users.kerjasama_id')
                    ->orderBy('users.jabatan_id')
                    ->oldest('keterangan_lanjutans.created_at')
                    ->get();
            }

            $data = [
                'overtimes' => $this->transformOvertimes($overtimes),
                'person_ins' => $personIns,
                'person_outs' => $personOuts,
                'cuttings' => $this->transformCuttings($cuttings),
                'finished_trainings' => $finishedTrainings,
                'keterangan_lanjutan' => $keterangan,
                'client' => ['name' => 'Semua Mitra'],
                'period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            ];

            // dd($data['overtimes']);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
