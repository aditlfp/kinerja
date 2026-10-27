<?php

namespace App\Http\Controllers\SVP_Controller\Rekap;

use App\Http\Controllers\SVP_Controller\Rekap\Concerns\HasAllowedSeeData;
use App\Models\PersonIn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PersonInController extends RekapController
{
    use HasAllowedSeeData;
    public function index(Request $request, $kerjasama)
    {
        $kerjasamaModel = \App\Models\Kerjasama::findOrFail($kerjasama);

        $personIn = PersonIn::with(['jabatan', 'createdBy'])
            ->where('client_id', $kerjasamaModel->client_id)
            ->whereIn('status', ['Di Ajukan', 'Di Setujui', 'Di Tolak'])
            ->whereIn('jabatan_id', $this->allowedSeeData())
            ->when($request->month, function ($q) use ($request) {
                $date = Carbon::createFromFormat('Y-m', $request->month);
                $q->whereBetween('date_in', monthBounds($date));
            })
            ->orderByDesc('date_in')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $personIn,
            'message' => 'Data personil masuk berhasil diambil'
        ]);
    }
    public function updateStatus(Request $request, $id)
    {
        try {
            $personIn = PersonIn::findOrFail($id);
            $status = $request->input('status');
            if (!in_array($status, ['Di Setujui', 'Di Tolak'])) {
                return response()->json(['success' => false, 'message' => 'Status tidak valid'], 422);
            }
            $personIn->update(['status' => $status]);
            return response()->json(['success' => true, 'message' => 'Status berhasil diupdate']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
