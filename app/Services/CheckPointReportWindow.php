<?php

namespace App\Services;

use App\Models\CheckPointReportSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Weekly reporting window for checkpoint evidence ("bukti pekerjaan").
 *
 * Admins configure the last day reports are accepted (default Saturday).
 * From the next day until Monday the week is closed: no checkpoint input is
 * accepted. Admins and DIREKSI users are exempt so they can still help with
 * catch-up entries.
 */
class CheckPointReportWindow
{
    public function setting(): ?CheckPointReportSetting
    {
        return CheckPointReportSetting::query()->latest()->first();
    }

    public function lastDay(): int
    {
        return $this->setting()?->lastDay() ?? CheckPointReportSetting::DEFAULT_LAST_DAY;
    }

    public function dayName(): string
    {
        return CheckPointReportSetting::DAY_NAMES[$this->lastDay()];
    }

    public function weekStart(?Carbon $at = null): Carbon
    {
        return ($at ?? now())->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    public function deadlineForWeek(?Carbon $at = null): Carbon
    {
        return $this->weekStart($at)
            ->addDays($this->lastDay() - 1)
            ->endOfDay();
    }

    public function nextOpenAt(?Carbon $at = null): Carbon
    {
        return $this->weekStart($at)->addWeek();
    }

    public function isLocked(?Carbon $at = null): bool
    {
        return ($at ?? now())->dayOfWeekIso > $this->lastDay();
    }

    /**
     * Admin (role_id 2) and DIREKSI jabatan bypass the window.
     */
    public function isExempt(?User $user = null): bool
    {
        $user ??= Auth::user();

        if (! $user) {
            return false;
        }

        if ((int) $user->role_id === 2) {
            return true;
        }

        return $user->divisi?->jabatan?->code_jabatan === 'DIREKSI'
            || $user->jabatan?->code_jabatan === 'DIREKSI';
    }

    /**
     * True when this request must be rejected by the reporting window.
     */
    public function blocks(?User $user = null): bool
    {
        return $this->isLocked() && ! $this->isExempt($user);
    }

    public function message(): string
    {
        return 'Batas laporan: hari '.$this->dayName().'. '
            .'Input bukti pekerjaan dibuka lagi hari Senin.';
    }

    /**
     * Lock response for the current request, or null when input is allowed.
     *
     * @return RedirectResponse|JsonResponse|null
     */
    public function reject()
    {
        if (! $this->blocks()) {
            return null;
        }

        $message = $this->message();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'message' => $message,
                'data' => null,
                'error' => 'locked',
            ], 403);
        }

        return redirect()
            ->route('checkpoint-user.index')
            ->with('checkpoint_locked', $message);
    }
}
