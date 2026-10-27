<?php

namespace App\Http\Controllers\SVP_Controller\Rekap;

use App\Http\Controllers\SVP_Controller\Rekap\Concerns\HasAllowedSeeData;
use App\Models\PerformanceCuts;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CuttingController extends RekapController
{
    use HasAllowedSeeData;
    public function index(Request $request, $kerjasama)
    {
        \App\Models\Kerjasama::findOrFail($kerjasama);

        $cuttings = PerformanceCuts::with(['user' => function ($q) {
            $q->with(['jabatan', 'kerjasama.client']);
        }, 'createdBy'])
            ->whereIn('status', ['Di Ajukan', 'Di Setujui', 'Di Tolak'])
            ->whereHas('user', function ($q) use ($kerjasama) {
                $q->where('kerjasama_id', $kerjasama)
                    ->whereIn('jabatan_id', $this->allowedSeeData());
            })
            ->when($request->month, function ($q) use ($request) {
                $date = Carbon::createFromFormat('Y-m', $request->month);
                $q->whereBetween('date_cut', monthBounds($date));
            })
            ->orderByDesc('date_cut')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cuttings,
            'message' => 'Data cutting berhasil diambil'
        ]);
    }
    public function updateStatus(Request $request, $id)
    {
        try {
            $cutting = PerformanceCuts::findOrFail($id);
            $status = $request->input('status');
            if (!in_array($status, ['Di Setujui', 'Di Tolak'])) {
                return response()->json(['success' => false, 'message' => 'Status tidak valid'], 422);
            }
            $cutting->update(['status' => $status]);
            return response()->json(['success' => true, 'message' => 'Status berhasil diupdate']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
