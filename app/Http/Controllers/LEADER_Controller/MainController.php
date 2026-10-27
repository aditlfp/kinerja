<?php

namespace App\Http\Controllers\LEADER_Controller;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Laporan;
use App\Models\Lembur;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Kerjasama;
use App\Models\UploadImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{

    public function indexAbsen(Request $request)
    {
        // --- 1. Early Exit for Specific Condition ---
        $isSpecialDate = Carbon::now()->format('Y-m-d') == '2024-05-24' && Auth::user()->devisi_id == '18';
        if ($isSpecialDate) {
            abort(500);
        }

        // --- 2. Initialize Core Variables ---
        $user = Auth::user();
        $filter = $request->search; // This can be a full date or a month string
        $filterMitra = $request->mitra;
        $isDiv18 = $user->devisi_id == '18';

        // Data for the view
        $mitra = Kerjasama::with('client')->get();

        // --- 3. Start Building the Query ---
        $query = Absensi::with('user.divisi.jabatan')->latest();

        if ($filterMitra) {
            if (Auth::user()->jabatan_id == '20') {
                $jabatanCodes = ['OCS', 'CO-CS', 'TMN', 'PTR', 'KSR', 'PG', 'TKS'];
            } else {
                $jabatanCodes = ['SCR', 'CO-SCR', 'JM', 'DRV', 'FO', 'RCP', 'JK'];
            }
            $users = User::where('kerjasama_id', $filterMitra)
                ->select('id', 'nama_lengkap')
                ->whereHas('divisi.jabatan', fn($q) => $q->whereIn('code_jabatan', $jabatanCodes))
                ->orderBy('nama_lengkap', 'asc')
                ->get();
            if ($request->user) {
                $query->where('user_id', $request->user);
            }
        } else {
            $users = collect(); // Empty collection if no mitra is selected
        }

        // --- 4. Apply Date/Month Filter (THIS IS THE UPDATED SECTION) ---
        if ($isDiv18) {
            // For users in devisi_id 18:
            // - If a date is provided, filter by that exact date.
            // - If no date is provided, default to filtering by the current month.
            $query->when(
                $filter,
                fn($q) => $q->whereDate('tanggal_absen', $filter),
                fn($q) => $q->whereBetween('tanggal_absen', monthBounds(Carbon::now()))
            );
        } else {
            // For all other users:
            // - The filter is treated as a month selector.
            // - Default to the current month if no filter is provided.
            $targetMonth = $filter ? Carbon::parse($filter) : Carbon::now();
            $query->whereBetween('tanggal_absen', monthBounds($targetMonth));
        }

        // --- 5. Apply Role-Based and User-Specific Filters ---
        $userRole = $user->divisi->jabatan->code_jabatan ?? null;
        $isSPV = $user->jabatan->code_jabatan == 'SPV-W';

        $jabatanCodes = [
            'CO-CS' => ['OCS', 'CO-CS'],
            'CO-SCR' => ['SCR', 'CO-SCR'],
        ];

        if ($isDiv18) {
            $query->when($filterMitra, fn($q) => $q->where('kerjasama_id', $filterMitra));
        } elseif (isset($jabatanCodes[$userRole]) || $isSPV) {
            $codesToFilter = $jabatanCodes[$userRole] ?? $jabatanCodes['CO-SCR'];

            $query->whereHas('user.divisi.jabatan', fn($q) => $q->whereIn('code_jabatan', $codesToFilter));

            if ($isSPV) {
                if ($filterMitra) {
                    $query->where('kerjasama_id', $filterMitra);
                } else {
                    $query;
                }
            } else {
                $query->where('kerjasama_id', $user->kerjasama_id);
            }
        } else {
            $query->where('kerjasama_id', $user->kerjasama_id);
        }

        // --- 6. Apply Ordering and Pagination ---
        if ($isSPV && !$filterMitra) {
            $query->orderBy('kerjasama_id', 'asc')->latest();
        } elseif ($isDiv18) {
            $query->orderBy('tanggal_absen', 'desc')->orderBy('kerjasama_id', 'desc');
        } else {
            $query->latest();
        }

        $perPage = 31;
        if ($isSPV && $filterMitra) {
            $perPage = 20;
        } elseif ($filter) { // If any filter is applied, show more results
            $perPage = 50;
        }

        $absen = $query->paginate($perPage)->appends($request->except('page'));

        // dd($absen);

        // --- 7. Return the View ---
        return view('leader_view/absen/index', compact('absen', 'mitra', 'filterMitra', 'filter', 'users'));
    }

    public function indexLaporan()
    {
        $kerjasama = Auth::user()->kerjasama->client_id;
        $laporan = UploadImage::where('clients_id', $kerjasama)->latest()->paginate(30);
        $isMitra = Auth::user()->divisi->jabatan->code_jabatan === 'MITRA';
        $view = $isMitra ? 'mitra_view/laporan/index' : 'leader_view/laporan/index';

        return view($view, compact('laporan'));
    }
    public function showLaporan($id)
    {
        $kerjasama = Auth::user()->kerjasama->client_id;
        $laporan = Laporan::findOrfail($id);
        return view('leader_view/laporan/show', compact('laporan'));
    }

    public function indexUser(Request $request)
    {
        // 1. Eager load relationships to prevent N+1 queries
        $authUser = Auth::user()->load(['divisi.jabatan', 'kerjasama.client']);

        // 2. Get filter inputs
        $filterDivisi = $request->input('divisi');
        $filterMitra = $request->input('mitra');

        // 3. Optimize queries for dropdowns (select only what's needed)
        $divisi = Divisi::orderBy('name', 'asc')->pluck('name', 'id');
        $mitra = Kerjasama::query()
            ->select('kerjasamas.id', 'kerjasamas.client_id')
            ->join('clients', 'kerjasamas.client_id', '=', 'clients.id')
            ->orderBy('clients.name', 'asc')
            ->with('client:id,name,panggilan')
            ->get();

        // 4. Base query with eager-loaded relations and only the columns the view needs.
        // This removes the N+1 on `$i->divisi->jabatan` and `$i->kerjasama->client`
        // that previously fired up to 4 queries per row.
        $baseQuery = fn() => User::query()
            ->select(['id', 'name', 'nama_lengkap', 'email', 'image', 'devisi_id', 'kerjasama_id', 'jabatan_id'])
            ->with(['divisi:id,name,jabatan_id', 'divisi.jabatan:id,code_jabatan', 'kerjasama:id,client_id', 'kerjasama.client:id,name,panggilan']);

        $query = $baseQuery();

        // 5. Apply permission-based filtering and ordering using guard clauses
        // This flattens the nested if/else structure, making it much easier to read.


        // Super Admin / Direksi: Can see all users
        if ($authUser->role_id == 2 || $authUser->divisi->jabatan->code_jabatan === 'DIREKSI') {
            // Apply division filter if it exists, otherwise show all
            if ($filterDivisi) {
                $query->where('devisi_id', $filterDivisi);
            }
            $user = $query->orderBy('nama_lengkap', 'asc')->paginate(100); // Use a reasonable pagination limit
            return view('leader_view/user/index', compact('user', 'divisi', 'mitra', 'filterMitra', 'filterDivisi'));
        }

        // Apply division filter if it exists for non-admin users
        if ($filterDivisi) {
            // This condition was at the top, but it's more logical here.
            // It overrides the complex logic below if a division is selected.
            $user = $query->orderBy('nama_lengkap', 'asc')->paginate(90);
            return view('leader_view/user/index', compact('user', 'divisi', 'mitra', 'filterMitra', 'filterDivisi'));
        }

        // CO-CS Role
        if ($authUser->divisi->jabatan->code_jabatan === "CO-CS" || $authUser->jabatan_id == 20) {
            $query->whereHas('divisi.jabatan', fn($q) => $q->whereIn('code_jabatan', ['OCS', 'CO-CS']))
                ->where('kerjasama_id', Auth::user()->kerjasama_id)
                ->orderBy('nama_lengkap', 'asc');
        }
        // MITRA Role
        elseif ($authUser->divisi->jabatan->code_jabatan === "MITRA") {
            $query->where('kerjasama_id', Auth::user()->kerjasama_id)->orderBy('nama_lengkap', 'asc');
        }
        // Special Division (ID 26)
        elseif ($authUser->devisi_id == 26) {
            $query->where('kerjasama_id', '!=', 1)->orderBy('nama_lengkap', 'asc');

            if ($authUser->jabatan_id == 35) {
                $query->whereHas('divisi.jabatan', fn($q) => $q->whereNotIn('code_jabatan', ['MITRA', 'OCS', 'CO-CS', 'TMN']));
            } else {
                $query->whereHas('divisi.jabatan', fn($q) => $q->whereNotIn('code_jabatan', ['MITRA', 'SCR', 'CO-SCR', 'JM']));
            }

            $query->orderBy('kerjasama_id', 'asc')->orderBy('nama_lengkap', 'asc');

            // Apply the mitra filter specifically for this role
            if ($filterMitra) {
                $query->where('kerjasama_id', $filterMitra);
                // dd($query->get());
            }
            $user = $query->paginate(50);
        }
        // Default SCR / CO-SCR Role
        else {
            $codeJabatan = ['SCR', 'CO-SCR'];
            $query->whereHas('divisi.jabatan', fn($q) => $q->whereIn('code_jabatan', $codeJabatan));

            // Special Jabatan (ID 35) that can filter by Mitra
            if ($authUser->jabatan_id == 35) {
                $query->where('kerjasama_id', Auth::user()->kerjasama_id)->orderBy('kerjasama_id', 'asc')->orderBy('nama_lengkap', 'asc');

                // Apply the mitra filter specifically for this role
                if ($filterMitra) {
                    $query->where('kerjasama_id', $filterMitra);
                }
                $user = $query->paginate(50);
                return view('leader_view/user/index', compact('user', 'divisi', 'mitra', 'filterMitra', 'filterDivisi'));
            } else {
                $query->orderBy('nama_lengkap', 'asc');
            }
        }

        // 6. Execute the final query with a default pagination
        $user = $query->paginate(30);


        return view('leader_view/user/index', compact('user', 'divisi', 'mitra', 'filterMitra', 'filterDivisi'));
    }

    public function indexLembur()
    {
        $kerjasama = Auth::user()->kerjasama_id;
        if (Auth::user()->divisi->jabatan->code_jabatan == "CO-CS") {
            $codeJabatan = ['OCS', 'CO-CS'];
            $lembur = Lembur::latest()->where('kerjasama_id', $kerjasama)->whereHas('user.divisi.jabatan', function ($query) use ($codeJabatan) {
                $query->whereIn('code_jabatan', $codeJabatan);
            })->paginate(31);
        } else if (Auth::user()->divisi->jabatan->code_jabatan == "CO-SCR") {
            $codeJabatan = ['SCR', 'CO-SCR'];
            $lembur = Lembur::latest()->where('kerjasama_id', $kerjasama)->whereHas('user.divisi.jabatan', function ($query) use ($codeJabatan) {
                $query->whereIn('code_jabatan', $codeJabatan);
            })->paginate(31);
        } else {
            $lembur = Lembur::where('kerjasama_id', $kerjasama)->paginate(30);
        }
        return view('leader_view/lembur/index', compact('lembur'));
    }

    public function indexAbsenSholat()
    {
        $authUser = Auth::user()->loadMissing('divisi.jabatan');
        $kerjasama = $authUser->kerjasama_id;
        $jabatan = $authUser->divisi->jabatan->code_jabatan ?? null;

        $codeJabatan = match ($jabatan) {
            'CO-CS' => ['OCS', 'CO-CS', 'TMN', 'TKS', 'SA', 'PBI'],
            'CO-SCR' => ['SCR', 'CO-SCR', 'RCP', 'JK', 'FO', 'KSR', 'PTR', 'DRV', 'PBI'],
            default => [],
        };

        $user = User::query()
            ->select('id')
            ->where('kerjasama_id', $kerjasama)
            ->when($codeJabatan, function ($query) use ($codeJabatan) {
                $query->whereHas('divisi.jabatan', function ($query) use ($codeJabatan) {
                    $query->whereIn('code_jabatan', $codeJabatan);
                });
            })
            ->get();

        $absen = Absensi::query()
            ->select('id', 'user_id', 'subuh', 'dzuhur', 'asar', 'maghrib', 'isya')
            ->with('user:id,nama_lengkap')
            ->where('kerjasama_id', $kerjasama)
            ->where('tanggal_absen', Carbon::now()->format('Y-m-d'))
            ->latest('id')
            ->get();

        return view('leader_view/absenSholat/index', compact('user', 'absen'));
    }

    public function storeAbsenSholat(Request $request)
    {
        $now = Carbon::now();
        $time = $now->format('H:i:s');
        $field = match (true) {
            $time >= '11:20:00' && $time <= '14:10:00' => ['fotoDzuhur', 'dzuhur'],
            $time >= '17:20:00' && $time <= '18:45:00' => ['fotoMagrib', 'magrib'],
            default => null,
        };

        if ($field) {
            $imageParts = explode(';base64,', (string) $request->fotoSholat, 2);
            abort_unless(count($imageParts) === 2, 422, 'Foto tidak valid.');
            $fileName = uniqid('', true) . '-data.png';
            Storage::put('public/images/' . $fileName, base64_decode($imageParts[1], true));

            Absensi::whereIn('user_id', $request->user)
                ->where('tanggal_absen', $now->format('Y-m-d'))
                ->update([$field[0] => $fileName, $field[1] => 1]);
        }

        toastr()->success('Berhasil Absen Sholat');
        return to_route('dashboard.index');
    }
}
