<?php

namespace App\Http\Controllers\LEADER_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\ScopesLeaderMitra;
use App\Http\Controllers\Concerns\UsesToastRedirects;
use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\PersonIn;
use App\Models\User;
use App\Notifications\PersonInSubmitted;
use App\Services\ApprovalNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonInController extends Controller
{
    use LocksRekapByDueDate, ScopesLeaderMitra, UsesToastRedirects;

    public function index()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $jabatans = Jabatan::select(['id', 'name_jabatan'])
            ->where('type_jabatan', auth()->user()->jabatan->type_jabatan)
            ->orderBy('name_jabatan')
            ->get();

        $users = $this->allowedUsersQuery()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'jabatan_id', 'kerjasama_id']);

        return view('leader_view.data_rekap.person_in.index', [
            'jabatans' => $jabatans,
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

        $personIn = $this->filteredHistoryQuery($request)
            ->paginate($perPage)
            ->withQueryString();
        $jabatans = Jabatan::select(['id', 'name_jabatan'])
            ->where('type_jabatan', auth()->user()->jabatan->type_jabatan)
            ->orderBy('name_jabatan')
            ->get();

        return view('leader_view.data_rekap.person_in.history', [
            'personIn' => $personIn,
            'jabatans' => $jabatans,
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
        $personIn = $this->baseQuery()
            ->with('jabatan')
            ->findOrFail($id);

        return response()->json([
            'message' => 'Get person in',
            'data' => $personIn,
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
            unset($validated['has_account']);

            $personUser = $this->allowedUsersQuery()->with('kerjasama:id,client_id')
                ->where('nama_lengkap', $validated['fullname'])
                ->first();
            $validated['client_id'] = $personUser?->kerjasama?->client_id ?? auth()->user()->kerjasama->client_id;
            $validated['status'] = 'pending';

            // Set created_by_user_id
            $validated['created_by_user_id'] = auth()->id();

            // Check if person already exists with same fullname, client_id, and date_in
            $personIn = PersonIn::where('fullname', $validated['fullname'])
                ->where('client_id', $validated['client_id'])
                ->whereDate('date_in', $validated['date_in'])
                ->first();

            if ($personIn) {
                // Update existing record
                $personIn->update($validated);
                $message = 'Data personil masuk berhasil diperbarui!';
            } else {
                // Create new record
                $personIn = PersonIn::create($validated);
                $message = 'Data personil masuk berhasil disimpan!';
            }

            return response()->json([
                'message' => $message,
                'data' => $personIn,
                'error' => ''
            ], 201);
        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data personil masuk.',
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
            $personIn = $this->baseQuery()->findOrFail($id);

            $validated = $request->validate($this->rules($request));
            unset($validated['has_account']);

            $personUser = $this->allowedUsersQuery()->with('kerjasama:id,client_id')
                ->where('nama_lengkap', $validated['fullname'])
                ->first();
            $validated['client_id'] = $personUser?->kerjasama?->client_id ?? auth()->user()->kerjasama->client_id;

            // Set created_by_user_id
            $validated['created_by_user_id'] = auth()->id();

            $personIn->update($validated);

            return response()->json([
                'message' => 'Data personil masuk berhasil diperbarui!',
                'data' => $personIn->fresh(),
                'error' => ''
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data personil masuk.',
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

        $personIn = $this->baseQuery()->findOrFail($id);
        $personIn->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Person in deleted',
                'data' => null,
                'error' => ''
            ]);
        }

        return $this->redirectBackWithToast('warning', 'Personil masuk berhasil dihapus!');
    }

    public function changeStatus($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $personIn = $this->baseQuery()->findOrFail($id);
        $currentStatus = $personIn->status ?? 'pending';

        if (!in_array($currentStatus, ['pending', null, ''], true)) {
            return $this->redirectBackWithToast('info', 'Data ini tidak dapat diajukan lagi.');
        }

        $personIn->update(['status' => 'Di Ajukan']);

        $this->notifyApproverForSubmission($personIn);

        return $this->redirectBackWithToast('success', 'Personil masuk berhasil diajukan!');
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
            return $this->backWithToast('info', 'Tidak ada data personil masuk yang bisa diajukan.');
        }

        PersonIn::whereIn('id', $items->pluck('id'))
            ->update(['status' => 'Di Ajukan']);

        $firstSubmitted = PersonIn::whereIn('id', $items->pluck('id'))->first();
        if ($firstSubmitted) {
            $this->notifyApproverForSubmission($firstSubmitted);
        }

        return $this->backWithToast('success', 'Berhasil mengajukan semua personil masuk sesuai filter!');
    }

    public function fetchApi($id)
    {
        $personIn = $this->baseQuery()
            ->with('jabatan')
            ->findOrFail($id);

        return response()->json([
            'message' => 'Get data by id',
            'data' => $personIn,
            'error' => ''
        ]);
    }

    private function filteredHistoryQuery(Request $request)
    {
        return $this->baseQuery()
            ->with('jabatan')
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('month'), function ($q) use ($request) {
                try {
                    $date = Carbon::createFromFormat('Y-m', $request->month);
                    $q->whereBetween('date_in', monthBounds($date));
                } catch (\Throwable $th) {
                    // Ignore invalid month and keep base query
                }
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . trim($request->search) . '%');
            })
            ->latest();
    }

    private function baseQuery()
    {
        return PersonIn::with(['jabatan:id,name_jabatan', 'createdBy:id,name,nama_lengkap'])
            ->select(['id', 'fullname', 'client_id', 'created_by_user_id', 'jabatan_id', 'date_in', 'method_salary', 'method_salary_manual', 'status', 'created_at'])
            ->whereIn('client_id', $this->leaderClientIds())
            ->whereHas('jabatan', function ($q) {
                $q->where('type_jabatan', auth()->user()->jabatan->type_jabatan);
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

    private function rules(Request $request): array
    {
        $allowedFullnames = $this->allowedUsersQuery()
            ->pluck('nama_lengkap')
            ->all();

        return [
            'has_account' => ['nullable', Rule::in(['yes', 'no'])],
            'jabatan_id' => [
                'required',
                Rule::exists('jabatans', 'id')->where(function ($q) {
                    $q->where('type_jabatan', auth()->user()->jabatan->type_jabatan);
                })
            ],
            'date_in' => ['required', 'date'],
            'total_mk' => ['required', 'string', 'max:255'],
            'method_salary' => ['required', Rule::in(['transfer', 'cash', 'manual'])],
            'method_salary_manual' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $request->method_salary === 'transfer'),
            ],
            'additional_reason' => ['nullable', 'string', 'max:255'],
            'fullname' => [
                'required',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $request->has_account === 'yes'),
                function ($attribute, $value, $fail) use ($request, $allowedFullnames) {
                    if ($request->has_account === 'yes' && !in_array($value, $allowedFullnames, true)) {
                        $fail('Nama lengkap tidak valid untuk akun yang dipilih.');
                    }
                },
            ],
        ];
    }

    private function notifyApproverForSubmission(PersonIn $personIn): void
    {
        $kerjasamaId = (int) auth()->user()->kerjasama_id;
        app(ApprovalNotificationService::class)->sendToApprovers(
            (string) auth()->user()->jabatan->code_jabatan,
            $kerjasamaId,
            'person_in',
            new PersonInSubmitted($personIn, $kerjasamaId)
        );
    }
}
