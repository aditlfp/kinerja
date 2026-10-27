<?php

namespace App\Http\Controllers\SPVW_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\UsesToastRedirects;
use App\Http\Controllers\Controller;
use App\Models\PerformanceCuts;
use App\Models\User;
use App\Notifications\CuttingSubmitted;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use App\Http\Controllers\SPVW_Controller\Concerns\HasAllowedSeeData;

class CuttingController extends Controller
{
    use LocksRekapByDueDate, UsesToastRedirects, HasAllowedSeeData;

    public function index()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $users = $this->allowedUsersQuery()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);

        return view('spv_w_view.cutting.index', [
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
            ->get(['id', 'nama_lengkap']);

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

        $cuttings = $this->filteredHistoryQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        $users = $this->allowedUsersQuery()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);

        return view('spv_w_view.cutting.history', [
            'cuttings' => $cuttings,
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
        $cutting = $this->baseQuery()
            ->findOrFail($id);

        return response()->json([
            'message' => 'Get cutting',
            'data' => $cutting,
            'error' => ''
        ]);
    }

    public function store(Request $request)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        try {
            $validated = $request->validate($this->rules($request));

            $cutting = PerformanceCuts::create([
                'user_id' => $validated['user_id'],
                'date_cut' => $validated['date_cutting'],
                'type_cut' => $validated['type_cutting'],
                'manual_type_cut' => $validated['type_cutting_manual'] ?? '',
                'desc' => $validated['desc'],
                'status' => 'pending',
                'created_by_user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Data cutting berhasil disimpan!',
                'data' => $cutting,
                'error' => ''
            ], 201);
        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data cutting.',
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
            $cutting = $this->baseQuery()->findOrFail($id);
            $validated = $request->validate($this->rules($request));

            $cutting->update([
                'user_id' => $validated['user_id'],
                'date_cut' => $validated['date_cutting'],
                'type_cut' => $validated['type_cutting'],
                'manual_type_cut' => $validated['type_cutting_manual'] ?? '',
                'desc' => $validated['desc'],
                'created_by_user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Data cutting berhasil diperbarui!',
                'data' => $cutting->fresh('user'),
                'error' => ''
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data cutting.',
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

        $cutting = $this->baseQuery()->findOrFail($id);
        $cutting->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Cutting deleted',
                'data' => null,
                'error' => ''
            ]);
        }

        return $this->redirectBackWithToast('success', 'Data cutting berhasil dihapus!');
    }

    public function changeStatus($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $cutting = $this->baseQuery()->findOrFail($id);
        $currentStatus = $cutting->status ?? 'pending';

        if (!in_array($currentStatus, ['pending', null, ''], true)) {
            return $this->redirectBackWithToast('info', 'Data ini tidak dapat diajukan lagi.');
        }

        $cutting->update(['status' => 'Di Ajukan']);
        $this->notifyApproverForSubmission($cutting->fresh('user'));
        return $this->redirectBackWithToast('success', 'Cutting berhasil diajukan!');
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
            return $this->backWithToast('info', 'Tidak ada data cutting yang bisa diajukan.');
        }

        PerformanceCuts::whereIn('id', $items->pluck('id'))
            ->update(['status' => 'Di Ajukan']);

        $firstSubmitted = PerformanceCuts::with('user')->whereIn('id', $items->pluck('id'))->first();
        if ($firstSubmitted) {
            $this->notifyApproverForSubmission($firstSubmitted);
        }

        return $this->backWithToast('success', 'Berhasil mengajukan semua data cutting sesuai filter!');
    }

    public function fetchApi($id)
    {
        $cutting = $this->baseQuery()->findOrFail($id);

        return response()->json([
            'message' => 'Get data by id',
            'data' => $cutting,
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
                    $q->whereBetween('date_cut', monthBounds($date));
                } catch (\Throwable $th) {
                    // Ignore invalid month and keep base query
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
        return PerformanceCuts::with(['user:id,name,nama_lengkap,jabatan_id', 'createdBy:id,name,nama_lengkap'])
            ->select(['id', 'user_id', 'created_by_user_id', 'date_cut', 'type_cut', 'manual_type_cut', 'desc', 'status', 'created_at'])
            ->whereHas('user', function ($q) {
                $q->whereIn('jabatan_id', $this->allowedSeeData())
                    ->when($this->selectedClientId() > 0, fn($userQuery) => $userQuery->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())));
            });
    }

    private function allowedUsersQuery()
    {
        return User::where('role_id', '!=', 2)
            ->when(!empty($this->allowedTargetJabatanIds()), fn($q) => $q->whereIn('jabatan_id', $this->allowedTargetJabatanIds()))
            ->when($this->selectedClientId() > 0, fn($q) => $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())));
    }

    private function rules(Request $request): array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($q) {
                    if ($this->selectedClientId() > 0) {
                        $q->whereIn('kerjasama_id', function ($subQuery) {
                            $subQuery->select('id')->from('kerjasamas')->where('client_id', $this->selectedClientId());
                        });
                    }
                }),
                function ($attribute, $value, $fail) {
                    $exists = $this->allowedUsersQuery()->where('id', $value)->exists();
                    if (!$exists) {
                        $fail('Nama lengkap tidak valid untuk area kerja Anda.');
                    }
                },
            ],
            'date_cutting' => ['required', 'date'],
            'type_cutting' => ['required', Rule::in(['Alpha', 'Kinerja', 'Lainnya'])],
            'type_cutting_manual' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $request->type_cutting === 'Lainnya'),
            ],
            'desc' => ['required', 'string'],
        ];
    }

    private function notifyApproverForSubmission(PerformanceCuts $cutting): void
    {
        $targetCode = $this->resolveTargetCode();
        if (!$targetCode) {
            return;
        }

        $kerjasamaId = (int) auth()->user()->kerjasama_id;
        $approvers = User::whereHas('jabatan', function ($q) use ($targetCode) {
            $q->where('code_jabatan', $targetCode);
        })->get();

        if ($approvers->isEmpty()) {
            return;
        }

        $filteredRecipients = $approvers->filter(function ($recipient) use ($kerjasamaId) {
            return !$recipient->unreadNotifications()
                ->where('data->type', 'cutting')
                ->where('data->kerjasama_id', $kerjasamaId)
                ->exists();
        });

        if ($filteredRecipients->isNotEmpty()) {
            Notification::send($filteredRecipients, new CuttingSubmitted($cutting));
        }
    }

    private function resolveTargetCode(): ?string
    {
        $code = strtoupper((string) auth()->user()->jabatan->code_jabatan);
        if ($code === 'CO-CS') {
            return 'SPV';
        }

        if ($code === 'CO-SCR') {
            return 'MARKETING';
        }

        return null;
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
