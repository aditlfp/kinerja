<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekapDueDateSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapSettingsController extends Controller
{
    public function index()
    {
        $setting = RekapDueDateSetting::with('updater')->latest()->first();

        return view('admin.rekap.settings', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
            'due_time' => ['required', 'date_format:H:i'],
        ]);

        // Store as template datetime; only day+time are used monthly.
        $dueDate = Carbon::create(2000, 1, (int) $validated['due_day'])
            ->setTimeFromTimeString($validated['due_time'].':00');

        RekapDueDateSetting::create([
            'due_date' => $dueDate,
            'updated_by' => auth()->id(),
        ]);

        toastr()->success('Batas waktu rekap berhasil diperbarui.');

        return back();
    }

    public function reset()
    {
        RekapDueDateSetting::query()->delete();

        toastr()->success('Batas waktu rekap dihapus. Pengajuan rekap terbuka kembali.');

        return back();
    }
}
