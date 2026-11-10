<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckPointReportSetting;
use App\Models\PerformanceRecapSetting;
use App\Services\CheckPointReportWindow;
use Illuminate\Http\Request;

class CheckpointSettingsController extends Controller
{
    public function __construct(
        private readonly CheckPointReportWindow $reportWindow,
    ) {}

    public function index()
    {
        $setting = CheckPointReportSetting::with('updater')->latest()->first();
        $recapSetting = PerformanceRecapSetting::with('updater')->latest()->first();

        return view('admin.check.settings', [
            'setting' => $setting,
            'days' => CheckPointReportSetting::DAY_NAMES,
            'lastDay' => $this->reportWindow->lastDay(),
            'isLocked' => $this->reportWindow->isLocked(),
            'deadline' => $this->reportWindow->deadlineForWeek(),
            'nextOpenAt' => $this->reportWindow->nextOpenAt(),
            'recapSetting' => $recapSetting,
            'recapDays' => PerformanceRecapSetting::DAY_NAMES,
            'recapVisibleDays' => $recapSetting?->visibleDays() ?? PerformanceRecapSetting::DEFAULT_VISIBLE_DAYS,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'last_day' => ['required', 'integer', 'between:1,7'],
        ]);

        CheckPointReportSetting::create([
            'last_day' => (int) $validated['last_day'],
            'updated_by' => auth()->id(),
        ]);

        toastr()->success('Batas laporan bukti pekerjaan berhasil diperbarui.');

        return back();
    }

    public function updateRecap(Request $request)
    {
        $validated = $request->validate([
            'visible_days' => ['required', 'array', 'min:1'],
            'visible_days.*' => ['integer', 'between:1,7'],
        ]);

        PerformanceRecapSetting::create([
            'visible_days' => array_values(array_unique(array_map('intval', $validated['visible_days']))),
            'updated_by' => auth()->id(),
        ]);

        toastr()->success('Hari tampil rekap persentase berhasil diperbarui.');

        return back();
    }

    public function reset()
    {
        CheckPointReportSetting::query()->delete();

        toastr()->success('Pengaturan dihapus. Batas laporan kembali ke default: Sabtu.');

        return back();
    }

    public function resetRecap()
    {
        PerformanceRecapSetting::query()->delete();

        toastr()->success('Pengaturan dihapus. Rekap persentase kembali tampil pada Sabtu dan Minggu.');

        return back();
    }
}
