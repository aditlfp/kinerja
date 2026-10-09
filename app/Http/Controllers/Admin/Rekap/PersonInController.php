<?php

namespace App\Http\Controllers\Admin\Rekap;

use App\Http\Controllers\Controller;
use App\Models\PersonIn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PersonInController extends Controller
{
    public function index(Request $request, $kerjasama)
    {
        try {
            $kerjasamaModel = \App\Models\Kerjasama::findOrFail($kerjasama);

            $personIn = PersonIn::with('jabatan')
                ->where('client_id', $kerjasamaModel->client_id)
                ->where('status', 'Di Ajukan')
                ->when($request->month, function ($q) use ($request) {
                    $date = Carbon::createFromFormat('Y-m', $request->month);
                    $q->whereYear('date_in', $date->year)
                        ->whereMonth('date_in', $date->month);
                })
                ->orderByDesc('date_in')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $personIn,
                'message' => 'Data personil masuk berhasil diambil',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Gagal mengambil data personil masuk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $personIn = PersonIn::findOrFail($id);
            $personIn->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Data personil masuk berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Gagal menghapus data personil masuk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAction($id)
    {
        try {
            $personIn = PersonIn::findOrFail($id);
            $personIn->delete();

            toastr()->success('Data personil masuk berhasil dihapus.');
            return back();
        } catch (\Throwable $e) {
            toastr()->error('Gagal menghapus data personil masuk.');
            return back();
        }
    }
}
