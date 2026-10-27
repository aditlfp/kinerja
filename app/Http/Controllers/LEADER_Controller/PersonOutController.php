<?php

namespace App\Http\Controllers\LEADER_Controller;

use App\Http\Controllers\Concerns\LocksRekapByDueDate;
use App\Http\Controllers\Concerns\ScopesLeaderMitra;
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
    use LocksRekapByDueDate, ScopesLeaderMitra;

    public function create()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $users = $this->visibleUsers()->orderBy('nama_lengkap')->get();
        return view('leader_view.data_rekap.person_out.create', [
            'users' => $users
        ]);
    }

    public function history(Request $request)
    {
        $isSubmissionLocked = $this->isSubmissionLockedByDueDate();

        $personOut = PersonOut::select(['id', 'user_id', 'total_mk', 'reason', 'reason_manual', 'out_date', 'img', 'status', 'created_at', 'updated_at'])
            ->with([
                'user' => function ($q) {
                    $q->withTrashed()->select(['id', 'name', 'nama_lengkap']);
                }
            ])->whereHas('user', function ($q) {
                $q->withTrashed();
                $this->scopeLeaderUser($q);
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

        return view('leader_view.data_rekap.person_out.show', [
            'personOut' => $personOut,
            'isSubmissionLocked' => $isSubmissionLocked,
            'canBulkSubmit' => !$isSubmissionLocked && PersonOut::whereHas('user', function ($q) {
                $q->withTrashed();
                $this->scopeLeaderUser($q);
            })->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) = ?', ['pending']);
            })->exists(),
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

            abort_unless($this->visibleUsers()->whereKey($validated['user_id'])->exists(), 403);

            if ($request->hasFile('img')) {
                try {
                    $validated['img'] = UploadImageV2($request, 'img');
                    if (empty($validated['img'])) {
                        return redirect()->back()->withInput()->with('toast', [
                            'type' => 'error',
                            'message' => 'Gagal mengunggah gambar. Silakan coba lagi.',
                        ]);
                    }
                } catch (\Exception $e) {
                    report($e);
                    return redirect()->back()->withInput()->with('toast', [
                        'type' => 'error',
                        'message' => 'Gagal mengunggah gambar. Silakan coba lagi.',
                    ]);
                }
            }

            $validated['created_by_user_id'] = auth()->id();
            PersonOut::create($validated);

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Berhasil mengajukan data!',
            ]);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'message' => collect($th->errors())->flatten()->first(),
            ]);
        } catch (\Throwable $th) {
            report($th);

            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data personil keluar. Silakan coba lagi.',
            ]);
        }
    }

    public function edit($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $personOut = PersonOut::whereHas('user', function ($q) {
            $q->withTrashed();
            $this->scopeLeaderUser($q);
        })->findOrFail($id);
        $users = $this->visibleUsers()->orderBy('nama_lengkap')->get();
        return view('leader_view.data_rekap.person_out.edit', [
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

                $personOut = PersonOut::whereHas('user', function ($q) {
                    $q->withTrashed();
                    $this->scopeLeaderUser($q);
                })->findOrFail($id);
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

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Berhasil update data!',
            ]);
        } catch (\Throwable $th) {
            report($th);
            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'message' => 'Gagal memperbarui data personil keluar. Silakan coba lagi.',
            ]);
        }
    }

    public function destroy(PersonOut $personOut)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        abort_unless(PersonOut::whereKey($personOut->id)->whereHas('user', function ($q) {
            $q->withTrashed();
            $this->scopeLeaderUser($q);
        })->exists(), 403);

        $personOut->delete();

        return redirect()->back()->with('toast', [
            'type' => 'warning',
            'message' => 'Data personil keluar berhasil dihapus!',
        ]);
    }

    public function changeStatus($id)
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $personOut = PersonOut::whereHas('user', function ($q) {
            $q->withTrashed();
            $this->scopeLeaderUser($q);
        })->findOrFail($id);
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
        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Personil keluar berhasil diajukan!',
        ]);
    }

    public function bulkStatus()
    {
        if ($response = $this->rejectIfRekapLocked()) {
            return $response;
        }

        $personOuts = PersonOut::whereHas('user', function ($q) {
            $q->withTrashed();
            $this->scopeLeaderUser($q);
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
            return back()->with('toast', [
                'type' => 'info',
                'message' => 'Tidak ada pengajuan personil keluar.',
            ]);
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

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Berhasil mengajukan semua personil keluar!',
        ]);
    }


    private function visibleUsers()
    {
        return User::select(['id', 'name', 'nama_lengkap', 'kerjasama_id'])
            ->with('kerjasama.client:id,name,panggilan')
            ->where('id', '!=', auth()->id())
            ->where(function ($q) {
                $this->scopeLeaderUser($q);
            });
    }

    public function fetchApi($id)
    {
        $personOut = PersonOut::with([
            'user' => function ($q) {
                $q->withTrashed();
            }
        ])->whereHas('user', function ($q) {
            $q->withTrashed();
            $this->scopeLeaderUser($q);
        })->findOrFail($id);
        return response()->json([
            'message' => 'Get data by id',
            'data' => $personOut,
            'error' => ''
        ]);
    }
}
