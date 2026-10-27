<?php

namespace App\Http\Controllers\SPVW_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\UsesToastRedirects;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SPVW_Controller\Concerns\HasAllowedSeeData;
use App\Models\Jabatan;
use App\Models\PersonIn;
use App\Models\User;
use App\Notifications\PersonInSubmitted;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PersonInController extends Controller
{
    use LocksRekapByDueDate, UsesToastRedirects, HasAllowedSeeData;

    public function index()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $jabatans = Jabatan::select(['id', 'name_jabatan'])
            ->whereIn('id', $this->allowedSeeData())
            ->orderBy('name_jabatan')
            ->get();


        $users = User::select(['id', 'nama_lengkap', 'jabatan_id'])
            ->where('role_id', '!=', 2)
            ->when(!empty($this->allowedTargetJabatanIds()), fn($q) => $q->whereIn('jabatan_id', $this->allowedTargetJabatanIds()))
            ->when($this->selectedClientId() > 0, fn($q) => $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())))
            ->orderBy('nama_lengkap')
            ->get();

        return view('spv_w_view.person_in.index', [
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

        $users = User::where('role_id', '!=', 2)
            ->when(!empty($this->allowedTargetJabatanIds()), fn($q) => $q->whereIn('jabatan_id', $this->allowedTargetJabatanIds()))
            ->when($this->selectedClientId() > 0, fn($q) => $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())))
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
            ->whereIn('id', $this->allowedSeeData())
            ->orderBy('name_jabatan')
            ->get();

        return view('spv_w_view.person_in.history', [
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

            $validated['client_id'] = $this->selectedClientId() > 0 ? $this->selectedClientId() : auth()->user()->kerjasama->client_id;
            $validated['status'] = 'pending';

            // Set created_by_user_id
            $validated['created_by_user_id'] = auth()->id();

            // Check if person already exists with same fullname, client_id, and date_in
            $personIn = PersonIn::where('fullname', $validated['fullname'])
                ->where('client_id', $validated['client_id'])
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

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'data' => $personIn,
                    'error' => ''
                ], 201);
            }

            return $this->redirectBackWithToast('success', $message);
        } catch (\Throwable $th) {
            report($th);
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal menyimpan data personil masuk.',
                    'data' => null,
                    'error' => $th->getMessage()
                ], 500);
            }

            return $this->redirectBackWithInputToast('error', 'Gagal menyimpan data personil masuk. Silakan coba lagi.');
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

            $validated['client_id'] = $this->selectedClientId() > 0 ? $this->selectedClientId() : auth()->user()->kerjasama->client_id;

            // Set created_by_user_id
            $validated['created_by_user_id'] = auth()->id();

            $personIn->update($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Data personil masuk berhasil diperbarui!',
                    'data' => $personIn->fresh(),
                    'error' => ''
                ]);
            }

            return $this->redirectBackWithToast('success', 'Data personil masuk berhasil diperbarui!');
        } catch (ValidationException $th) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Data personil masuk tidak valid.',
                    'data' => null,
                    'error' => $th->getMessage(),
                    'errors' => $th->errors(),
                ], 422);
            }

            return $this->redirectBackWithInputToast('error', 'Data personil masuk tidak valid.');
        } catch (\Throwable $th) {
            report($th);
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan saat memperbarui data personil masuk.',
                    'data' => null,
                    'error' => $th->getMessage()
                ], 500);
            }

            return $this->redirectBackWithInputToast('error', 'Gagal memperbarui data personil masuk. Silakan coba lagi.');
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

        return $this->redirectBackWithToast('success', 'Data personil masuk berhasil dihapus!');
    }

    public function changeStatus($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $personIn = $this->baseQuery()->findOrFail($id);
        $currentStatus = $personIn->status ?? 'pending';

        if (!in_array($currentStatus, ['pending', null, ''], true)) {
            return redirect()->back()->with('toast', [
                'type' => 'info',
                'message' => 'Data ini tidak dapat diajukan lagi.',
            ]);
        }

        $personIn->update(['status' => 'Di Ajukan']);

        $this->notifyApproverForSubmission($personIn);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Personil masuk berhasil diajukan!',
        ]);
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
            return back()->with('toast', [
                'type' => 'info',
                'message' => 'Tidak ada data personil masuk yang bisa diajukan.',
            ]);
        }

        PersonIn::whereIn('id', $items->pluck('id'))
            ->update(['status' => 'Di Ajukan']);

        $firstSubmitted = PersonIn::whereIn('id', $items->pluck('id'))->first();
        if ($firstSubmitted) {
            $this->notifyApproverForSubmission($firstSubmitted);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Berhasil mengajukan semua personil masuk sesuai filter!',
        ]);
    }

    public function fetchApi($id)
    {
        $personIn = $this->baseQuery()
            ->with(['jabatan:id,name_jabatan', 'client:id,name', 'createdBy:id,name,nama_lengkap'])
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
        return PersonIn::with(['jabatan:id,name_jabatan', 'client:id,name', 'createdBy:id,name,nama_lengkap'])
            ->select(['id', 'fullname', 'client_id', 'created_by_user_id', 'jabatan_id', 'date_in', 'method_salary', 'method_salary_manual', 'status', 'created_at'])
            ->where('client_id', $this->selectedClientId())
            ->whereIn('jabatan_id', $this->allowedSeeData());
    }

    private function rules(Request $request): array
    {
        $allowedFullnames = User::where('role_id', '!=', 2)
            ->when(!empty($this->allowedTargetJabatanIds()), fn($q) => $q->whereIn('jabatan_id', $this->allowedTargetJabatanIds()))
            ->when($this->selectedClientId() > 0, fn($q) => $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())))
            ->pluck('nama_lengkap')
            ->all();

        return [
            'has_account' => ['nullable', Rule::in(['yes', 'no'])],
            'jabatan_id' => [
                'required',
                Rule::exists('jabatans', 'id')->where(function ($q) {
                    $q->whereIn('id', $this->allowedSeeData());
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
                    $normalizedValue = mb_strtolower(
                        preg_replace('/\s+/', ' ', trim($value))
                    );
                    $normalizedAllowed = collect($allowedFullnames)
                        ->map(fn($name) => mb_strtolower(
                            preg_replace('/\s+/', ' ', trim($name))
                        ))
                        ->toArray();

                    if (
                        $request->has_account === 'yes' &&
                        !in_array($normalizedValue, $normalizedAllowed, true)
                    ) {
                        $fail('Nama lengkap tidak valid untuk akun yang dipilih.');
                    }
                },
            ],
        ];
    }

    private function notifyApproverForSubmission(PersonIn $personIn): void
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
                ->where('data->type', 'person_in')
                ->where('data->kerjasama_id', $kerjasamaId)
                ->exists();
        });

        if ($filteredRecipients->isNotEmpty()) {
            Notification::send($filteredRecipients, new PersonInSubmitted($personIn, $kerjasamaId));
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
