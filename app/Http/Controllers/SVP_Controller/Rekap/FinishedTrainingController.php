<?php

namespace App\Http\Controllers\SVP_Controller\Rekap;

use App\Http\Controllers\SVP_Controller\Rekap\Concerns\HasAllowedSeeData;
use App\Models\FinishedTraining;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinishedTrainingController extends RekapController
{
    use HasAllowedSeeData;
    public function index(Request $request, $kerjasama)
    {
        \App\Models\Kerjasama::findOrFail($kerjasama);

        $items = FinishedTraining::with(['user' => function ($q) {
            $q->with(['jabatan', 'kerjasama.client']);
        }])
            ->whereIn('status', ['Di Ajukan', 'Di Setujui', 'Di Tolak'])
            ->whereHas('user', function ($q) use ($kerjasama) {
                $q->where('kerjasama_id', $kerjasama)
                    ->whereIn('jabatan_id', $this->allowedSeeData());
            })
            ->when($request->month, function ($q) use ($request) {
                $date = Carbon::createFromFormat('Y-m', $request->month);
                $q->whereBetween('date_finish_train', monthBounds($date));
            })
            ->orderByDesc('date_finish_train')
            ->get()
            ->map(function ($item) {
                $item->masa_training_hari = Carbon::parse($item->date_finish_train)->day;
                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'Data lepas training berhasil diambil'
        ]);
    }
    public function updateStatus(Request $request, $id)
    {
        try {
            $training = FinishedTraining::findOrFail($id);
            $status = $request->input('status');
            if (!in_array($status, ['Di Setujui', 'Di Tolak'])) {
                return response()->json(['success' => false, 'message' => 'Status tidak valid'], 422);
            }
            $training->update(['status' => $status]);
            return response()->json(['success' => true, 'message' => 'Status berhasil diupdate']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
