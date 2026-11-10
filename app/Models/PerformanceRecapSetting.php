<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceRecapSetting extends Model
{
    use HasFactory;

    public const DAY_NAMES = CheckPointReportSetting::DAY_NAMES;

    public const DEFAULT_VISIBLE_DAYS = [6, 7];

    protected $fillable = [
        'visible_days',
        'updated_by',
    ];

    protected $casts = [
        'visible_days' => 'array',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Configured weekly days, falling back to Saturday and Sunday when unset.
     *
     * @return array<int, int>
     */
    public function visibleDays(): array
    {
        $days = array_values(array_unique(array_map('intval', (array) $this->visible_days)));
        $days = array_values(array_filter($days, fn (int $day): bool => $day >= 1 && $day <= 7));

        return $days ?: self::DEFAULT_VISIBLE_DAYS;
    }

    public function isVisibleAt(?Carbon $at = null): bool
    {
        return in_array(($at ?? now())->dayOfWeekIso, $this->visibleDays(), true);
    }

    public function daysLabel(): string
    {
        return collect($this->visibleDays())
            ->sort()
            ->map(fn (int $day): string => self::DAY_NAMES[$day])
            ->implode(' & ');
    }
}
