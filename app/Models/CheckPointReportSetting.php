<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckPointReportSetting extends Model
{
    use HasFactory;

    /** Monday-first day names, indexed by Carbon's dayOfWeekIso. */
    public const DAY_NAMES = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /** Default: reporting closes after Saturday. */
    public const DEFAULT_LAST_DAY = 6;

    protected $fillable = [
        'last_day',
        'updated_by',
    ];

    protected $casts = [
        'last_day' => 'integer',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Configured last reporting day, falling back to the default when unset.
     */
    public function lastDay(): int
    {
        $day = (int) ($this->last_day ?? self::DEFAULT_LAST_DAY);

        return $day >= 1 && $day <= 7 ? $day : self::DEFAULT_LAST_DAY;
    }

    public function dayName(): string
    {
        return self::DAY_NAMES[$this->lastDay()];
    }

    /**
     * Monday 00:00 of the week that contains $at.
     */
    public function weekStart(?Carbon $at = null): Carbon
    {
        return ($at ?? now())->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    /**
     * End of the reporting window (23:59:59 on the configured day).
     */
    public function deadlineForWeek(?Carbon $at = null): Carbon
    {
        return $this->weekStart($at)
            ->addDays($this->lastDay() - 1)
            ->endOfDay();
    }

    /**
     * A week is closed once today is past the configured last day.
     */
    public function isLockedAt(?Carbon $at = null): bool
    {
        return ($at ?? now())->dayOfWeekIso > $this->lastDay();
    }

    /**
     * First moment the next week's reports can be submitted (Monday 00:00).
     */
    public function nextOpenAt(?Carbon $at = null): Carbon
    {
        return $this->weekStart($at)->addWeek();
    }

    public function label(): string
    {
        return 'Hari '.$this->dayName();
    }
}
