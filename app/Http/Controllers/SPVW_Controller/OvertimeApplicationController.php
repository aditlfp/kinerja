<?php

namespace App\Http\Controllers\SPVW_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\UsesToastRedirects;
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
    use LocksRekapByDueDate, UsesToastRedirects;

    public function create()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $allowedJabatanIds = $this->allowedTargetJabatanIds();
        $users = User::select(['id', 'name', 'nama_lengkap'])
            ->where('role_id', '!=', 2)
            ->when(!empty($allowedJabatanIds), fn($q) => $q->whereIn('jabatan_id', $allowedJabatanIds))
            ->when($this->selectedClientId() > 0, fn($q) => $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())))
            ->orderBy('nama_lengkap')
            ->get();

        return view('spv_w_view.lembur.create', [
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
            if ($request->hasFile('foto_bukti')) {
                $data['foto_bukti'] = $request->file('foto_bukti')->store('overtimes/foto-bukti', 'public');
            }
            $data['created_by_user_id'] = auth()->id();
            Overtime::create($data);
            return $this->redirectBackWithToast('success', 'Lembur berhasil disimpan!');
        } catch (\Throwable $th) {
            report($th);
            return $this->redirectBackWithInputToast('error', 'Terjadi kesalahan saat menyimpan data lembur. Silakan coba lagi.');
        }
    }

    public function history(Request $request)
    {
        $isSubmissionLocked = $this->isSubmissionLockedByDueDate();
        $allowedJabatanIds = $this->allowedTargetJabatanIds();

        $overtimes = Overtime::with(['user:id,name,nama_lengkap', 'createdBy:id,nama_lengkap'])
            ->select(['id', 'user_id', 'date_overtime', 'desc', 'type_overtime', 'type_overtime_manual', 'foto_bukti', 'created_by_user_id', 'status', 'created_at'])
            ->whereHas('user', function ($q) use ($allowedJabatanIds) {
                $q                    ->where('role_id', '!=', 2)

                    ->when(!empty($allowedJabatanIds), fn($userQuery) => $userQuery->whereIn('jabatan_id', $allowedJabatanIds))
                    ->when($this->selectedClientId() > 0, fn($userQuery) => $userQuery->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())));
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

        return view('spv_w_view.lembur.show', [
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

        $allowedJabatanIds = $this->allowedTargetJabatanIds();
        $users = User::select(['id', 'name', 'nama_lengkap'])
            ->where('role_id', '!=', 2)
            ->when(!empty($allowedJabatanIds), fn($q) => $q->whereIn('jabatan_id', $allowedJabatanIds))
            ->when($this->selectedClientId() > 0, fn($q) => $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())))
            ->orderBy('nama_lengkap')
            ->get();
        $overtime = Overtime::findOrFail($id);
        return view('spv_w_view.lembur.edit', [
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
        if ($request->hasFile('foto_bukti')) {
            if (!empty($overtime->foto_bukti)) {
                Storage::disk('public')->delete($overtime->foto_bukti);
            }
            $data['foto_bukti'] = $request->file('foto_bukti')->store('overtimes/foto-bukti', 'public');
        }
        $data['created_by_user_id'] = auth()->id();
        $overtime->update($data);
        return redirect()->route('spvw.overtime-application.history', array_filter([
            'client_id' => $this->selectedClientId(),
        ]))->with('toast', $this->toastPayload('success', 'Lembur berhasil diupdate!'));
    }

    public function destroy($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        Overtime::findOrFail($id)->delete();
        return $this->redirectBackWithToast('success', 'Lembur berhasil dihapus!');
    }

    public function fetchApi($id)
    {
        $overtime = Overtime::with('user', 'createdBy')->findOrFail($id);
        // dd($overtime);
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
        return $this->redirectBackWithToast('success', 'Lembur berhasil diajukan!');
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

        $allowedJabatanIds = $this->allowedTargetJabatanIds();
        $overtimes = Overtime::whereHas('user', function ($q) use ($allowedJabatanIds) {
            $q                ->where('role_id', '!=', 2)

                ->when(!empty($allowedJabatanIds), fn($userQuery) => $userQuery->whereIn('jabatan_id', $allowedJabatanIds))
                ->when($this->selectedClientId() > 0, fn($userQuery) => $userQuery->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())));
        })
            ->whereBetween('date_overtime', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['pending']);
            })
            ->get();

        if ($overtimes->isEmpty()) {
            return $this->backWithToast('info', 'Tidak ada data lembur untuk diajukan.');
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


        return $this->backWithToast('success', 'Berhasil mengajukan semua lembur!');
    }

    private function hasBulkSubmittableOvertime(): bool
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $allowedJabatanIds = $this->allowedTargetJabatanIds();

        return Overtime::whereHas('user', function ($q) use ($allowedJabatanIds) {
            $q                ->where('role_id', '!=', 2)

                ->when(!empty($allowedJabatanIds), fn($userQuery) => $userQuery->whereIn('jabatan_id', $allowedJabatanIds))
                ->when($this->selectedClientId() > 0, fn($userQuery) => $userQuery->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())));
        })
            ->whereBetween('date_overtime', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['pending']);
            })
            ->exists();
    }

    private function selectedClientId(): int
    {
        $sessionKey = 'spvw.selected_client_id';

        if (request()->has('client_id')) {
            $clientId = max((int) request('client_id', 0), 0);

            if ($clientId > 0) {
                session([$sessionKey => $clientId]);
            } else {
                session()->forget($sessionKey);
            }

            return $clientId;
        }

        return max((int) session($sessionKey, 0), 0);
    }

    private function allowedTargetJabatanIds(): array
    {
        $authJabatanId = (int) (auth()->user()->jabatan_id ?? 0);

        $allowed = match ($authJabatanId) {
            35 => [8, 11, 16, 17, 18],
            20 => [9, 10, 34, 36],
            default => [],
        };

        // The supervisor's own account must stay selectable inside their own
        // mitra, even when their jabatan is not one they manage.
        return $authJabatanId > 0
            ? array_values(array_unique([...$allowed, $authJabatanId]))
            : $allowed;
    }
}
