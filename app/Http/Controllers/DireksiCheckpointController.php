<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\Kerjasama;
use App\Models\PekerjaanCp;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;
use App\Services\CheckPointCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class DireksiCheckpointController extends Controller
{
    public function __construct(
        private readonly CheckPointCalendarService $calendarService,
    ) {
    }
    public function index(Request $request)
    {
        $now = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();
        $filter = $request->filterKerjasama;
        $search = $request->search;
        $kerjasama = $this->kerjasama();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $users = User::query()
            ->select(['id', 'nama_lengkap', 'devisi_id', 'jabatan_id', 'kerjasama_id'])
            ->with(['divisi:id,name', 'jabatan:id,name_jabatan', 'kerjasama:id,client_id'])
            ->where('kerjasama_id', 1)
            ->whereNot('name', 'admin')
            ->when($filter, fn($query) => $query->where('kerjasama_id', $filter))
            ->when($search, fn($query) => $query->where('nama_lengkap', 'like', '%' . $search . '%'))
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();
        $previousMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        return view('direksi.checkpoint.index', compact('users', 'kerjasama', 'filter', 'search', 'previousMonth', 'nextMonth', 'start', 'end'));
    }

    public function calendar(Request $request, $user)
    {
        $type = $request->type ?? 'dikerjakan';
        $selectedMonth = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();

        $start = $selectedMonth->copy()->startOfMonth();
        $end = $selectedMonth->copy()->endOfMonth();
        $employee = User::query()
            ->select(['id', 'nama_lengkap', 'devisi_id', 'jabatan_id', 'kerjasama_id'])
            ->with(['divisi:id,name', 'jabatan:id,name_jabatan', 'kerjasama:id,client_id'])
            ->findOrFail($user);

        // Same calendar shape as the employee pages, filtered by type.
        $calendar = $this->calendarService->forMonth($employee->id, $start, $end, $type);

        return view(
            'direksi.checkpoint.calendar',
            compact('employee', 'calendar', 'start', 'type') + [
                'previousMonth' => $start->copy()->subMonth()->format('Y-m'),
                'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
                'totalRecords' => $calendar->sum('total'),
            ],
        );
    }

    public function history(Request $request)
    {
        $type = $request->type ?? 'dikerjakan';
        $selectedMonth = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();
        $start = $selectedMonth->copy()->startOfMonth();
        $filter = $request->filterKerjasama;
        $query = CheckPoint::where('type_check', $type);
        if ($filter) {
            $query->whereHas('user', fn($q) => $q->where('kerjasama_id', $filter));
        }
        $recordsByDate = collect();
        $countsByDate = collect();
        $query->with('items:id,check_point_id,tanggal,approve_status')
            ->get(['id', 'created_at'])
            ->each(function (CheckPoint $checkpoint) use ($recordsByDate, $countsByDate): void {
                if ($checkpoint->items->isEmpty()) {
                    $recordsByDate->put($checkpoint->created_at->toDateString(), $checkpoint->id);
                    return;
                }

                $byDate = $checkpoint->items->groupBy(
                    fn ($item) => Carbon::parse($item->tanggal ?? $checkpoint->created_at)->toDateString(),
                );

                foreach ($byDate as $date => $dayItems) {
                    $recordsByDate->put($date, $checkpoint->id);

                    // A day can span several batches; merge the status counts.
                    $dayCounts = CheckPointCalendarService::statusCounts($dayItems);
                    $existing = $countsByDate->get($date, ['accept' => 0, 'denied' => 0, 'process' => 0]);
                    $countsByDate->put($date, [
                        'accept' => $existing['accept'] + $dayCounts['accept'],
                        'denied' => $existing['denied'] + $dayCounts['denied'],
                        'process' => $existing['process'] + $dayCounts['process'],
                    ]);
                }
            });
        $calendar = collect();
        for ($date = $start->copy(); $date->lte($start->copy()->endOfMonth()); $date->addDay()) {
            $key = $date->toDateString();
            $calendar->push([
                'date' => $date->copy(),
                'hasData' => $recordsByDate->has($key),
                'recordId' => $recordsByDate->get($key),
                'counts' => $countsByDate->get($key, ['accept' => 0, 'denied' => 0, 'process' => 0]),
            ]);
        }
        return view('direksi.checkpoint.history', [
            'calendar' => $calendar,
            'start' => $start,
            'type' => $type,
            'filter' => $filter,
            'kerjasama' => $this->kerjasama(),
            'previousMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'totalRecords' => $recordsByDate->count(),
        ]);
    }

    public function historyDetail(Request $request, $id)
    {
        $isMcs = in_array(
            auth()->user()?->jabatan?->code_jabatan
                ?? auth()->user()?->divisi?->jabatan?->code_jabatan,
            ['MCS'],
            true
        );

        if ($request->worker) {
            $workOrder = WorkOrder::findOrFail($id);
            $selectedDate = $workOrder->tanggal->format('Y-m-d');
            $checkpoint = CheckPoint::with(['user:id,nama_lengkap', 'items.images', 'items.pekerjaanCp'])
                ->where('user_id', $workOrder->user_id)
                ->whereHas('items', fn ($q) => $q->whereDate('tanggal', $selectedDate))
                ->first();
        } else {
            $checkpoint = CheckPoint::with(['user:id,nama_lengkap', 'items.images', 'items.pekerjaanCp'])->findOrFail($id);
        }

        abort_if(! $checkpoint, 404, 'Bukti pekerjaan belum tersedia untuk perintah kerja ini.');

        return view('direksi.checkpoint.history-detail', compact('checkpoint', 'isMcs'));
    }

    public function updateApproval(Request $request, $id)
    {
        $data = $request->validate([
            'index' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['accept', 'denied'])],
            'note' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn() => $request->status === 'denied')],
        ]);
        $checkpoint = CheckPoint::findOrFail($id);
        $item = $checkpoint->items()->orderBy('urutan')->skip($data['index'])->first();
        abort_unless($item, 422);
        $item->approve_status = $data['status'];
        $item->note = $data['note'] ?? null;
        $item->save();

        $user = User::where('id', $checkpoint->user_id)->first();
        $workOrder = $checkpoint->work_order_id ?? null;
        if ($workOrder != null) {
            $user->notify(
                new WorkOrderNotification(
                    workOrderId: $workOrder,
                    title: 'Status Pekerjaan Di Update',
                    message: 'Pekerjaan sudah di update oleh Direksi.'
                )
            );
        } //else masih pending notify
        return back()->with('success', 'Status approval berhasil diperbarui.');
    }

    private function kerjasama()
    {
        return Cache::remember('admin.checkpoint.kerjasama-options', 300, fn() => Kerjasama::select('id', 'client_id')->with('client:id,name')->get());
    }
}
