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
use App\Services\PerformanceRecapService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request, PerformanceRecapService $recapService)
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
        $hitungNews = News::query()->tampilPada($now)->get();


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
                        ->orWhereHas('items', fn ($q) => $q->whereBetween('tanggal', [$weekStart->toDateString(), $now->toDateString()]));
                })
                ->with('items:id,check_point_id,tanggal')
                ->get(['id', 'created_at'])
                ->flatMap(fn ($checkpoint) => $checkpoint->items->pluck('tanggal')->filter()->map(fn ($date) => Carbon::parse($date)->toDateString()))
                ->merge(CheckPoint::where('user_id', $user->id)->whereBetween('created_at', [$weekStart, $now])->pluck('created_at')->map->toDateString())
                ->unique();
            $missingCheckpointDates = $dates->reject(fn($date) => $filledDates->contains($date->toDateString()));
        }

        $isDireksi = ($user->jabatan?->code_jabatan ?? $user->divisi?->jabatan?->code_jabatan) === 'DIREKSI';
        $direksiRecapAvailable = $isDireksi && $recapService->isVisible($now);

        $pendingDireksiCheckpoints = collect();
        if ($isDireksi) {
            $pendingDireksiCheckpoints = CheckPoint::with(['user:id,nama_lengkap', 'items:id,check_point_id,tanggal,approve_status,pekerjaan_cp_id,input_manual'])
                ->where('type_check', 'dikerjakan')
                ->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])
                ->latest('id')->get()
                ->flatMap(function (CheckPoint $checkpoint) {
                    return $checkpoint->items
                        ->where('approve_status', 'proccess')
                        ->map(function ($item) use ($checkpoint) {
                            return (object) [
                                'id' => $checkpoint->id,
                                'employee' => $checkpoint->user->nama_lengkap ?? '-',
                                'date' => $item->tanggal?->format('Y-m-d') ?? optional($checkpoint->created_at)->format('Y-m-d'),
                                'job' => $item->pekerjaan_cp_id ?? ($item->input_manual ?? 'Pekerjaan'),
                            ];
                        });
                })->groupBy('employee')->map(function ($items) {
                    $month = Carbon::parse($items->first()->date)->translatedFormat('F Y');
                    return (object) [
                        'id' => $items->first()->id,
                        'employee' => $items->first()->employee,
                        'dates' => $items->map(fn($item) => Carbon::parse($item->date)->day)->unique()->sort()->values()->implode(', '),
                        'month' => $month,
                    ];
                })->values();
        }

        $rejectedCheckpoints = CheckPoint::with('items:id,check_point_id,tanggal,approve_status,note,pekerjaan_cp_id,input_manual')
            ->where('user_id', $user->id)
            ->latest('id')
            ->get(['id', 'created_at'])
            ->flatMap(function (CheckPoint $checkpoint) {
                return $checkpoint->items
                    ->where('approve_status', 'denied')
                    ->map(function ($item) use ($checkpoint) {
                        return (object) [
                            'id' => $checkpoint->id,
                            'date' => $item->tanggal?->format('Y-m-d') ?? optional($checkpoint->created_at)->format('Y-m-d'),
                            'reason' => $item->note ?? 'Bukti pekerjaan perlu diperbaiki.',
                            'job' => $item->pekerjaan_cp_id ?? $item->input_manual,
                        ];
                    });
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
        if (($user->jabatan?->code_jabatan ?? $user->divisi?->jabatan?->code_jabatan) === 'MITRA') {
            return view('mitra_view.index', ['mitra' => $this->mitraDashboardData($user)]);
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
                'pendingDireksiCheckpoints',
                'direksiRecapAvailable'
            ))->with($this->dashboardViewContext($user, $kontrak));
        }
    }

    private function dashboardViewContext(User $user, ?Kontrak $kontrak = null): array
    {
        $jabatan = $user->jabatan;
        $jabatanCode = $jabatan?->code_jabatan;
        $jabatanId = $user->jabatan_id;
        $isDireksi = $jabatanId === 24;
        $isSPVW = $jabatanCode === 'SPV-W';
        $isManajemen = $user->kerjasama_id === 1;
        $isAdmin = $user->role_id === 2;
        $isSPV = in_array($jabatanId, [4, 14], true);
        $isCoCs = $jabatanCode === 'CO-CS';
        $isCoScr = $jabatanCode === 'CO-SCR';
        $isLeader = $jabatanCode === 'LEADER';
        $isMitra = $jabatanCode === 'MITRA';
        $kerjasamaId = $user->kerjasama_id;
        $directJabatan = $user->jabatan?->code_jabatan;
        $hariIni = now()->dayOfWeekIso;
        $tampilkanAbsensi = !in_array($hariIni, [6, 7], true) || !$isManajemen || $user->devisi_id != 26;
        $bisaLihatMenuManajemen = $isSPV;
        $bisaLihatMenuSlip = in_array($user->id, [7, 10, 5], true);

        $bisaLihatRekap = in_array($jabatanCode, ['CO-CS', 'CO-SCR', 'SPV-W'], true) && $user->id != 7;
        $rekapRoute = $isSPVW ? 'spvw.rekap.index' : 'index.rekap.data.leader';
        $absensiRoute = match ($jabatanCode) {
            'CO-CS' => 'absensi-karyawan-co-cs.index',
            'CO-SCR' => 'absensi-karyawan-co-scr.index',
            default => null,
        };

        $routes = [
            'MITRA' => ['karyawan' => 'mitra_user', 'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'mitra_jadwal', 'absensi' => 'mitra_absensi', 'izin' => 'mitra_izin', 'lembur' => 'mitra_lembur', 'laporan' => 'mitra_laporan', 'rating' => 'mitra-rating.index', 'laporan bulanan' => 'mitra-laporan-bulanan.index'],
            'LEADER' => ['karyawan' => 'lead_user', 'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'leader-jadwal.index', 'absensi' => 'lead_absensi', 'izin' => 'lead_izin', 'lembur' => 'lead_lembur', 'laporan' => 'lead_laporan', 'rating' => 'leader-rating.index'],
            'DIREKSI' => ['karyawan' => 'direksi_user', 'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'direksi_jadwal', 'absensi' => 'direksi_absensi', 'izin' => 'direksi_izin', 'lembur' => 'direksi_lembur', 'laporan' => 'direksi_laporan', 'rating' => 'direksi-rating.index', 'kinerja' => 'direksi.cp.index', 'kontrak' => 'direksi-cekKontrak'],
        ];
        $icons = ['karyawan' => 'ri-pass-pending-line', 'jadwal' => 'ri-calendar-check-line', 'absensi' => 'ri-todo-line', 'izin' => 'ri-shield-user-line', 'lembur' => 'ri-time-line', 'laporan' => 'ri-image-add-line', 'laporan bulanan' => 'ri-image-add-line', 'rating' => 'ri-sparkling-line', 'kinerja' => 'ri-sparkling-line', 'kontrak' => 'ri-pass-pending-line'];
        $kontrakSelesai = $kontrak?->tgl_selesai_kontrak && \Carbon\Carbon::parse($kontrak->tgl_selesai_kontrak)->isPast();
        $kontrakBerjalan = $kontrak && ($kontrak->isPending() || $kontrak->isActive() || $kontrak->isProses());
        $bisaAjukanKontrak = !$kontrak || $kontrakSelesai || !$kontrakBerjalan;

        $dashboardMenus = collect([
            ['key' => 'kehadiran', 'label' => 'Kehadiran', 'route' => 'absensi.index', 'icon' => 'ri-calendar-line', 'color' => 'emerald'],
            ['key' => 'data-rekap', 'label' => 'Data Rekap', 'route' => $rekapRoute, 'icon' => 'ri-database-line', 'color' => 'indigo', 'show' => $bisaLihatRekap],
            ['key' => 'izin', 'label' => 'Izin', 'route' => 'izin.create', 'icon' => 'ri-file-line', 'color' => 'blue'],
            ['key' => 'kinerja', 'label' => 'Kinerja', 'route' => 'checkpoint-user.index', 'icon' => 'ri-bar-chart-line', 'color' => 'amber', 'show' => !$isDireksi || $isManajemen],
            ['key' => 'rating', 'label' => 'Rating', 'route' => 'ratingSaya', 'params' => [$user->id], 'icon' => 'ri-star-line', 'color' => 'indigo', 'show' => !$isDireksi],
            ['key' => 'laporan', 'label' => 'Laporan', 'route' => 'laporan.index', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => !$isDireksi],
            ['key' => 'kontrak', 'label' => 'Pengajuan Kontrak', 'route' => 'form-kontrak-request', 'icon' => 'ri-file-add-line', 'color' => 'green', 'show' => $bisaAjukanKontrak && !$isDireksi],
            ['key' => 'alquran', 'label' => "Al Qur'an", 'url' => 'https://baca-alquran.sac-po.com', 'icon' => 'ri-book-line', 'color' => 'amber'],
            ['key' => 'sppd', 'label' => 'SPPD', 'url' => 'https://sppd-online.sac-po.com/login', 'icon' => 'ri-road-map-line', 'color' => 'sky', 'show' => $isManajemen],
        ])->filter(fn ($menu) => $menu['show'] ?? true)->values();

        $dashboardAddMenus = collect([
            ['key' => 'karyawan-direksi', 'label' => 'Karyawan', 'route' => 'direksi_user', 'icon' => 'ri-team-line', 'color' => 'blue', 'show' => $isDireksi],
            ['key' => 'absensi-direksi', 'label' => 'Absensi', 'route' => 'direksi_absensi', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isDireksi],
            ['key' => 'izin-direksi', 'label' => 'Izin', 'route' => 'direksi_izin', 'icon' => 'ri-shield-user-line', 'color' => 'blue', 'show' => $isDireksi],
            ['key' => 'kinerja-direksi', 'label' => 'Kinerja', 'route' => 'direksi.cp.index', 'icon' => 'ri-bar-chart-line', 'color' => 'amber', 'show' => $isDireksi],
            ['key' => 'kontrak-direksi', 'label' => 'Kontrak', 'route' => 'direksi-cekKontrak', 'icon' => 'ri-file-add-line', 'color' => 'green', 'show' => $isDireksi],
            ['key' => 'data-rekap', 'label' => 'Data Rekap', 'route' => 'manajemen_rekap', 'icon' => 'ri-database-line', 'color' => 'purple', 'show' => $isSPV],
            ['key' => 'slip-gaji-karyawan', 'label' => 'Slip Gaji Karyawan', 'route' => 'slip-karyawan', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => $isSPV],
            ['key' => 'absensi-karyawan', 'label' => 'Absensi Karyawan', 'route' => 'manajemen_absensi', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isSPV],
            ['key' => 'laporan-karyawan', 'label' => 'Laporan Karyawan', 'route' => 'manajemen_laporan', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => $isSPV],
            ['key' => 'data-karyawan', 'label' => 'Data Karyawan', 'route' => 'manajemen_user', 'icon' => 'ri-user-line', 'color' => 'green', 'show' => $isSPV],
            ['key' => 'data-karyawan-spvw', 'label' => 'Data Karyawan', 'route' => 'spvw_user', 'icon' => 'ri-user-line', 'color' => 'green', 'show' => $isSPVW],
            ['key' => 'data-absensi-spvw', 'label' => 'Data Absensi', 'route' => 'spvw_absensi', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isSPVW],
            ['key' => 'data-izin-spvw', 'label' => 'Data Izin', 'route' => 'spvw_izin', 'icon' => 'ri-file-line', 'color' => 'blue', 'show' => $isSPVW],
            ['key' => 'data-rating-spvw', 'label' => 'Data Rating', 'route' => 'spvw-rating.index', 'icon' => 'ri-star-line', 'color' => 'yellow', 'show' => $isSPVW],
            ['key' => 'data-checklist-spvw', 'label' => 'Data Checklist', 'route' => 'spvw-checklist.index', 'icon' => 'ri-list-check', 'color' => 'indigo', 'show' => $isSPVW],
            ['key' => 'data-slip-spvw', 'label' => 'Slip Gaji Karyawan', 'route' => 'spvw-slip', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => $isSPVW],
            ['key' => 'data-karyawan-leader', 'label' => 'Data Karyawan', 'route' => $isCoCs ? 'lead_user' : 'danru_user', 'icon' => 'ri-user-line', 'color' => 'green', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-absensi-leader', 'label' => 'Data Absensi', 'route' => $isCoCs ? 'lead_absensi' : 'danru_absensi', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-izin-leader', 'label' => 'Data Izin', 'route' => $isCoCs ? 'lead_izin' : 'danru_izin', 'icon' => 'ri-file-line', 'color' => 'blue', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-absen-sholat-leader', 'label' => 'Data Absen Sholat', 'route' => 'leader-absenSholat', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-rating-leader', 'label' => 'Data Rating', 'route' => $isCoCs ? 'leader-rating.index' : 'danru-rating.index', 'icon' => 'ri-star-line', 'color' => 'yellow', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-checklist-leader', 'label' => 'Data Checklist', 'route' => 'leader-checklist.index', 'icon' => 'ri-list-check', 'color' => 'indigo', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-slip-leader', 'label' => 'Slip Gaji Karyawan', 'route' => $isCoCs ? 'leader-slip' : 'danru-slip', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => $isCoCs || $isCoScr],
        ])->filter(fn ($menu) => $menu['show'] ?? true)->values();

        $historyMenus = collect([
            ['label' => 'Kehadiran', 'url' => url('historyAbsensi'), 'icon' => 'ri-calendar-line', 'color' => 'emerald'],
            ['label' => 'Izin', 'url' => route('izin.index'), 'icon' => 'ri-file-line', 'color' => 'blue'],
            ['label' => 'Kinerja', 'url' => route('checkpoint-user.history'), 'icon' => 'ri-bar-chart-line', 'color' => 'amber'],
            ['label' => 'Laporan', 'url' => 'https://laporan-sac.sac-po.com', 'icon' => 'ri-file-text-line', 'color' => 'sky'],
        ]);

        return compact('dashboardMenus', 'dashboardAddMenus', 'historyMenus', 'kerjasamaId', 'kontrak', 'routes', 'icons', 'absensiRoute', 'tampilkanAbsensi', 'bisaLihatMenuManajemen', 'bisaLihatMenuSlip', 'isMitra', 'isLeader', 'isCoCs', 'isCoScr', 'isSPVW', 'isManajemen', 'isAdmin', 'isSPV', 'isDireksi', 'directJabatan');
    }

    private function dashboardViewContext(User $user, ?Kontrak $kontrak = null): array
    {
        $jabatan = $user->jabatan;
        $jabatanCode = $jabatan?->code_jabatan;
        $jabatanId = $user->jabatan_id;
        $isDireksi = $jabatanId === 24;
        $isSPVW = $jabatanCode === 'SPV-W';
        $isManajemen = $user->kerjasama_id === 1;
        $isAdmin = $user->role_id === 2;
        $isSPV = in_array($jabatanId, [4, 14], true);
        $isCoCs = $jabatanCode === 'CO-CS';
        $isCoScr = $jabatanCode === 'CO-SCR';
        $isLeader = $jabatanCode === 'LEADER';
        $isMitra = $jabatanCode === 'MITRA';
        $kerjasamaId = $user->kerjasama_id;
        $directJabatan = $user->jabatan?->code_jabatan;
        $hariIni = now()->dayOfWeekIso;
        $tampilkanAbsensi = !in_array($hariIni, [6, 7], true) || !$isManajemen || $user->devisi_id != 26;
        $bisaLihatMenuManajemen = $isSPV;
        $bisaLihatMenuSlip = in_array($user->id, [7, 10, 5], true);

        $bisaLihatRekap = in_array($jabatanCode, ['CO-CS', 'CO-SCR', 'SPV-W'], true) && $user->id != 7;
        $rekapRoute = $isSPVW ? 'spvw.rekap.index' : 'index.rekap.data.leader';
        $absensiRoute = match ($jabatanCode) {
            'CO-CS' => 'absensi-karyawan-co-cs.index',
            'CO-SCR' => 'absensi-karyawan-co-scr.index',
            default => null,
        };

        $routes = [
            'MITRA' => ['karyawan' => 'mitra_user', 'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'mitra_jadwal', 'absensi' => 'mitra_absensi', 'izin' => 'mitra_izin', 'lembur' => 'mitra_lembur', 'laporan' => 'mitra_laporan', 'rating' => 'mitra-rating.index', 'laporan bulanan' => 'mitra-laporan-bulanan.index'],
            'LEADER' => ['karyawan' => 'lead_user', 'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'leader-jadwal.index', 'absensi' => 'lead_absensi', 'izin' => 'lead_izin', 'lembur' => 'lead_lembur', 'laporan' => 'lead_laporan', 'rating' => 'leader-rating.index'],
            'DIREKSI' => ['karyawan' => 'direksi_user', 'jadwal' => $isAdmin ? 'admin.jadwal.index' : 'direksi_jadwal', 'absensi' => 'direksi_absensi', 'izin' => 'direksi_izin', 'lembur' => 'direksi_lembur', 'laporan' => 'direksi_laporan', 'rating' => 'direksi-rating.index', 'kinerja' => 'direksi.cp.index', 'kontrak' => 'direksi-cekKontrak'],
        ];
        $icons = ['karyawan' => 'ri-pass-pending-line', 'jadwal' => 'ri-calendar-check-line', 'absensi' => 'ri-todo-line', 'izin' => 'ri-shield-user-line', 'lembur' => 'ri-time-line', 'laporan' => 'ri-image-add-line', 'laporan bulanan' => 'ri-image-add-line', 'rating' => 'ri-sparkling-line', 'kinerja' => 'ri-sparkling-line', 'kontrak' => 'ri-pass-pending-line'];
        $kontrakSelesai = $kontrak?->tgl_selesai_kontrak && \Carbon\Carbon::parse($kontrak->tgl_selesai_kontrak)->isPast();
        $kontrakBerjalan = $kontrak && ($kontrak->isPending() || $kontrak->isActive() || $kontrak->isProses());
        $bisaAjukanKontrak = !$kontrak || $kontrakSelesai || !$kontrakBerjalan;

        $dashboardMenus = collect([
            ['key' => 'kehadiran', 'label' => 'Kehadiran', 'route' => 'absensi.index', 'icon' => 'ri-calendar-line', 'color' => 'emerald'],
            ['key' => 'data-rekap', 'label' => 'Data Rekap', 'route' => $rekapRoute, 'icon' => 'ri-database-line', 'color' => 'indigo', 'show' => $bisaLihatRekap],
            ['key' => 'izin', 'label' => 'Izin', 'route' => 'izin.create', 'icon' => 'ri-file-line', 'color' => 'blue'],
            ['key' => 'kinerja', 'label' => 'Kinerja', 'route' => 'checkpoint-user.index', 'icon' => 'ri-bar-chart-line', 'color' => 'amber', 'show' => !$isDireksi || $isManajemen],
            ['key' => 'rating', 'label' => 'Rating', 'route' => 'ratingSaya', 'params' => [$user->id], 'icon' => 'ri-star-line', 'color' => 'indigo', 'show' => !$isDireksi],
            ['key' => 'laporan', 'label' => 'Laporan', 'route' => 'laporan.index', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => !$isDireksi],
            ['key' => 'kontrak', 'label' => 'Pengajuan Kontrak', 'route' => 'form-kontrak-request', 'icon' => 'ri-file-add-line', 'color' => 'green', 'show' => $bisaAjukanKontrak && !$isDireksi],
            ['key' => 'alquran', 'label' => "Al Qur'an", 'url' => 'https://baca-alquran.sac-po.com', 'icon' => 'ri-book-line', 'color' => 'amber'],
            ['key' => 'sppd', 'label' => 'SPPD', 'url' => 'https://sppd-online.sac-po.com/login', 'icon' => 'ri-road-map-line', 'color' => 'sky', 'show' => $isManajemen],
        ])->filter(fn ($menu) => $menu['show'] ?? true)->values();

        $dashboardAddMenus = collect([
            ['key' => 'karyawan-direksi', 'label' => 'Karyawan', 'route' => 'direksi_user', 'icon' => 'ri-team-line', 'color' => 'blue', 'show' => $isDireksi],
            ['key' => 'absensi-direksi', 'label' => 'Absensi', 'route' => 'direksi_absensi', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isDireksi],
            ['key' => 'izin-direksi', 'label' => 'Izin', 'route' => 'direksi_izin', 'icon' => 'ri-shield-user-line', 'color' => 'blue', 'show' => $isDireksi],
            ['key' => 'kinerja-direksi', 'label' => 'Kinerja', 'route' => 'direksi.cp.index', 'icon' => 'ri-bar-chart-line', 'color' => 'amber', 'show' => $isDireksi],
            ['key' => 'kontrak-direksi', 'label' => 'Kontrak', 'route' => 'direksi-cekKontrak', 'icon' => 'ri-file-add-line', 'color' => 'green', 'show' => $isDireksi],
            ['key' => 'data-rekap', 'label' => 'Data Rekap', 'route' => 'manajemen_rekap', 'icon' => 'ri-database-line', 'color' => 'purple', 'show' => $isSPV],
            ['key' => 'slip-gaji-karyawan', 'label' => 'Slip Gaji Karyawan', 'route' => 'slip-karyawan', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => $isSPV],
            ['key' => 'absensi-karyawan', 'label' => 'Absensi Karyawan', 'route' => 'manajemen_absensi', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isSPV],
            ['key' => 'laporan-karyawan', 'label' => 'Laporan Karyawan', 'route' => 'manajemen_laporan', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => $isSPV],
            ['key' => 'data-karyawan', 'label' => 'Data Karyawan', 'route' => 'manajemen_user', 'icon' => 'ri-user-line', 'color' => 'green', 'show' => $isSPV],
            ['key' => 'data-karyawan-spvw', 'label' => 'Data Karyawan', 'route' => 'spvw_user', 'icon' => 'ri-user-line', 'color' => 'green', 'show' => $isSPVW],
            ['key' => 'data-absensi-spvw', 'label' => 'Data Absensi', 'route' => 'spvw_absensi', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isSPVW],
            ['key' => 'data-izin-spvw', 'label' => 'Data Izin', 'route' => 'spvw_izin', 'icon' => 'ri-file-line', 'color' => 'blue', 'show' => $isSPVW],
            ['key' => 'data-rating-spvw', 'label' => 'Data Rating', 'route' => 'spvw-rating.index', 'icon' => 'ri-star-line', 'color' => 'yellow', 'show' => $isSPVW],
            ['key' => 'data-checklist-spvw', 'label' => 'Data Checklist', 'route' => 'spvw-checklist.index', 'icon' => 'ri-list-check', 'color' => 'indigo', 'show' => $isSPVW],
            ['key' => 'data-slip-spvw', 'label' => 'Slip Gaji Karyawan', 'route' => 'spvw-slip', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => $isSPVW],
            ['key' => 'data-karyawan-leader', 'label' => 'Data Karyawan', 'route' => $isCoCs ? 'lead_user' : 'danru_user', 'icon' => 'ri-user-line', 'color' => 'green', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-absensi-leader', 'label' => 'Data Absensi', 'route' => $isCoCs ? 'lead_absensi' : 'danru_absensi', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-izin-leader', 'label' => 'Data Izin', 'route' => $isCoCs ? 'lead_izin' : 'danru_izin', 'icon' => 'ri-file-line', 'color' => 'blue', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-absen-sholat-leader', 'label' => 'Data Absen Sholat', 'route' => 'leader-absenSholat', 'icon' => 'ri-calendar-line', 'color' => 'emerald', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-rating-leader', 'label' => 'Data Rating', 'route' => $isCoCs ? 'leader-rating.index' : 'danru-rating.index', 'icon' => 'ri-star-line', 'color' => 'yellow', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-checklist-leader', 'label' => 'Data Checklist', 'route' => 'leader-checklist.index', 'icon' => 'ri-list-check', 'color' => 'indigo', 'show' => $isCoCs || $isCoScr],
            ['key' => 'data-slip-leader', 'label' => 'Slip Gaji Karyawan', 'route' => $isCoCs ? 'leader-slip' : 'danru-slip', 'icon' => 'ri-file-text-line', 'color' => 'sky', 'show' => $isCoCs || $isCoScr],
        ])->filter(fn ($menu) => $menu['show'] ?? true)->values();

        $historyMenus = collect([
            ['label' => 'Kehadiran', 'url' => url('historyAbsensi'), 'icon' => 'ri-calendar-line', 'color' => 'emerald'],
            ['label' => 'Izin', 'url' => route('izin.index'), 'icon' => 'ri-file-line', 'color' => 'blue'],
            ['label' => 'Kinerja', 'url' => route('checkpoint-user.history'), 'icon' => 'ri-bar-chart-line', 'color' => 'amber'],
            ['label' => 'Laporan', 'url' => 'https://laporan-sac.sac-po.com', 'icon' => 'ri-file-text-line', 'color' => 'sky'],
        ]);

        return compact('dashboardMenus', 'dashboardAddMenus', 'historyMenus', 'kerjasamaId', 'kontrak', 'routes', 'icons', 'absensiRoute', 'tampilkanAbsensi', 'bisaLihatMenuManajemen', 'bisaLihatMenuSlip', 'isMitra', 'isLeader', 'isCoCs', 'isCoScr', 'isSPVW', 'isManajemen', 'isAdmin', 'isSPV', 'isDireksi', 'directJabatan');
    }

    /**
     * Ringkasan operasional untuk dashboard mitra, seluruhnya dihitung dari data nyata.
     */
    private function mitraDashboardData(User $user): array
    {
        $now = Carbon::now();
        $kerjasamaId = $user->kerjasama_id;
        $todayStart = $now->copy()->startOfDay();
        $monthStart = $now->copy()->startOfMonth();

        $totalPersonel = User::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->whereNotIn('nama_lengkap', User::PLACEHOLDER_ACCOUNT_NAMES)
            ->count();

        $absensiQuery = Absensi::query()->where('kerjasama_id', $kerjasamaId);

        $hadirIds = (clone $absensiQuery)
            ->where('created_at', '>=', $todayStart)
            ->distinct()
            ->pluck('user_id');
        $hadirHariIni = $hadirIds->count();

        $hadirKemarin = (clone $absensiQuery)
            ->whereBetween('created_at', [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ])
            ->distinct()
            ->count('user_id');

        $persenKehadiran = $totalPersonel > 0 ? round(($hadirHariIni / $totalPersonel) * 100, 1) : 0.0;
        $persenKemarin = $totalPersonel > 0 ? round(($hadirKemarin / $totalPersonel) * 100, 1) : 0.0;
        $deltaKehadiran = round($persenKehadiran - $persenKemarin, 1);

        // Satu query harian dipakai ulang untuk seri harian, mingguan, dan bulanan.
        $dailyAttendance = (clone $absensiQuery)
            ->where('created_at', '>=', $now->copy()->subMonthsNoOverflow(5)->startOfMonth())
            ->selectRaw('DATE(created_at) as tanggal, COUNT(DISTINCT user_id) as jumlah')
            ->groupBy('tanggal')
            ->pluck('jumlah', 'tanggal');

        $rataRataRentang = function (Carbon $start, Carbon $end) use ($dailyAttendance, $now): int {
            $total = 0;
            $hari = 0;

            for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addDay()) {
                if ($cursor->greaterThan($now)) {
                    break;
                }

                $total += (int) ($dailyAttendance[$cursor->toDateString()] ?? 0);
                $hari++;
            }

            return $hari > 0 ? (int) round($total / $hari) : 0;
        };

        $harian = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = $now->copy()->subDays($i);
            $harian[] = [
                'label' => $tanggal->locale('id')->isoFormat('ddd'),
                'keterangan' => $tanggal->locale('id')->isoFormat('D MMM'),
                'value' => (int) ($dailyAttendance[$tanggal->toDateString()] ?? 0),
            ];
        }

        $mingguan = [];
        for ($i = 3; $i >= 0; $i--) {
            $awal = $now->copy()->subWeeks($i)->startOfWeek();
            $mingguan[] = [
                'label' => $awal->locale('id')->isoFormat('D MMM'),
                'keterangan' => $awal->locale('id')->isoFormat('D MMM'),
                'value' => $rataRataRentang($awal, $awal->copy()->endOfWeek()),
            ];
        }

        $bulanan = [];
        for ($i = 5; $i >= 0; $i--) {
            $awal = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $bulanan[] = [
                'label' => $awal->locale('id')->isoFormat('MMM'),
                'keterangan' => $awal->locale('id')->isoFormat('MMM Y'),
                'value' => $rataRataRentang($awal, $awal->copy()->endOfMonth()),
            ];
        }

        $seri = [
            'harian' => $this->lengkapiSeriGrafik($harian, $totalPersonel),
            'mingguan' => $this->lengkapiSeriGrafik($mingguan, $totalPersonel),
            'bulanan' => $this->lengkapiSeriGrafik($bulanan, $totalPersonel),
        ];

        $izinMenunggu = Izin::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->where('approve_status', 'process')
            ->count();

        $lemburBerjalan = Lembur::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->whereNull('jam_selesai')
            ->where('created_at', '>=', $todayStart)
            ->count();

        $izinHarian = Izin::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah')
            ->groupBy('tanggal')
            ->pluck('jumlah', 'tanggal');

        $seriPengajuan = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = $now->copy()->subDays($i);
            $seriPengajuan[] = (int) ($izinHarian[$tanggal->toDateString()] ?? 0);
        }
        $puncakPengajuan = max(1, max($seriPengajuan));

        $izinHariIni = Izin::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->where('created_at', '>=', $todayStart)
            ->distinct()
            ->count('user_id');

        $terlambatHariIni = (clone $absensiQuery)
            ->where('created_at', '>=', $todayStart)
            ->where('keterangan', 'telat')
            ->distinct()
            ->count('user_id');

        $tepatWaktu = max($hadirHariIni - $terlambatHariIni, 0);
        $belumAbsen = max($totalPersonel - $hadirHariIni - $izinHariIni, 0);
        $basisDistribusi = max($tepatWaktu + $terlambatHariIni + $izinHariIni + $belumAbsen, 1);

        $distribusi = [
            [
                'label' => 'Hadir Tepat Waktu',
                'value' => $tepatWaktu,
                'persen' => round(($tepatWaktu / $basisDistribusi) * 100, 1),
                'warna' => 'var(--mitra-accent)',
            ],
            [
                'label' => 'Terlambat',
                'value' => $terlambatHariIni,
                'persen' => round(($terlambatHariIni / $basisDistribusi) * 100, 1),
                'warna' => 'var(--mitra-warning)',
            ],
            [
                'label' => 'Izin / Cuti',
                'value' => $izinHariIni,
                'persen' => round(($izinHariIni / $basisDistribusi) * 100, 1),
                'warna' => 'var(--mitra-success)',
            ],
            [
                'label' => 'Belum Absen',
                'value' => $belumAbsen,
                'persen' => round(($belumAbsen / $basisDistribusi) * 100, 1),
                'warna' => '#94a3b8',
            ],
        ];

        $personelTerbaru = (clone $absensiQuery)
            ->with('user:id,nama_lengkap')
            ->where('created_at', '>=', $todayStart)
            ->latest('id')
            ->get(['id', 'user_id', 'created_at'])
            ->unique('user_id')
            ->take(5)
            ->map(fn (Absensi $absen) => [
                'nama' => $absen->user?->nama_lengkap ?? '-',
                'jam' => optional($absen->created_at)->format('H:i'),
            ])
            ->values();

        $totalAbsensiBulanIni = (clone $absensiQuery)
            ->whereBetween('created_at', [$monthStart, $now])
            ->count();
        $telatBulanIni = (clone $absensiQuery)
            ->whereBetween('created_at', [$monthStart, $now])
            ->where('keterangan', 'telat')
            ->count();
        $tingkatDisiplin = $totalAbsensiBulanIni > 0
            ? round((1 - ($telatBulanIni / $totalAbsensiBulanIni)) * 100, 1)
            : null;

        $menitLembur = Lembur::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->whereNotNull('jam_selesai')
            ->whereBetween('created_at', [$monthStart, $now])
            ->get(['jam_mulai', 'jam_selesai'])
            ->sum(function (Lembur $lembur) {
                if (blank($lembur->jam_mulai) || blank($lembur->jam_selesai)) {
                    return 0;
                }

                $mulai = Carbon::parse($lembur->jam_mulai);
                $selesai = Carbon::parse($lembur->jam_selesai);
                $menit = $selesai->diffInMinutes($mulai, false);

                if ($menit < 0) {
                    $menit += 24 * 60;
                }

                return max($menit, 0);
            });

        $izinBulanIni = Izin::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->whereBetween('created_at', [$monthStart, $now])
            ->count();
        $izinDisetujuiBulanIni = Izin::query()
            ->where('kerjasama_id', $kerjasamaId)
            ->where('approve_status', 'accept')
            ->whereBetween('created_at', [$monthStart, $now])
            ->count();

        return [
            'workspace' => $user->kerjasama?->client?->name,
            'periodeLabel' => $now->locale('id')->isoFormat('D MMMM Y'),
            'namaMitra' => $user->nama_lengkap ?? $user->name,
            'totalPersonel' => $totalPersonel,
            'hadirHariIni' => $hadirHariIni,
            'persenKehadiran' => $persenKehadiran,
            'deltaKehadiran' => $deltaKehadiran,
            'izinMenunggu' => $izinMenunggu,
            'lemburBerjalan' => $lemburBerjalan,
            'izinHariIni' => $izinHariIni,
            'terlambatHariIni' => $terlambatHariIni,
            'seri' => $seri,
            'seriPengajuan' => $seriPengajuan,
            'persenPengajuan' => array_map(
                fn (int $jumlah) => (int) round(($jumlah / $puncakPengajuan) * 100),
                $seriPengajuan
            ),
            'distribusi' => $distribusi,
            'personelTerbaru' => $personelTerbaru,
            'ringkasan' => [
                'jamLembur' => round($menitLembur / 60, 1),
                'disiplin' => $tingkatDisiplin,
                'izinDisetujui' => $izinDisetujuiBulanIni,
                'izinBulanIni' => $izinBulanIni,
            ],
        ];
    }

    /**
     * Lengkapi setiap titik seri dengan tinggi relatif dan penanda puncak.
     */
    private function lengkapiSeriGrafik(array $titik, int $totalPersonel): array
    {
        $nilai = array_map(static fn (array $item) => (int) $item['value'], $titik);
        $tertinggi = $nilai === [] ? 0 : max($nilai);
        $skala = max(1, $tertinggi);
        // Hanya satu batang yang diberi badge puncak, meski ada nilai yang seri.
        $indexPuncak = $tertinggi > 0 ? array_search($tertinggi, $nilai, true) : null;

        $hasil = [];

        foreach ($titik as $index => $item) {
            $value = (int) $item['value'];

            $hasil[] = [
                'label' => $item['label'],
                'keterangan' => $item['keterangan'],
                'value' => $value,
                'tinggi' => $value > 0 ? max(6, (int) round(($value / $skala) * 100)) : 0,
                'persenPersonel' => $totalPersonel > 0 ? round(($value / $totalPersonel) * 100, 1) : 0.0,
                'puncak' => $index === $indexPuncak,
            ];
        }

        return $hasil;
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
