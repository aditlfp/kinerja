<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * National holidays used to keep them out of working-day denominators and to
 * label the dates on the calendar.
 *
 * Two sources are merged: the holiday feed the attendance pages already use
 * (cached for a day, so a calendar browse does not fetch it again) and rows an
 * admin added from the holiday menu. The feed is best-effort: when it is
 * unreachable the recap still renders, just without the remote dates.
 */
class NationalHolidayService
{
    private const CACHE_TTL_SECONDS = 86400;

    private const FEED_URL = 'https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/holidays.json';

    /**
     * @return list<string> dates in Y-m-d
     */
    public function datesForYear(int $year): array
    {
        return array_keys($this->namesForYear($year));
    }

    /**
     * Dates mapped to their holiday name, e.g. ['2026-01-01' => 'Hari Tahun Baru'].
     * An admin row wins over the feed when both describe the same date.
     *
     * @return array<string, string>
     */
    public function namesForYear(int $year): array
    {
        $remote = Cache::remember(
            $this->cacheKey($year),
            self::CACHE_TTL_SECONDS,
            fn () => $this->fetchYear($year),
        );

        $local = Holiday::query()
            ->where('tanggal_libur', 'like', $year . '-%')
            ->get(['tanggal_libur', 'name'])
            ->mapWithKeys(fn (Holiday $holiday) => [
                Carbon::parse($holiday->tanggal_libur)->toDateString() => (string) $holiday->name,
            ])
            ->all();

        return array_replace($remote, $local);
    }

    /**
     * @return array<string, string>
     */
    private function fetchYear(int $year): array
    {
        try {
            $response = Http::timeout(5)->get(self::FEED_URL);

            if (! $response->successful()) {
                return [];
            }

            // The feed is keyed by date: {"2026-01-01": {"summary": "Hari Tahun Baru"}}.
            return collect($response->json() ?? [])
                ->filter(fn ($entry, $date) => is_string($date) && str_starts_with($date, $year . '-'))
                ->mapWithKeys(fn ($entry, $date) => [
                    Carbon::parse($date)->toDateString() => (string) (is_array($entry) ? ($entry['summary'] ?? '') : ''),
                ])
                ->all();
        } catch (\Throwable $e) {
            // A holiday feed outage must never take the recap page down.
            Log::warning('Daftar hari libur nasional tidak bisa dimuat.', [
                'year' => $year,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function cacheKey(int $year): string
    {
        return 'holidays.national.names.' . $year;
    }
}
