<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\User;
use App\Models\PekerjaanCp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Http as httped;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManagerStatic as Images;

class CheckPointController extends Controller
{
    public function history(Request $request)
    {
        $now = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();
        $records = CheckPoint::where('user_id', Auth::id())->get();
        $recordsByDate = $records->flatMap(function (CheckPoint $checkpoint): array {
            $dates = collect((array) $checkpoint->tanggal)->filter();
            if ($dates->isEmpty()) $dates = collect([$checkpoint->created_at]);
            return $dates->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => $checkpoint->id])->all();
        });
        $rejectedByDate = $records->flatMap(function (CheckPoint $checkpoint): array {
            $statuses = is_array($checkpoint->approve_status) ? $checkpoint->approve_status : json_decode($checkpoint->approve_status ?: '[]', true);
            if (!is_array($statuses) || !collect($statuses)->contains('denied')) return [];
            $dates = collect((array) $checkpoint->tanggal)->filter();
            if ($dates->isEmpty()) $dates = collect([$checkpoint->created_at]);
            return $dates->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true])->all();
        });
        $calendar = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $calendar->push(['date' => $date->copy(), 'hasData' => $recordsByDate->has($date->toDateString()), 'recordId' => $recordsByDate->get($date->toDateString()), 'rejected' => $rejectedByDate->has($date->toDateString())]);
        }
        return view('check.history', [
            'calendar' => $calendar,
            'previousMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'start' => $start,
        ]);
    }

    public function historyShow($id)
    {
        $checkpoint = CheckPoint::where('user_id', Auth::id())->findOrFail($id);
        $jobs = PekerjaanCp::whereIn('id', array_filter((array) $checkpoint->pekerjaan_cp_id))->get()->keyBy('id');
        return view('check.history-detail', compact('checkpoint', 'jobs'));
    }

    public function index(Request $request)
    {
        $now = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();
        $endMonth = $now->endOfMonth()->format('d');
        $today = $now->format('Y-m-d');
        $todayName = $now->translatedFormat('l');
        $start = $now->copy()->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        $weekendDates = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekend()) {
                $weekendDates[] = $date->format('d');
            }
        }

        $recordsByDate = Cache::remember(
            "checkpoint-calendar:" . Auth::id(),
            now()->addSeconds(30),
            fn () => CheckPoint::where('user_id', Auth::id())
                ->select(['id', 'tanggal', 'created_at'])
                ->get()
                ->flatMap(function (CheckPoint $checkpoint): array {
                    $dates = collect(is_array($checkpoint->tanggal) ? $checkpoint->tanggal : [$checkpoint->tanggal])->filter();
                    if ($dates->isEmpty()) {
                        $dates = collect([$checkpoint->created_at]);
                    }
                    return $dates->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => $checkpoint->id])->all();
                })
        );
        $rejectedDates = Cache::remember(
            "checkpoint-calendar-rejected:" . Auth::id(),
            now()->addSeconds(30),
            fn () => CheckPoint::where('user_id', Auth::id())
                ->select(['id', 'tanggal', 'created_at', 'approve_status'])
                ->get()
                ->flatMap(function (CheckPoint $checkpoint): array {
                    $statuses = is_array($checkpoint->approve_status) ? $checkpoint->approve_status : json_decode($checkpoint->approve_status ?: '[]', true);
                    if (!is_array($statuses) || !collect($statuses)->contains('denied')) return [];
                    $dates = collect(is_array($checkpoint->tanggal) ? $checkpoint->tanggal : [$checkpoint->tanggal])->filter();
                    if ($dates->isEmpty()) $dates = collect([$checkpoint->created_at]);
                    return $dates->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true])->all();
                })
        );
        $calendar = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $calendar->push(['date' => $date->copy(), 'hasData' => $recordsByDate->has($date->format('Y-m-d')), 'recordId' => $recordsByDate->get($date->format('Y-m-d')), 'rejected' => $rejectedDates->has($date->format('Y-m-d'))]);
        }
        $previousMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');
        return view('check.index', compact('calendar', 'previousMonth', 'nextMonth', 'start', 'today', 'weekendDates', 'endMonth'));
    }



    public function create(Request $request)
    {
        $selectedDate = $request->input('tanggal', Carbon::today()->format('Y-m-d'));
        $id = null;
        $user = Auth::user()->id;
        $c = CheckPoint::where('user_id', $user)->whereDate('created_at', Carbon::now()->format('Y-m-d'))->get();
        $pcp = PekerjaanCp::where('user_id', Auth::user()->id)->get();
        $pch = Checkpoint::where('user_id', Auth::user()->id)->where('type_check', 'harian')->whereDate('created_at', Carbon::now()->format('Y-m-d'))->get();

        $che = CheckPoint::query()->where('user_id', Auth::user()->id)->where('type_check', 'harian')->whereDate('created_at', Carbon::now()->format('Y-m-d'));
        $cheli = $che->selectRaw('pekerjaan_cp_id')->get();
        // dd($cheli);
        return view('check.create', compact('pcp', 'c', 'cheli', 'pch', 'id', 'selectedDate'));
    }

    public function store(Request $request)
    {
        $cek = new CheckPoint();

        $jobs = array_values($request->input('pekerjaan_id', []));
        $manual = array_values($request->input('input_manual', []));
        $data = [
            'user_id' => $request->user_id,
            'divisi_id' => $request->divisi_id,
            'pekerjaan_cp_id' => collect($jobs)->map(fn ($job) => $job === 'manual' || $job === '' || $job === null ? null : $job)->all(),
            'input_manual' => collect($jobs)->map(fn ($job, $i) => $job === 'manual' || $job === '' || $job === null ? ($manual[$i] ?? null) : null)->all(),
            'deskripsi' => array_values($request->input('deskripsi', [])),
            'latitude' => $request->latitude,
            'longtitude' => $request->longtitude,
            'type_check' => 'dikerjakan',
            'approve_status' => array_values($request->input('approve_status', [])),
            'tanggal' => array_values($request->input('tanggal', [])),
        ];
        // dd($request->all(), $data);

        $imagePaths = [];
        foreach ((array) $request->file('img') as $jobImages) {
            $paths = [];
            foreach ((array) $jobImages as $file) {
                if ($file?->isValid()) {
                    $name = 'data' . md5(uniqid('', true)) . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('images', $name, 'public');
                    $paths[] = $name;
                }
            }
            $imagePaths[] = $paths;
        }
        $data['img'] = $imagePaths;


        DB::beginTransaction();

        try {
            $cek->create($data);
            Cache::forget('checkpoint-calendar:' . Auth::id());
            DB::commit();
            toastr()->success('Data Berhasil Ditambahkan', [], 'success');
            return to_route('checkpoint-user.index');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            toastr()->error('Error in storing data', [], 'error');
            return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {
        $cex = CheckPoint::findOrFail($id);
        $pcp = PekerjaanCp::where('user_id', $cex->user_id)->get();

        return view('check.edit', ['pcp' => $pcp, 'cex' => $cex, 'id' => $id, 'selectedDate' => $request->input('tanggal', optional($cex->created_at)->format('Y-m-d'))]);
    }

    public function update(Request $request, $id)
    {
        $cex2 = CheckPoint::findOrFail($id);

        $jobs = $request->input('pekerjaan_id', []);
        $manual = $request->input('input_manual', []);
        $deskripsi = $request->input('deskripsi', []);
        $tanggal = $request->input('tanggal', []);
        $approve = $request->input('approve_status', []);
        $existing = $request->input('existing_img', []);
        $files = $request->file('img', []);
        $originalIndexes = $request->input('original_index', []);
        $originalStatuses = is_array($cex2->approve_status) ? $cex2->approve_status : json_decode($cex2->approve_status ?: '[]', true);
        $originalStatuses = is_array($originalStatuses) ? $originalStatuses : [];
        $originalNotes = is_array($cex2->note) ? $cex2->note : json_decode($cex2->note ?: '[]', true);
        $originalNotes = is_array($originalNotes) ? $originalNotes : [];

        $indexes = array_keys($jobs);
        sort($indexes);

        $pekerjaanCpId = [];
        $inputManual = [];
        $deskripsiOut = [];
        $tanggalOut = [];
        $approveOut = [];
        $images = [];

        foreach ($indexes as $i) {
            $originalIndex = array_key_exists($i, $originalIndexes) && $originalIndexes[$i] !== '' ? (int) $originalIndexes[$i] : $i;
            $job = $jobs[$i] ?? null;
            $isManual = $job === 'manual' || $job === '' || $job === null;

            $pekerjaanCpId[] = $isManual ? null : $job;
            $inputManual[] = $isManual ? trim((string) ($manual[$i] ?? '')) : null;
            $deskripsiOut[] = $deskripsi[$i] ?? null;
            $tanggalOut[] = $tanggal[$i] ?? null;
            $originalStatus = strtolower(trim((string) ($originalStatuses[$originalIndex] ?? '')));
            $submittedStatus = strtolower(trim((string) ($approve[$i] ?? 'proccess')));
            // Any edited rejected row must return to Direksi review.
            $approveOut[] = $originalStatus === 'denied' ? 'proccess' : ($submittedStatus ?: 'proccess');

            $paths = [];
            foreach ((array) ($files[$i] ?? []) as $file) {
                if ($file?->isValid()) {
                    $name = 'data' . md5(uniqid('', true)) . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('images', $name, 'public');
                    $paths[] = $name;
                }
            }
            if (!count($paths)) {
                $paths = array_values(array_filter(explode(',', (string) ($existing[$i][0] ?? '')), fn ($p) => $p !== ''));
            }
            $images[] = $paths;
        }

        $cex2->pekerjaan_cp_id = $pekerjaanCpId;
        $cex2->input_manual = $inputManual;
        $cex2->deskripsi = $deskripsiOut;
        $cex2->tanggal = $tanggalOut;
        $cex2->note = array_map(fn ($status, $i) => $status === 'proccess' ? null : ($originalNotes[$originalIndexes[$i] ?? $i] ?? null), $approveOut, array_keys($approveOut));
        $cex2->img = $images;
        $cex2->latitude = $request->input('latitude', $cex2->latitude);
        $cex2->longtitude = $request->input('longtitude', $cex2->longtitude);
        $cex2->type_check = $cex2->type_check ?: 'dikerjakan';

        try {
            $cex2->save();
            Cache::forget('checkpoint-calendar:' . $cex2->user_id);
            Cache::forget('checkpoint-calendar-rejected:' . $cex2->user_id);
            toastr()->success('Data berhasil diedit', [], 'success');
            return to_route('checkpoint-user.index');
        } catch (\Illuminate\Database\QueryException $e) {
            toastr()->error('Data Tidak Ada', [], 'error');
            return redirect()->back();
        }
    }

    public function editBukti(Request $request)
    {

        $cId = $request->cpId;

        $pcp = PekerjaanCp::all();
        // $cex = CheckPoint::latest()->first();
        $awalMinggu = Carbon::now()->startOfWeek()->subWeek();
        $akhirMinggu = Carbon::now()->endOfWeek()->subDays(2); // Mengurangi 2 hari untuk mendapatkan hari Jumat sebagai akhir minggu
        if ($cId) {
            $cex = CheckPoint::findOrFail($cId);
        } else {
            $cex = CheckPoint::whereBetween('created_at', [$awalMinggu, $akhirMinggu])
                ->where('user_id', Auth::user()->id)
                ->where('type_check', 'rencana')
                ->latest()
                ->first();
        }

        // dd($cId);
        return view('check.editBukti', compact('cex', 'pcp', 'cId'));
    }

    public function uploadBukti(Request $request)
    {
        $awalMinggu = Carbon::now()->startOfWeek()->subWeek()->addDays(5);
        $akhirMinggu = Carbon::now()->endOfWeek()->subDays(2);
        $cId = $request->cpId;

        $userId = Auth::user()->id;
        $cex = CheckPoint::whereBetween('created_at', [$awalMinggu, $akhirMinggu])
            ->where('user_id', $userId)
            ->where('type_check', 'rencana')
            ->latest()
            ->first();
        $cex2 = CheckPoint::whereBetween('created_at', [Carbon::now()->startOfWeek(), $akhirMinggu])
            ->where('user_id', $userId)
            ->where('type_check', 'dikerjakan')
            ->latest()
            ->first();

        // Initialize data for new or existing checkpoint
        $data = [
            'user_id' => $request->user_id,
            'divisi_id' => $request->divisi_id,
            // 'latitude' => $request->latitude,
            // 'longtitude' => $request->longtitude,
            'type_check' => 'dikerjakan'
        ];

        // If no 'dikerjakan' checkpoint exists, create data array for new record
        if (!$cex2) {
            $data['pekerjaan_cp_id'] = $request->pekerjaan_cp_id;
            $data['approve_status'] = $request->approve_status;
            $data['note'] = $request->note;
            $data['latitude'] = $request->latitude;
            $data['longtitude'] = $request->longtitude;
            $data['tanggal'] = $request->tanggal;
        } else {
            $cex2->fill($data);
            $cex2->pekerjaan_cp_id = array_merge($cex2->pekerjaan_cp_id ?? [], $request->pekerjaan_cp_id ?? []);
            $cex2->approve_status = array_merge($cex2->approve_status ?? [], $request->approve_status ?? []);
            $cex2->latitude = array_merge($cex2->latitude ?? [], $request->latitude ?? []);
            $cex2->longtitude = array_merge($cex2->longtitude ?? [], $request->longtitude ?? []);
            $cex2->tanggal = array_merge($cex2->tanggal ?? [], $request->tanggal ?? []);
        }

        // Handle file uploads
        $imagePaths = [];
        if ($request->hasFile('img')) {
            foreach ($request->file('img') as $image) {
                if ($image) {
                    $extensions = $image->getClientOriginalExtension();
                    $randomNumber = md5(uniqid(rand(), true));
                    $rename = 'data' . $randomNumber . '.' . $extensions;
                    $path = public_path('storage/images/' . $rename);
                    $image->storeAs('images', $rename, 'public');
                    $imagePaths[] = $rename;
                }
            }
            if (!$cex2) {
                $data['img'] = $imagePaths;
            } else {
                $cex2->img = array_merge($cex2->img ?? [], $imagePaths);
            }
        }

        // Filter and merge deskripsi values
        if ($request->has('deskripsi')) {
            $deskripsi = array_filter($request->deskripsi, function ($value) {
                return $value !== null;
            });
            if (!$cex2) {
                $data['deskripsi'] = array_values($deskripsi);
            } else {
                $cex2->deskripsi = array_merge($cex2->deskripsi ?? [], array_values($deskripsi));
            }
        }
        // dd($request->all(), $data, $cex2);

        try {
            if (!$cex2) {
                CheckPoint::create($data);
            } else {
                $cex2->save();
            }
            Cache::forget('checkpoint-calendar:' . $userId);

            toastr()->success('Data Berhasil Diupload', [], 'success');
            return to_route('checkpoint-user.index', 'type=dikerjakan');
        } catch (\Illuminate\Database\QueryException $e) {
            toastr()->error('Data Tidak Berhasil Dikirim', [], 'error');
            return redirect()->back();
        }
    }
    public function uploadNilai(Request $request, $id)
    {
        $cex2 = CheckPoint::findOrFail($id);

        $arrKe = (int) $request->arrKe;

        // dd($cex2, $request->all());

        // Ensure we get arrays from DB (decode JSON if not casted)
        $approveStatus = is_array($cex2->approve_status)
            ? $cex2->approve_status
            : json_decode($cex2->approve_status ?? '[]', true);

        $note = is_array($cex2->note)
            ? $cex2->note
            : json_decode($cex2->note ?? '[]', true);

        // Fill missing indexes if arrays are shorter
        $approveStatus = array_pad($approveStatus, max($arrKe + 1, count($approveStatus)), null);
        $note = array_pad($note, max($arrKe + 1, count($note)), null);

        // Update at the given index
        if ($request->filled('approve_status')) {
            $approveStatus[$arrKe] = $request->approve_status[0];
        }

        // Note can be null, so we just assign directly
        $note[$arrKe] = $request->note[0] ?? null;

        // Save back to model
        $cex2->approve_status = $approveStatus;
        $cex2->note = $note;

        try {
            $cex2->save();
            toastr()->success('Data berhasil diedit', [], 'success');
        } catch (\Throwable $e) {
            toastr()->error('Gagal menyimpan data', [], 'error');
        }

        return redirect()->back();
    }




    public function deleteRencana(Request $request, $id)
    {
        $cek = CheckPoint::findOrFail($id);
        try {
            // Check if arrKe exists in the request
            if ($request->has('arrKe')) {
                $arrKe = $request->arrKe;

                // Check if arrKe is a valid index in the array
                if (isset($cek->pekerjaan_cp_id[$arrKe])) {
                    // Create a copy of the arrays
                    $pekerjaan_cp_id = $cek->pekerjaan_cp_id;

                    // Unset the item at the specified index
                    unset($pekerjaan_cp_id[$arrKe]);

                    // Reset array keys to maintain continuity
                    $pekerjaan_cp_id = array_values($pekerjaan_cp_id);

                    // Assign modified arrays back to the object properties
                    $cek->pekerjaan_cp_id = $pekerjaan_cp_id;

                    // dd($cek);
                    // Save the changes to the model
                    $cek->save();

                    toastr()->warning('Data Telah Dihapus', [], 'warning');
                    return redirect()->back();
                } else {
                    toastr()->error('Index Tidak Valid', [], 'error');
                    return redirect()->back();
                }
            } else {
                toastr()->error('Parameter arrKe Tidak Ditemukan', [], 'error');
                return redirect()->back();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            toastr()->error('Data Tidak Ditemukan', [], 'error');
            return redirect()->back();
        }
    }
    public function destroy($id)
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

    public function exportWith(Request $request)
    {

        $currentMonth = $request->this_month;
        $user_id = $request->user_id;

        $user = User::firstWhere('id', $user_id);
        $nowMonth = Carbon::createFromFormat('Y-m', $currentMonth)->translatedFormat('F Y');

        // dd($request->all());

        if ($request->has(['this_month', 'user_id'])) {

            $cp = CheckPoint::whereMonth('created_at', Carbon::createFromFormat('Y-m', $currentMonth))->where('user_id', $user_id)->get();
            // dd($cp, $currentMonth);
            $path = 'logo/sac.png';
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $options = new Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');

            $pdf = new Dompdf($options);
            $html = view('admin.check.export', compact('base64', 'cp', 'user', 'nowMonth'))->render();
            $pdf->loadHtml($html);

            $pdf->setPaper('A4', 'landscape');
            $pdf->render();

            $output = $pdf->output();
            $filename = 'check_point.pdf';

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

    public function show($id)
    {
        $cex = CheckPoint::findOrFail($id);
        return view('admin.check.maps', compact('cex'));
    }
}
