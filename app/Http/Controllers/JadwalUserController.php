<?php

namespace App\Http\Controllers;

use App\Http\Requests\JadwalUserRequest;
use App\Models\Area;
use App\Models\JadwalUser;
use App\Models\Kerjasama;
use App\Models\Divisi;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JadwalImport;
use App\Exports\JadwalBulananExport;

class JadwalUserController extends Controller
{

    private const ALLOWED_ROLES = [
        'CO-CS'  => ['OCS', 'CO-CS', 'TMN', 'TKS', 'SA', 'PBI'],
        'CO-SCR' => ['SCR', 'CO-SCR', 'RCP', 'JK', 'FO', 'KSR', 'PTR', 'DRV', 'PBI'],
    ];

    /** Kode jabatan bawahan berdasarkan type_jabatan (SUPERVISOR PUSAT/SPV-A/SPV-W). */
    private const TYPE_ROLE_CODES = [
        'SUPERVISOR PUSAT SECURITY' => ['SCR', 'CO-SCR', 'RCP', 'JK', 'FO', 'KSR', 'PTR', 'DRV', 'PBI'],
        'SUPERVISOR PUSAT'          => ['OCS', 'CO-CS', 'TMN', 'TKS', 'SA', 'PBI'],
        'SECURITY'                  => ['SCR', 'CO-SCR', 'RCP', 'JK', 'FO', 'KSR', 'PTR', 'DRV', 'PBI'],
        'CLEANING SERVICE'          => ['OCS', 'CO-CS', 'TMN', 'TKS', 'SA', 'PBI'],
    ];

    /**
     * Tentukan kode jabatan yang boleh dilihat/dikelola oleh user.
     * Prioritas: type_jabatan jabatan user, lalu code_jabatan bawahan langsung.
     */
    private function resolveVisibleCodes(?string $typeJabatan, ?string $codeJabatan, ?string $nameJabatan = null): array
    {
        $type = strtoupper(trim((string) $typeJabatan));
        $name = strtoupper(trim((string) $nameJabatan));
        $label = $type . ' ' . $name;
        foreach (array_keys(self::TYPE_ROLE_CODES) as $key) {
            if (str_contains($label, $key)) {
                return self::TYPE_ROLE_CODES[$key];
            }
        }

        return $this->allowedCodes($codeJabatan);
    }

    private function allowedCodes(?string $code): array
    {
        return self::ALLOWED_ROLES[$code] ?? [];
    }

    public function __construct(Request $request)
    {
        $this->str = $request->input('str1');
        $this->ended = $request->input('end1');
        $this->divisi = $request->input('divisi');
    }

    public function index()
    {
        $kerj = Kerjasama::all();
        $divisi = Divisi::all();
        if (Auth::user()->divisi->jabatan->code_jabatan == "CO-CS") {
            $codeJabatan = self::ALLOWED_ROLES['CO-CS'];
            $user = User::where('kerjasama_id', Auth::user()->kerjasama_id)->whereHas('divisi.jabatan', function ($query) use ($codeJabatan) {
                $query->whereIn('code_jabatan', $codeJabatan);
            })->paginate(50);
        } else if (Auth::user()->divisi->jabatan->code_jabatan == "CO-SCR") {
            $codeJabatan = self::ALLOWED_ROLES['CO-SCR'];
            $user = User::where('kerjasama_id', Auth::user()->kerjasama_id)->whereHas('divisi.jabatan', function ($query) use ($codeJabatan) {
                $query->whereIn('code_jabatan', $codeJabatan);
            })->paginate(50);
        } elseif (in_array(Auth::user()->divisi->jabatan->code_jabatan, ['SPV-A', 'SPV-W', 'CO-SPV', 'MITRA'], true)) {
            $jabatanLogin = Auth::user()->divisi->jabatan;
            $visibleCodes = $this->resolveVisibleCodes($jabatanLogin->type_jabatan, $jabatanLogin->code_jabatan, $jabatanLogin->name_jabatan);
            $query = User::where('kerjasama_id', Auth::user()->kerjasama_id);
            if ($visibleCodes) {
                $query->whereHas('divisi.jabatan', fn ($q) => $q->whereIn('code_jabatan', $visibleCodes));
            }
            $user = $query->paginate(50);
        } else {
            $user = User::where('id', Auth::id())->paginate(50);
        }
        $shift = Shift::all();
        $area = Area::all();
        $jadwalUser = JadwalUser::latest()->paginate(50);
        $jadwalBulan = JadwalUser::query()
            ->select('tanggal')
            ->whereIn('user_id', $user->getCollection()->pluck('id'))
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($row) => Carbon::parse($row->tanggal)->startOfMonth())
            ->unique(fn ($date) => $date->format('Y-m'))
            ->values();
        $bulanIni = now()->startOfMonth();
        $jadwalBulanIniAda = $jadwalBulan->contains(fn ($bulan) => $bulan->isSameMonth($bulanIni));
        $canMonitor = in_array(Auth::user()->divisi->jabatan->code_jabatan ?? null, ['SPV-A', 'SPV-W', 'CO-SPV'], true);
        return view('leader_view.jadwalUser.index', compact('jadwalUser', 'jadwalBulan', 'jadwalBulanIniAda', 'canMonitor', 'kerj', 'divisi', 'user', 'shift', 'area'));
    }

    public function create(Request $request)
    {
        $auth = Auth::user()->load(['divisi.jabatan', 'kerjasama.client']);
        abort_unless(in_array($auth->divisi?->jabatan?->code_jabatan, ['CO-CS', 'CO-SCR'], true), 403);
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $days = collect(range(0, $start->daysInMonth - 1))->map(fn($day) => $start->copy()->addDays($day));
        $codes = $this->allowedCodes($auth->divisi->jabatan->code_jabatan);
        $users = User::select(['id', 'nama_lengkap', 'devisi_id', 'kerjasama_id'])->where('kerjasama_id', $auth->kerjasama_id)->whereHas('divisi.jabatan', fn($q) => $q->whereIn('code_jabatan', $codes))->orderBy('nama_lengkap')->get();

        $alreadyExists = JadwalUser::whereIn('user_id', $users->pluck('id'))
            ->whereBetween('tanggal', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
            ->exists();
        if ($alreadyExists) {
            toastr()->error("Jadwal bulan {$start->translatedFormat('F Y')} sudah dibuat.");
            return redirect()->route('leader-jadwal.index');
        }

        $schedules = JadwalUser::whereIn('user_id', $users->pluck('id'))->whereBetween('tanggal', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])->get()->keyBy(fn($row) => $row->user_id . '|' . $row->tanggal->format('Y-m-d'));
        return view('leader_view.jadwalUser.create', compact('users', 'days', 'schedules', 'month'));
    }

    public function processDate(Request $request)
    {
        $area = Area::with('kerjasama')->where('kerjasama_id', Auth::user()->kerjasama_id)->get();

        $str1 = $this->str;
        $end1 = $this->ended;
        $divisi = $this->divisi;

        $kerj = Kerjasama::all();
        $totalHari =  Carbon::parse($this->ended)->diffInDays(Carbon::parse($this->str));
        if ($request->has(['str1', 'end1', 'divisi'])) {
            if (Auth::user()->divisi->jabatan->code_jabatan == "MITRA" || Auth::user()->divisi->jabatan->code_jabatan == "LEADER") {
                $user = User::with('kerjasama')->where('kerjasama_id', Auth::user()->kerjasama_id)->where('devisi_id', $divisi)->get();
                $filter = Auth::user()->kerjasama_id;
            } else {
                $kerj = Kerjasama::all();
                $filter = $request->filter;
                $user = User::with('kerjasama')->where('kerjasama_id', $filter)->where('devisi_id', $divisi)->get();
            }
            $jadwal = JadwalUser::all();
            $shift = Shift::all();
            return view('leader_view.jadwalUser.create', compact('user', 'shift', 'totalHari', 'area', 'jadwal', 'str1', 'end1', 'kerj', 'filter'));
        } else {
            toastr()->error('Mohon Masukkan Taggal');
            return redirect()->back();
        }


        // return redirect()->to(route('leader-jadwal.create')->with('totalHari', $totalHari));
    }

    public function store(JadwalUserRequest $request)
    {
        dd($request->all());

        $jadwal = new JadwalUser();
        $user = User::all();

        $jadwal = [
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'tanggal' => $request->tanggal,
            'area_id' => $request->area,
            'status' => $request->status
        ];

        JadwalUser::create($jadwal);
        return redirect()->back();
    }

    public function edit($id) {}

    public function update(Request $request, $id) {}

    public function destroy($id)
    {
        $jadwalId = JadwalUser::findOrFail($id);
        $jadwalId->delete();
        toastr()->warning('Jadwal Telah Dihapus');
        return redirect()->back();
    }

    public function exportJadwal(Request $request)
    {
        $tanggalSekarang = Carbon::now();
        $currentMonth = Carbon::parse($this->ended)->month;
        $currentYear = Carbon::parse($this->str)->year;
        $str1 = $this->str;
        $end1 = $this->ended;
        $filter = $request->filter;
        $kerj = Kerjasama::all();
        $kerjabagus = Kerjasama::where('id', $filter)->first();

        $totalHari =  Carbon::parse($this->ended)->diffInDays(Carbon::parse($this->str));

        if ($request->has(['end1', 'str1'])) {

            $expPDF = User::with(['jadwalUser' => function ($query) use ($str1, $end1) {
                return $query->whereBetween('created_at', [$str1, $end1]);
            }])->when($filter, function ($query) use ($filter) {
                return $query->where('kerjasama_id', $filter);
            })->get();

            $path = 'logo/sac.png';
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $options = new Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');

            $pdf = new Dompdf($options);
            $html = view('leader_view.jadwalUser.export', compact('expPDF', 'base64', 'totalHari', 'currentYear', 'currentMonth', 'str1', 'end1', 'filter', 'kerj', 'kerjabagus'))->render();
            $pdf->loadHtml($html);

            $pdf->setPaper('A4', 'landscape');
            $pdf->render();

            $output = $pdf->output();
            $filename = 'jadwal.pdf';

            if ($request->input('action') == 'download') {
                return response()->download($output, $filename);
            }

            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        } else {
            toastr()->error('Mohon Masukkan Filter Export');
            return redirect()->back();
        }
    }

    public function storeMonthly(Request $request)
    {
        $auth = Auth::user()->load('divisi.jabatan');
        abort_unless(in_array($auth->divisi?->jabatan?->code_jabatan, ['CO-CS', 'CO-SCR'], true), 403);
        $data = $request->validate(['month' => ['required', 'date_format:Y-m'], 'schedule' => ['required', 'array'], 'schedule.*.*' => ['required', 'in:Pagi,Siang,Sore,Malam,OFF']]);
        $start = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
        $codes = $this->allowedCodes($auth->divisi->jabatan->code_jabatan);
        $allowed = User::where('kerjasama_id', $auth->kerjasama_id)->whereHas('divisi.jabatan', fn($q) => $q->whereIn('code_jabatan', $codes))->pluck('id')->flip();

        $alreadyExists = JadwalUser::whereIn('user_id', $allowed->keys())
            ->whereBetween('tanggal', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
            ->exists();
        abort_if($alreadyExists, 422, "Jadwal bulan {$start->translatedFormat('F Y')} sudah pernah dibuat.");
        $rows = [];
        foreach ($data['schedule'] as $userId => $dates) {
            abort_unless($allowed->has((int) $userId), 403);
            foreach ($dates as $day => $status) {
                $rows[] = ['user_id' => $userId, 'tanggal' => $start->copy()->day((int) $day)->toDateString(), 'status' => $status, 'shift_id' => null, 'area_id' => null, 'created_at' => now(), 'updated_at' => now()];
            }
        }
        JadwalUser::upsert($rows, ['user_id', 'tanggal'], ['status', 'shift_id', 'area_id', 'updated_at']);
        toastr()->success('Jadwal berhasil disimpan');
        return redirect()->route('leader-jadwal.create', ['month' => $data['month']]);
    }

    /** Handler lama untuk route non-leader (SPVW/DANRU/leader_view). */
    public function storeJadwal(Request $request)
    {
        $userIDs = $request->input('userID', []);
        $shifts = $request->input('shift', []);
        abort_if(count($userIDs) !== count($shifts), 422, 'Mismatch in user and shift data.');
        foreach ($userIDs as $index => $userID) {
            JadwalUser::create(['user_id' => $userID, 'shift_id' => $shifts[$index], 'tanggal' => $request->input('hari'), 'status' => 'M']);
        }
        toastr()->success('Data berhasil ditambahkan');
        return redirect()->back();
    }

    public function exportBulanan(Request $request)
    {
        $auth = Auth::user()->load(['divisi.jabatan', 'kerjasama.client']);
        $roleCode = $auth->divisi?->jabatan?->code_jabatan;
        abort_unless(in_array($roleCode, ['CO-CS', 'CO-SCR', 'SPV-A', 'SPV-W', 'CO-SPV'], true), 403);
        $month = $request->validate(['month' => ['required', 'date_format:Y-m']])['month'];
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $jabatan = $auth->divisi?->jabatan;
        $codes = $this->resolveVisibleCodes($jabatan?->type_jabatan, $roleCode, $jabatan?->name_jabatan);
        $usersQuery = User::where('kerjasama_id', $auth->kerjasama_id);
        if ($codes) {
            $usersQuery->whereHas('divisi.jabatan', fn($q) => $q->whereIn('code_jabatan', $codes));
        }
        $users = $usersQuery->orderBy('nama_lengkap')->get(['id', 'nama_lengkap']);
        $days = collect(range(0, $start->daysInMonth - 1))->map(fn($day) => $start->copy()->addDays($day));
        $schedules = JadwalUser::whereIn('user_id', $users->pluck('id'))->whereBetween('tanggal', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])->get()->keyBy(fn($row) => $row->user_id . '|' . $row->tanggal->format('Y-m-d'));
        if ($request->input('format') === 'xlsx') {
            return Excel::download(new JadwalBulananExport($users, $days, $schedules, $month, $auth->kerjasama->client->name), "jadwal-{$month}.xlsx");
        }
        $options = new Options();
        $options->setIsHtml5ParserEnabled(true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $pdf = new Dompdf($options);
        $clientName = $auth->kerjasama->client->name ?? '-';
        $pdf->loadHtml(view('leader_view.jadwalUser.export-table', compact('users', 'days', 'schedules', 'month', 'clientName'))->render());
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();
        if ($request->input('action') === 'inline') {
            return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');
        }
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="jadwal-' . $month . '.pdf"',
        ]);
    }

    public function getJadwal(Request $request, $id)
    {
        $jadwal = JadwalUser::where('user_id', $id)->latest()->get();
        return response()->json($jadwal);
    }

    public function import(Request $request)
    {
        Excel::import(new JadwalImport,  $request->file);
        toastr()->success('Data Success To Add');
        return redirect()->back();
    }
}
