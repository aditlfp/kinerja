<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckPointReportSetting;
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

        return view('admin.check.settings', [
            'setting' => $setting,
            'days' => CheckPointReportSetting::DAY_NAMES,
            'lastDay' => $this->reportWindow->lastDay(),
            'isLocked' => $this->reportWindow->isLocked(),
            'deadline' => $this->reportWindow->deadlineForWeek(),
            'nextOpenAt' => $this->reportWindow->nextOpenAt(),
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

    public function reset()
    {
        CheckPointReportSetting::query()->delete();

        toastr()->success('Pengaturan dihapus. Batas laporan kembali ke default: Sabtu.');

        return back();
    }
}
