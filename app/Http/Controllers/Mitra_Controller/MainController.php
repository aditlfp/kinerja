<?php

namespace App\Http\Controllers\Mitra_Controller;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Client;
use App\Models\LaporanMitra;
use App\Models\Letters;
use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MainController extends Controller
{
    /**
     * Dokumen laporan mitra disimpan di aplikasi laporan terpisah.
     */
    public const PDF_BASE_URL = 'https://laporan-sac.sac-po.com/storage/pdf';

    public function indexKehadiran(Request $request)
    {
        $kerjasamaId = auth()->user()->kerjasama_id;
        $periode = $request->string('periode')->toString();
        $periode = in_array($periode, ['today', '30days'], true) ? $periode : 'today';

        $query = Absensi::with(['user', 'shift'])
            ->where('kerjasama_id', $kerjasamaId);

        // Backward compatibility: dukung filter bulan lama (?search=YYYY-MM).
        if ($request->filled('search') && preg_match('/^\d{4}-\d{2}$/', $request->search)) {
            $periode = 'custom';
            $query->where('tanggal_absen', 'like', $request->search . '%');
            try {
                $periodeLabel = 'Bulan ' . Carbon::createFromFormat('Y-m', $request->search)->translatedFormat('F Y');
            } catch (\Throwable $th) {
                $periodeLabel = 'Periode Bulanan';
            }
        } elseif ($periode === '30days') {
            $query->whereDate('created_at', '>=', now()->subDays(29)->toDateString());
            $periodeLabel = '30 Hari Terakhir';
        } else {
            $query->whereDate('created_at', now()->toDateString());
            $periodeLabel = 'Hari Ini';
        }

        $latestAbsensi = (clone $query)->latest('id')->first(['id', 'created_at', 'updated_at']);
        $dataSignature = $latestAbsensi
            ? $latestAbsensi->id . '|' . optional($latestAbsensi->created_at)->timestamp . '|' . optional($latestAbsensi->updated_at)->timestamp
            : 'empty';

        if ($request->boolean('check_updates') && $request->ajax()) {
            return response()->json([
                'signature' => $dataSignature,
            ]);
        }

        $absensiList = $query->latest()->get();
        $jumlahKehadiran = $absensiList
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->count();
        $jumlahAbsensiHariIni = Absensi::where('kerjasama_id', $kerjasamaId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return view('mitra_view.kehadiran.index', compact(
            'absensiList',
            'jumlahKehadiran',
            'jumlahAbsensiHariIni',
            'periode',
            'periodeLabel',
            'dataSignature',
        ));
    }

    public function showLocation(Request $request, $id)
    {
        $tgl = $request->tgl;
        $us = $request->user;

        if ($request->tgl) {
            $absen = Absensi::with('kerjasama')->where('user_id', $request->user)->firstWhere('tanggal_absen', $request->tgl);
            $lokMitra = Lokasi::firstWhere('client_id', $absen?->kerjasama->client_id);
        } else {
            $absen = Absensi::with('kerjasama')->findOrFail($id);
            $lokMitra = Lokasi::firstWhere('client_id', $absen?->kerjasama->client_id);
        }

        return view('mitra_view.kehadiran.maps', compact('absen', 'lokMitra', 'tgl', 'us'));
    }

    public function indexKaryawan()
    {
        $search = request('search');
        $kerjasamaId = auth()->user()->kerjasama_id;

        $query = User::with(['divisi.jabatan', 'kerjasama.client'])
            ->where('kerjasama_id', $kerjasamaId)
            ->whereNotIn('nama_lengkap', ['admin', 'user', 'MITRA SAC']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->orderBy('nama_lengkap', 'asc')->paginate(50)->appends(request()->query());
        $totalKaryawan = (clone $query)->count();

        return view('mitra_view.karyawan.index', compact('users', 'search', 'totalKaryawan'));
    }

    public function indexLembur()
    {
        return view('mitra_view.lembur.index');
    }

    public function indexIzin()
    {
        return view('mitra_view.izin.index');
    }

    public function indexRekap(Request $request)
    {
        $client = auth()->user()->kerjasama->client;
        $hasFilter = $request->filled('bulan') && $request->filled('tahun');
        $bulan = $request->integer('bulan');
        $tahun = $request->integer('tahun');

        $letters = Letters::query()
            ->whereHas('cover', fn ($query) => $query->where('clients_id', $client->id))
            ->latest('created_at')
            ->get();

        $laporan = $letters->filter(function (Letters $letter) use ($hasFilter, $bulan, $tahun): bool {
                if (!$hasFilter) {
                    return true;
                }

                $period = strtolower((string) $letter->period);
                $month = strtolower(Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F'));

                return str_contains($period, $month) && str_contains($period, (string) $tahun);
            })->map(function (Letters $letter) use ($client): array {
                preg_match('/(\d{4})/', (string) $letter->period, $yearMatch);
                $year = (int) ($yearMatch[1] ?? $letter->created_at?->format('Y'));
                $month = $this->monthNumberFromPeriod($letter->period);

                if (!$month || !$year) {
                    return [];
                }

                $formatName = 'laporan-' . Str::slug($client->name) . '-' . $year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT);

                return [
                    'model' => $letter->cover,
                    'letter' => $letter,
                    'formatName' => $formatName,
                    'url' => route('mitra_rekap_berkas', ['file' => $formatName . '.pdf']),
                    'monthYear' => sprintf('%02d-%d', $month, $year),
                ];
            })->filter()->values();

        return view('mitra_view.rekap.index', [
            'laporanMitra' => $laporan,
            'totalLaporan' => $laporan->count(),
            'laporanBulanIni' => $laporan->filter(fn (array $item): bool => $item['letter']->created_at?->isSameMonth(now()))->count(),
            'laporanTerbaru' => $laporan->first()['letter'] ?? null,
            'hasFilter' => $hasFilter,
            'client' => $client,
        ]);
    }

    private function monthNumberFromPeriod(?string $period): ?int
    {
        $months = ['januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12];
        $period = strtolower((string) $period);

        foreach ($months as $name => $number) {
            if (str_contains($period, $name)) return $number;
        }

        return null;
    }

    public function previewRekap(string $file)
    {
        $file = basename($file);
        abort_unless($file === basename($file) && preg_match('/^[a-z0-9-]+\.pdf$/i', $file), 404);

        $response = Http::timeout(60)->get(self::PDF_BASE_URL . '/' . $file);
        abort_unless($response->successful() && str_contains($response->header('Content-Type', ''), 'application/pdf'), 404);

        return response($response->body(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file . '"',
            'Content-Length' => strlen($response->body()),
        ]);
    }

    public function downloadRekap($url)
    {
        $urlDownload = "https://laporan-sac.sac-po.com/storage/pdf/" . $url . ".pdf";
        $response = Http::timeout(60)->get($urlDownload);

        if (
            !$response->successful() ||
            !str_contains(
                $response->header('Content-Type', ''),
                'application/pdf'
            )
        ) {
            abort(404, 'File PDF tidak ditemukan atau server mengembalikan respons yang tidak valid.');
        }

        return response($response->body(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . basename(parse_url($url, PHP_URL_PATH)) . '.pdf"',
            'Content-Length' => strlen($response->body()),
        ]);
    }
}
