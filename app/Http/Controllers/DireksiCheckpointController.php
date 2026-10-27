<?php

namespace App\Http\Controllers;

use App\Models\CheckPoint;
use App\Models\CheckPointItem;
use App\Models\CheckPointMessage;
use App\Models\Kerjasama;
use App\Models\PekerjaanCp;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\CheckPointReplyNotification;
use App\Notifications\WorkOrderNotification;
use App\Services\CheckPointCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        // How much evidence each employee on this page is still waiting on us
        // to review in the month on screen, so the list doubles as a to-do
        // list for that month.
        $pendingCounts = $this->pendingReviewCounts($users->getCollection()->pluck('id'), $start, $end);

        return view('direksi.checkpoint.index', compact('users', 'kerjasama', 'filter', 'search', 'previousMonth', 'nextMonth', 'start', 'end', 'pendingCounts'));
    }

    /**
     * Unreviewed job rows per employee, keyed by user id — one grouped query
     * for the whole page instead of one per card.
     *
     * "Unreviewed" means `approve_status` still holds its stored default, the
     * legacy misspelling `proccess` (see CheckPointCalendarService): a rejected
     * row comes back to `proccess` when the employee answers it, and an
     * accepted row never does, so this is exactly the reviewer's queue.
     * Rejected rows waiting on the employee, and plan (`rencana`) rows, are
     * left out — the same rule the dashboard's pending list uses.
     *
     * The count covers the browsed month only, never the whole backlog, so the
     * number lines up with the days the calendar and history screens show for
     * that month. A row is dated by its own `tanggal`, falling back to its
     * batch's `created_at` when that is empty — the same fallback the history
     * calendar uses.
     *
     * @param  Collection<int, int>  $userIds
     * @return Collection<int, int>
     */
    private function pendingReviewCounts(Collection $userIds, Carbon $start, Carbon $end): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        return CheckPointItem::query()
            ->join('check_points', 'check_points.id', '=', 'check_point_items.check_point_id')
            ->whereIn('check_points.user_id', $userIds)
            ->where('check_points.type_check', 'dikerjakan')
            ->where('check_point_items.approve_status', 'proccess')
            ->where(function ($query) use ($start, $end): void {
                $query
                    ->whereBetween('check_point_items.tanggal', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($undated) use ($start, $end): void {
                        $undated
                            ->whereNull('check_point_items.tanggal')
                            ->whereBetween('check_points.created_at', [$start, $end]);
                    });
            })
            ->groupBy('check_points.user_id')
            ->selectRaw('check_points.user_id as user_id, count(*) as total')
            ->pluck('total', 'user_id')
            ->map(fn ($total) => (int) $total);
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
            $checkpoint = CheckPoint::with(['user:id,nama_lengkap', 'items.images', 'items.pekerjaanCp', 'items.messages.images', 'items.messages.user', 'items.messages.acknowledgement'])
                ->where('user_id', $workOrder->user_id)
                ->whereHas('items', fn ($q) => $q->whereDate('tanggal', $selectedDate))
                ->first();
        } else {
            $checkpoint = CheckPoint::with(['user:id,nama_lengkap', 'items.images', 'items.pekerjaanCp', 'items.messages.images', 'items.messages.user', 'items.messages.acknowledgement'])->findOrFail($id);
        }

        abort_if(! $checkpoint, 404, 'Bukti pekerjaan belum tersedia untuk perintah kerja ini.');

        return view('direksi.checkpoint.history-detail', compact('checkpoint', 'isMcs'));
    }

    public function updateApproval(Request $request, $id)
    {
        $data = $request->validate([
            'index' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['accept', 'denied'])],
            // A quick reaction picked in the modal. Only the allow-listed keys
            // are accepted, so the stored note can never carry arbitrary text
            // from the request — the emoji + label are resolved server-side.
            'emoji' => ['nullable', 'string', Rule::in(array_keys(CheckPointItem::APPROVAL_REACTIONS))],
            'note' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn() => $request->status === 'denied')],
        ]);
        $checkpoint = CheckPoint::findOrFail($id);
        $item = $checkpoint->items()->orderBy('urutan')->skip($data['index'])->first();
        abort_unless($item, 422);

        $comment = trim((string) ($data['note'] ?? ''));
        $reaction = $data['status'] === 'accept'
            ? (CheckPointItem::APPROVAL_REACTIONS[$data['emoji'] ?? null] ?? null)
            : null;

        // Reaction and comment share one note: the card's "Catatan Evaluasi"
        // banner shows the reaction, and the thread below carries the same text
        // as a normal management reply.
        $reactionText = $reaction ? $reaction['emoji'] . ' ' . $reaction['label'] : null;
        $note = collect([$reactionText, $comment])
            ->filter(fn ($part) => filled($part))
            ->implode(' — ');

        $item->approve_status = $data['status'];
        $item->note = $note !== '' ? $note : null;
        $item->save();

        $user = User::where('id', $checkpoint->user_id)->first();

        // The verdict note also opens (or continues) the conversation thread,
        // so the employee can answer it in context instead of a dead-end note.
        if (filled($item->note)) {
            $item->messages()->create([
                'user_id' => auth()->id(),
                'sender_role' => CheckPointMessage::ROLE_MANAGEMENT,
                // Only words ask for a checklist. A reaction alone ("👍 Baik")
                // has nothing to confirm, so it must not lock the employee's
                // composer behind a confirmation they cannot act on.
                'requires_confirmation' => $comment !== '',
                'body' => $item->note,
            ]);

            $user?->notify(new CheckPointReplyNotification(
                title: 'Catatan baru pada bukti pekerjaan',
                message: 'Direksi menanggapi bukti pekerjaan Anda.',
                checkPointId: (int) $checkpoint->id,
                itemId: (int) $item->id,
            ));
        }

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

    /** Periods the note history offers, in days. Anything else falls back to 14. */
    private const NOTE_PERIODS = [7, 14, 30, 90];

    /**
     * Every management note the signed-in direksi wrote on a subordinate's
     * report inside the chosen window. The thread is the source of truth:
     * `updateApproval()` mirrors the verdict note there and every later reply
     * lands there too, so reading it once covers both.
     */
    public function notes(Request $request)
    {
        $days = (int) $request->input('periode', 14);
        if (! in_array($days, self::NOTE_PERIODS, true)) {
            $days = 14;
        }

        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::now();

        $search = trim((string) $request->input('search', ''));
        $category = (string) $request->input('jenis', 'semua');
        if (! array_key_exists($category, CheckPointMessage::NOTE_CATEGORIES)) {
            $category = 'semua';
        }

        $confirmation = (string) $request->input('konfirmasi', 'semua');
        if (! in_array($confirmation, ['sudah', 'belum'], true)) {
            $confirmation = 'semua';
        }

        $base = CheckPointMessage::query()
            ->where('sender_role', CheckPointMessage::ROLE_MANAGEMENT)
            ->where('user_id', auth()->id())
            ->whereNull('retracted_at')
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('item.checkPoint');

        // The summary counts the window, not the filtered list — otherwise the
        // number moves whenever someone types in the search box.
        $totalNotes = (clone $base)->count();

        // Only notes that asked for the checklist can be confirmed or still be
        // waiting; a reaction-only note has nothing to answer. An acknowledgement
        // row can exist from the employee merely opening the thread, so the
        // `confirmed_at` stamp — not the row — is what counts.
        $confirmedNotes = (clone $base)
            ->whereHas('acknowledgement', fn ($ack) => $ack->whereNotNull('confirmed_at'))
            ->count();

        $pendingNotes = (clone $base)
            ->where('requires_confirmation', true)
            ->whereDoesntHave('acknowledgement', fn ($ack) => $ack->whereNotNull('confirmed_at'))
            ->count();

        // Neither of the two above: reaction-only notes, i.e. the ones with no
        // checklist to answer. Surfaced so the numbers add up to the total.
        $noConfirmationNotes = $totalNotes - $confirmedNotes - $pendingNotes;

        $notes = $base
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('body', 'like', '%' . $search . '%')
                        ->orWhereHas(
                            'item.checkPoint.user',
                            fn ($user) => $user->where('nama_lengkap', 'like', '%' . $search . '%'),
                        );
                });
            })
            ->when($category !== 'semua', function ($query) use ($category): void {
                $query->whereHas('item', function ($item) use ($category): void {
                    if ($category === 'apresiasi') {
                        $item->where('approve_status', 'accept');
                    } elseif ($category === 'teguran') {
                        $item->where('approve_status', 'denied');
                    } else {
                        // `proccess` is the stored default; legacy rows can also
                        // carry no status at all, and both are revision requests.
                        $item->where(function ($pending): void {
                            $pending->whereNull('approve_status')
                                ->orWhereNotIn('approve_status', ['accept', 'denied']);
                        });
                    }
                });
            })
            ->when($confirmation !== 'semua', function ($query) use ($confirmation): void {
                if ($confirmation === 'sudah') {
                    $query->whereHas('acknowledgement', fn ($ack) => $ack->whereNotNull('confirmed_at'));
                } else {
                    $query->where('requires_confirmation', true)
                        ->whereDoesntHave('acknowledgement', fn ($ack) => $ack->whereNotNull('confirmed_at'));
                }
            })
            ->with([
                'item:id,check_point_id,approve_status',
                'item.checkPoint:id,user_id',
                'item.checkPoint.user:id,nama_lengkap,image',
                'acknowledgement',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('direksi.checkpoint.notes', [
            'notes' => $notes,
            'totalNotes' => $totalNotes,
            'confirmedNotes' => $confirmedNotes,
            'pendingNotes' => $pendingNotes,
            'noConfirmationNotes' => $noConfirmationNotes,
            'start' => $start,
            'end' => $end,
            'days' => $days,
            'search' => $search,
            'category' => $category,
            'confirmation' => $confirmation,
        ]);
    }

    private function kerjasama()
    {
        return Cache::remember('admin.checkpoint.kerjasama-options', 300, fn() => Kerjasama::select('id', 'client_id')->with('client:id,name')->get());
    }
}
