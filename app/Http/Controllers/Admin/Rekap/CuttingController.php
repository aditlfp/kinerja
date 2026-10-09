<?php

namespace App\Http\Controllers\Admin\Rekap;

use App\Http\Controllers\Controller;
use App\Models\PerformanceCuts;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CuttingController extends Controller
{
    public function index(Request $request, $kerjasama)
    {
        try {
            \App\Models\Kerjasama::findOrFail($kerjasama);

            $cuttings = PerformanceCuts::with(['user' => function ($q) {
                $q->with(['jabatan', 'kerjasama.client']);
            }])
                ->where('status', 'Di Ajukan')
                ->whereHas('user', function ($q) use ($kerjasama) {
                    $q->where('kerjasama_id', $kerjasama);
                })
                ->when($request->month, function ($q) use ($request) {
                    $date = Carbon::createFromFormat('Y-m', $request->month);
                    $q->whereYear('date_cut', $date->year)
                        ->whereMonth('date_cut', $date->month);
                })
                ->orderByDesc('date_cut')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cuttings,
                'message' => 'Data cutting berhasil diambil',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Gagal mengambil data cutting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cutting = PerformanceCuts::findOrFail($id);
            $cutting->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Data cutting berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Gagal menghapus data cutting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAction($id)
    {
        try {
            $cutting = PerformanceCuts::findOrFail($id);
            $cutting->delete();

            toastr()->success('Data cutting berhasil dihapus.');
            return back();
        } catch (\Throwable $e) {
            toastr()->error('Gagal menghapus data cutting.');
            return back();
        }
    }
}
