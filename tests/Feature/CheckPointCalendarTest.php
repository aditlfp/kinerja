<?php

namespace Tests\Feature;

use App\Models\CheckPoint;
use App\Models\User;
use App\Services\CheckPointCalendarService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CheckPointCalendarTest extends TestCase
{
    private bool $addedDeletedAtColumn = false;
    private bool $createdItemsTable = false;
    private bool $createdParticipantsTable = false;
    private bool $createdReportSettingsTable = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->softDeletes());
            $this->addedDeletedAtColumn = true;
        }

        if (! Schema::hasTable('check_point_items')) {
            Schema::create('check_point_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('check_point_id');
                $table->string('pekerjaan_cp_id')->nullable();
                $table->text('input_manual')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('approve_status')->default('proccess');
                $table->text('note')->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();
                $table->index('check_point_id');
            });
            $this->createdItemsTable = true;
        }

        if (! Schema::hasTable('check_point_participants')) {
            Schema::create('check_point_participants', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('check_point_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->index('user_id');
                $table->unique(['check_point_id', 'user_id']);
            });
            $this->createdParticipantsTable = true;
        }

        if (! Schema::hasTable('check_point_report_settings')) {
            Schema::create('check_point_report_settings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedTinyInteger('last_day')->default(6);
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->timestamps();
            });
            $this->createdReportSettingsTable = true;
        }

        $this->withoutMiddleware();
        CheckPoint::query()->delete();
        User::query()->where('email', 'like', 'calendar-test-%')->delete();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        if ($this->createdParticipantsTable) {
            Schema::dropIfExists('check_point_participants');
        }
        if ($this->createdItemsTable) {
            Schema::dropIfExists('check_point_items');
        }
        if ($this->createdReportSettingsTable) {
            Schema::dropIfExists('check_point_report_settings');
        }
        if ($this->addedDeletedAtColumn) {
            Schema::table('users', fn (Blueprint $table) => $table->dropSoftDeletes());
        }

        parent::tearDown();
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

    public function test_a_day_with_an_accepted_item_is_flagged_accepted(): void
    {
        $userId = $this->user()->id;
        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
        $checkpoint->items()->create(['pekerjaan_cp_id' => '1', 'tanggal' => '2026-10-05', 'approve_status' => 'accept', 'urutan' => 0]);

        $day = app(CheckPointCalendarService::class)->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'))->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-05');

        $this->assertTrue($day['hasData']);
        $this->assertTrue($day['accepted']);
        $this->assertFalse($day['rejected']);
        $this->assertSame($checkpoint->id, $day['recordId']);
    }

    public function test_a_day_with_a_denied_item_is_flagged_rejected(): void
    {
        $userId = $this->user()->id;
        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
        $checkpoint->items()->create(['pekerjaan_cp_id' => '1', 'tanggal' => '2026-10-06', 'approve_status' => 'denied', 'urutan' => 0]);

        $day = app(CheckPointCalendarService::class)->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'))->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-06');

        $this->assertTrue($day['hasData']);
        $this->assertFalse($day['accepted']);
        $this->assertTrue($day['rejected']);
    }

    public function test_a_batch_without_items_uses_the_created_date(): void
    {
        $userId = $this->user()->id;
        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'harian']);
        $checkpoint->forceFill([
            'created_at' => Carbon::parse('2026-10-15'),
            'updated_at' => Carbon::parse('2026-10-15'),
        ])->saveQuietly();

        $day = app(CheckPointCalendarService::class)->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'))->firstWhere(fn ($row) => $row['date']->toDateString() === $checkpoint->created_at->toDateString());

        $this->assertNotNull($day);
        $this->assertTrue($day['hasData']);
        $this->assertSame($checkpoint->id, $day['recordId']);
    }

    public function test_calendar_ignores_items_outside_requested_month(): void
    {
        $userId = $this->user()->id;
        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'harian']);
        $checkpoint->items()->createMany([
            ['tanggal' => '2026-09-30', 'approve_status' => 'accept'],
            ['tanggal' => '2026-10-10', 'approve_status' => 'denied'],
        ]);

        $calendar = app(CheckPointCalendarService::class)->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $day = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-10');

        $this->assertTrue($day['rejected']);
        $this->assertFalse($day['accepted']);
    }

    public function test_calendar_limits_parent_batches_to_requested_month_or_matching_item_dates(): void
    {
        $userId = $this->user()->id;
        $start = Carbon::parse('2026-10-01');
        $end = Carbon::parse('2026-10-31');
        $oldWithOctoberItem = CheckPoint::create(['user_id' => $userId, 'type_check' => 'harian']);
        $oldWithOctoberItem->forceFill(['created_at' => Carbon::parse('2026-09-01')])->saveQuietly();
        $oldWithOctoberItem->items()->create(['tanggal' => '2026-10-12', 'approve_status' => 'accept']);
        $oldWithoutOctoberItem = CheckPoint::create(['user_id' => $userId, 'type_check' => 'harian']);
        $oldWithoutOctoberItem->forceFill(['created_at' => Carbon::parse('2026-09-02')])->saveQuietly();
        $oldWithoutOctoberItem->items()->create(['tanggal' => '2026-09-12', 'approve_status' => 'denied']);

        $calendar = app(CheckPointCalendarService::class)->forMonth($userId, $start, $end);
        $octoberDay = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-12');
        $septemberDay = $calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-09-12');

        $this->assertSame($oldWithOctoberItem->id, $octoberDay['recordId']);
        $this->assertTrue($octoberDay['accepted']);
        $this->assertNull($septemberDay);
    }

    public function test_days_without_data_are_not_flagged(): void
    {
        $calendar = app(CheckPointCalendarService::class)->forMonth($this->user()->id, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $this->assertSame(31, $calendar->count());
        $this->assertTrue($calendar->every(fn ($row) => $row['hasData'] === false));
    }

    public function test_forget_does_not_use_a_stale_cache_for_calendar_maps(): void
    {
        $service = app(CheckPointCalendarService::class);
        $userId = $this->user()->id;
        $service->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $this->assertNull(Cache::get('checkpoint-calendar:' . $userId));
        $service->forget($userId);
        $this->assertNull(Cache::get('checkpoint-calendar:' . $userId));
    }

    public function test_building_the_calendar_does_not_trigger_n_plus_one(): void
    {
        $userId = $this->user()->id;
        for ($i = 0; $i < 10; $i++) {
            $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
            $checkpoint->items()->create(['pekerjaan_cp_id' => '1', 'tanggal' => '2026-10-' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), 'approve_status' => 'proccess', 'urutan' => 0]);
        }

        DB::enableQueryLog();
        app(CheckPointCalendarService::class)->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(2, $queryCount);
    }

    public function test_history_and_index_share_the_same_calendar_shape(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $checkpoint = CheckPoint::create(['user_id' => $user->id, 'type_check' => 'dikerjakan']);
        $checkpoint->items()->create(['pekerjaan_cp_id' => '1', 'tanggal' => '2026-10-07', 'approve_status' => 'accept', 'urutan' => 0]);

        $index = $this->get(route('checkpoint-user.index', ['month' => '2026-10']));
        $history = $this->get(route('checkpoint-user.history', ['month' => '2026-10']));
        $index->assertOk();
        $history->assertOk();

        $expectedKeys = ['date', 'hasData', 'recordId', 'accepted', 'rejected', 'hasNote', 'total', 'counts'];
        $this->assertSame($expectedKeys, array_keys($index->viewData('calendar')->first()));
        $this->assertSame($expectedKeys, array_keys($history->viewData('calendar')->first()));
    }

    public function test_a_day_reports_a_count_per_status(): void
    {
        $userId = $this->user()->id;
        $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
        foreach (['accept', 'accept', 'denied', 'proccess'] as $urutan => $status) {
            $checkpoint->items()->create(['pekerjaan_cp_id' => '1', 'tanggal' => '2026-10-08', 'approve_status' => $status, 'urutan' => $urutan]);
        }

        $day = app(CheckPointCalendarService::class)->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'))->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-08');
        $this->assertSame(2, $day['counts']['accept']);
        $this->assertSame(1, $day['counts']['denied']);
        $this->assertSame(1, $day['counts']['process']);
        $this->assertSame(4, $day['total']);
        $this->assertTrue($day['accepted']);
        $this->assertTrue($day['rejected']);
    }

    public function test_counts_merge_across_batches_on_the_same_day(): void
    {
        $userId = $this->user()->id;
        foreach (['accept', 'denied'] as $status) {
            $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => 'dikerjakan']);
            $checkpoint->items()->create(['tanggal' => '2026-10-09', 'approve_status' => $status]);
        }

        $day = app(CheckPointCalendarService::class)->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'))->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-09');
        $this->assertSame(1, $day['counts']['accept']);
        $this->assertSame(1, $day['counts']['denied']);
    }

    public function test_type_filter_scopes_the_calendar(): void
    {
        $userId = $this->user()->id;
        foreach (['rencana', 'dikerjakan'] as $type) {
            $checkpoint = CheckPoint::create(['user_id' => $userId, 'type_check' => $type]);
            $checkpoint->items()->create(['tanggal' => '2026-10-10', 'approve_status' => 'accept']);
        }

        $calendar = app(CheckPointCalendarService::class)->forMonth($userId, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'), 'rencana');
        $this->assertTrue($calendar->firstWhere(fn ($row) => $row['date']->toDateString() === '2026-10-10')['hasData']);
    }

    public function test_status_counts_bucket_unknown_values_as_pending(): void
    {
        $item = new \stdClass();
        $item->approve_status = 'unknown';
        $this->assertSame(['accept' => 0, 'denied' => 0, 'process' => 1], CheckPointCalendarService::statusCounts(collect([$item])));
    }
}
