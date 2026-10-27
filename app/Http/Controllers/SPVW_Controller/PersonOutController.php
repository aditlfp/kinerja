<?php

namespace App\Http\Controllers\SPVW_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\UsesToastRedirects;
use App\Http\Controllers\Controller;
use App\Models\PersonOut;
use App\Models\User;
use App\Notifications\PersonOutSubmitted;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PersonOutController extends Controller
{
    use LocksRekapByDueDate, UsesToastRedirects;

    public function create()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $users = User::select(['id', 'name', 'nama_lengkap'])
            ->where('id', '!=', auth()->user()->id)
            ->where('role_id', '!=', 2)
            ->when(!empty($this->allowedTargetJabatanIds()), fn($q) => $q->whereIn('jabatan_id', $this->allowedTargetJabatanIds()))
            ->when($this->selectedClientId() > 0, fn($q) => $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())))
            ->orderBy('nama_lengkap')
            ->get();
        return view('spv_w_view.person_out.create', [
            'users' => $users
        ]);
    }

    public function history(Request $request)
    {
        $isSubmissionLocked = $this->isSubmissionLockedByDueDate();
        $targetJabatanIds = $this->allowedTargetJabatanIds();

        $personOut = PersonOut::select(['id', 'user_id', 'total_mk', 'reason', 'reason_manual', 'out_date', 'img', 'status', 'created_by_user_id', 'created_at'])
            ->with([
                'user' => function ($q) {
                    $q->withTrashed()->select(['id', 'name', 'nama_lengkap']);
                },
                'inputBy' => function ($q) {
                    $q->select(['id', 'name', 'nama_lengkap']);
                }
            ])->whereHas('user', function ($q) use ($targetJabatanIds) {
                $q->withTrashed()
                    ->when(!empty($targetJabatanIds), fn($userQuery) => $userQuery->whereIn('jabatan_id', $targetJabatanIds))
                    ->when($this->selectedClientId() > 0, fn($userQuery) => $userQuery->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())));
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->month, function ($q) use ($request) {
                try {
                    $date = Carbon::createFromFormat('Y-m', $request->month);

                    $q->whereBetween('out_date', monthBounds($date));
                } catch (\Throwable $th) {
                    // ignore invalid month
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
        // dd($request->month, $personOut[0]);

        return view('spv_w_view.person_out.show', [
            'personOut' => $personOut,
            'isSubmissionLocked' => $isSubmissionLocked,
            'canBulkSubmit' => !$isSubmissionLocked && (function () {
                $allowedJabatanIds = $this->allowedTargetJabatanIds();
                return PersonOut::whereHas('user', function ($q) use ($allowedJabatanIds) {
                    $q->withTrashed()
                        ->where('role_id', '!=', 2)
                        ->when(!empty($allowedJabatanIds), fn($uq) => $uq->whereIn('jabatan_id', $allowedJabatanIds))
                        ->when($this->selectedClientId() > 0, fn($uq) => $uq->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())));
                })->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereRaw('LOWER(status) = ?', ['pending']);
                })->exists();
            })(),
        ]);
    }

    public function show(Request $request, $id)
    {
        return $this->history($request);
    }

    public function store(Request $request)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        try {
            $validated = $request->validate([
                'user_id' => ['required', 'exists:users,id', Rule::unique('person_outs', 'user_id')],
                'total_mk' => ['required'],
                'reason' => ['required', 'string', 'max:255'],
                'reason_manual' => ['nullable', 'string', 'max:255'],
                'out_date' => ['required', 'date'],
                'img' => ['required', 'image', 'max:2048'],
            ], [
                'user_id.required' => 'Nama personil wajib dipilih.',
                'user_id.exists' => 'Nama personil tidak valid.',
                'user_id.unique' => 'User ini sudah pernah diajukan pada data personil keluar. Silakan edit data yang sudah ada.',
                'total_mk.required' => 'Masa kerja wajib diisi.',
                'reason.required' => 'Alasan keluar wajib dipilih.',
                'reason.max' => 'Alasan keluar maksimal 255 karakter.',
                'reason_manual.max' => 'Alasan manual maksimal 255 karakter.',
                'out_date.required' => 'Tanggal keluar wajib diisi.',
                'out_date.date' => 'Format tanggal keluar tidak valid.',
                'img.required' => 'Bukti pendukung wajib diunggah.',
                'img.image' => 'File bukti harus berupa gambar.',
                'img.max' => 'Ukuran gambar maksimal 2MB.',
            ]);

                if ($request->hasFile('img')) {
                    try {
                        $validated['img'] = UploadImageV2($request, 'img');
                        if (empty($validated['img'])) {
                            return $this->redirectBackWithInputToast('error', 'Gagal mengunggah gambar. Silakan coba lagi.');
                        }
                    } catch (\Exception $e) {
                        report($e);
                        return $this->redirectBackWithInputToast('error', 'Gagal mengunggah gambar. Silakan coba lagi.');
                    }
                }

            $validated['created_by_user_id'] = auth()->id();
            PersonOut::create($validated);

            return $this->redirectBackWithToast('success', 'Berhasil mengajukan data!');
        } catch (QueryException $th) {
            if ((int) $th->getCode() === 23000 && str_contains(strtolower($th->getMessage()), 'person_outs_user_id_unique')) {
                return $this->redirectBackWithInputToast('error', 'User ini sudah pernah diajukan pada data personil keluar. Silakan edit data yang sudah ada.');
            }

            throw $th;
        } catch (\Throwable $th) {
            report($th);
            return $this->redirectBackWithInputToast('error', 'Terjadi kesalahan saat menyimpan data personil keluar. Silakan coba lagi.');
        }
    }

    public function edit($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $personOut = PersonOut::findOrFail($id);
        $users = User::select(['id', 'name', 'nama_lengkap'])
            ->where('id', '!=', auth()->user()->id)
            ->where('role_id', '!=', 2)
            ->when(!empty($this->allowedTargetJabatanIds()), fn($q) => $q->whereIn('jabatan_id', $this->allowedTargetJabatanIds()))
            ->when($this->selectedClientId() > 0, fn($q) => $q->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())))
            ->orderBy('nama_lengkap')
            ->get();
        return view('spv_w_view.person_out.edit', [
            'users' => $users,
            'personOut' => $personOut
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        try {
            $validated = $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'total_mk' => ['required'],
                'reason' => ['required', 'string', 'max:255'],
                'reason_manual' => ['nullable', 'string', 'max:255'],
                'out_date' => ['required', 'date'],
                'img' => ['nullable', 'image', 'max:2048'],
            ]);

            DB::transaction(function () use ($validated, $request, $id) {

                $personOut = PersonOut::findOrFail($id);
                $oldImageName = $personOut->img;

                // prevent user_id mutation if you decided it must be immutable
                if ($personOut->user_id !== (int) $validated['user_id']) {
                    throw new \Exception('user_id cannot be changed');
                }

                // handle image replacement
                if ($request->hasFile('img') && $request->file('img')->isValid()) {
                    try {
                        $newImageName = UploadImageV2($request, 'img');
                    } catch (\Exception $e) {
                        report($e);
                        throw new \Exception('Gagal mengunggah gambar. Silakan coba lagi.');
                    }

                    if (empty($newImageName)) {
                        throw new \Exception('Gagal mengunggah gambar. Silakan coba lagi.');
                    }

                    $validated['img'] = $newImageName;
                } else {
                    // keep old image
                    unset($validated['img']);
                }

                $validated['created_by_user_id'] = auth()->id();
                $personOut->update($validated);

                if (isset($validated['img']) && $oldImageName && $oldImageName !== $validated['img']) {
                    $oldPath = 'public/images/' . $oldImageName;
                    if (Storage::exists($oldPath)) {
                        Storage::delete($oldPath);
                    }
                }
            });

            return $this->redirectBackWithToast('success', 'Berhasil update data!');
        } catch (\Throwable $th) {
            report($th);
            return $this->redirectBackWithInputToast('error', 'Gagal memperbarui data personil keluar. Silakan coba lagi.');
        }
    }

    public function destroy(PersonOut $personOut)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $personOut->delete();

        return $this->redirectBackWithToast('success', 'Data personil keluar berhasil dihapus!');
    }

    public function changeStatus($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $personOut = PersonOut::findOrFail($id);
        $personOut->update(["status" => "Di Ajukan"]);
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
                new PersonOutSubmitted($personOut)
            );
        }
        return $this->redirectBackWithToast('success', 'Personil keluar berhasil diajukan!');
    }

    public function bulkStatus()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $allowedJabatanIds = $this->allowedTargetJabatanIds();
        $personOuts = PersonOut::whereHas('user', function ($q) use ($allowedJabatanIds) {
            $q->withTrashed()
                ->where('role_id', '!=', 2)
                ->when(!empty($allowedJabatanIds), fn($uq) => $uq->whereIn('jabatan_id', $allowedJabatanIds))
                ->when($this->selectedClientId() > 0, fn($uq) => $uq->whereHas('kerjasama', fn($k) => $k->where('client_id', $this->selectedClientId())));
        })
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['pending']);
            })
            ->get();
        $targetCode = auth()->user()->jabatan->code_jabatan == 'CO-CS'
            ? 'SPV'
            : (auth()->user()->jabatan->code_jabatan == 'CO-SCR'
                ? 'MARKETING'
                : null);
        if ($personOuts->isEmpty()) {
            return $this->backWithToast('info', 'Tidak ada pengajuan personil keluar.');
        }

        PersonOut::whereIn('id', $personOuts->pluck('id'))
            ->update(['status' => 'Di Ajukan']);

        $approvers = User::whereHas(
            'jabatan',
            fn($q) =>
            $q->where('code_jabatan', $targetCode)
        )->get();

        foreach ($personOuts as $personOut) {
            Notification::send(
                $approvers,
                new PersonOutSubmitted($personOut)
            );
        }

        return $this->backWithToast('success', 'Berhasil mengajukan semua personil keluar!');
    }


    public function fetchApi($id)
    {
        $personOut = PersonOut::with([
            'user' => function ($q) {
                $q->withTrashed();
            },
            'inputBy:id,name,nama_lengkap',
        ])->findOrFail($id);
        return response()->json([
            'message' => 'Get data by id',
            'data' => $personOut,
            'error' => ''
        ]);
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
