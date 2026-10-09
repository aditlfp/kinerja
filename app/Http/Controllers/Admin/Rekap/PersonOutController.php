<?php

namespace App\Http\Controllers\Admin\Rekap;

use App\Http\Controllers\Controller;
use App\Models\PersonOut;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PersonOutController extends Controller
{
    public function index(Request $request, $kerjasama)
    {
        try {
            $startDate = Carbon::now()->startOfMonth()->startOfDay();
            $endDate = Carbon::now()->startOfMonth()->addDays(24)->endOfDay();

            $personOut = PersonOut::with([
                'user' => function ($q) {
                    $q->withTrashed()->with(['jabatan', 'kerjasama.client']);
                },
            ])
                ->where('status', 'Di Ajukan')
                ->whereHas('user', function ($q) use ($kerjasama) {
                    $q->withTrashed()->where('kerjasama_id', $kerjasama);
                })
                ->whereBetween('out_date', [$startDate, $endDate])
                ->when($request->month, function ($q) use ($request) {
                    $date = Carbon::createFromFormat('Y-m', $request->month);
                    $q->whereYear('out_date', $date->year)
                        ->whereMonth('out_date', $date->month);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $personOut,
                'message' => 'Data personil keluar berhasil diambil',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Gagal mengambil data personil keluar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $personOut = PersonOut::findOrFail($id);
            $personOut->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Data personil keluar berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Gagal menghapus data personil keluar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAction($id)
    {
        try {
            $personOut = PersonOut::findOrFail($id);
            $personOut->delete();

            toastr()->success('Data personil keluar berhasil dihapus.');
            return back();
        } catch (\Throwable $e) {
            toastr()->error('Gagal menghapus data personil keluar.');
            return back();
        }
    }

    public function restoreUser($id)
    {
        try {
            $personOut = PersonOut::findOrFail($id);
            $user = User::withTrashed()->findOrFail($personOut->user_id);

            if ($user->trashed()) {
                $user->restore();

                return response()->json([
                    'success' => true,
                    'data' => ['restored' => true],
                    'message' => 'User berhasil direstore',
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => ['restored' => false],
                'message' => 'User sudah dalam kondisi aktif',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Gagal restore user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restoreUserAction($id)
    {
        try {
            $personOut = PersonOut::findOrFail($id);
            $user = User::withTrashed()->findOrFail($personOut->user_id);

            if ($user->trashed()) {
                $user->restore();
                toastr()->success('User berhasil direstore.');
            } else {
                toastr()->success('User sudah dalam kondisi aktif.');
            }

            return back();
        } catch (\Throwable $e) {
            toastr()->error('Gagal restore user.');
            return back();
        }
    }
}
