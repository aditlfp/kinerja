<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\User;
use App\Models\PekerjaanCp;
use App\Models\WorkOrder;
use App\Services\CheckPointCalendarService;
use App\Services\CheckPointReportWindow;
use App\Services\CheckPointSyncService;
use App\Services\CheckPointService;
use App\Http\Requests\StoreCheckPointRequest;
use App\Http\Requests\UpdateCheckPointRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Http as httped;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Images;

class CheckPointController extends Controller
{
    public function __construct(
        private readonly CheckPointSyncService $syncService,
        private readonly CheckPointCalendarService $calendarService,
        private readonly CheckPointService $checkPointService,
        private readonly CheckPointReportWindow $reportWindow,
    ) {}

    public function history(Request $request)
    {
        $now = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $calendar = $this->calendarService->forMonth(Auth::id(), $start, $end);

        return view('check.history', [
            'calendar' => $calendar,
            'previousMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'start' => $start,
        ]);
    }

    public function historyShow($id)
    {
        $checkpoint = CheckPoint::with('items.images', 'items.messages.images', 'items.messages.user', 'items.messages.acknowledgement')
            ->where(function ($query): void {
                $query->where('user_id', Auth::id())
                    ->orWhereHas('participants', fn ($participants) => $participants->whereKey(Auth::id()));
            })
            ->findOrFail($id);
        $date = request()->input('tanggal');
        $date ??= $checkpoint->items->first()?->tanggal?->format('Y-m-d') ?? $checkpoint->created_at->format('Y-m-d');
        $related = CheckPoint::with('items.images', 'items.messages.images', 'items.messages.user', 'items.messages.acknowledgement')
            ->where(function ($query): void {
                $query->where('user_id', Auth::id())
                    ->orWhereHas('participants', fn ($participants) => $participants->whereKey(Auth::id()));
            })
            ->whereHas('items', fn ($items) => $items->whereDate('tanggal', $date))
            ->get();
        $checkpoint->setRelation('items', $related->flatMap->items->sortBy('urutan')->values());
        $jobs = PekerjaanCp::whereIn('id', $checkpoint->items->pluck('pekerjaan_cp_id')->filter())
            ->get()
            ->keyBy('id');
        return view('check.history-detail', compact('checkpoint', 'jobs'));
    }

    public function index(Request $request)
    {
        $now = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();
        $endMonth = $now->copy()->endOfMonth()->format('d');
        $today = $now->format('Y-m-d');
        $start = $now->copy()->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        $weekendDates = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekend()) {
                $weekendDates[] = $date->format('d');
            }
        }

        $calendar = $this->calendarService->forMonth(Auth::id(), $start, $end);

        $previousMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        return view('check.index', compact('calendar', 'previousMonth', 'nextMonth', 'start', 'today', 'weekendDates', 'endMonth'));
    }



    public function workOrderFile(\App\Models\WorkOrderImage $image)
    {
        abort_unless($image->workOrder->user_id === Auth::id(), 403);
        return response()->download(storage_path('app/public/' . $image->path), basename($image->path));
    }

    public function create(Request $request)
    {
        if ($denied = $this->reportWindow->reject()) {
            return $denied;
        }

        $workOrder = $request->filled('work_order')
            ? WorkOrder::with(['creator', 'images'])->findOrFail($request->integer('work_order'))
            : null;

        $selectedDate = $workOrder?->tanggal
            ? Carbon::parse($workOrder->tanggal)->format('Y-m-d')
            : $request->input('tanggal', Carbon::today()->format('Y-m-d'));

        if ($denied = $this->reportWindow->rejectDate($selectedDate)) {
            return $denied;
        }

        $pcp = PekerjaanCp::where('user_id', Auth::id())->get();
        $sharedCheckpoints = CheckPoint::with(['items.images'])
            ->whereHas('participants', fn ($query) => $query->whereKey(Auth::id()))
            ->whereHas('items', fn ($query) => $query->whereDate('tanggal', $selectedDate))
            ->latest('id')
            ->get();
        $sharedJobs = PekerjaanCp::whereIn('id', $sharedCheckpoints->flatMap(fn ($checkpoint) => $checkpoint->items->pluck('pekerjaan_cp_id'))->filter())->get()->keyBy('id');
        $sharedUsers = Auth::user()->devisi_id == 3
            ? User::query()
                ->whereKeyNot(Auth::id())
                ->where(function ($query): void {
                    $query->where('kerjasama_id', 1)
                        ->orWhereHas('divisi', fn ($divisi) => $divisi->where('name', 'SPV-W'));
                })
                ->where('name', '!=', 'admin')
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'devisi_id'])
            : collect();

        return view('check.create', compact('pcp', 'selectedDate', 'workOrder', 'sharedUsers', 'sharedCheckpoints', 'sharedJobs'));
    }

    public function store(StoreCheckPointRequest $request)
    {
        if ($denied = $this->reportWindow->reject()) {
            return $denied;
        }

        $date = collect((array) $request->input('tanggal', []))->filter()->first();
        if ($date && ($denied = $this->reportWindow->rejectDate($date))) {
            return $denied;
        }

        if ($error = $this->syncService->imageLimitError($request) ?? $this->syncService->uploadSizeError($request)) {
            toastError($error);
            return redirect()->back()->withInput();
        }

        try {
            $checkpoint = $this->checkPointService->store($request->validated(), $request);
            $this->calendarService->forget(Auth::id());
            $checkpoint->participants()->pluck('users.id')->each(fn ($userId) => $this->calendarService->forget((int) $userId));
            toastSuccess('Data Berhasil Disimpan');
            return to_route('checkpoint-user.index');
        } catch (\Throwable $e) {
            report($e);
            toastError('Data gagal disimpan');
            return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {
        $cex = CheckPoint::where('user_id', Auth::id())->with(['workOrder', 'items.images', 'items.messages.images', 'items.messages.user', 'items.messages.acknowledgement'])->findOrFail($id);

        // Pekan laporan tertutup: halaman ini tetap dibuka hanya bila ada
        // catatan evaluasi Direksi yang boleh ditindaklanjuti karyawan.
        $windowLocked = $this->reportWindow->blocks();

        if ($windowLocked && ! $cex->hasEvaluationNotes()) {
            return $this->reportWindow->reject();
        }

        $editDate = $request->input('tanggal', $cex->items->first()?->tanggal?->format('Y-m-d'));
        $relatedItems = CheckPoint::where('user_id', Auth::id())
            ->whereHas('items', fn ($items) => $items->whereDate('tanggal', $editDate))
            ->with('items.images')
            ->get()
            ->flatMap->items
            ->sortBy('urutan')
            ->values();
        if ($relatedItems->isNotEmpty()) {
            $cex->setRelation('items', $relatedItems);
        }
        $pcp = PekerjaanCp::where('user_id', $cex->user_id)->get();

        return view('check.edit', [
            'pcp' => $pcp,
            'cex' => $cex,
            'id' => $id,
            'windowLocked' => $windowLocked,
            'selectedDate' => $request->input('tanggal', optional($cex->created_at)->format('Y-m-d')),
        ]);
    }

    public function update(UpdateCheckPointRequest $request, $id)
    {
        $cex2 = CheckPoint::where('user_id', Auth::id())->with('items')->findOrFail($id);

        // Saat pekan ditutup hanya baris bercatatan evaluasi yang boleh diubah,
        // dan sync service menolak menambah/menghapus baris lain.
        $evaluationRowsOnly = $this->reportWindow->blocks();

        if ($evaluationRowsOnly && ! $cex2->hasEvaluationNotes()) {
            return $this->reportWindow->reject();
        }

        if ($error = $this->syncService->imageLimitError($request) ?? $this->syncService->uploadSizeError($request)) {
            toastError($error);
            return redirect()->back()->withInput();
        }

        try {
            $this->checkPointService->update($cex2, $request, $evaluationRowsOnly);
            $this->calendarService->forget($cex2->user_id);
            toastSuccess('Data berhasil diedit');
            return to_route('checkpoint-user.index');
        } catch (\Throwable $e) {
            report($e);
            toastError('Data gagal diedit');
            return redirect()->back();
        }
    }

    public function editBukti(Request $request)
    {
        if ($denied = $this->reportWindow->reject()) {
            return $denied;
        }

        $cId = $request->cpId;

        $pcp = PekerjaanCp::all();
        // $cex = CheckPoint::latest()->first();
        $awalMinggu = $this->reportWindow->weekStart()->subWeek();
        // Batas pekan mengikuti pengaturan admin (default Sabtu).
        $akhirMinggu = $this->reportWindow->deadlineForWeek();
        if ($cId) {
            $cex = CheckPoint::where('user_id', Auth::id())->findOrFail($cId);
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

    public function uploadChunk(Request $request)
    {
        $data = $request->validate([
            'upload_id' => ['required', 'uuid'],
            'chunk' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:1', 'max:10000'],
            'file' => ['required', 'file', 'max:2048'],
        ]);
        abort_if($data['chunk'] >= $data['total'], 422, 'Chunk tidak valid.');

        $directory = 'checkpoint-chunks/' . Auth::id() . '/' . $data['upload_id'];
        Storage::disk('local')->putFileAs($directory, $request->file('file'), $data['chunk'] . '.part');

        return response()->json(['ok' => true]);
    }

    public function completeUpload(Request $request)
    {
        $data = $request->validate(['upload_id' => ['required', 'uuid']]);
        $directory = 'checkpoint-chunks/' . Auth::id() . '/' . $data['upload_id'];
        $disk = Storage::disk('local');
        $parts = collect($disk->files($directory))
            ->filter(fn (string $path): bool => preg_match('/\\/\\d+\\.part$/', str_replace('\\', '/', $path)) === 1)
            ->sortBy(fn (string $path): int => (int) pathinfo($path, PATHINFO_FILENAME))
            ->values();
        abort_if($parts->isEmpty(), 422, 'Upload tidak ditemukan.');

        $token = (string) \Illuminate\Support\Str::uuid();
        $assembled = storage_path('app/checkpoint-complete/' . Auth::id() . '/' . $token . '.upload');
        if (! is_dir(dirname($assembled))) {
            mkdir(dirname($assembled), 0700, true);
        }
        $output = fopen($assembled, 'wb');
        foreach ($parts as $part) {
            $input = fopen($disk->path($part), 'rb');
            stream_copy_to_stream($input, $output);
            fclose($input);
        }
        fclose($output);
        $disk->deleteDirectory($directory);
        $disk->put('checkpoint-complete/' . Auth::id() . '/' . $token . '.json', json_encode([
            'path' => $assembled,
            'name' => $data['upload_id'] . '.jpg',
        ], JSON_THROW_ON_ERROR));

        return response()->json(['token' => $token]);
    }

    public function uploadBukti(Request $request)
    {
        if ($request->filled('cpId')) {
            abort_unless(CheckPoint::where('id', $request->cpId)->where('user_id', Auth::id())->exists(), 403);
        }

        if ($denied = $this->reportWindow->reject()) {
            return $denied;
        }

        if ($error = $this->syncService->imageLimitError($request) ?? $this->syncService->uploadSizeError($request)) {
            toastError($error);
            return redirect()->back()->withInput();
        }

        $akhirMinggu = $this->reportWindow->deadlineForWeek();
        $cId = $request->cpId;

        $userId = Auth::user()->id;
        $cex2 = CheckPoint::whereBetween('created_at', [$this->reportWindow->weekStart(), $akhirMinggu])
            ->where('user_id', $userId)
            ->where('type_check', 'dikerjakan')
            ->latest()
            ->first();

        DB::beginTransaction();

        try {
            if (! $cex2) {
                $cex2 = CheckPoint::create([
                    'user_id' => $request->user_id,
                    'divisi_id' => $request->divisi_id,
                    'type_check' => 'dikerjakan',
                ]);
            }

            $this->syncService->appendFromRequest($cex2, $request);

            $this->calendarService->forget($userId);
            DB::commit();

            toastSuccess('Data Berhasil Diupload');
            return to_route('checkpoint-user.index', 'type=dikerjakan');
        } catch (\Throwable $e) {
            DB::rollback();
            report($e);
            toastError('Data Tidak Berhasil Dikirim');
            return redirect()->back();
        }
    }
    public function uploadNilai(Request $request, $id)
    {
        $cex2 = CheckPoint::where('user_id', Auth::id())->findOrFail($id);

        $status = $request->input('approve_status');
        if (is_array($status)) {
            $status = reset($status);
        }

        $note = $request->input('note');
        if (is_array($note)) {
            $note = reset($note);
        }

        $item = $this->resolveItem($cex2, $request->input('arrKe'));

        if (! $item) {
            toastError('Pekerjaan tidak ditemukan');
            return redirect()->back();
        }

        try {
            if (filled($status)) {
                $item->approve_status = $status;
            }
            $item->note = filled($note) ? $note : null;
            $item->save();

            toastSuccess('Data berhasil diedit');
        } catch (\Throwable $e) {
            report($e);
            toastError('Gagal menyimpan data');
        }

        return redirect()->back();
    }

    /**
     * Resolve a submitted row reference to a CheckPointItem.
     *
     * Newer views post the item primary key; legacy callers posted the row
     * position, so fall back to the ordered row when the id does not match.
     */
    private function resolveItem(CheckPoint $checkPoint, mixed $reference): ?CheckPointItem
    {
        if (! filled($reference)) {
            return null;
        }

        $reference = (int) $reference;

        return $checkPoint->items()->whereKey($reference)->first()
            ?: $checkPoint->items()->orderBy('urutan')->skip($reference)->first();
    }




    public function deleteRencana(Request $request, $id)
    {
        $cek = CheckPoint::findOrFail($id);

        try {
            $item = $this->resolveItem($cek, $request->input('arrKe'));

            if (! $item) {
                toastError('Parameter arrKe Tidak Ditemukan');
                return redirect()->back();
            }

            foreach ($item->images as $image) {
                Storage::disk('public')->delete('images/' . $image->path);
            }
            $item->delete();

            toastWarning('Data Telah Dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            toastError('Data Tidak Ditemukan');
        }

        return redirect()->back();
    }

    public function destroy($id)
    {
        try {
            $cek = CheckPoint::with('items.images')->findOrFail($id);
            $userId = $cek->user_id;

            foreach ($cek->items as $item) {
                foreach ($item->images as $image) {
                    Storage::disk('public')->delete('images/' . $image->path);
                }
            }
            $cek->delete();

            $this->calendarService->forget($userId);

            toastWarning('Data Telah Dihapus');
            return redirect()->back();
        } catch (\Illuminate\Database\QueryException $e) {
            toastError('Data Tidak Ditemukan');
            return redirect()->back();
        }
    }

    public function exportWith(Request $request)
    {

        $currentMonth = $request->this_month;
        $user_id = $request->user_id;

        $user = User::firstWhere('id', $user_id);
        $monthDate = Carbon::createFromFormat('Y-m', $currentMonth);
        $nowMonth = $monthDate->copy()->translatedFormat('F Y');

        // dd($request->all());

        if ($request->has(['this_month', 'user_id'])) {

            $cp = CheckPoint::with('items.images')
                ->whereBetween('created_at', [
                    $monthDate->copy()->startOfMonth()->startOfDay(),
                    $monthDate->copy()->endOfMonth()->endOfDay(),
                ])
                ->where('user_id', $user_id)
                ->get();
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
            toastError('Mohon Masukkan Filter Export');
            return redirect()->back();
        }
    }

    public function show($id)
    {
        $cex = CheckPoint::with('items.images')->findOrFail($id);
        return view('admin.check.maps', compact('cex'));
    }
}
