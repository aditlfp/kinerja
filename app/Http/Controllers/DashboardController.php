<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\Lokasi;
use App\Models\Izin;
use App\Models\CheckPoint;
use App\Models\Kontrak;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Simpan waktu sekarang dan user dalam variabel
        $now = Carbon::now();
        $today = $now->toDateString();
        $dayStart = $now->copy()->startOfDay();
        $dayEnd = $now->copy()->endOfDay();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $user = Auth::user()->loadMissing(['divisi.jabatan']);

        $kontrak = Kontrak::where('nama_pk_kda', auth()->user()->nama_lengkap)->latest()->first();

        if (! $user->relationLoaded('jabatan') && $user->divisi?->jabatan && $user->jabatan_id === $user->divisi->jabatan->id) {
            $user->setRelation('jabatan', $user->divisi->jabatan);
        }

        // Hitung news yang masih berlaku dan dipilih pada tanggal hari ini
        $hitungNews = News::query()
            ->where('tanggal_lihat', '<=', $today)
            ->where('tanggal_tutup', '>=', $today)
            ->whereJsonContains('tanggal_muncul', (int) $now->day)
            ->get();


        // Ambil data lembur dengan sorting berdasarkan 'jam_selesai'
        // $lembur = Lembur::latest('jam_selesai')->get();

        // Buat dasar query absensi untuk user
        $absenQueryBase = Absensi::query()
            ->where('user_id', $user->id);

        // Ambil data absensi pada rentang waktu dari kemarin hingga hari ini
        // Fix: ambil absen terakhir yang belum pulang dalam 24 jam terakhir
        // Untuk shift overnight, tetap bisa kebaca di esok hari
        $absenP = (clone $absenQueryBase)
            ->with([
                'shift:id,jam_start,jam_end,is_overnight',
                'kerjasama:id,client_id',
            ])
            ->whereNull('absensi_type_pulang')
            ->where('created_at', '>=', $now->copy()->subHours(16))
            ->latest('id')
            ->first();

        // Filter lagi: jika shift overnight dan sudah lewat batas pulang + 2 jam, anggap expired
        if ($absenP && $absenP->shift && $absenP->shift->is_overnight) {
            // Shift overnight tetap valid sampai jam_end + 2 jam di hari berikutnya
        }

        $shouldTrackPulang = $absenP &&
            $absenP->user_id == $user->id &&
            is_null($absenP->absensi_type_pulang);

        $clientIdPulang = $absenP?->kerjasama?->client_id;

        $lokasiMitra = $shouldTrackPulang && $clientIdPulang
            ? Lokasi::query()
            ->where('client_id', $clientIdPulang)
            ->get(['id', 'client_id', 'latitude', 'longtitude', 'radius'])
            : collect();

        // Filter absensi untuk mengambil yang memiliki tipe "Tidak Absen Pulang"
        // dan terjadi di bulan berjalan.
        $warn = (clone $absenQueryBase)
            ->whereNull('absensi_type_pulang')
            ->whereBetween('tanggal_absen', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['id', 'absensi_type_pulang', 'tanggal_absen'])
            ->filter(function ($item) use ($now) {
                return $item->absensi_type_pulang === ''
                    && $item->tanggal_absen->month === $now->month;
            });

        $cekAbsen = (clone $absenQueryBase)
            ->whereNull('absensi_type_pulang')
            ->where('tanggal_absen', $today)
            ->get(['id', 'tanggal_absen']);

        // Ambil data izin untuk user terkait
        $izin = Izin::query()
            ->where('user_id', $user->id)
            ->whereBetween('updated_at', [$dayStart, $dayEnd])
            ->latest('id')
            ->first();
        if ($izin) {
            $status = $izin->approve_status;
        } else {
            $status = '';
        }
        $statusClass = $status == 'process' ? 'bg-yellow-500' : ($status == 'accept' ? 'bg-green-500' : 'bg-red-500');
        $statusMessage = $status == 'process' ? 'Izin Masih Dalam Proses !!' : ($status == 'accept' ? 'Izin Sudah Disetujui !!' : 'Izin Anda Ditolak !!');

        // Tentukan rentang tanggal untuk pengecekan CheckPoint
        $awalMinggu = $now->copy()->subWeek()->startOfWeek()->addDays(5);
        $akhirMinggu = $now->copy()->endOfWeek()->subDays(2);

        // Ambil CheckPoint dengan tipe 'rencana'
        $cpQuery = CheckPoint::whereBetween('created_at', [$awalMinggu, $akhirMinggu])
            ->where('user_id', $user->id);

        $cex = (clone $cpQuery)->where('type_check', 'rencana')
            ->latest('id')
            ->first();

        // Ambil CheckPoint dengan tipe 'dikerjakan'
        $cex2 = (clone $cpQuery)->where('type_check', 'dikerjakan')
            ->latest('id')
            ->first();

        // Ambil CheckPoint tipe 'dikerjakan' untuk bulan berjalan
        $startOfMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $missingCheckpointDates = collect();
        if ($user->kerjasama_id == 1 && $now->isFriday()) {
            $weekStart = $now->copy()->startOfWeek();
            $dates = collect();
            for ($date = $weekStart->copy(); $date->lte($now); $date->addDay()) {
                if ($date->isWeekday()) $dates->push($date->copy());
            }
            $filledDates = CheckPoint::where('user_id', $user->id)
                ->where(function ($query) use ($weekStart, $now) {
                    $query->whereBetween('created_at', [$weekStart, $now])
                        ->orWhereJsonContains('tanggal', $weekStart->toDateString());
                })
                ->get(['tanggal', 'created_at'])
                ->flatMap(fn($checkpoint) => collect((array) $checkpoint->tanggal)->map(fn($date) => Carbon::parse($date)->toDateString()))
                ->merge(CheckPoint::where('user_id', $user->id)->whereBetween('created_at', [$weekStart, $now])->pluck('created_at')->map->toDateString())
                ->unique();
            $missingCheckpointDates = $dates->reject(fn($date) => $filledDates->contains($date->toDateString()));
        }

        $pendingDireksiCheckpoints = collect();
        if (($user->jabatan?->code_jabatan ?? $user->divisi?->jabatan?->code_jabatan) === 'DIREKSI') {
            $pendingDireksiCheckpoints = CheckPoint::with('user:id,nama_lengkap')
                ->where('type_check', 'dikerjakan')
                ->whereYear('created_at', $now->year)
                ->latest('id')->get()
                ->flatMap(function (CheckPoint $checkpoint) {
                    $statuses = is_array($checkpoint->approve_status) ? $checkpoint->approve_status : json_decode($checkpoint->approve_status ?: '[]', true);
                    if (!is_array($statuses)) return [];
                    $jobs = is_array($checkpoint->pekerjaan_cp_id) ? $checkpoint->pekerjaan_cp_id : [];
                    $manual = is_array($checkpoint->input_manual) ? $checkpoint->input_manual : [];
                    return collect($statuses)->map(function ($status, $index) use ($checkpoint, $jobs, $manual) {
                        if ($status !== 'proccess') return null;
                        return (object) ['id' => $checkpoint->id, 'employee' => $checkpoint->user->nama_lengkap ?? '-', 'date' => data_get((array) $checkpoint->tanggal, $index, optional($checkpoint->created_at)->format('Y-m-d')), 'job' => $jobs[$index] ?? ($manual[$index] ?? 'Pekerjaan')];
                    })->filter();
                })->groupBy('employee')->map(function ($items) {
                    $month = Carbon::parse($items->first()->date)->translatedFormat('F Y');
                    return (object) [
                        'id' => $items->first()->id,
                        'employee' => $items->first()->employee,
                        'dates' => $items->map(fn ($item) => Carbon::parse($item->date)->day)->unique()->sort()->values()->implode(', '),
                        'month' => $month,
                    ];
                })->values();
        }

        $rejectedCheckpoints = CheckPoint::where('user_id', $user->id)
            ->latest('id')
            ->get(['id', 'tanggal', 'created_at', 'approve_status', 'note', 'pekerjaan_cp_id', 'input_manual'])
            ->flatMap(function (CheckPoint $checkpoint) {
                $statuses = is_array($checkpoint->approve_status) ? $checkpoint->approve_status : json_decode($checkpoint->approve_status ?: '[]', true);
                if (!is_array($statuses)) return [];
                $notes = is_array($checkpoint->note) ? $checkpoint->note : json_decode($checkpoint->note ?: '[]', true);
                $notes = is_array($notes) ? $notes : [];
                $jobs = is_array($checkpoint->pekerjaan_cp_id) ? $checkpoint->pekerjaan_cp_id : [];
                return collect($statuses)->map(function ($status, $index) use ($checkpoint, $notes, $jobs) {
                    if ($status !== 'denied') return null;
                    return (object) [
                        'id' => $checkpoint->id,
                        'date' => data_get((array) $checkpoint->tanggal, $index, optional($checkpoint->created_at)->format('Y-m-d')),
                        'reason' => $notes[$index] ?? 'Bukti pekerjaan perlu diperbaiki.',
                        'job' => $jobs[$index] ?? null,
                    ];
                })->filter();
            });

        $totcex = CheckPoint::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('type_check', 'dikerjakan')
            ->count();

        $luweh1Dino = false;
        if ($absenP && $absenP->shift) {
            if ($absenP->shift->is_overnight) {
                // Untuk shift lintas hari, hitung dari jam selesai shift kemarin
                $shiftEndTime = Carbon::createFromFormat('Y-m-d H:i', $absenP->created_at->format('Y-m-d') . ' ' . $absenP->shift->jam_end)
                    ->addDay(); // Tambah satu hari karena shiftnya lintas hari
                $luweh1Dino = $shiftEndTime->diffInHours(Carbon::now()) <= 4;
            } else {
                // Untuk shift reguler, hitung dari waktu absen dibuat
                $luweh1Dino = Carbon::createFromFormat('Y-m-d, H:i:s', $absenP->created_at->format('Y-m-d, H:i:s'))
                    ->diffInHours(Carbon::now()) <= 20;
            }
        }

        // data untuk dashboard mitra
        if ($user->jabatan->code_jabatan == "MITRA") {
            $jumlahKaryawan = User::where('kerjasama_id', $user->kerjasama_id)->count();
            $jumlahAbsensiHariIni = Absensi::whereHas('user', function ($query) use ($user) {
                $query->where('kerjasama_id', $user->kerjasama_id);
            })->whereBetween('created_at', [$dayStart, $dayEnd])->count();
            $jumlahIzinHariIni = Izin::whereHas('user', function ($query) use ($user) {
                $query->where('kerjasama_id', $user->kerjasama_id);
            })->whereBetween('updated_at', [$dayStart, $dayEnd])->count();
            $jumlahLemburHariIni = Lembur::whereHas('user', function ($query) use ($user) {
                $query->where('kerjasama_id', $user->kerjasama_id);
            })->whereBetween('created_at', [$dayStart, $dayEnd])->count();

            return view('mitra_view.index', compact(
                'jumlahKaryawan',
                'jumlahAbsensiHariIni',
                'jumlahIzinHariIni',
                'jumlahLemburHariIni'
            ));
        } else {
            return view('dashboard', compact(
                'absenP',
                'user',
                'izin',
                'hitungNews',
                'cekAbsen',
                'cex',
                'cex2',
                'totcex',
                'lokasiMitra',
                'warn',
                'luweh1Dino',
                'shouldTrackPulang',
                'statusClass',
                'statusMessage',
                'kontrak',
                'missingCheckpointDates',
                'rejectedCheckpoints',
                'pendingDireksiCheckpoints'
            ));
        }
    }

    public function sendTestEmail()
    {
        return;
    }

    public function waktuSholat(Request $request)
    {
        $lat = $request->input('lat');
        $lon = $request->input('lng');

        $user = Auth::user();
        $now = Carbon::now();
        $absenQueryBase = Absensi::with(['user', 'shift', 'kerjasama', 'tipeAbsensi'])
            ->where('user_id', $user->id)
            ->whereNull('absensi_type_pulang')
            ->where('created_at', '>=', $now->copy()->subHours(24))
            ->latest()
            ->first();
        if (!$absenQueryBase) {
            return response()->json(['sholat_sekarang' => 'kosong']);
        }

        $statusSholat = $absenQueryBase->status_sholat;

        $date = Carbon::now()->format('d-m-Y');

        $response = Http::withHeaders([
            'Accept-Encoding' => ''
        ])->get("https://api.aladhan.com/v1/timings/{$date}", [
            'latitude'                 => $lat,
            'longitude'                => $lon,
            'method'                   => '3',
            'shafaq'                   => 'general',
            'tune'                     => '5,3,5,7,9,-1,0,8,-6', // %2C otomatis di-encode oleh Laravel menjadi koma
            'school'                   => '0',
            'midnightMode'             => '0',
            'timezonestring'           => '', // Kosongkan agar API mendeteksi timezone otomatis berdasarkan koordinat GPS user
            'latitudeAdjustmentMethod' => '1',
            'calendarMethod'           => 'UAQ',
            'iso8601'                  => 'false'
        ]);

        if ($response->successful()) {
            // 1. Ambil data timings dan timezone dari response API
            $timings = $response->json('data.timings');
            $timezone = $response->json('data.meta.timezone');

            // 2. Ambil waktu sekarang berdasarkan timezone lokasi user
            $now = Carbon::now($timezone);

            // 3. Konversi string jam sholat menjadi objek Carbon penuh
            $subuh   = Carbon::createFromFormat('H:i', $timings['Fajr'], $timezone);
            $zuhur   = Carbon::createFromFormat('H:i', $timings['Dhuhr'], $timezone);
            $ashar   = Carbon::createFromFormat('H:i', $timings['Asr'], $timezone);
            $maghrib = Carbon::createFromFormat('H:i', $timings['Maghrib'], $timezone);
            $isya    = Carbon::createFromFormat('H:i', $timings['Isha'], $timezone);

            // 4. Logika penentuan nama waktu sholat dengan gap 15 menit sebelum waktu berikutnya
            $subuhMulai   = $subuh->copy()->subMinutes(10);
            $subuhSelesai = $subuh->copy()->addMinutes(90);

            $zuhurMulai   = $zuhur->copy()->subMinutes(10);
            $zuhurSelesai = $zuhur->copy()->addMinutes(90);

            $asharMulai   = $ashar->copy()->subMinutes(10);
            $asharSelesai = $ashar->copy()->addMinutes(90);

            $maghribMulai   = $maghrib->copy()->subMinutes(10);
            $maghribSelesai = $maghrib->copy()->addMinutes(45);

            $isyaMulai   = $isya->copy()->subMinutes(10);
            $isyaSelesai = $isya->copy()->addMinutes(120);

            $sholatSekarang = null;

            if ($now->between($subuhMulai, $subuhSelesai) && $statusSholat->Subuh == 0) {
                $sholatSekarang = 'Subuh';
            } elseif ($now->between($zuhurMulai, $zuhurSelesai) && $statusSholat->Zuhur == 0) {
                $sholatSekarang = 'Zuhur';
            } elseif ($now->between($asharMulai, $asharSelesai) && $statusSholat->Ashar == 0) {
                $sholatSekarang = 'Ashar';
            } elseif ($now->between($maghribMulai, $maghribSelesai) && $statusSholat->Maghrib == 0) {
                $sholatSekarang = 'Maghrib';
            } elseif ($now->between($isyaMulai, $isyaSelesai) && $statusSholat->Isya == 0) {
                $sholatSekarang = 'Isya';
            }

            // 5. Hanya mengembalikan nama waktu sholat saja
            return response()->json([
                'sholat_sekarang' => $sholatSekarang ?? 'kosong'
            ]);
        }

        return response()->json($response->json(), $response->status());
    }
}
