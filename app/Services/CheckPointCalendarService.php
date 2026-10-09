<?php

namespace App\Services;

use App\Models\CheckPoint;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Builds the monthly calendar shown on the checkpoint index (interactive) and
 * history (read-only) pages, plus the per-employee calendar Direksi uses.
 *
 * Every row is
 *
 *   ['date' => Carbon, 'hasData' => bool, 'recordId' => int|null,
 *    'accepted' => bool, 'rejected' => bool, 'total' => int,
 *    'counts' => ['accept' => int, 'denied' => int, 'process' => int]]
 *
 * The per-day maps are cached per user; the cache key matches the
 * `Cache::forget('checkpoint-calendar:'.$id)` calls made after writes. Typed
 * (Direksi) calendars are queried fresh so their key never needs invalidating.
 */
class CheckPointCalendarService
{
    /** Cache lifetime for the per-user date maps. */
    private const CACHE_TTL_SECONDS = 30;

    /**
     * Build the calendar rows for a single month of one user.
     *
     * @param  string|null  $type  Optional `type_check` filter (Direksi); when
     *                             given the query skips the cache.
     * @return Collection<int, array{date: Carbon, hasData: bool, recordId: int|null, accepted: bool, rejected: bool, total: int, counts: array{accept: int, denied: int, process: int}}>
     */
    public function forMonth(int $userId, Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        $maps = $this->dateMaps($userId, $type);

        $calendar = collect();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $counts = $maps['counts']->get($key, ['accept' => 0, 'denied' => 0, 'process' => 0]);

            $calendar->push([
                'date' => $date->copy(),
                'hasData' => $maps['records']->has($key),
                'recordId' => $maps['records']->get($key),
                'accepted' => $counts['accept'] > 0,
                'rejected' => $counts['denied'] > 0,
                'total' => array_sum($counts),
                'counts' => $counts,
            ]);
        }

        return $calendar;
    }

    /**
     * Count a set of checkpoint items by approval status.
     *
     * The stored "pending" value is the legacy misspelling `proccess`; anything
     * that is not accept/denied is treated as pending.
     *
     * @return array{accept: int, denied: int, process: int}
     */
    public static function statusCounts(Collection $items): array
    {
        $counts = ['accept' => 0, 'denied' => 0, 'process' => 0];

        foreach ($items as $item) {
            match ($item->approve_status) {
                'accept' => $counts['accept']++,
                'denied' => $counts['denied']++,
                default => $counts['process']++,
            };
        }

        return $counts;
    }

    /**
     * Drop the cached date maps for a user (call after create/update/delete).
     */
    public function forget(int $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    /**
     * Per-user maps keyed by Y-m-d: which batch owns a day and the per-status
     * item counts for that day.
     *
     * @return array{records: Collection<string, int>, counts: Collection<string, array{accept: int, denied: int, process: int}>}
     */
    private function dateMaps(int $userId, ?string $type): array
    {
        if ($type !== null) {
            return $this->queryDateMaps($userId, $type);
        }

        return Cache::remember(
            $this->cacheKey($userId),
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            fn () => $this->queryDateMaps($userId, null),
        );
    }

    /**
     * @return array{records: Collection<string, int>, counts: Collection<string, array{accept: int, denied: int, process: int}>}
     */
    private function queryDateMaps(int $userId, ?string $type): array
    {
        $records = collect();
        $counts = collect();

        // Select only the columns the calendar consumes; items are eager
        // loaded so this stays at two queries no matter how many batches exist.
        CheckPoint::query()
            ->with('items:id,check_point_id,tanggal,approve_status')
            ->where('user_id', $userId)
            ->when($type, fn ($query) => $query->where('type_check', $type))
            ->get()
            ->each(function (CheckPoint $checkPoint) use ($records, $counts): void {
                if ($checkPoint->items->isEmpty()) {
                    $records->put($checkPoint->created_at->toDateString(), $checkPoint->id);

                    return;
                }

                $byDate = $checkPoint->items->groupBy(
                    fn ($item) => Carbon::parse($item->tanggal ?? $checkPoint->created_at)->toDateString(),
                );

                foreach ($byDate as $date => $dayItems) {
                    $records->put($date, $checkPoint->id);

                    // A day can span batches, so merge counts instead of overwriting.
                    $dayCounts = self::statusCounts($dayItems);
                    $existing = $counts->get($date, ['accept' => 0, 'denied' => 0, 'process' => 0]);
                    $counts->put($date, [
                        'accept' => $existing['accept'] + $dayCounts['accept'],
                        'denied' => $existing['denied'] + $dayCounts['denied'],
                        'process' => $existing['process'] + $dayCounts['process'],
                    ]);
                }
            });

        return ['records' => $records, 'counts' => $counts];
    }

    private function cacheKey(int $userId): string
    {
        return 'checkpoint-calendar:' . $userId;
    }
}
