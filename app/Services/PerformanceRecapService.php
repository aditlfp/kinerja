<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\PerformanceRecapSetting;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;

/**
 * Monthly performance recap shown on the Direksi employee calendar: attendance,
 * daily checkpoint evidence, and work orders, each as a percentage of what was
 * expected in the browsed month.
 *
 * The recap for the month that is still running is only readable on configured
 * weekly days or the end of a month, so a reviewer cannot grade an employee on
 * a period that has not finished. Months that are already over stay open at any
 * time: their numbers are final.
 *
 * Working days are Monday–Friday across the whole month. National holidays
 * are excluded from that denominator as well.
 */
class PerformanceRecapService
{
    /** @var array<int, int>|null */
    private ?array $configuredVisibleDays = null;

    public function __construct(
        private readonly CheckPointCalendarService $calendarService,
        private readonly NationalHolidayService $holidayService,
    ) {
    }

    /**
     * @return array<int, int>
     */
    public function visibleDays(): array
    {
        return $this->configuredVisibleDays ??= PerformanceRecapSetting::query()->latest()->first()?->visibleDays()
            ?? PerformanceRecapSetting::DEFAULT_VISIBLE_DAYS;
    }

    public function visibleDaysLabel(): string
    {
        return collect($this->visibleDays())
            ->sort()
            ->map(fn (int $day): string => PerformanceRecapSetting::DAY_NAMES[$day])
            ->implode(' & ');
    }

    public function isVisible(?Carbon $at = null): bool
    {
        $at = ($at ?? now())->copy();

        return in_array($at->dayOfWeekIso, $this->visibleDays(), true);
    }

    /**
     * Whether the browsed month ended before today, i.e. its recap is final.
     */
    private function isPastMonth(Carbon $end, Carbon $at): bool
    {
        return $end->copy()->endOfDay()->lt($at->copy()->startOfDay());
    }

    /**
     * The next date the recap opens, today included when it is already open.
     */
    public function nextVisibleAt(?Carbon $at = null): Carbon
    {
        $candidate = ($at ?? now())->copy()->startOfDay();

        // A year is more than enough; the rule opens at least once per configured week.
        for ($i = 0; $i < 366; $i++) {
            if ($this->isVisible($candidate)) {
                return $candidate;
            }

            $candidate->addDay();
        }

        return $candidate;
    }

    /**
     * @return array{
     *     visible: bool,
     *     nextVisibleAt: Carbon,
     *     visibleDaysLabel: string,
     *     workingDays: int,
     *     excludedHolidays: array<string, string>,
     *     metrics: array<string, array{percent: int|null, achieved: int, total: int}>
     * }
     */
    public function forMonth(User $employee, Carbon $start, Carbon $end, ?Carbon $at = null): array
    {
        $at ??= now();

        [$workingDates, $excludedHolidays] = $this->workingDates($start, $end);
        $workingDays = $workingDates->count();

        $attended = $this->attendanceDates($employee->id, $start, $end)->intersect($workingDates)->count();
        $evidenced = $this->checkpointDates($employee->id, $start, $end)->intersect($workingDates)->count();

        [$ordersTotal, $ordersDone] = $this->workOrderCounts($employee->id, $start, $end);

        return [
            'visible' => $this->isVisible($at) || $this->isPastMonth($end, $at),
            'nextVisibleAt' => $this->nextVisibleAt($at),
            'visibleDaysLabel' => $this->visibleDaysLabel(),
            'workingDays' => $workingDays,
            'excludedHolidays' => $excludedHolidays,
            'metrics' => [
                'attendance' => [
                    'percent' => $this->percent($attended, $workingDays),
                    'achieved' => $attended,
                    'total' => $workingDays,
                ],
                'checkpoint' => [
                    'percent' => $this->percent($evidenced, $workingDays),
                    'achieved' => $evidenced,
                    'total' => $workingDays,
                ],
                'work_order' => [
                    'percent' => $this->percent($ordersDone, $ordersTotal),
                    'achieved' => $ordersDone,
                    'total' => $ordersTotal,
                ],
            ],
        ];
    }

    /**
     * Weekdays across the full month, excluding national holidays.
     *
     * @return array{0: \Illuminate\Support\Collection<int, string>, 1: array<string, string>}
     */
    private function workingDates(Carbon $start, Carbon $end): array
    {
        $holidays = $this->holidayService->namesForYear($start->year);
        $dates = collect();
        $excluded = [];

        for ($date = $start->copy()->startOfDay(); $date->lte($end->copy()->endOfDay()); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            $key = $date->toDateString();

            if (array_key_exists($key, $holidays)) {
                $excluded[$key] = $holidays[$key];

                continue;
            }

            $dates->push($key);
        }

        return [$dates, $excluded];
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function attendanceDates(int $userId, Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        return Absensi::query()
            ->where('user_id', $userId)
            ->whereBetween('tanggal_absen', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('absensi_type_masuk')
            ->where('absensi_type_masuk', '!=', '')
            ->pluck('tanggal_absen')
            ->map(fn ($tanggal) => Carbon::parse($tanggal)->toDateString())
            ->unique()
            ->values();
    }

    /**
     * Days carrying at least one job row, the same evidence the calendar marks
     * with a status row.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function checkpointDates(int $userId, Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        return $this->calendarService
            ->forMonth($userId, $start->copy(), $end->copy(), 'dikerjakan')
            ->filter(fn (array $day) => $day['hasData'])
            ->map(fn (array $day) => $day['date']->toDateString())
            ->values();
    }

    /**
     * @return array{0: int, 1: int} total orders, completed orders
     */
    private function workOrderCounts(int $userId, Carbon $start, Carbon $end): array
    {
        $row = WorkOrder::query()
            ->where('user_id', $userId)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('count(*) as total, coalesce(sum(has_complete), 0) as done')
            ->first();

        return [(int) ($row->total ?? 0), (int) ($row->done ?? 0)];
    }

    private function percent(int $achieved, int $total): ?int
    {
        if ($total < 1) {
            return null;
        }

        return min(100, (int) round($achieved / $total * 100));
    }
}
