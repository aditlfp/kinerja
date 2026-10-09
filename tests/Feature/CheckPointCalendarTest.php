<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\User;
use App\Services\CheckPointCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Coverage for the shared checkpoint calendar used by both the interactive
 * `index` page and the read-only `history` page.
 *
 * The important guarantees:
 *   - a day is flagged accepted / rejected from its items' approve_status;
 *   - the two pages read the same shape (so they cannot drift apart);
 *   - building the calendar does not fan out into per-row queries (N+1).
 */
class CheckPointCalendarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        CheckPoint::query()->delete();
        User::query()->where('email', 'like', 'calendar-test-%')->delete();

        Cache::flush();
    }

    private function user(): User
    {
        return User::create([
            'kerjasama_id' => 1,
            'devisi_id' => \App\Models\Divisi::query()->value('id') ?? 1,
            'jabatan_id' => \App\Models\Jabatan::query()->value('id') ?? 1,
            'name' => 'calendar-test-' . uniqid(),
            'nama_lengkap' => 'Karyawan Kalender',
            'image' => 'default.png',
            'email' => 'calendar-test-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** A month of a single accepted day is flagged accepted, not rejected. */
    public function test_a_day_with_an_accepted_item_is_flagged_accepted(): void
    {
        $userId = $this->user()->id;

        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
        $checkpoint->items()->create([
            'pekerjaan_cp_id' => '1',
            'tanggal' => '2026-10-05',
            'approve_status' => 'accept',
            'urutan' => 0,
        ]);

        $calendar = app(CheckPointCalendarService::class)
            ->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));

        $day = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-05');

        $this->assertTrue($day['hasData']);
        $this->assertTrue($day['accepted']);
        $this->assertFalse($day['rejected']);
        $this->assertSame($checkpoint->id, $day['recordId']);
    }

    /** A denied item marks the day rejected. */
    public function test_a_day_with_a_denied_item_is_flagged_rejected(): void
    {
        $userId = $this->user()->id;

        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
        $checkpoint->items()->create([
            'pekerjaan_cp_id' => '1',
            'tanggal' => '2026-10-06',
            'approve_status' => 'denied',
            'urutan' => 0,
        ]);

        $calendar = app(CheckPointCalendarService::class)
            ->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));

        $day = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-06');

        $this->assertTrue($day['hasData']);
        $this->assertFalse($day['accepted']);
        $this->assertTrue($day['rejected']);
    }

    /** A batch with no items falls back to its created_at date. */
    public function test_a_batch_without_items_uses_the_created_date(): void
    {
        $userId = $this->user()->id;

        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'harian']);

        $calendar = app(CheckPointCalendarService::class)
            ->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));

        $day = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === $checkpoint->created_at->toDateString());

        $this->assertNotNull($day);
        $this->assertTrue($day['hasData']);
        $this->assertSame($checkpoint->id, $day['recordId']);
    }

    /** Calendar rows only exist for days that carry data. */
    public function test_days_without_data_are_not_flagged(): void
    {
        $userId = $this->user()->id;

        $calendar = app(CheckPointCalendarService::class)
            ->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));

        $this->assertSame(31, $calendar->count());
        $this->assertTrue($calendar->every(fn ($row) => $row['hasData'] === false));
    }

    /** Writes invalidate the cached maps so the calendar reflects them. */
    public function test_forget_clears_the_cached_calendar(): void
    {
        $service = app(CheckPointCalendarService::class);
        $userId = $this->user()->id;

        $service->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $this->assertNotNull(Cache::get('checkpoint-calendar:' . $userId));

        $service->forget($userId);

        $this->assertNull(Cache::get('checkpoint-calendar:' . $userId));
    }

    /**
     * Building the calendar must not issue one query per checkpoint (N+1).
     * Ten batches should still cost a constant number of queries.
     */
    public function test_building_the_calendar_does_not_trigger_n_plus_one(): void
    {
        $userId = $this->user()->id;

        for ($i = 0; $i < 10; $i++) {
            $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
            $checkpoint->items()->create([
                'pekerjaan_cp_id' => '1',
                'tanggal' => '2026-10-' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'approve_status' => 'proccess',
                'urutan' => 0,
            ]);
        }

        DB::enableQueryLog();
        app(CheckPointCalendarService::class)
            ->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // One query for the batches + one eager-load query for their items.
        $this->assertLessThanOrEqual(2, $queryCount, 'calendar building should use a constant number of queries');
    }

    /**
     * The read-only history page and the interactive index page must expose
     * the exact same calendar row shape, so the two can never diverge.
     */
    public function test_history_and_index_share_the_same_calendar_shape(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        $checkpoint = CheckPoint::create(['user_id' => $user->id, 'type_check' => 'dikerjakan']);
        $checkpoint->items()->create([
            'pekerjaan_cp_id' => '1',
            'tanggal' => '2026-10-07',
            'approve_status' => 'accept',
            'urutan' => 0,
        ]);

        $expectedKeys = ['date', 'hasData', 'recordId', 'accepted', 'rejected', 'total', 'counts'];

        $index = $this->get(route('checkpoint-user.index', ['month' => '2026-10']));
        $history = $this->get(route('checkpoint-user.history', ['month' => '2026-10']));

        $index->assertOk();
        $history->assertOk();

        $indexCalendar = $index->viewData('calendar');
        $historyCalendar = $history->viewData('calendar');

        $this->assertSame($expectedKeys, array_keys($indexCalendar->first()));
        $this->assertSame($expectedKeys, array_keys($historyCalendar->first()));

        $indexDay = $indexCalendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-07');
        $historyDay = $historyCalendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-07');

        $this->assertSame($indexDay['recordId'], $historyDay['recordId']);
        $this->assertTrue($indexDay['accepted']);
        $this->assertTrue($historyDay['accepted']);
    }

    /** A day with mixed statuses reports a count for each one. */
    public function test_a_day_reports_a_count_per_status(): void
    {
        $userId = $this->user()->id;

        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
        foreach (['accept', 'accept', 'denied', 'proccess'] as $urutan => $status) {
            $checkpoint->items()->create([
                'pekerjaan_cp_id' => '1',
                'tanggal' => '2026-10-08',
                'approve_status' => $status,
                'urutan' => $urutan,
            ]);
        }

        $calendar = app(CheckPointCalendarService::class)
            ->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));

        $day = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-08');

        $this->assertSame(2, $day['counts']['accept']);
        $this->assertSame(1, $day['counts']['denied']);
        $this->assertSame(1, $day['counts']['process']);
        $this->assertSame(4, $day['total']);
        $this->assertTrue($day['accepted']);
        $this->assertTrue($day['rejected']);
    }

    /** Counts from several batches on the same day are merged, not overwritten. */
    public function test_counts_merge_across_batches_on_the_same_day(): void
    {
        $userId = $this->user()->id;

        $first = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
        $first->items()->create([
            'pekerjaan_cp_id' => '1',
            'tanggal' => '2026-10-09',
            'approve_status' => 'accept',
            'urutan' => 0,
        ]);

        $second = CheckPoint::create(['user_id' => $userId, 'type_check' => 'rencana']);
        $second->items()->create([
            'pekerjaan_cp_id' => '2',
            'tanggal' => '2026-10-09',
            'approve_status' => 'denied',
            'urutan' => 0,
        ]);

        $calendar = app(CheckPointCalendarService::class)
            ->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));

        $day = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-09');

        $this->assertSame(1, $day['counts']['accept']);
        $this->assertSame(1, $day['counts']['denied']);
        $this->assertSame(2, $day['total']);
    }

    /** The type filter (used by Direksi) only counts matching batches. */
    public function test_type_filter_scopes_the_calendar(): void
    {
        $userId = $this->user()->id;

        $worked = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
        $worked->items()->create([
            'pekerjaan_cp_id' => '1',
            'tanggal' => '2026-10-10',
            'approve_status' => 'accept',
            'urutan' => 0,
        ]);

        $planned = CheckPoint::create(['user_id' => $userId, 'type_check' => 'rencana']);
        $planned->items()->create([
            'pekerjaan_cp_id' => '2',
            'tanggal' => '2026-10-10',
            'approve_status' => 'proccess',
            'urutan' => 0,
        ]);

        $calendar = app(CheckPointCalendarService::class)
            ->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'), 'dikerjakan');

        $day = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-10');

        $this->assertSame(1, $day['counts']['accept']);
        $this->assertSame(0, $day['counts']['process']);
        $this->assertSame(1, $day['total']);
    }

    /**
     * statusCounts is the shared helper Direksi reuses; the legacy misspelling
     * `proccess` (and anything unknown) must fall into the pending bucket.
     */
    public function test_status_counts_bucket_unknown_values_as_pending(): void
    {
        $checkpoint = CheckPoint::create(['user_id' => $this->user()->id, 'type_check' => 'dikerjakan']);
        foreach (['accept', 'denied', 'proccess', 'something-else'] as $urutan => $status) {
            $checkpoint->items()->create([
                'pekerjaan_cp_id' => '1',
                'approve_status' => $status,
                'urutan' => $urutan,
            ]);
        }

        $counts = CheckPointCalendarService::statusCounts($checkpoint->items()->get());

        $this->assertSame(1, $counts['accept']);
        $this->assertSame(1, $counts['denied']);
        $this->assertSame(2, $counts['process']);
    }
}
