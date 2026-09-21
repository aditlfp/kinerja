<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\Kerjasama;
use App\Models\PekerjaanCp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class DireksiCheckpointController extends Controller
{
    public function index(Request $request)
    {
        $now = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();
        $filter = $request->filterKerjasama;
        $search = $request->search;
        $kerjasama = $this->kerjasama();
        $start = $now->copy()->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        $users = User::query()
            ->select(['id', 'nama_lengkap', 'devisi_id', 'jabatan_id', 'kerjasama_id'])
            ->with(['divisi:id,name', 'jabatan:id,name_jabatan', 'kerjasama:id,client_id'])
            ->where('kerjasama_id', 1)
            ->whereNot('name', 'admin')
            ->when($filter, fn($query) => $query->where('kerjasama_id', $filter))
            ->when($search, fn($query) => $query->where('nama_lengkap', 'like', '%' . $search . '%'))
            ->orderBy('id')->paginate(25)->withQueryString();
        $previousMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        return view('direksi.checkpoint.index', compact('users', 'kerjasama', 'filter', 'search', 'previousMonth', 'nextMonth', 'start', 'end'));
    }

    public function calendar(Request $request, $user)
    {
        $type = $request->type ?? 'dikerjakan';
        $selectedMonth = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();

        $start = $selectedMonth->copy()->startOfMonth();
        $employee = User::query()->select(['id', 'nama_lengkap', 'devisi_id', 'jabatan_id', 'kerjasama_id'])
            ->with(['divisi:id,name', 'jabatan:id,name_jabatan', 'kerjasama:id,client_id'])->findOrFail($user);
        $records = CheckPoint::where('user_id', $employee->id)->where('type_check', $type)->get(['id', 'user_id', 'tanggal', 'type_check', 'created_at']);
        $recordsByDate = $records->flatMap(function (CheckPoint $checkpoint): array {
            $dates = collect((array) $checkpoint->tanggal)->filter();
            if ($dates->isEmpty()) $dates = collect([$checkpoint->created_at]);
            return $dates->mapWithKeys(fn($date) => [Carbon::parse($date)->toDateString() => $checkpoint->id])->all();
        });
        $calendar = collect();
        for ($date = $start->copy(); $date->lte($start->copy()->endOfMonth()); $date->addDay()) {
            $calendar->push(['date' => $date->copy(), 'hasData' => $recordsByDate->has($date->toDateString()), 'recordId' => $recordsByDate->get($date->toDateString())]);
        }
        return view('direksi.checkpoint.calendar', compact('employee', 'calendar', 'start', 'type') + [
            'previousMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'totalRecords' => $records->count(),
        ]);
    }

    public function history(Request $request)
    {
        $type = $request->type ?? 'dikerjakan';
        $selectedMonth = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();
        $start = $selectedMonth->copy()->startOfMonth();
        $filter = $request->filterKerjasama;
        $query = CheckPoint::where('type_check', $type);
        if ($filter) {
            $query->whereHas('user', fn ($q) => $q->where('kerjasama_id', $filter));
        }
        $recordsByDate = $query->get(['id', 'tanggal', 'created_at'])->flatMap(function (CheckPoint $checkpoint): array {
            $dates = collect((array) $checkpoint->tanggal)->filter();
            if ($dates->isEmpty()) $dates = collect([$checkpoint->created_at]);
            return $dates->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => $checkpoint->id])->all();
        });
        $calendar = collect();
        for ($date = $start->copy(); $date->lte($start->copy()->endOfMonth()); $date->addDay()) {
            $calendar->push(['date' => $date->copy(), 'hasData' => $recordsByDate->has($date->toDateString()), 'recordId' => $recordsByDate->get($date->toDateString())]);
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

    public function historyDetail($id)
    {
        $checkpoint = CheckPoint::with('user:id,nama_lengkap')->findOrFail($id);
        $jobs = PekerjaanCp::whereIn('id', array_filter((array) $checkpoint->pekerjaan_cp_id))->get()->keyBy('id');
        return view('direksi.checkpoint.history-detail', compact('checkpoint', 'jobs'));
    }

    public function updateApproval(Request $request, $id)
    {
        $data = $request->validate([
            'index' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['accept', 'denied'])],
            'note' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn () => $request->status === 'denied')],
        ]);
        $checkpoint = CheckPoint::findOrFail($id);
        $statuses = is_array($checkpoint->approve_status) ? $checkpoint->approve_status : json_decode($checkpoint->approve_status ?: '[]', true);
        $statuses = is_array($statuses) ? $statuses : [];
        abort_unless(array_key_exists($data['index'], (array) $checkpoint->pekerjaan_cp_id), 422);
        $statuses[$data['index']] = $data['status'];
        $checkpoint->approve_status = $statuses;
        $notes = is_array($checkpoint->note) ? $checkpoint->note : json_decode($checkpoint->note ?: '[]', true);
        $notes = is_array($notes) ? $notes : [];
        $notes[$data['index']] = $data['status'] === 'denied' ? ($data['note'] ?? null) : null;
        $checkpoint->note = $notes;
        $checkpoint->save();
        return back()->with('success', 'Status approval berhasil diperbarui.');
    }

    private function kerjasama()
    {
        return Cache::remember('admin.checkpoint.kerjasama-options', 300, fn() => Kerjasama::select('id', 'client_id')->with('client:id,name')->get());
    }
}
