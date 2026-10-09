<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\User;
use App\Models\PekerjaanCp;
use App\Models\WorkOrder;
use App\Services\CheckPointCalendarService;
use App\Services\CheckPointSyncService;
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
        $checkpoint = CheckPoint::with('items.images')
            ->where('user_id', Auth::id())
            ->findOrFail($id);
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



    public function create(Request $request)
    {
        $workOrder = $request->filled('work_order')
            ? WorkOrder::with('creator')->findOrFail($request->integer('work_order'))
            : null;

        $selectedDate = $workOrder?->tanggal
            ? Carbon::parse($workOrder->tanggal)->format('Y-m-d')
            : $request->input('tanggal', Carbon::today()->format('Y-m-d'));

        $pcp = PekerjaanCp::where('user_id', Auth::id())->get();

        return view('check.create', compact('pcp', 'selectedDate', 'workOrder'));
    }

    public function store(Request $request)
    {
        if ($error = $this->syncService->imageLimitError($request)) {
            toastr()->error($error);
            return redirect()->back()->withInput();
        }

        $workOrderId = $request->work_order_id ?: null;

        DB::beginTransaction();

        try {
            $checkpoint = CheckPoint::create([
                'user_id' => $request->user_id,
                'divisi_id' => $request->divisi_id,
                'work_order_id' => $workOrderId,
                'type_check' => 'dikerjakan',
            ]);

            $this->syncService->syncFromRequest($checkpoint, $request);

            if ($workOrderId) {
                WorkOrder::findOrFail($workOrderId)->update(['has_complete' => 1]);
            }

            $this->calendarService->forget(Auth::id());
            DB::commit();
            toastr()->success('Data Berhasil Disimpan');
            return to_route('checkpoint-user.index');
        } catch (\Throwable $e) {
            DB::rollback();
            report($e);
            toastr()->error('Data gagal disimpan');
            return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {
        $cex = CheckPoint::with(['workOrder', 'items.images'])->findOrFail($id);
        $pcp = PekerjaanCp::where('user_id', $cex->user_id)->get();

        return view('check.edit', ['pcp' => $pcp, 'cex' => $cex, 'id' => $id, 'selectedDate' => $request->input('tanggal', optional($cex->created_at)->format('Y-m-d'))]);
    }

    public function update(Request $request, $id)
    {
        if ($error = $this->syncService->imageLimitError($request)) {
            toastr()->error($error);
            return redirect()->back()->withInput();
        }

        $cex2 = CheckPoint::findOrFail($id);

        DB::beginTransaction();

        try {
            $this->syncService->syncFromRequest($cex2, $request);

            $cex2->type_check = $cex2->type_check ?: 'dikerjakan';
            $cex2->save();

            $this->calendarService->forget($cex2->user_id);
            DB::commit();
            toastr()->success('Data berhasil diedit');
            return to_route('checkpoint-user.index');
        } catch (\Throwable $e) {
            DB::rollback();
            report($e);
            toastr()->error('Data gagal diedit');
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
        if ($error = $this->syncService->imageLimitError($request)) {
            toastr()->error($error);
            return redirect()->back()->withInput();
        }

        $awalMinggu = Carbon::now()->startOfWeek()->subWeek()->addDays(5);
        $akhirMinggu = Carbon::now()->endOfWeek()->subDays(2);
        $cId = $request->cpId;

        $userId = Auth::user()->id;
        $cex2 = CheckPoint::whereBetween('created_at', [Carbon::now()->startOfWeek(), $akhirMinggu])
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

            toastr()->success('Data Berhasil Diupload');
            return to_route('checkpoint-user.index', 'type=dikerjakan');
        } catch (\Throwable $e) {
            DB::rollback();
            report($e);
            toastr()->error('Data Tidak Berhasil Dikirim');
            return redirect()->back();
        }
    }
    public function uploadNilai(Request $request, $id)
    {
        $cex2 = CheckPoint::findOrFail($id);

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
            toastr()->error('Pekerjaan tidak ditemukan');
            return redirect()->back();
        }

        try {
            if (filled($status)) {
                $item->approve_status = $status;
            }
            $item->note = filled($note) ? $note : null;
            $item->save();

            toastr()->success('Data berhasil diedit');
        } catch (\Throwable $e) {
            report($e);
            toastr()->error('Gagal menyimpan data');
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
                toastr()->error('Parameter arrKe Tidak Ditemukan');
                return redirect()->back();
            }

            foreach ($item->images as $image) {
                Storage::disk('public')->delete('images/' . $image->path);
            }
            $item->delete();

            toastr()->warning('Data Telah Dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            toastr()->error('Data Tidak Ditemukan');
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

            toastr()->warning('Data Telah Dihapus');
            return redirect()->back();
        } catch (\Illuminate\Database\QueryException $e) {
            toastr()->error('Data Tidak Ditemukan');
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

            $cp = CheckPoint::with('items.images')
                ->whereMonth('created_at', Carbon::createFromFormat('Y-m', $currentMonth))
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
            toastr()->error('Mohon Masukkan Filter Export');
            return redirect()->back();
        }
    }

    public function show($id)
    {
        $cex = CheckPoint::with('items.images')->findOrFail($id);
        return view('admin.check.maps', compact('cex'));
    }
}
