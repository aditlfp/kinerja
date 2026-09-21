<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CheckPoint;
use App\Models\PekerjaanCp;
use App\Models\Client;
use App\Models\Divisi;
use App\Models\Kerjasama;
use App\Models\Point;
use App\Models\Shift;
use App\Models\User;
use App\Models\Izin;
use App\Models\SlipGaji;
use App\Models\TempUser;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class AdminController extends Controller
{

    public function __construct(Request $request)
    {
        $this->str = $request->input('str1');
        $this->ended = $request->input('end1');
    }

    public function index(Request $request)
    {
        $now = now();
        $oneMonthAgo = $now->copy()->subMonth()->startOfMonth();
        $today = $now->toDateString();
        $twoMonthsFromNow = $now->copy()->addMonths(2)->toDateString();

        $online = DB::table('sessions')
            ->where('last_seen_at', '>', $now->copy()->subMinutes(5))
            ->whereNotNull('user_id')
            ->count();

        $inactiveUsersQuery = function () use ($oneMonthAgo) {
            return User::query()
                ->select([
                    'users.id',
                    'users.nama_lengkap',
                    'users.name',
                    'users.devisi_id',
                ])
                ->where('users.kerjasama_id', '!=', 1)
                ->whereNotIn('users.devisi_id', [8, 18])
                ->whereNotExists(function ($query) use ($oneMonthAgo) {
                    $query->selectRaw('1')
                        ->from('absensis')
                        ->whereColumn('absensis.user_id', 'users.id')
                        ->where('absensis.created_at', '>=', $oneMonthAgo);
                });
        };

        $notActiveUsers = Cache::remember(
            'admin.dashboard.inactive-users',
            60,
            fn() => $inactiveUsersQuery()
                ->get()
        );

        $inactiveUsersCount = $notActiveUsers->count();

        $user   = User::count();
        $client = Client::count();

        $izin = Izin::where('approve_status', 'process')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $expiringContractsQuery = function () use ($today, $twoMonthsFromNow) {
            return Kerjasama::query()
                ->select('id', 'client_id', 'experied')
                ->with('client:id,name')
                ->whereBetween('experied', [$today, $twoMonthsFromNow]);
        };

        $expiringContractsCount = Cache::remember('admin.dashboard.expiring-contracts-upcoming-count', 300, function () use ($expiringContractsQuery) {
            return $expiringContractsQuery()->count();
        });

        $expert = Cache::remember('admin.dashboard.expiring-contracts-upcoming', 300, function () use ($expiringContractsQuery) {
            return $expiringContractsQuery()
                ->orderBy('experied')
                ->get();
        });

        return view('admin.index', compact(
            'user',
            'client',
            'izin',
            'online',
            'expert',
            'notActiveUsers',
            'inactiveUsersCount',
            'expiringContractsCount'
        ));
    }
    public function getUptime()
    {
        $startTime = Cache::get('app_start_time');
        $currentTime = now();
        $uptime = $currentTime->diffInSeconds($startTime);

        // Menambahkan waktu yang telah berlalu sejak app_start_time ke waktu sekarang
        $uptime += $currentTime->day * 24 * 3600;

        $days = intdiv($uptime, 86400); // 86400 detik dalam satu hari
        $uptime %= 86400; // Sisa detik setelah menghitung hari

        $hours = intdiv($uptime, 3600);
        $minutes = intdiv(($uptime % 3600), 60);
        $seconds = $uptime % 60;

        // Menampilkan "hari" jika jumlah hari adalah 1, dan "hari" jika lebih dari 1
        $daysLabel = $days == 1 ? 'Hari' : 'Hari';

        $formattedUptime = sprintf('%d %s %02d:%02d:%02d', $days, $daysLabel, $hours, $minutes, $seconds);

        return response()->json(['uptime' => $formattedUptime]);
    }

    public function checkPoint(Request $request)
    {
        $type = $request->type ?? 'dikerjakan';
        $inMonth = Carbon::now()->month;
        $filter = $request->filterKerjasama;

        $kerjasama = Cache::remember('admin.checkpoint.kerjasama-options', 300, function () {
            return Kerjasama::query()
                ->select('id', 'client_id')
                ->with('client:id,name')
                ->get();
        });

        $check_points_query = CheckPoint::query()
            ->select([
                'id',
                'user_id',
                'pekerjaan_cp_id',
                'type_check',
                'img',
                'deskripsi',
                'approve_status',
                'note',
                'created_at',
            ])
            ->with('user:id,nama_lengkap')
            ->whereMonth('created_at', $inMonth);

        $cex2 = (clone $check_points_query)
            ->where('type_check', $type)
            ->latest()
            ->get();

        $pcp = Cache::remember('admin.checkpoint.pekerjaan-cp', 300, function () {
            return PekerjaanCp::query()
                ->select('id', 'name', 'type_check')
                ->get();
        });

        return view('admin.old-check.index', compact('cex2', 'pcp', 'type', 'kerjasama', 'filter'));
    }

    public function lihatCheck(Request $request, $id)
    {
        $type = $request->type;
        $inMonth = Carbon::now()->month;
        $user = User::findOrFail($id);

        // Ambil data berdasarkan user_id dan bulan
        // $check_points_query = CheckPoint::where('user_id', $id)
        //     ->whereMonth('created_at', $inMonth);
        // $pekerjaan_cp_query = PekerjaanCP::where('user_id', $id);

        // Lakukan paginasi
        // $cek = (clone $check_points_query)->orderBy('created_at', 'asc')->paginate(15);

        // Ambil data berdasarkan tipe
        // $typeHarian = (clone $check_points_query)->where('type_check', 'harian')->get();
        // $typeMingguan = (clone $check_points_query)->where('type_check', 'mingguan')->get();
        // $typeBulanan = (clone $check_points_query)->where('type_check', 'bulanan')->get();
        // $typeIsi = (clone $check_points_query)->where('type_check', 'isidental')->get();

        // $pkHarian = (clone $pekerjaan_cp_query)->where('type_check', 'harian')->get();
        // $pkMingguan = (clone $pekerjaan_cp_query)->where('type_check', 'mingguan')->get();
        // $pkBulanan = (clone $pekerjaan_cp_query)->where('type_check', 'bulanan')->get();
        // $pkIsi = (clone $pekerjaan_cp_query)->where('type_check', 'isidental')->get();

        // $awalMinggu = Carbon::now()->startOfMonth()->subMonth();
        // $akhirMinggu = Carbon::now()->endOfMonth(); // Mengurangi 2 hari untuk mendapatkan hari Jumat sebagai akhir minggu
        // if ($type == 'rencana') {
        //     $cex2 = (clone $check_points_query)
        //         ->where('type_check', 'rencana')
        //         ->latest()
        //         ->first();
        // } else {
        //     $cex2 = (clone $check_points_query)
        //         ->where('type_check', 'dikerjakan')
        //         ->latest()
        //         ->first();
        // }

        // $pcp = (clone $pekerjaan_cp_query)->get();
        // dd($cex2);

        // dd($cex2);

        return view('admin.check.lihatCP', compact('user', 'type', 'cek', 'cex2', 'pcp', 'typeHarian', 'typeMingguan', 'typeBulanan', 'typeIsi', 'pkHarian', 'pkMingguan', 'pkBulanan', 'pkIsi'));
    }

    public function approveCheck(Request $request, $id)
    {
        $appCheck = [
            'approve_status' => $request->approve_status,
            'note' => $request->note
        ];
        // dd($appCheck);
        CheckPoint::findOrFail($id)->update($appCheck);
        toastr()->success('Check Point Has Approve', [], 'success');
        return redirect()->back();
    }

    public function deniedCheck(Request $request, $id)
    {
        $appCheck = [
            'approve_status' => $request->approve_status,
            'note' => $request->note
        ];
        // dd($appCheck);
        CheckPoint::findOrFail($id)->update($appCheck);
        toastr()->warning('Check Point Has Denied', [], 'success');
        return redirect()->back();
    }

    public function destroyCheck($id)
    {

        try {
            $cek = CheckPoint::findOrFail($id);
            if ($cek->img != null) {

                Storage::disk('public')->delete('images/' . $cek->img);

                $cek->delete();
                toastr()->warning('Data Telah Dihapus', [], 'warning');
                return redirect()->back();
            } else {
                toastr()->error('Foto Tidak Ditemukan', [], 'error');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            toastr()->error('Data Tidak Ditemukan', [], 'error');
            return redirect()->back();
        }
    }

    public function absen(Request $request)
    {

        // Retrieve filter values from the request
        $filter = $request->filterKerjasama;
        $filterDivisi = $request->filterDevisi;
        $searchUser = trim((string) $request->searchUser);
        $searchUserId = $request->filled('searchUserId') ? (int) $request->searchUserId : null;

        // Build the initial query
        $absenQuery = Absensi::query()
            ->select([
                'id',
                'user_id',
                'kerjasama_id',
                'shift_id',
                'tanggal_absen',
                'absensi_type_masuk',
                'absensi_type_pulang',
                'image',
                'point_id',
                'subuh',
                'dzuhur',
                'asar',
                'maghrib',
                'isya',
                'msk_lat',
                'msk_long',
                'plg_lat',
                'plg_long',
                'keterangan',
                'tipe_id',
                'terus',
                'created_at',
            ])
            ->with([
                'user:id,nama_lengkap',
                'shift:id,shift_name,jam_start,jam_end',
                'kerjasama:id,client_id',
                'kerjasama.client:id,name,panggilan',
                'tipeAbsensi:id,name',
                'point:id,client_id,sac_point',
            ])
            ->orderBy('tanggal_absen', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters if provided
        if ($filter && $filterDivisi) {
            $absenQuery = $absenQuery->where('kerjasama_id', $filter)
                ->whereHas('user', function ($query) use ($filterDivisi) {
                    $query->where('devisi_id', $filterDivisi);
                });
        } elseif ($filterDivisi != null) {
            $absenQuery = $absenQuery->whereHas('user', function ($query) use ($filterDivisi) {
                $query->where('devisi_id', $filterDivisi);
            });
        } elseif ($filter) {
            $absenQuery = $absenQuery->where('kerjasama_id', $filter);
        }

        if ($filter && $searchUserId) {
            $absenQuery = $absenQuery->where('user_id', $searchUserId);
        } elseif ($filter && $searchUser !== '') {
            $absenQuery = $absenQuery->whereHas('user', function ($query) use ($filter, $searchUser) {
                $query->where('kerjasama_id', $filter)
                    ->where(function ($userQuery) use ($searchUser) {
                        $userQuery->where('name', 'like', '%' . $searchUser . '%')
                            ->orWhere('nama_lengkap', 'like', '%' . $searchUser . '%')
                            ->orWhere('email', 'like', '%' . $searchUser . '%');
                    });
            });
        }

        // Paginate and include the filter values in the pagination links
        $absen = $absenQuery->paginate(50);
        $absen->appends([
            'filterKerjasama' => $filter,
            'filterDevisi' => $filterDivisi,
            'searchUser' => $searchUser,
            'searchUserId' => $searchUserId,
        ]);

        // Other data retrieval
        $absenSi = Cache::remember('admin.absen.kerjasama-options', 300, function () {
            return Kerjasama::query()
                ->select('id', 'client_id')
                ->with('client:id,name,panggilan')
                ->get();
        });

        $point = Cache::remember('admin.absen.point-options', 300, function () {
            return Point::query()
                ->select('id', 'client_id', 'sac_point')
                ->get();
        });

        $divisi = Cache::remember('admin.absen.divisi-options', 300, function () {
            return Divisi::query()
                ->select('id', 'name')
                ->get();
        });

        $absenBounds = Cache::remember('admin.absen.date-bounds', 300, function () {
            $min = Absensi::query()->orderBy('tanggal_absen', 'asc')->first(['created_at']);
            $max = Absensi::query()->orderBy('tanggal_absen', 'desc')->first(['created_at']);

            return [
                'min' => optional($min?->created_at)->format('Y-m-d'),
                'max' => optional($max?->created_at)?->subMonth(3)->format('Y-m-d'),
            ];
        });

        $min2 = $absenBounds['min'] ?? now()->format('Y-m-d');
        $max2 = $absenBounds['max'] ?? now()->subMonth(3)->format('Y-m-d');

        // dd($max2);

        return view('admin.absen.index', [
            'min' => $min2,
            'max' => $max2,
            'absen' => $absen,
            'filterDivisi' => $filterDivisi,
            'absenSi' => $absenSi,
            'point' => $point,
            'divisi' => $divisi,
            'filter' => $filter,
            'searchUser' => $searchUser,
            'searchUserId' => $searchUserId,
        ]);
    }

    public function searchAbsenUsers(Request $request)
    {
        $kerjasamaId = $request->filled('kerjasama_id') ? (int) $request->kerjasama_id : null;
        $query = trim((string) $request->input('q', ''));

        if (!$kerjasamaId || mb_strlen($query) < 2) {
            return response()->json([
                'message' => 'Query too short',
                'data' => [],
                'error' => ''
            ]);
        }

        $users = User::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->where(function ($builder) use ($query) {
                $builder->where('nama_lengkap', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%')
                    ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->orderByRaw('COALESCE(nama_lengkap, name) asc')
            ->limit(15)
            ->get(['id', 'name', 'nama_lengkap', 'email']);

        return response()->json([
            'message' => 'User list',
            'data' => $users,
            'error' => ''
        ]);
    }

    public function izin()
    {
        $izin = Absensi::where('keterangan', 'izin')->paginate(5);
        return view('admin.absen.izin', ['izin' => $izin]);
    }

    public function export(Request $request)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $tanggalSekarang = Carbon::now();
        $totalHari = $tanggalSekarang->daysInMonth;
        $tanggalFormat = Carbon::now()->format('Y-m-d');

        $libur = $request->input('libur');

        if ($request->has(['libur']) && $libur != null) {
            $dataAbsen = User::with([
                'absensi' => function ($query) use ($currentMonth, $currentYear) {
                    $query->whereMonth('tanggal_absen', $currentMonth)->whereYear('tanggal_absen', $currentYear);
                }
            ])->get();

            $dataUser = User::all();
            $all = Absensi::all();
            $user = Absensi::all();
            $aku = Absensi::where('keterangan', 'masuk')->get();
            $absen = Absensi::orderBy('absensi_type_masuk', 'asc')->where('keterangan', 'masuk')->get();

            $path = 'logo/sac.png';
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $options = new Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');

            $pdf = new Dompdf($options);
            $html = view('admin.absen.export', compact('absen', 'all', 'base64', 'user', 'dataUser', 'totalHari', 'dataAbsen', 'currentMonth', 'currentYear', 'libur'))->render();
            $pdf->loadHtml($html);

            $pdf->setPaper('A4', 'landscape');
            $pdf->render();

            $output = $pdf->output();
            $filename = 'absensi.pdf';

            if ($request->input('action') == 'download') {
                return response()->download($output, $filename);
            }

            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        } else {
            toastr()->error('Mohon Masukkan Hari Libur', [], 'error');
            return redirect()->back();
        }
    }

    public function exportWith(Request $request)
    {
        ini_set('max_execution_time', 1800); // 30 minutes
        ini_set('memory_limit', '1024M');    // 1GB memory
        set_time_limit(1800);                // Also 30 minutes

        $startDate = Carbon::parse($this->str);
        $endDate = Carbon::parse($this->ended);

        $currentMonth = $endDate->month;
        $currentYear = $startDate->year;
        $strYear = $startDate->year;
        $endYear = $endDate->year;

        $dailyData = Cache::remember("libur_{$strYear}_{$endYear}", now()->addHours(12), function () use ($strYear, $endYear) {
            $allDates = [];

            $years = $strYear == $endYear ? [$strYear] : [$strYear, $endYear];

            foreach ($years as $year) {
                $res = Http::get("https://dayoffapi.vercel.app/api?year={$year}");
                if ($res->successful()) {
                    foreach ($res->json() as $d) {
                        $allDates[] = $d['tanggal'];
                    }
                }
            }

            return $allDates;
        });
        // dd($dailyData);

        $tanggalSekarang = Carbon::now();

        $user = Absensi::select('created_at')->get();
        $str1 = $this->str;
        $end1 = $this->ended;
        $starte = Carbon::createFromFormat('Y-m-d', $str1);
        $ende = Carbon::createFromFormat('Y-m-d', $end1);

        $izin = Izin::whereBetween('created_at', [$str1, $end1])->get();

        $mitra = $request->input('kerjasama_id');
        $divisiId = $request->input('divisi_id');
        $libur = $request->input('libur');
        $jdwl = $request->input('jadwal');

        $totalHari = Carbon::parse($this->str)->diffInDays(Carbon::parse($this->ended));

        if ($request->has(['libur', 'end1', 'str1'])) {
            $expPDF = User::query()
                ->with([
                    'absensi' => function ($query) use ($str1, $end1) {
                        $query->whereBetween('tanggal_absen', [$str1, $end1]);
                    },
                    'jadwalUser' => function ($query) use ($str1, $end1) {
                        $query->whereBetween('created_at', [$str1, $end1]);
                    }
                ])
                ->whereHas('absensi', function ($query) use ($str1, $end1) {
                    $query->whereBetween('tanggal_absen', [$str1, $end1]);
                })
                ->when($mitra, fn($q) => $q->where('kerjasama_id', $mitra))
                ->when($divisiId, fn($q) => $q->where('devisi_id', $divisiId))
                ->orderBy('nama_lengkap', 'asc')
                ->get();


            $point = Point::all();
            $kerjasama = Kerjasama::find($mitra);
            $kantor = optional($kerjasama)->id === 1;
            $liburCount = 0;
            $hae = 0;
            $calendarHeaders = [];

            $startDate = Carbon::parse($str1);
            $endDate = Carbon::parse($end1);
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                $isHoliday = in_array($current->format('Y-m-j'), $dailyData);

                if ($current->isWeekend() || $isHoliday) {
                    $liburCount++;
                }

                if (!$current->isWeekend() && !$isHoliday) {
                    $hae++;
                }

                $calendarHeaders[] = [
                    'day' => $current->format('d'),
                    'isHoliday' => in_array($current->format('Y-m-j'), $dailyData),
                    'isWeekend' => $current->isWeekend(),
                ];

                $current->addDay();
            }
            // dd($expPDF);

            $processedUsers = [];
            $dummy = [];

            foreach ($expPDF as $user) {
                if (in_array(strtolower($user->nama_lengkap), ['admin', 'user', 'subhan santosa'])) {
                    continue;
                }

                $uid = $user->id;

                // Preload user's absensi and izin
                $userAbsensi = $user->absensi->groupBy(fn($absen) => $absen->created_at->format('Y-m-d'));
                $userIzin = $izin->where('user_id', $uid)->keyBy(fn($izin) => $izin->created_at->format('Y-m-d'));

                $rows = [];
                $totalMasuk = 0;
                $totalMasukTidakPulang = 0;
                $totalTelat = 0;
                $totalIzin = 0;
                $totalTerus = 0;
                $totalMS = 0;
                $totalST = 0;

                $current = $starte->copy();
                while ($current->lte($ende)) {
                    $dateKey = $current->format('Y-m-d');
                    $isHoliday = in_array($current->format('Y-m-j'), $dailyData);
                    $isWeekend = $current->isWeekend();

                    $absensiList = $userAbsensi->get($dateKey) ?? collect();
                    $izinItem = $userIzin->get($dateKey);


                    // Default status
                    $symbol = '-';
                    $alterSymbol = '-';
                    $hasTerus = false;
                    $mainMarked = false;

                    foreach ($absensiList as $absen) {
                        if (!$mainMarked) {
                            if ($absen->keterangan === 'masuk' && $absen->terus == null) {
                                $symbol = 'M';
                                $totalMasuk++;
                                $mainMarked = true;
                            } elseif ($absen->keterangan === 'masuk' && $absen->absensi_type_pulang == null) {
                                $symbol = 'TP';
                                $totalMasukTidakPulang++;
                                $mainMarked = true;
                            } elseif ($absen->keterangan === 'telat' && $absen->terus == null) {
                                $symbol = 'T';
                                $totalTelat++;
                                $mainMarked = true;
                            } elseif ($absen->tukar) {
                                $symbol = 'MS';
                                $totalMS++;
                                $mainMarked = true;
                            } elseif ($absen->tukar_id === $uid) {
                                $symbol = 'ST';
                                $totalST++;
                                $mainMarked = true;
                            }
                        }

                        if ($absen->terus) {
                            if ($absen->keterangan === 'masuk') {
                                $alterSymbol = 'N';
                            } elseif ($absen->keterangan === 'telat') {
                                $alterSymbol = 'NT';
                            }
                            $hasTerus = true;
                        } elseif ($isHoliday || $isWeekend) {
                            $alterSymbol = '//';
                        }
                    }

                    if (!$mainMarked && $izinItem && $izinItem->approve_status === 'accept') {
                        $symbol = 'I';
                        $totalIzin++;
                        $mainMarked = true;
                    }

                    if (!$mainMarked && ($isHoliday || $isWeekend)) {
                        $symbol = '//';
                        $alterSymbol = '//';
                    }

                    if ($hasTerus)
                        $totalTerus++;

                    $rows[] = [
                        'date' => $dateKey,
                        'symbol' => $symbol,
                        'alterSymbol' => $alterSymbol,
                        'isHoliday' => $isHoliday,
                        'isWeekend' => $isWeekend,
                    ];

                    $current->addDay();
                    // $dummy[] = $absen;
                }


                $totalHariKerja = $kantor ? $hae - $libur : $totalHari - $libur + 1;

                $totalMasukAdjusted = $kantor ? $totalMasuk : $totalMasuk + $totalTerus;
                $tesPer = $totalHariKerja > 0 ? round(($totalMasukAdjusted + $totalMasukTidakPulang) / $totalHariKerja * 100) : 0;

                // $totalMasukAdjusted = $kantor ? $totalMasuk : $totalMasuk + $totalTerus;
                // $totalMinusAdjusted = $totalHariKerja > 0 ? ((($totalTelat + $totalMasukTidakPulang) / $totalHariKerja * 100) / 2) : 0;
                // $tesPer = $totalHariKerja > 0 ? round(($totalMasukAdjusted / $totalHariKerja * 100) + $totalMinusAdjusted) : 0;

                $tesPer = min($tesPer, 100);

                $processedUsers[] = [
                    'user' => $user,
                    'rows' => $rows,
                    'm' => $totalMasuk,
                    'mt' => $totalMasukTidakPulang,
                    't' => $totalTelat,
                    'z' => $totalIzin,
                    'terus' => $totalTerus,
                    'ms' => $totalMS,
                    'st' => $totalST,
                    'percentage' => $tesPer,
                    'totalHariKerja' => $totalHariKerja,
                    'totalPoints' => $user->absensi->whereNotNull('point_id')->sum(fn($p) => (int) optional($p->point)->sac_point),
                ];
            }

            // dd($dummy);

            $logoPath = public_path('logo/sac.png');
            $base64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath));

            $options = new Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');

            $pdf = new Dompdf($options);
            $html = view('admin.absen.exportV2', compact('point', 'starte', 'ende', 'dailyData', 'expPDF', 'izin', 'jdwl', 'base64', 'totalHari', 'user', 'currentYear', 'currentMonth', 'libur', 'str1', 'end1', 'mitra', 'kerjasama', 'kantor', 'liburCount', 'hae', 'calendarHeaders', 'processedUsers'))->render();
            $pdf->loadHtml($html);

            $pdf->setPaper('A4', 'landscape');
            $pdf->render();

            $output = $pdf->output();
            $filename = 'Absensi_Penempatan ' . $kerjasama?->client?->name . '_' . $str1 . '-' . $end1 . '.pdf';

            if ($request->input('action') == 'download') {
                return response()->download($output, $filename);
            }

            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        } else {
            toastr()->error('Mohon Masukkan Filter Export', [], 'error');
            return redirect()->back();
        }
    }

    public function exp(Request $request)
    {
        $all = Absensi::all();
        $kerjasamaId = $request->kerjasama_id;
        $kerjasama = Kerjasama::firstWhere('id', $kerjasamaId);
        $izin = Izin::orderBy('updated_at', 'desc')->where('kerjasama_id', $kerjasamaId)->where('approve_status', 'accept')->get();
        // dd($izin);

        $path = 'logo/sac.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $options = new Options();
        $options->setIsHtml5ParserEnabled(true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $pdf = new Dompdf($options);
        $html = view('admin.absen.export-izin', compact('izin', 'all', 'base64', 'kerjasama'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $output = $pdf->output();
        $filename = 'absensi-izin.pdf';

        if ($request->input('action') == 'download') {
            return response()->download($output, $filename);
        }

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function exportCheck()
    {
        // dd($check);
        $arr = User::all();
        return view('admin.user.test', compact('arr'));
    }

    public function prosesExport(Request $request)
    {
        $check = $request->input('check');
        $checkAll = $request->input('check_all');
        $exportType = $request->input('export_type');

        // dd($request->all());
        $temps = TempUser::all();
        $users = User::whereIn('id', $check)->get(); // fetch all needed users at once

        foreach ($temps as $temp) {
            // Clean JSON
            $json = trim($temp->data, "\" \n\r\t");
            $json = preg_replace('/^"+\s*{/', '{', $json);
            $json = preg_replace('/}\s*"+$/', '}', $json);

            $decoded = json_decode($json, true);
            if (!is_array($decoded)) {
                continue;
            }

            $username = $decoded['username'] ?? null;

            if ($username) {
                // Cari user yang cocok di koleksi $users
                $user = $users->firstWhere('name', $username);

                if ($user) {
                    // Masukkan data dari JSON (misal 'password') ke field temporary di objek $user
                    // Kita beri nama field-nya 'pw_temp' agar tidak bentrok dengan attribute asli
                    $user->pw_temp = $decoded['pw'] ?? null;

                    // Jika Anda juga butuh data lain dari JSON masuk ke objek user:
                    $user->temporary_data = $decoded;
                }
            }
        }

        if ($exportType == 'delete') {
            // dd($check, $request->all());
            User::destroy($check);

            toastr()->success('User berhasil dihapus', [], 'success');
            return redirect()->back();
        } else {
            $options = new Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');

            $pdf = new Dompdf($options);
            if ($exportType === 'data') {
                // Logic for exporting data
                $html = view('admin.user.export-user', [
                    'data' => $users,
                    'exportType' => $exportType,
                ])->render();
            } elseif ($exportType === 'id_card') {
                // Logic for exporting ID cards
                $html = view('admin.user.export-card', [
                    'data' => $users,
                    'exportType' => $exportType,
                ])->render();
            }
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'landscape');
            $pdf->render();

            $output = $pdf->output();
            $filename = 'user.pdf';

            if ($request->input('action') == 'download') {
                return response()->download($output, $filename);
            }

            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        }
    }

    public function hapusFotoAbsen(Request $request)
    {
        $mulai = $request->mulai;
        $selesai = $request->selesai;

        $absen = Absensi::whereBetween('tanggal_absen', [$mulai, $selesai])->get();
        // dd($mulai, $selesai, $absen);

        foreach ($absen as $abs) {
            if ($abs->image != null) {
                Storage::disk('public')->delete('images/' . $abs->image);
            }
            $abs->delete();
        }

        toastr()->warning('Data Sudah Dihapus', [], 'success');
        return redirect()->back();
    }

    public function checkUserRelations(int $id)
    {
        $user = User::findOrFail($id);

        // sacpocom_absensi
        $absensiTables = ['absensis', 'lemburs', 'izins', 'laporans', 'overtimes', 'finished_trainings', 'performance_cuts'];
        $absensiCount = 0;
        foreach ($absensiTables as $t) {
            $absensiCount += DB::connection('mysql')->table($t)->where('user_id', $id)->count();
        }

        // sacpocom_edata — employes match by user_id OR name == nama_lengkap
        $employesByUserId = DB::connection('mysql2')->table('employes')
            ->where('user_id', $id)->count();
        $employesByName = DB::connection('mysql2')->table('employes')
            ->where('name', $user->nama_lengkap)->count();

        // sacpocom_edata — slip_gajis by user_id
        $slipGajiCount = DB::connection('mysql2')->table('slip_gajis')
            ->where('user_id', $id)->count();

        // sacpocom_rekap — semua tabel yang pakai user_id
        $rekapTables = [
            'findings',
            'fixed_images',
            'upload_images',
            'upload_tambahans',
            'user_settings',
            'pending_syncs',
        ];
        $rekapCounts = [];
        foreach ($rekapTables as $table) {
            $count = DB::connection('mysql3')->table($table)
                ->where('user_id', $id)->count();
            if ($count > 0) {
                $rekapCounts[$table] = $count;
            }
        }

        return response()->json([
            'user'             => ['id' => $user->id, 'name' => $user->name, 'nama_lengkap' => $user->nama_lengkap],
            'absensi'          => $absensiCount,
            'employes_user_id' => $employesByUserId,
            'employes_by_name' => $employesByName,
            'slip_gaji'        => $slipGajiCount,
            'rekap'            => $rekapCounts,
            'rekap_total'      => array_sum($rekapCounts),
        ]);
    }

    public function hardDeleteUser(int $id)
    {
        $user = User::findOrFail($id);
        $namaLengkap = $user->nama_lengkap;
        $userName    = $user->name;

        // Cross-DB: tiap connection punya transaction sendiri.
        // Urutan: relasi jauh dulu → user utama terakhir.
        // Kalau mysql3/mysql2 gagal → mysql (absensi + user) tidak disentuh → data konsisten.
        // Kalau mysql2 sukses tapi mysql gagal → mysql2 sudah committed (tidak bisa rollback lintas DB).
        // ponytail: untuk atomic cross-DB pakai saga/outbox pattern.

        try {
            // 1. sacpocom_rekap
            DB::connection('mysql3')->transaction(function () use ($id) {
                foreach (['user_settings', 'pending_syncs', 'findings', 'upload_tambahans', 'upload_images', 'fixed_images'] as $table) {
                    DB::connection('mysql3')->table($table)->where('user_id', $id)->delete();
                }
            });

            // 2. sacpocom_edata
            DB::connection('mysql2')->transaction(function () use ($id, $namaLengkap) {
                DB::connection('mysql2')->table('employes')
                    ->where('user_id', $id)
                    ->orWhere('name', $namaLengkap)
                    ->delete();
                DB::connection('mysql2')->table('slip_gajis')
                    ->where('user_id', $id)
                    ->orWhere('karyawan', $namaLengkap)
                    ->delete();
                DB::connection('mysql2')->table('p_g_j__kontraks')
                    ->where('nama_pk_kda', $namaLengkap)
                    ->delete();
            });

            // 3. sacpocom_absensi + relasi + user utama
            DB::connection('mysql')->transaction(function () use ($id, $user) {
                foreach (['lemburs', 'izins', 'laporans', 'overtimes', 'finished_trainings', 'performance_cuts'] as $table) {
                    DB::connection('mysql')->table($table)->where('user_id', $id)->delete();
                }
                DB::connection('mysql')->table('absensis')->where('user_id', $id)->delete();
                $user->delete();
            });
        } catch (\Throwable $e) {
            toastr()->error('Gagal menghapus: ' . $e->getMessage(), [], 'Error');
            return redirect()->back();
        }

        Cache::forget('not_active_users');
        Cache::forget('admin.dashboard.inactive-users-count');

        toastr()->warning('User ' . $userName . ' dan semua data terkait berhasil dihapus.', [], 'Dihapus');
        return redirect()->route('admin.index');
    }

    public function indexSlip(Request $request)
    {
        $bulan = $request->bulan;
        $mitra = Kerjasama::all();

        $penempatan = $request->penempatan;
        if ($penempatan == 'semua') {
            $user = User::pluck('id');
        } else if ($penempatan) {
            $user = User::where('kerjasama_id', $penempatan)->pluck('id');
        } else {
            $user = User::pluck('id');
        }

        $slip = SlipGaji::on('mysql2')->whereIn('user_id', $user)->where('bulan_tahun', $bulan ? $bulan : Carbon::now()->subMonth()->format('Y-m'))->orderby('karyawan', 'asc')->get();
        // dd(count($slip));

        return view('admin.slip.index', compact('slip', 'bulan', 'mitra', 'penempatan'));
    }
}
