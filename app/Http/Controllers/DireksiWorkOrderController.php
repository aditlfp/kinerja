<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DireksiWorkOrderController extends Controller
{
    /**
     * Jabatan codes that use the MCS (Manager Cleaning Service) work-order flow.
     */
    private const MCS_CODES = ['MCS'];

    /**
     * Resolve the current user's jabatan code, tolerating both the direct
     * `jabatan` relation and the `divisi->jabatan` fallback used elsewhere.
     */
    private function actorCode(): ?string
    {
        $user = auth()->user();

        return $user?->jabatan?->code_jabatan
            ?? $user?->divisi?->jabatan?->code_jabatan;
    }

    /**
     * True when the acting user should be routed through the MCS prefix.
     */
    private function isMcs(): bool
    {
        return in_array($this->actorCode(), self::MCS_CODES, true);
    }

    /**
     * Route name prefix for the acting user: `mcs` or `direksi`.
     * Keeps every redirect/link on the correct middleware group so an MCS
     * user is never sent to a direksi-only URL (which logs them out).
     */
    private function prefix(): string
    {
        return $this->isMcs() ? 'mcs' : 'direksi';
    }

    /**
     * Users visible to the acting user.
     *
     * Direksi sees the whole kerjasama; MCS is scoped to the divisions it
     * manages (same rule used by MainController::indexUser).
     */
    private function visibleUsersQuery()
    {
        $query = User::query()
            ->with('divisi.jabatan', 'jabatan')
            ->select(['id', 'nama_lengkap', 'devisi_id', 'jabatan_id', 'kerjasama_id'])
            ->whereNotIn('name', ['admin', 'user']);

        // if ($this->isMcs()) {
        //     $managedJabatanIds = [9, 10, 12, 16, 19, 20, 21, 22, 23, 30, 31, 32, 34, 36, 37, 40, 41];

        //     return $query->whereHas('divisi', fn ($d) => $d->whereIn('jabatan_id', $managedJabatanIds));
        // }

        return $query->whereNot('devisi_id', 18)->where('kerjasama_id', 1);
    }

    public function index(Request $request)
    {
        if ($request->filled('user_id')) {
            return redirect()->route($this->prefix() . '.work-order.calendar', [
                'user' => $request->integer('user_id'),
            ]);
        }

        $users = $this->visibleUsersQuery()
            ->orderBy('nama_lengkap')
            ->get();

        return view('direksi.work-order.index', [
            'users' => $users,
            'isMcs' => $this->isMcs(),
        ]);
    }

    public function calendar(Request $request, User $user)
    {
        $start = $request->filled('month') ? now()->createFromFormat('Y-m', $request->month)->startOfMonth() : now()->startOfMonth();
        $orders = WorkOrder::where('user_id', $user->id)->get()->keyBy(fn($order) => $order->tanggal->format('Y-m-d'));
        $gridStart = $start->copy()->subDays($start->dayOfWeekIso - 1);
        $gridEnd = $start->copy()->endOfMonth()->addDays(7 - $start->copy()->endOfMonth()->dayOfWeekIso);
        $calendar = collect();
        for ($date = $gridStart->copy(); $date->lte($gridEnd); $date->addDay()) $calendar->push($date->copy());
        return view('direksi.work-order.calendar', compact('user', 'start', 'orders', 'calendar') + [
            'previousMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'isMcs' => $this->isMcs(),
        ]);
    }

    public function create(User $user, Request $request)
    {
        abort_if(!$request->filled('tanggal'), 404);
        $orders = WorkOrder::with('creator')->where('user_id', $user->id)->where('tanggal', $request->tanggal)->get();
        return view('direksi.work-order.create', [
            'user' => $user,
            'tanggal' => $request->tanggal,
            'orders' => $orders,
            'isMcs' => $this->isMcs(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'deskripsi' => ['required', 'string', 'max:5000'],
        ]);

        $workOrder = WorkOrder::create($data + ['created_by' => Auth::id()]);
        $user = User::where('id', $data['user_id'])->first();

        $user->notify(
            new WorkOrderNotification(
                workOrderId: $workOrder->id,
                title: 'Pekerjaan Baru',
                message: 'Ada pekerjaan baru yang perlu kamu proses.'
            )
        );

        return redirect()->route($this->prefix() . '.work-order.calendar', ['user' => $data['user_id']])
            ->with('success', 'Pekerjaan berhasil dikirim ke ' . $user->name . '.');
    }
}
