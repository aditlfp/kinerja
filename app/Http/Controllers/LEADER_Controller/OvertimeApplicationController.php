<?php

namespace App\Http\Controllers\LEADER_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\ScopesLeaderMitra;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeStoreRequest;
use App\Models\Overtime;
use App\Models\User;
use App\Notifications\OvertimeSubmitted;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class OvertimeApplicationController extends Controller
{
    use LocksRekapByDueDate, ScopesLeaderMitra;

    public function create()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $users = $this->visibleUsers()->orderBy('nama_lengkap')->get();

        return view('leader_view.data_rekap.lembur.create', [
            'users' => $users,
        ]);
    }

    public function store(OvertimeStoreRequest $request)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        try {
            $data = $request->validated();
            $this->abortIfUserNotVisible($data['user_id']);
            if ($request->hasFile('foto_bukti')) {
                $data['foto_bukti'] = $request->file('foto_bukti')->store('overtimes/foto-bukti', 'public');
            }
            $data['created_by_user_id'] = auth()->id();
            Overtime::create($data);
            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Lembur berhasil disimpan!',
            ]);
        } catch (\Throwable $th) {
            report($th);
            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data lembur. Silakan coba lagi.',
            ]);
        }
    }

    public function history(Request $request)
    {
        $isSubmissionLocked = $this->isSubmissionLockedByDueDate();

        $overtimes = Overtime::with(['user:id,name,nama_lengkap'])
            ->select(['id', 'user_id', 'date_overtime', 'desc', 'type_overtime', 'type_overtime_manual', 'foto_bukti', 'status', 'created_at'])
            ->whereHas('user', function ($q) {
                $this->scopeVisibleUsers($q);
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->month, function ($q) use ($request) {
                try {
                    $date = Carbon::createFromFormat('Y-m', $request->month);
                    $q->whereBetween('date_overtime', monthBounds($date));
                } catch (\Throwable $th) {
                    // Ignore invalid month and keep base query
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leader_view.data_rekap.lembur.show', [
            'overtimes' => $overtimes,
            'isSubmissionLocked' => $isSubmissionLocked,
            'canBulkSubmit' => !$isSubmissionLocked && $this->hasBulkSubmittableOvertime(),
        ]);
    }

    public function show(Request $request, $id)
    {
        return $this->history($request);
    }

    public function edit($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $users = $this->visibleUsers()->orderBy('nama_lengkap')->get();
        $overtime = Overtime::findOrFail($id);
        return view('leader_view.data_rekap.lembur.edit', [
            'overtime' => $overtime,
            'users' => $users
        ]);
    }

    public function update(OvertimeStoreRequest $request, $id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $overtime = Overtime::findOrFail($id);
        $data = $request->validated();
        $this->abortIfUserNotVisible($data['user_id']);
        if ($request->hasFile('foto_bukti')) {
            if (!empty($overtime->foto_bukti)) {
                Storage::disk('public')->delete($overtime->foto_bukti);
            }
            $data['foto_bukti'] = $request->file('foto_bukti')->store('overtimes/foto-bukti', 'public');
        }
        $data['created_by_user_id'] = auth()->id();
        $overtime->update($data);
        return to_route('overtime-application.show', 1)->with('toast', [
            'type' => 'success',
            'message' => 'Lembur berhasil diupdate!',
        ]);
    }

    public function destroy($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        Overtime::findOrFail($id)->delete();
        return redirect()->back()->with('toast', [
            'type' => 'warning',
            'message' => 'Lembur berhasil dihapus!',
        ]);
    }

    public function fetchApi($id)
    {
        $overtime = Overtime::with('user')->findOrFail($id);
        return response()->json([
            'message' => 'Get data by id',
            'data' => $overtime,
            'error' => ''
        ]);
    }

    public function changeStatus($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $overtime = Overtime::findOrFail($id);

        $overtime->update([
            'status' => 'Di Ajukan'
        ]);


        $targetCode = auth()->user()->jabatan->code_jabatan == 'CO-CS'
            ? 'SPV'
            : (auth()->user()->jabatan->code_jabatan == 'CO-SCR'
                ? 'MARKETING'
                : null);

        if ($targetCode) {
            $users = User::whereHas(
                'jabatan',
                fn($q) =>
                $q->where('code_jabatan', $targetCode)
            )->get();

            Notification::send(
                $users,
                new OvertimeSubmitted($overtime)
            );
        }
        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Lembur berhasil diajukan!',
        ]);
    }

    public function bulkStatus()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $startDate = Carbon::now()->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        $targetCode = auth()->user()->jabatan->code_jabatan == 'CO-CS'
            ? 'SPV'
            : (auth()->user()->jabatan->code_jabatan == 'CO-SCR'
                ? 'MARKETING'
                : null);

        $overtimes = Overtime::whereHas('user', function ($q) {
            $this->scopeVisibleUsers($q);
        })
            ->whereBetween('date_overtime', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['pending']);
            })
            ->get();

        if ($overtimes->isEmpty()) {
            return back()->with('toast', [
                'type' => 'info',
                'message' => 'Tidak ada data lembur untuk diajukan.',
            ]);
        }

        Overtime::whereIn('id', $overtimes->pluck('id'))
            ->update(['status' => 'Di Ajukan']);

        // ambil approver
        $approvers = User::whereHas(
            'jabatan',
            fn($q) =>
            $q->where('code_jabatan', $targetCode)
        )->get();

        foreach ($overtimes as $overtime) {
            Notification::send(
                $approvers,
                new OvertimeSubmitted($overtime)
            );
        }


        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Berhasil mengajukan semua lembur!',
        ]);
    }

    private function visibleUsers()
    {
        return User::select(['id', 'name', 'nama_lengkap', 'kerjasama_id'])
            ->with('kerjasama.client:id,name,panggilan')
            ->where(function ($q) {
                $this->scopeVisibleUsers($q);
            });
    }

    private function scopeVisibleUsers($q): void
    {
        $this->scopeLeaderUser($q);
    }

    private function abortIfUserNotVisible($userId): void
    {
        abort_unless($this->visibleUsers()->whereKey($userId)->exists(), 403);
    }

    private function hasBulkSubmittableOvertime(): bool
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        return Overtime::whereHas('user', function ($q) {
            $this->scopeVisibleUsers($q);
        })
            ->whereBetween('date_overtime', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['pending']);
            })
            ->exists();
    }
}
