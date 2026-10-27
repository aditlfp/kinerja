<?php

namespace App\Http\Controllers\SVP_Controller;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Laporan;
use App\Models\Lembur;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Kerjasama;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    public function indexAbsen(Request $request)
    {
        $filter = $request->search;
        $filterMitra = $request->mitra;
        $filter2 = Carbon::parse($filter);

        $mitra = Kerjasama::with('client')->get();

        $tanggalIki = Carbon::now()->format('Y-m-d') == '2024-05-24' && Auth::user()->devisi_id == 18;

        $kerjasama = Auth::user()->kerjasama_id;
        $absenQue = Absensi::latest();

        if ($filter) {
            $absenQ = $absenQue->whereBetween('tanggal_absen', monthBounds($filter2));
            if ($filterMitra) {
                $absenQ = $absenQ->where('kerjasama_id', $filterMitra);
            }
            $absen = $absenQ->paginate(50)->appends($request->except('page'));;
        } else {
            $absen = Absensi::orderBy('tanggal_absen', 'desc')->orderBy('kerjasama_id', 'desc')->whereBetween('tanggal_absen', monthBounds(Carbon::now()))->latest()->paginate(31);
        }

        return view('spv_view/absen/index', compact('absen', 'mitra', 'filterMitra', 'filter'));
    }

    public function indexLaporan()
    {
        $laporan = Laporan::paginate(15);
        return view('spv_view/laporan/index', compact('laporan'));
    }

    public function indexUser()
    {
        $kerjasama = Auth::user()->kerjasama_id;
        $authCode = Auth::user()->jabatan->code_jabatan ?? Auth::user()->divisi->jabatan->code_jabatan ?? null;
        $authJabatanId = Auth::user()->jabatan_id;
        $allowedJabatanIds = [9, 10, 12, 16, 19, 20, 21, 22, 23, 30, 31, 32, 34, 36, 37, 40, 41];
        $jabatan14Ids = [9, 10, 34, 40, 41];
        $jabatan5 = [8, 11, 16, 17, 18, 21, 23, 30, 35, 36, 37];
        $baseUser = User::with('divisi.jabatan')
            ->when($authCode != "MCS" && $authCode != "SPV" && $authCode != "MRT", fn($q) => $q->where('kerjasama_id', $kerjasama))
            ->when($authCode == "MCS", fn($q) => $q->whereHas('divisi', fn($d) => $d->whereIn('jabatan_id', $allowedJabatanIds)))
            ->when($authJabatanId == '14', fn($q) => $q->whereHas('divisi', fn($d) => $d->whereIn('jabatan_id', $jabatan14Ids)))
            ->when($authJabatanId == '4', fn($q) => $q->whereHas('divisi', fn($d) => $d->whereIn('jabatan_id', $jabatan5)))
            ->whereNotIn('nama_lengkap', ['admin', 'user']);
        $jabatanCounts = (clone $baseUser)->get()
            ->groupBy(fn($i) => $i->divisi->jabatan->code_jabatan ?? 'Jabatan Kosong ?')
            ->map->count()
            ->sortKeys();
        $user = $baseUser->paginate(15);
        return view('spv_view.user.index', compact('user', 'jabatanCounts'));
    }

    public function indexLembur()
    {
        $kerjasama = Auth::user()->kerjasama_id;
        $lembur = Lembur::where('kerjasama_id', $kerjasama)->paginate(15);
        $isMitra = Auth::user()->divisi->jabatan->code_jabatan === 'MITRA';
        $view = $isMitra ? 'mitra_view.lembur.index' : 'leader_view.lembur.index';

        return view($view, compact('lembur'));
    }
}
