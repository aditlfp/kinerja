<?php

namespace App\Http\Controllers\Admin\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Kerjasama;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;

class OvertimesController extends Controller
{
    public function index($kerjasama)
    {
        if (!Kerjasama::where('id', $kerjasama)->exists()) {
            abort(404);
        }

        try {
            $startDate = Carbon::now()->subMonth()->day(26)->startOfDay();
            $endDate = Carbon::now()->day(24)->endOfDay();

            $overtimes = Overtime::with(['user', 'user.jabatan', 'user.kerjasama.client'])
                ->whereBetween('date_overtime', [$startDate, $endDate])
                ->where('status', 'Di Ajukan')
                ->whereHas('user', fn($q) => $q->where('kerjasama_id', $kerjasama))
                ->get();

            $employees = User::where('kerjasama_id', $kerjasama)->count();

            $groupedOvertimes = $overtimes->groupBy(function ($item) {
                return $item->user_id . '_' . strtolower($item->type_overtime);
            })->map(function ($group) {
                $first = $group->first();
                $typeOvertime = strtolower($first->type_overtime);

                if (in_array($typeOvertime, ['jam', 'lainnya'], true)) {
                    $totalJam = 0;
                    $totalRupiah = 0;

                    foreach ($group as $overtime) {
                        $value = $this->parseOvertimeValue($overtime->type_overtime_manual);
                        if ($value['type'] === 'jam') {
                            $totalJam += $value['value'];
                        } elseif ($value['type'] === 'rupiah') {
                            $totalRupiah += $value['value'];
                        }
                    }

                    $formattedOvertime = '';
                    if ($totalJam > 0 && $totalRupiah > 0) {
                        $formattedOvertime = $totalJam . ' Jam + Rp ' . number_format($totalRupiah, 0, ',', '.');
                    } elseif ($totalJam > 0) {
                        $formattedOvertime = $totalJam . ' Jam';
                    } elseif ($totalRupiah > 0) {
                        $formattedOvertime = 'Rp ' . number_format($totalRupiah, 0, ',', '.');
                    }

                    return [
                        'id' => $first->id,
                        'user' => $first->user,
                        'date_overtime' => $first->date_overtime,
                        'type_overtime' => $first->type_overtime,
                        'type_overtime_manual' => $formattedOvertime,
                        'total_jam' => $totalJam,
                        'total_rupiah' => $totalRupiah,
                        'status' => $first->status,
                        'desc' => $first->desc,
                        'count' => $group->count(),
                    ];
                }

                return [
                    'id' => $first->id,
                    'user' => $first->user,
                    'date_overtime' => $first->date_overtime,
                    'type_overtime' => $first->type_overtime,
                    'type_overtime_manual' => $first->type_overtime_manual,
                    'status' => $first->status,
                    'desc' => $first->desc,
                    'count' => $group->count(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $groupedOvertimes,
                'users_count' => $employees,
                'message' => 'Data lembur berhasil diambil',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Gagal mengambil data lembur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $overtime = Overtime::findOrFail($id);
            $overtime->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Data lembur berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Gagal menghapus data lembur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAction($id)
    {
        try {
            $overtime = Overtime::findOrFail($id);
            $overtime->delete();

            toastr()->success('Data lembur berhasil dihapus.');
            return back();
        } catch (\Throwable $e) {
            toastr()->error('Gagal menghapus data lembur.');
            return back();
        }
    }

    private function parseOvertimeValue($text)
    {
        if (!$text || !is_string($text)) {
            return ['type' => 'none', 'value' => 0];
        }

        $clean = preg_replace('/[^0-9]/', '', $text);

        if ($clean === '') {
            return ['type' => 'none', 'value' => 0];
        }

        $value = (int) $clean;

        if ($value >= 1 && $value <= 500) {
            return ['type' => 'jam', 'value' => $value];
        }

        if ($value >= 1000) {
            return ['type' => 'rupiah', 'value' => $value];
        }

        return ['type' => 'none', 'value' => 0];
    }
}
