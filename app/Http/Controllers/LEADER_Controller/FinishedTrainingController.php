<?php

namespace App\Http\Controllers\LEADER_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\ScopesLeaderMitra;
use App\Http\Controllers\Concerns\UsesToastRedirects;
use App\Http\Controllers\Controller;
use App\Models\FinishedTraining;
use App\Models\User;
use App\Notifications\FinishedTrainingSubmitted;
use App\Services\ApprovalNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinishedTrainingController extends Controller
{
    use LocksRekapByDueDate, ScopesLeaderMitra, UsesToastRedirects;

    public function index()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $users = $this->allowedUsersQuery()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'kerjasama_id']);

        return view('leader_view.data_rekap.finished_training.index', [
            'users' => $users,
        ]);
    }

    public function searchUsers(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'message' => 'Query too short',
                'data' => [],
                'error' => ''
            ]);
        }

        $users = $this->allowedUsersQuery()
            ->where('nama_lengkap', 'like', '%' . $query . '%')
            ->orderBy('nama_lengkap')
            ->limit(15)
            ->get(['id', 'nama_lengkap', 'kerjasama_id']);

        return response()->json([
            'message' => 'User list',
            'data' => $users,
            'error' => ''
        ]);
    }

    public function history(Request $request)
    {
        $isSubmissionLocked = $this->isSubmissionLockedByDueDate();

        $allowedPerPage = [10, 15, 25, 50];
        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        $finishedTrainings = $this->filteredHistoryQuery($request)
            ->paginate($perPage)
            ->through(function ($item) {
                $item->masa_training_hari = Carbon::parse($item->date_finish_train)->day;
                return $item;
            })
            ->withQueryString();

        $users = $this->allowedUsersQuery()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'kerjasama_id']);

        return view('leader_view.data_rekap.finished_training.history', [
            'finishedTrainings' => $finishedTrainings,
            'users' => $users,
            'perPage' => $perPage,
            'allowedPerPage' => $allowedPerPage,
            'isSubmissionLocked' => $isSubmissionLocked,
            'canBulkSubmit' => !$isSubmissionLocked && $this->filteredHistoryQuery($request)
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereRaw('LOWER(status) = ?', ['pending']);
                })->exists(),
        ]);
    }

    public function show($id)
    {
        $finishedTraining = $this->baseQuery()->findOrFail($id);
        $finishedTraining->masa_training_hari = Carbon::parse($finishedTraining->date_finish_train)->day;

        return response()->json([
            'message' => 'Get finished training',
            'data' => $finishedTraining,
            'error' => ''
        ]);
    }

    public function store(Request $request)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        try {
            $validated = $request->validate($this->rules());

            $finishedTraining = FinishedTraining::create([
                'user_id' => $validated['user_id'],
                'date_in' => $validated['date_in'],
                'date_finish_train' => $validated['date_finish_train'],
                'desc' => $validated['desc'],
                'status' => 'pending',
                'created_by_user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Data lepas training berhasil disimpan!',
                'data' => $finishedTraining,
                'error' => ''
            ], 201);
        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data lepas training.',
                'data' => null,
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        try {
            $finishedTraining = $this->baseQuery()->findOrFail($id);
            $validated = $request->validate($this->rules());

            $finishedTraining->update([
                'user_id' => $validated['user_id'],
                'date_in' => $validated['date_in'],
                'date_finish_train' => $validated['date_finish_train'],
                'desc' => $validated['desc'],
                'created_by_user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Data lepas training berhasil diperbarui!',
                'data' => $finishedTraining->fresh('user'),
                'error' => ''
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data lepas training.',
                'data' => null,
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $finishedTraining = $this->baseQuery()->findOrFail($id);
        $finishedTraining->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Finished training deleted',
                'data' => null,
                'error' => ''
            ]);
        }

        return $this->redirectBackWithToast('warning', 'Data lepas training berhasil dihapus!');
    }

    public function changeStatus($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $finishedTraining = $this->baseQuery()->findOrFail($id);
        $currentStatus = $finishedTraining->status ?? 'pending';

        if (!in_array($currentStatus, ['pending', null, ''], true)) {
            return $this->redirectBackWithToast('info', 'Data ini tidak dapat diajukan lagi.');
        }

        $finishedTraining->update(['status' => 'Di Ajukan']);
        $this->notifyApproverForSubmission($finishedTraining->fresh('user'));
        return $this->redirectBackWithToast('success', 'Lepas training berhasil diajukan!');
    }

    public function bulkStatus(Request $request)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $query = $this->filteredHistoryQuery($request)
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['pending']);
            });

        $items = $query->get(['id']);

        if ($items->isEmpty()) {
            return $this->backWithToast('info', 'Tidak ada data lepas training yang bisa diajukan.');
        }

        FinishedTraining::whereIn('id', $items->pluck('id'))
            ->update(['status' => 'Di Ajukan']);

        $firstSubmitted = FinishedTraining::with('user')->whereIn('id', $items->pluck('id'))->first();
        if ($firstSubmitted) {
            $this->notifyApproverForSubmission($firstSubmitted);
        }

        return $this->backWithToast('success', 'Berhasil mengajukan semua data lepas training sesuai filter!');
    }

    public function fetchApi($id)
    {
        $finishedTraining = $this->baseQuery()->findOrFail($id);
        $finishedTraining->masa_training_hari = Carbon::parse($finishedTraining->date_finish_train)->day;

        return response()->json([
            'message' => 'Get data by id',
            'data' => $finishedTraining,
            'error' => ''
        ]);
    }

    private function filteredHistoryQuery(Request $request)
    {
        return $this->baseQuery()
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('month'), function ($q) use ($request) {
                try {
                    $date = Carbon::createFromFormat('Y-m', $request->month);
                    $q->whereBetween('date_finish_train', monthBounds($date));
                } catch (\Throwable $th) {
                    // ignore invalid month
                }
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = trim($request->search);
                $q->whereHas('user', function ($userQuery) use ($keyword) {
                    $userQuery->where('nama_lengkap', 'like', '%' . $keyword . '%');
                });
            })
            ->latest();
    }

    private function baseQuery()
    {
        return FinishedTraining::with(['user:id,name,nama_lengkap', 'createdBy:id,name,nama_lengkap'])
            ->select(['id', 'user_id', 'created_by_user_id', 'date_in', 'date_finish_train', 'desc', 'status', 'created_at'])
            ->whereHas('user', function ($q) {
                $this->scopeLeaderUser($q);
            });
    }

    private function allowedUsersQuery()
    {
        return User::query()
            ->with('kerjasama.client:id,name,panggilan')
            ->where(function ($q) {
                $this->scopeLeaderUser($q);
            });
    }

    private function rules(): array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id'),
                function ($attribute, $value, $fail) {
                    $exists = $this->allowedUsersQuery()->where('id', $value)->exists();
                    if (!$exists) {
                        $fail('Nama lengkap tidak valid untuk area kerja Anda.');
                    }
                },
            ],
            'date_in' => ['required', 'date'],
            'date_finish_train' => ['required', 'date', 'after_or_equal:date_in'],
            'desc' => ['required', 'string'],
        ];
    }

    private function notifyApproverForSubmission(FinishedTraining $finishedTraining): void
    {
        $kerjasamaId = (int) auth()->user()->kerjasama_id;
        app(ApprovalNotificationService::class)->sendToApprovers(
            (string) auth()->user()->jabatan->code_jabatan,
            $kerjasamaId,
            'finished_training',
            new FinishedTrainingSubmitted($finishedTraining)
        );
    }
}
